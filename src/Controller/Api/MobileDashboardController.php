<?php

namespace App\Controller\Api;

use App\Entity\Auftrag;
use App\Entity\Firma;
use App\Entity\KundeCreditTransaction;
use App\Entity\Portal;
use App\Entity\User;
use App\Repository\AuftragRepository;
use App\Repository\KundeCreditTransactionRepository;
use App\Repository\KundeRepository;
use App\Repository\PortalRepository;
use App\Service\Api\MobileApiAuthService;
use App\Service\Api\MobileApiPayloadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/mobile')]
final class MobileDashboardController extends AbstractController
{
    use ApiControllerTrait;
    use MobileAuthTrait;

    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly KundeRepository $kundeRepository,
        private readonly PortalRepository $portalRepository,
        private readonly KundeCreditTransactionRepository $kundeCreditTransactionRepository,
        private readonly MobileApiAuthService $authService,
        private readonly MobileApiPayloadService $payloadService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/dashboard', name: 'api_mobile_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $portalKey = trim((string) $request->query->get('portalKey', ''));
        $portal = '' !== $portalKey ? $this->portalRepository->findOneBy(['portalKey' => strtolower($portalKey)]) : null;

        $allPortals = $this->portalRepository->findBy(
            ['active' => true, 'portalType' => Portal::TYPE_CUSTOMER_PORTAL],
            ['sortOrder' => 'ASC', 'id' => 'ASC']
        );

        $now = new \DateTimeImmutable();
        $todayStart = $now->setTime(0, 0);
        $weekStart = $now->modify('-6 days')->setTime(0, 0);

        $totalOrders = (int) $this->auftragRepository->count([]);
        $totalCustomers = (int) $this->kundeRepository->count([]);

        $todayOrders = (int) $this->auftragRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.createdAt >= :todayStart')
            ->setParameter('todayStart', $todayStart)
            ->getQuery()
            ->getSingleScalarResult();

        $weekOrders = (int) $this->auftragRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.createdAt >= :weekStart')
            ->setParameter('weekStart', $weekStart)
            ->getQuery()
            ->getSingleScalarResult();

        $open = (int) $this->auftragRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.status IN (:openStatuses)')
            ->setParameter('openStatuses', [Auftrag::STATUS_RECEIVED, Auftrag::STATUS_IN_PROGRESS, Auftrag::STATUS_PARKED])
            ->getQuery()
            ->getSingleScalarResult();

        $statusRows = $this->auftragRepository->createQueryBuilder('a')
            ->select('a.status AS status', 'COUNT(a.id) AS cnt')
            ->groupBy('a.status')
            ->getQuery()
            ->getResult();

        $statusBreakdown = [];
        foreach ($statusRows as $row) {
            $statusBreakdown[(string) $row['status']] = (int) $row['cnt'];
        }

