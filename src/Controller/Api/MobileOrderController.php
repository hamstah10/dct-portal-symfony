<?php

namespace App\Controller\Api;

use App\Entity\Auftrag;
use App\Entity\User;
use App\Repository\AuftragRepository;
use App\Service\Api\MobileApiAuthService;
use App\Service\Api\MobileApiPayloadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/mobile/orders')]
final class MobileOrderController extends AbstractController
{
    use ApiControllerTrait;
    use MobileAuthTrait;

    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly MobileApiAuthService $authService,
        private readonly MobileApiPayloadService $payloadService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_mobile_orders_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $status = trim((string) $request->query->get('status', ''));
        $search = trim((string) $request->query->get('search', ''));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $offset = max(0, (int) $request->query->get('offset', 0));

        $qb = $this->auftragRepository->createQueryBuilder('a');

        if ('' !== $status) {
            $qb->andWhere('a.status = :status')->setParameter('status', $status);
        }

        if ('' !== $search) {
            $qb->andWhere('LOWER(a.customerEmail) LIKE :search OR LOWER(a.fileName) LIKE :search OR LOWER(a.vehicleDisplay) LIKE :search OR LOWER(a.manufacturer) LIKE :search OR LOWER(a.model) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        $total = (int) (clone $qb)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        $orders = $qb->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'orders' => array_map(fn (Auftrag $auftrag): array => $this->payloadService->normalizeOrder($auftrag), $orders),
            'total' => $total,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'api_mobile_orders_show', methods: ['GET'])]
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $auftrag = $this->auftragRepository->find($id);
        if (!$auftrag instanceof Auftrag) {
            return $this->errorResponse('Auftrag nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->payloadService->normalizeOrder($auftrag));
    }

    #[Route('/{id<\d+>}/status', name: 'api_mobile_orders_status', methods: ['PATCH'])]
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $auftrag = $this->auftragRepository->find($id);
        if (!$auftrag instanceof Auftrag) {
            return $this->errorResponse('Auftrag nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $status = trim((string) ($payload['status'] ?? ''));
        if (!\in_array($status, Auftrag::getStatusChoices(), true)) {
            return $this->errorResponse('Ungueltiger Status.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $auftrag->setStatus($status);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Status aktualisiert.',
            'order' => $this->payloadService->normalizeOrder($auftrag),
        ]);
    }
}
