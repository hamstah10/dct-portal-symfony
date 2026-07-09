<?php

namespace App\Controller\Api;

use App\Entity\Kunde;
use App\Entity\User;
use App\Repository\KundeRepository;
use App\Service\Api\MobileApiAuthService;
use App\Service\Api\MobileApiPayloadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/mobile/customers')]
final class MobileCustomerController extends AbstractController
{
    use ApiControllerTrait;
    use MobileAuthTrait;

    public function __construct(
        private readonly KundeRepository $kundeRepository,
        private readonly MobileApiAuthService $authService,
        private readonly MobileApiPayloadService $payloadService,
    ) {
    }

    #[Route('', name: 'api_mobile_customers_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $search = trim((string) $request->query->get('search', ''));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $offset = max(0, (int) $request->query->get('offset', 0));

        $qb = $this->kundeRepository->createQueryBuilder('k');

        if ('' !== $search) {
            $qb->andWhere('LOWER(k.vorname) LIKE :search OR LOWER(k.nachname) LIKE :search OR LOWER(k.email) LIKE :search OR LOWER(k.firmenname) LIKE :search OR LOWER(k.kundennummer) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        $total = (int) (clone $qb)->select('COUNT(k.id)')->getQuery()->getSingleScalarResult();

        $customers = $qb->orderBy('k.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'customers' => array_map(fn (Kunde $kunde): array => $this->payloadService->normalizeCustomer($kunde), $customers),
            'total' => $total,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'api_mobile_customers_show', methods: ['GET'])]
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $kunde = $this->kundeRepository->find($id);
        if (!$kunde instanceof Kunde) {
            return $this->errorResponse('Kunde nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->payloadService->normalizeCustomer($kunde));
    }
}