        $recentOrders = $this->auftragRepository->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'portal' => [
                'name' => $portal?->getName() ?? '',
                'firma' => $portal?->getFirma()?->getName(),
                'kuerzel' => $portal?->getFirma()?->getKuerzel(),
                'logoUrl' => $portal?->getResolvedDesign()['logoUrl'] ?? null,
                'firmaLogoUrl' => $this->buildFirmaLogoUrl($portal?->getFirma()),
            ],
            'portals' => array_map(
                fn (Portal $p): array => [
                    'id' => $p->getId(),
                    'portalKey' => $p->getPortalKey(),
                    'name' => $p->getName(),
                    'firma' => $p->getFirma()?->getName(),
                    'kuerzel' => $p->getFirma()?->getKuerzel(),
                    'logoUrl' => $p->getResolvedDesign()['logoUrl'] ?? null,
                    'firmaLogoUrl' => $this->buildFirmaLogoUrl($p->getFirma()),
                ],
                $allPortals
            ),
            'kpis' => [
                'totalOrders' => $totalOrders,
                'totalCustomers' => $totalCustomers,
                'todayOrders' => $todayOrders,
                'weekOrders' => $weekOrders,
                'open' => $open,
            ],
            'statusBreakdown' => $statusBreakdown,
            'recentOrders' => array_map(
                fn (Auftrag $auftrag): array => $this->payloadService->normalizeOrder($auftrag),
                $recentOrders
            ),
        ]);
    }

    #[Route('/portal/details', name: 'api_mobile_portal_details', methods: ['GET'])]
    public function portalDetails(Request $request): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $portal = $this->resolvePortalFromRequest($request);
        if (!$portal instanceof Portal) {
            return $this->errorResponse('Portal nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->buildPortalDetails($portal));
    }

    #[Route('/portal/settings', name: 'api_mobile_portal_settings', methods: ['PATCH'])]
    public function portalSettings(Request $request): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $portal = $this->resolvePortalFromRequest($request);
        if (!$portal instanceof Portal) {
            return $this->errorResponse('Portal nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('active', $payload)) {
            if (!is_bool($payload['active'])) {
                return $this->errorResponse('Feld "active" muss ein Boolean sein.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $portal->setActive($payload['active']);
        }

        if (array_key_exists('registrationsOpen', $payload)) {
            if (!is_bool($payload['registrationsOpen'])) {
                return $this->errorResponse('Feld "registrationsOpen" muss ein Boolean sein.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $portal->setCustomerRegistrationEnabled($payload['registrationsOpen']);
        }

        if (array_key_exists('maintenanceMode', $payload)) {
            if (!is_bool($payload['maintenanceMode'])) {
                return $this->errorResponse('Feld "maintenanceMode" muss ein Boolean sein.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $portal->setMaintenanceMode($payload['maintenanceMode']);
        }

        if (array_key_exists('creditPrice', $payload)) {
            if (null !== $payload['creditPrice'] && !is_numeric($payload['creditPrice'])) {
                return $this->errorResponse('Feld "creditPrice" muss numerisch sein.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $portal->setCreditPrice(null !== $payload['creditPrice'] ? (string) $payload['creditPrice'] : null);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Portal aktualisiert.',
            'portal' => $this->buildPortalDetails($portal),
        ]);
    }

    private function resolvePortalFromRequest(Request $request): ?Portal
    {
        $portalId = $request->query->get('id') ?? $request->query->get('portalId');
        if (null !== $portalId && ctype_digit((string) $portalId)) {
            $portal = $this->portalRepository->find((int) $portalId);
            if ($portal instanceof Portal) {
                return $portal;
            }
        }

        $portalKey = trim((string) $request->query->get('portalKey', ''));
        if ('' !== $portalKey) {
            return $this->portalRepository->findOneBy(['portalKey' => strtolower($portalKey)]);
        }

        return null;
    }

    /**
     * @return array<string, mixed> PortalDetails
     */
    private function buildPortalDetails(Portal $portal): array
    {
        $firma = $portal->getFirma();

        $totalCustomers = 0;
        $totalOrders = 0;
        $openOrders = 0;
        $creditsInCirculation = 0;
        $revenue30d = 0.0;

        if ($firma !== null) {
            $totalCustomers = (int) $this->kundeRepository->createQueryBuilder('k')
                ->select('COUNT(k.id)')
                ->andWhere('k.firma = :firma')
                ->setParameter('firma', $firma)
                ->getQuery()
                ->getSingleScalarResult();

            $totalOrders = (int) $this->auftragRepository->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->join('a.kunde', 'k')
                ->andWhere('k.firma = :firma')
                ->setParameter('firma', $firma)
                ->getQuery()
                ->getSingleScalarResult();

            $openOrders = (int) $this->auftragRepository->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->join('a.kunde', 'k')
                ->andWhere('k.firma = :firma')
                ->andWhere('a.status IN (:openStatuses)')
                ->setParameter('firma', $firma)
                ->setParameter('openStatuses', [Auftrag::STATUS_RECEIVED, Auftrag::STATUS_IN_PROGRESS, Auftrag::STATUS_PARKED])
                ->getQuery()
                ->getSingleScalarResult();

            $creditsInCirculation = (int) ($this->kundeRepository->createQueryBuilder('k')
                ->select('COALESCE(SUM(k.credits), 0)')
                ->andWhere('k.firma = :firma')
                ->setParameter('firma', $firma)
                ->getQuery()
                ->getSingleScalarResult());

            $since = (new \DateTimeImmutable())->modify('-30 days');
            $revenue30d = (float) ($this->kundeCreditTransactionRepository->createQueryBuilder('t')
                ->select('COALESCE(SUM(t.priceGross), 0)')
                ->join('t.kunde', 'k')
                ->andWhere('k.firma = :firma')
                ->andWhere('t.transactionType = :type')
                ->andWhere('t.createdAt >= :since')
                ->setParameter('firma', $firma)
                ->setParameter('type', KundeCreditTransaction::TYPE_CUSTOMER_TOPUP)
                ->setParameter('since', $since)
                ->getQuery()
                ->getSingleScalarResult());
        }

        return [
            'id' => $portal->getId(),
            'portalKey' => $portal->getPortalKey(),
            'name' => $portal->getName(),
            'firma' => $firma?->getName(),
            'kuerzel' => $firma?->getKuerzel(),
            'logoUrl' => $portal->getResolvedDesign()['logoUrl'] ?? null,
            'firmaLogoUrl' => $this->buildFirmaLogoUrl($firma),
            'url' => $portal->getPortalUrl(),
            'active' => $portal->isActive(),
            'registrationsOpen' => $portal->isCustomerRegistrationEnabled(),
            'maintenanceMode' => $portal->isMaintenanceMode(),
            'creditPrice' => $portal->getCreditPrice(),
            'openOrders' => $openOrders,
            'totalCustomers' => $totalCustomers,
            'totalOrders' => $totalOrders,
            'creditsInCirculation' => $creditsInCirculation,
            'revenue30d' => $revenue30d,
        ];
    }

    private function buildFirmaLogoUrl(?Firma $firma): ?string
    {
        $logo = $firma?->getLogo();

        return null !== $logo && '' !== $logo ? '/uploads/firmen/' . $logo : null;
    }
}
