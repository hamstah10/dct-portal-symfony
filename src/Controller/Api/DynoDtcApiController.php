<?php

namespace App\Controller\Api;

use App\Entity\DynoDtc;
use App\Repository\DynoDtcRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dtcs')]
final class DynoDtcApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly DynoDtcRepository $dtcRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_dyno_dtcs_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $dtcs = $this->dtcRepository->findBy([], ['createdAt' => 'DESC']);

        return new JsonResponse([
            'data' => array_map(fn (DynoDtc $dtc): array => $this->normalizeDtc($dtc), $dtcs),
        ]);
    }

    #[Route('/batch', name: 'api_dyno_dtcs_batch', methods: ['POST'])]
    public function batch(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            return $this->errorResponse('Ungueltiges JSON.', Response::HTTP_BAD_REQUEST);
        }

        $items = $payload;
        if (array_key_exists('items', $payload)) {
            $items = $payload['items'];
        }

        if (!is_array($items)) {
            return $this->errorResponse('items muss ein Array sein.', Response::HTTP_BAD_REQUEST);
        }

        $created = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $code = trim((string) ($item['code'] ?? $item['dtc'] ?? ''));
            if ('' === $code) {
                continue;
            }

            $dtc = new DynoDtc();
            $dtc->setCode($code);
            $dtc->setDescription(isset($item['description']) ? (string) $item['description'] : null);
            $dtc->setPayload($item);
            $this->entityManager->persist($dtc);
            $created[] = $dtc;
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'created' => count($created),
            'data' => array_map(fn (DynoDtc $dtc): array => $this->normalizeDtc($dtc), $created),
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'api_dyno_dtcs_delete', methods: ['DELETE'])]
    public function deleteAll(Request $request): Response
    {
        $dtcs = $this->dtcRepository->findAll();
        foreach ($dtcs as $dtc) {
            $this->entityManager->remove($dtc);
        }
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function normalizeDtc(DynoDtc $dtc): array
    {
        return [
            'id' => $dtc->getId(),
            'code' => $dtc->getCode(),
            'description' => $dtc->getDescription(),
            'created_at' => $dtc->getCreatedAt()->format(DATE_ATOM),
            'payload' => $dtc->getPayload(),
        ];
    }

    // /api/dtcs endpoints are intentionally public (no token required).
}
