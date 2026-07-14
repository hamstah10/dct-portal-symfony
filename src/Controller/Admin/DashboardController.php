<?php

namespace App\Controller\Admin;

use App\Entity\CustomerChatMessage;
use App\Entity\Ecu;
use App\Entity\EcuOption;
use App\Entity\Gearbox;
use App\Entity\GearboxType;
use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use App\Entity\Auftrag;
use App\Entity\EmailKonto;
use App\Entity\EmailLabel;
use App\Entity\MediaAsset;
use App\Entity\MediaFolder;
use App\Entity\SupportTicket;
use App\Entity\SupportEmailMessage;
use App\Entity\SupportTicketMessage;
use App\Entity\VehicleBrand;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use App\Entity\Zugangsdaten;
use App\Entity\ShopCategory;
use App\Entity\ShopOrder;
use App\Entity\ShopProduct;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardTool;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardCombination;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardPackage;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardLead;

use Tfd\PartnerWizardBundle\Controller\Admin\PartnerWizardClientCrudController;
use Tfd\PartnerWizardBundle\Controller\Admin\PartnerWizardToolCrudController;
use Tfd\PartnerWizardBundle\Controller\Admin\PartnerWizardCombinationCrudController;
use Tfd\PartnerWizardBundle\Controller\Admin\PartnerWizardPackageCrudController;
use Tfd\PartnerWizardBundle\Controller\Admin\PartnerWizardLeadCrudController;
use Tfd\PartnerWizardBundle\Controller\Admin\PartnerWizardQuestionCrudController;
use Tfd\PartnerWizardBundle\Controller\Admin\PartnerWizardQuestionOptionCrudController;
use Tfd\PartnerWizardBundle\Controller\Admin\PartnerWizardStepCrudController;
use Tuningfux\InstagramBundle\Entity\InstagramAccount;
use App\Repository\CustomerChatMessageRepository;
use App\Service\SupportTicketFileService;
use App\Repository\FirmaRepository;
use App\Repository\AboRepository;
use App\Repository\KundeRepository;
use App\Repository\EmailKontoRepository;
use App\Repository\SoftwareRepository;
use App\Repository\SupportEmailMessageRepository;
use App\Repository\SupportTicketMessageRepository;
use App\Repository\SupportTicketRepository;
use App\Repository\VehicleUsageEventRepository;
use App\Repository\ZahlungsartRepository;
use App\Repository\AuftragRepository;
use App\Service\AdminPageAccessService;
use App\Service\AdminPageRegistry;
use App\Service\CustomerMapBuilder;
use App\Service\SupportEmailFetchService;
use App\Service\SupportEmailSendService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\ActionGroup;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\ColorScheme;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Controller\Admin\WatermarkPresetCrudController;
use App\Controller\Admin\TextbausteinCrudController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly KundeRepository $kundeRepository,
        private readonly FirmaRepository $firmaRepository,
        private readonly SoftwareRepository $softwareRepository,
        private readonly AboRepository $aboRepository,
        private readonly ZahlungsartRepository $zahlungsartRepository,
        private readonly AuftragRepository $auftragRepository,
        private readonly CustomerChatMessageRepository $chatMessageRepository,
        private readonly SupportTicketRepository $supportTicketRepository,
        private readonly SupportEmailMessageRepository $supportEmailMessageRepository,
        private readonly SupportTicketMessageRepository $supportTicketMessageRepository,
        private readonly EmailKontoRepository $emailKontoRepository,
        private readonly VehicleUsageEventRepository $vehicleUsageEventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CustomerMapBuilder $customerMapBuilder,
        private readonly Connection $connection,
        private readonly Packages $packages,
        private readonly SupportTicketFileService $fileService,
        private readonly SupportEmailFetchService $supportEmailFetchService,
        private readonly SupportEmailSendService $supportEmailSendService,
        private readonly AdminPageAccessService $pageAccessService,
        private readonly ?Stopwatch $stopwatch = null,
    ) {
    }

    #[Route('/admin/', name: 'admin_trailing_slash_redirect', methods: ['GET', 'HEAD'])]
    public function redirectTrailingSlash(Request $request): RedirectResponse
    {
        $targetUrl = $this->generateUrl('admin');
        $queryString = $request->getQueryString();
        if (null !== $queryString && '' !== $queryString) {
            $targetUrl .= '?' . $queryString;
        }

        return new RedirectResponse($targetUrl, Response::HTTP_MOVED_PERMANENTLY);
    }

    public function index(): Response
    {
        $this->profileStart('easyadmin.dashboard.index');
        $this->profileStart('easyadmin.dashboard.counts_and_orders');

        $kundenCount = $this->kundeRepository->count([]);
        $softwareCount = $this->softwareRepository->count([]);
        $aboCount = $this->aboRepository->count([]);
        $auftragCount = $this->auftragRepository->count([]);

        $orderCountsByStatus = [
            Auftrag::STATUS_RECEIVED => $this->auftragRepository->count(['status' => Auftrag::STATUS_RECEIVED]),
            Auftrag::STATUS_IN_PROGRESS => $this->auftragRepository->count(['status' => Auftrag::STATUS_IN_PROGRESS]),
            Auftrag::STATUS_PARKED => $this->auftragRepository->count(['status' => Auftrag::STATUS_PARKED]),
            Auftrag::STATUS_COMPLETED => $this->auftragRepository->count(['status' => Auftrag::STATUS_COMPLETED]),
            Auftrag::STATUS_REJECTED => $this->auftragRepository->count(['status' => Auftrag::STATUS_REJECTED]),
        ];

        $openOrderCount = ($orderCountsByStatus[Auftrag::STATUS_RECEIVED] ?? 0) + ($orderCountsByStatus[Auftrag::STATUS_IN_PROGRESS] ?? 0) + ($orderCountsByStatus[Auftrag::STATUS_PARKED] ?? 0);
        $completionRate = $auftragCount > 0
            ? (int) round((($orderCountsByStatus[Auftrag::STATUS_COMPLETED] ?? 0) / $auftragCount) * 100)
            : 0;

        $latestOrders = $this->entityManager->createQueryBuilder()
            ->select('a', 'k', 'f')
            ->from(Auftrag::class, 'a')
            ->leftJoin('a.kunde', 'k')
            ->leftJoin('k.firma', 'f')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();

        $orderStatusItems = [];
        foreach (Auftrag::getStatusChoices() as $label => $status) {
            $count = (int) ($orderCountsByStatus[$status] ?? 0);
            $percentage = $auftragCount > 0 ? (int) round(($count / $auftragCount) * 100) : 0;
            $tone = match ($status) {
                Auftrag::STATUS_RECEIVED => 'info',
                Auftrag::STATUS_IN_PROGRESS => 'warning',
                Auftrag::STATUS_PARKED => 'secondary',
                Auftrag::STATUS_COMPLETED => 'success',
                Auftrag::STATUS_REJECTED => 'danger',
                default => 'secondary',
            };

            $orderStatusItems[] = [
                'label' => $label,
                'status' => $status,
                'count' => $count,
                'percentage' => $percentage,
                'tone' => $tone,
            ];
        }

        $this->profileStop('easyadmin.dashboard.counts_and_orders');
        $this->profileStart('easyadmin.dashboard.charts_base');

        $overviewChart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $overviewChart->setData([
            'labels' => ['Kunden', 'Software', 'Abos', 'Aufträge'],
            'datasets' => [[
                'data' => [$kundenCount, $softwareCount, $aboCount, $auftragCount],
                'backgroundColor' => ['#bbcf4e', '#198754', '#bbcf4e', '#111931'],
                'borderWidth' => 1,
            ]],
        ]);
        $overviewChart->setOptions([
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
            'maintainAspectRatio' => false,
        ]);

        $statusRows = $this->aboRepository->createQueryBuilder('a')
            ->select('a.status AS status, COUNT(a.id) AS total')
            ->groupBy('a.status')
            ->getQuery()
            ->getArrayResult();

        $statusLabels = [];
        $statusValues = [];
        foreach ($statusRows as $row) {
            $statusLabels[] = (string) $row['status'];
            $statusValues[] = (int) $row['total'];
        }

        if ([] === $statusLabels) {
            $statusLabels = ['Keine Daten'];
            $statusValues = [0];
        }

        $statusChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $statusChart->setData([
            'labels' => $statusLabels,
            'datasets' => [[
                'label' => 'Abos pro Status',
                'data' => $statusValues,
                'backgroundColor' => '#bbcf4e',
            ]],
        ]);
        $statusChart->setOptions([
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
            'maintainAspectRatio' => false,
        ]);

        $fromDate = (new \DateTimeImmutable('first day of this month'))->modify('-11 months')->setTime(0, 0);
        $months = [];
        $monthLabels = [];
        $monthStarts = [];
        $monthEnds = [];
        $cursor = $fromDate;
        for ($i = 0; $i < 12; ++$i) {
            $key = $cursor->format('Y-m');
            $months[] = $key;
            $monthLabels[] = $cursor->format('m/Y');
            $monthStarts[$key] = $cursor;
            $monthEnds[$key] = $cursor->modify('last day of this month')->setTime(23, 59, 59);
            $cursor = $cursor->modify('+1 month');
        }

        $aboMonthlyRows = $this->connection->fetchAllAssociative(
            'SELECT DATE_FORMAT(start_datum, "%Y-%m") AS ym, COUNT(*) AS total
             FROM abo
             WHERE start_datum >= :fromDate
             GROUP BY ym
             ORDER BY ym ASC',
            ['fromDate' => $fromDate->format('Y-m-d H:i:s')]
        );
        $aboMonthlyMap = [];
        foreach ($aboMonthlyRows as $row) {
            $aboMonthlyMap[(string) $row['ym']] = (int) $row['total'];
        }
        $aboMonthlyValues = array_map(
            static fn (string $month): int => $aboMonthlyMap[$month] ?? 0,
            $months
        );

        $aboPerMonthChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $aboPerMonthChart->setData([
            'labels' => $monthLabels,
            'datasets' => [[
                'label' => 'Neue Abos',
                'data' => $aboMonthlyValues,
                'borderColor' => '#bbcf4e',
                'backgroundColor' => 'rgba(189, 30, 33, 0.15)',
                'fill' => true,
                'tension' => 0.35,
            ]],
        ]);
        $aboPerMonthChart->setOptions([
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $this->profileStop('easyadmin.dashboard.charts_base');
        $this->profileStart('easyadmin.dashboard.revenue');

        $todayMonthStart = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $todayMonthEnd = $todayMonthStart->modify('last day of this month')->setTime(23, 59, 59);
        $revenueRangeStart = $todayMonthStart->modify('-11 months')->setTime(0, 0);
        $revenueRangeEnd = $todayMonthEnd;

        $revenueMonths = [];
        $revenueMonthLabels = [];
        $revenueMonthStarts = [];
        $revenueMonthEnds = [];
        $revenueCursor = $revenueRangeStart;
        while ($revenueCursor <= $revenueRangeEnd) {
            $revenueKey = $revenueCursor->format('Y-m');
            $revenueMonths[] = $revenueKey;
            $revenueMonthLabels[] = $revenueCursor->format('m/Y');
            $revenueMonthStarts[$revenueKey] = $revenueCursor;
            $revenueMonthEnds[$revenueKey] = $revenueCursor->modify('last day of this month')->setTime(23, 59, 59);
            $revenueCursor = $revenueCursor->modify('+1 month');
        }

        $abosForRevenue = $this->aboRepository->createQueryBuilder('a')
            ->leftJoin('a.preisstaffel', 'ps')
            ->addSelect('ps')
            ->where('a.startDatum <= :revenueRangeEnd')
            ->andWhere('a.endDatum IS NULL OR a.endDatum >= :revenueRangeStart')
            ->setParameter('revenueRangeStart', $revenueRangeStart)
            ->setParameter('revenueRangeEnd', $revenueRangeEnd)
            ->getQuery()
            ->getResult();

        $aboRevenueMap = array_fill_keys($revenueMonths, 0.0);
        foreach ($abosForRevenue as $abo) {
            $preisstaffel = $abo->getPreisstaffel();
            if (null === $preisstaffel) {
                continue;
            }

            if ('jaehrlich' === $abo->getZahlweise()) {
                $monatlicherPreis = null !== $preisstaffel->getPreisJaehrlich()
                    ? (float) $preisstaffel->getPreisJaehrlich()
                    : (float) ($preisstaffel->getPreisMonatlich() ?? 0);
            } else {
                $monatlicherPreis = null !== $preisstaffel->getPreisMonatlich()
                    ? (float) $preisstaffel->getPreisMonatlich()
                    : (float) ($preisstaffel->getPreisJaehrlich() ?? 0);
            }

            $start = $abo->getStartDatum();
            $ende = $abo->getEndDatum();

            foreach ($revenueMonths as $monthKey) {
                $monthStart = $revenueMonthStarts[$monthKey];
                $monthEnd = $revenueMonthEnds[$monthKey];

                if ($start > $monthEnd) {
                    continue;
                }
                if (null !== $ende && $ende < $monthStart) {
                    continue;
                }

                $aboRevenueMap[$monthKey] += $monatlicherPreis;
            }
        }

        $aboRevenueValues = array_map(
            static fn (string $month): float => $aboRevenueMap[$month] ?? 0.0,
            $revenueMonths
        );
        $defaultVatRate = 19.0;
        $aboRevenueGrossValues = array_map(
            static fn (float $value): float => $value * (1 + ($defaultVatRate / 100)),
            $aboRevenueValues
        );

        $creditRevenueNetMap = array_fill_keys($revenueMonths, 0.0);
        $creditRevenueGrossMap = array_fill_keys($revenueMonths, 0.0);
        $creditRows = $this->connection->fetchAllAssociative(
            'SELECT created_at, price_net, price_gross, tax_rate
             FROM kunde_credit_transaction t
             WHERE t.transaction_type = :type
               AND (t.price_net IS NOT NULL OR t.price_gross IS NOT NULL)
               AND t.created_at >= :startDate
               AND t.created_at <= :endDate
               AND NOT EXISTS (
                   SELECT 1 FROM kunde_credit_transaction r WHERE r.reversal_of_id = t.id
               )
             ORDER BY t.created_at ASC',
            [
                'type' => KundeCreditTransaction::TYPE_CUSTOMER_TOPUP,
                'startDate' => $revenueRangeStart->format('Y-m-d H:i:s'),
                'endDate' => $revenueRangeEnd->format('Y-m-d H:i:s'),
            ]
        );

        foreach ($creditRows as $row) {
            $createdAt = new \DateTimeImmutable((string) $row['created_at']);
            $monthKey = $createdAt->format('Y-m');
            if (!array_key_exists($monthKey, $creditRevenueNetMap)) {
                continue;
            }

            $net = isset($row['price_net']) ? (float) $row['price_net'] : 0.0;
            $gross = isset($row['price_gross']) ? (float) $row['price_gross'] : 0.0;
            $taxRate = isset($row['tax_rate']) ? (float) $row['tax_rate'] : $defaultVatRate;
            if ($taxRate < 0) {
                $taxRate = $defaultVatRate;
            }

            if ($net <= 0 && $gross > 0) {
                $divider = 1 + ($taxRate / 100);
                $net = $divider > 0 ? $gross / $divider : $gross;
            }
            if ($gross <= 0 && $net > 0) {
                $gross = $net * (1 + ($taxRate / 100));
            }

            if ($net <= 0 && $gross <= 0) {
                continue;
            }

            $creditRevenueNetMap[$monthKey] += $net;
            $creditRevenueGrossMap[$monthKey] += $gross;
        }

        $creditRevenueValues = array_map(
            static fn (string $month): float => $creditRevenueNetMap[$month] ?? 0.0,
            $revenueMonths
        );
        $creditRevenueGrossValues = array_map(
            static fn (string $month): float => $creditRevenueGrossMap[$month] ?? 0.0,
            $revenueMonths
        );

        $ecommerceRevenueNetMap = array_fill_keys($revenueMonths, 0.0);
        $ecommerceRevenueGrossMap = array_fill_keys($revenueMonths, 0.0);
        $ecommerceRows = $this->connection->fetchAllAssociative(
            'SELECT created_at, total, tax_amount
             FROM tx_tuningtoolshop_domain_model_order
             WHERE deleted = 0 AND hidden = 0
               AND created_at >= :startDate
               AND created_at <= :endDate',
            [
                'startDate' => $revenueRangeStart->getTimestamp(),
                'endDate' => $revenueRangeEnd->getTimestamp(),
            ]
        );

        $ecommerceTimezone = new \DateTimeZone(date_default_timezone_get());
        foreach ($ecommerceRows as $row) {
            $createdAt = (int) ($row['created_at'] ?? 0);
            if ($createdAt <= 0) {
                continue;
            }

            $monthKey = (new \DateTimeImmutable('@' . $createdAt))->setTimezone($ecommerceTimezone)->format('Y-m');
            if (!array_key_exists($monthKey, $ecommerceRevenueNetMap)) {
                continue;
            }

            $total = is_numeric($row['total'] ?? null) ? (float) $row['total'] : 0.0;
            $taxAmount = is_numeric($row['tax_amount'] ?? null) ? (float) $row['tax_amount'] : 0.0;
            if ($total <= 0 && $taxAmount <= 0) {
                continue;
            }

            $net = $total;
            $gross = $total;
            if ($taxAmount > 0) {
                if ($total >= $taxAmount) {
                    $net = $total - $taxAmount;
                    $gross = $total;
                } else {
                    $net = $total;
                    $gross = $total + $taxAmount;
                }
            }

            if ($net <= 0 && $gross <= 0) {
                continue;
            }

            $ecommerceRevenueNetMap[$monthKey] += $net;
            $ecommerceRevenueGrossMap[$monthKey] += $gross;
        }

        $ecommerceRevenueValues = array_map(
            static fn (string $month): float => $ecommerceRevenueNetMap[$month] ?? 0.0,
            $revenueMonths
        );
        $ecommerceRevenueGrossValues = array_map(
            static fn (string $month): float => $ecommerceRevenueGrossMap[$month] ?? 0.0,
            $revenueMonths
        );

        $combinedRevenueValues = [];
        foreach ($revenueMonths as $index => $monthKey) {
            $combinedRevenueValues[] = ($aboRevenueValues[$index] ?? 0.0)
                + ($creditRevenueValues[$index] ?? 0.0)
                + ($ecommerceRevenueValues[$index] ?? 0.0);
        }
        $combinedRevenueGrossValues = [];
        foreach ($revenueMonths as $index => $monthKey) {
            $combinedRevenueGrossValues[] = ($aboRevenueGrossValues[$index] ?? 0.0)
                + ($creditRevenueGrossValues[$index] ?? 0.0)
                + ($ecommerceRevenueGrossValues[$index] ?? 0.0);
        }

        $revenueChartNet = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $revenueChartNet->setData([
            'labels' => $revenueMonthLabels,
            'datasets' => [
                [
                    'label' => 'Abo-Umsatz (EUR)',
                    'data' => $aboRevenueValues,
                    'backgroundColor' => '#20c997',
                    'stack' => 'revenue',
                ],
                [
                    'label' => 'Credit-Umsatz (EUR)',
                    'data' => $creditRevenueValues,
                    'backgroundColor' => '#111931',
                    'stack' => 'revenue',
                ],
                [
                    'label' => 'eCommerce-Umsatz (EUR)',
                    'data' => $ecommerceRevenueValues,
                    'backgroundColor' => '#0d6efd',
                    'stack' => 'revenue',
                ],
                [
                    'type' => 'line',
                    'label' => 'Gesamt (EUR)',
                    'data' => $combinedRevenueValues,
                    'borderColor' => '#bbcf4e',
                    'backgroundColor' => 'rgba(253, 126, 20, 0.15)',
                    'tension' => 0.3,
                    'stack' => 'total',
                ],
            ],
        ]);
        $revenueChartNet->setOptions([
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['beginAtZero' => true, 'stacked' => true],
            ],
            'maintainAspectRatio' => false,
        ]);

        $revenueChartGross = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $revenueChartGross->setData([
            'labels' => $revenueMonthLabels,
            'datasets' => [
                [
                    'label' => 'Abo-Umsatz (Brutto)',
                    'data' => $aboRevenueGrossValues,
                    'backgroundColor' => '#20c997',
                    'stack' => 'revenue',
                ],
                [
                    'label' => 'Credit-Umsatz (Brutto)',
                    'data' => $creditRevenueGrossValues,
                    'backgroundColor' => '#111931',
                    'stack' => 'revenue',
                ],
                [
                    'label' => 'eCommerce-Umsatz (Brutto)',
                    'data' => $ecommerceRevenueGrossValues,
                    'backgroundColor' => '#0d6efd',
                    'stack' => 'revenue',
                ],
                [
                    'type' => 'line',
                    'label' => 'Gesamt (Brutto)',
                    'data' => $combinedRevenueGrossValues,
                    'borderColor' => '#bbcf4e',
                    'backgroundColor' => 'rgba(253, 126, 20, 0.15)',
                    'tension' => 0.3,
                    'stack' => 'total',
                ],
            ],
        ]);
        $revenueChartGross->setOptions([
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['beginAtZero' => true, 'stacked' => true],
            ],
            'maintainAspectRatio' => false,
        ]);

        $this->profileStop('easyadmin.dashboard.revenue');
        $this->profileStart('easyadmin.dashboard.misc_stats');

        $zahlungsartAktiv = $this->zahlungsartRepository->count(['aktiv' => true]);
        $zahlungsartInaktiv = $this->zahlungsartRepository->count(['aktiv' => false]);
        $zahlungsartenChart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $zahlungsartenChart->setData([
            'labels' => ['Aktiv', 'Inaktiv'],
            'datasets' => [[
                'data' => [$zahlungsartAktiv, $zahlungsartInaktiv],
                'backgroundColor' => ['#198754', '#dc3545'],
                'borderWidth' => 1,
            ]],
        ]);
        $zahlungsartenChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'maintainAspectRatio' => false,
        ]);

        $kundenProFirmaRows = $this->connection->fetchAllAssociative(
            'SELECT f.name AS firma_name, COUNT(k.id) AS total
             FROM firma f
             LEFT JOIN kunde k ON k.firma_id = f.id
             GROUP BY f.id, f.name
             ORDER BY total DESC, f.name ASC'
        );
        $kundenProFirmaLabels = [];
        $kundenProFirmaValues = [];
        foreach ($kundenProFirmaRows as $row) {
            $kundenProFirmaLabels[] = (string) $row['firma_name'];
            $kundenProFirmaValues[] = (int) $row['total'];
        }
        if ([] === $kundenProFirmaLabels) {
            $kundenProFirmaLabels = ['Keine Daten'];
            $kundenProFirmaValues = [0];
        }

        $kundenProFirmaChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $kundenProFirmaChart->setData([
            'labels' => $kundenProFirmaLabels,
            'datasets' => [[
                'label' => 'Kunden pro Firma',
                'data' => $kundenProFirmaValues,
                'backgroundColor' => '#bbcf4e',
            ]],
        ]);
        $kundenProFirmaChart->setOptions([
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'maintainAspectRatio' => false,
        ]);

        $this->profileStop('easyadmin.dashboard.misc_stats');
        $this->profileStart('easyadmin.dashboard.customer_map');

        $customerMapData = $this->customerMapBuilder->build($this->kundeRepository->findAll());

        $this->profileStop('easyadmin.dashboard.customer_map');
        $this->profileStart('easyadmin.dashboard.chat_data');

        $chatData = $this->buildChatData();

        $this->profileStop('easyadmin.dashboard.chat_data');
        $this->profileStart('easyadmin.dashboard.render');

        $response = $this->render('admin/dashboard.html.twig', [
            'overviewChart' => $overviewChart,
            'overviewValues' => [
                'Kunden' => $kundenCount,
                'Software' => $softwareCount,
                'Abos' => $aboCount,
                'Auftraege' => $auftragCount,
            ],
            'statusChart' => $statusChart,
            'statusValues' => array_combine($statusLabels, $statusValues) ?: [],
            'aboPerMonthChart' => $aboPerMonthChart,
            'aboMonthlyValues' => array_combine($monthLabels, $aboMonthlyValues) ?: [],
            'revenueChartNet' => $revenueChartNet,
            'revenueChartGross' => $revenueChartGross,
            'revenueAboValues' => array_combine($revenueMonthLabels, $aboRevenueValues) ?: [],
            'revenueCreditValues' => array_combine($revenueMonthLabels, $creditRevenueValues) ?: [],
            'revenueEcommerceValues' => array_combine($revenueMonthLabels, $ecommerceRevenueValues) ?: [],
            'revenueTotalValues' => array_combine($revenueMonthLabels, $combinedRevenueValues) ?: [],
            'revenueAboTotal' => array_sum($aboRevenueValues),
            'revenueCreditTotal' => array_sum($creditRevenueValues),
            'revenueEcommerceTotal' => array_sum($ecommerceRevenueValues),
            'revenueTotal' => array_sum($combinedRevenueValues),
            'revenueAboGrossTotal' => array_sum($aboRevenueGrossValues),
            'revenueCreditGrossTotal' => array_sum($creditRevenueGrossValues),
            'revenueEcommerceGrossTotal' => array_sum($ecommerceRevenueGrossValues),
            'revenueTotalGross' => array_sum($combinedRevenueGrossValues),
            'revenueCurrentMonth' => $combinedRevenueValues !== [] ? $combinedRevenueValues[array_key_last($combinedRevenueValues)] : 0.0,
            'revenueCurrentMonthGross' => $combinedRevenueGrossValues !== [] ? $combinedRevenueGrossValues[array_key_last($combinedRevenueGrossValues)] : 0.0,
            'revenueAboCurrentMonth' => $aboRevenueValues !== [] ? $aboRevenueValues[array_key_last($aboRevenueValues)] : 0.0,
            'revenueAboCurrentMonthGross' => $aboRevenueGrossValues !== [] ? $aboRevenueGrossValues[array_key_last($aboRevenueGrossValues)] : 0.0,
            'revenueCreditCurrentMonth' => $creditRevenueValues !== [] ? $creditRevenueValues[array_key_last($creditRevenueValues)] : 0.0,
            'revenueCreditCurrentMonthGross' => $creditRevenueGrossValues !== [] ? $creditRevenueGrossValues[array_key_last($creditRevenueGrossValues)] : 0.0,
            'revenueEcommerceCurrentMonth' => $ecommerceRevenueValues !== [] ? $ecommerceRevenueValues[array_key_last($ecommerceRevenueValues)] : 0.0,
            'revenueEcommerceCurrentMonthGross' => $ecommerceRevenueGrossValues !== [] ? $ecommerceRevenueGrossValues[array_key_last($ecommerceRevenueGrossValues)] : 0.0,
            'revenueVatRate' => $defaultVatRate,
            'zahlungsartenChart' => $zahlungsartenChart,
            'zahlungsartenValues' => [
                'Aktiv' => $zahlungsartAktiv,
                'Inaktiv' => $zahlungsartInaktiv,
            ],
            'kundenCount' => $kundenCount,
            'softwareCount' => $softwareCount,
            'aboCount' => $aboCount,
            'auftragCount' => $auftragCount,
            'openOrderCount' => $openOrderCount,
            'completedOrderCount' => $orderCountsByStatus[Auftrag::STATUS_COMPLETED] ?? 0,
            'completionRate' => $completionRate,
            'latestOrders' => $latestOrders,
            'orderStatusItems' => $orderStatusItems,
            'customersMap' => $customerMapData['map'],
            'customerMapMarkerCount' => $customerMapData['markerCount'],
            'customerMapUnresolvedCount' => $customerMapData['unresolvedCount'],
            'kundenProFirmaChart' => $kundenProFirmaChart,
            'kundenProFirmaValues' => array_combine($kundenProFirmaLabels, $kundenProFirmaValues) ?: [],
            'chatFirmen' => $chatData['firmen'],
            'chatMessagesByFirma' => $chatData['messagesByFirma'],
            'changelog' => $this->loadChangelog(),
        ]);

        $this->profileStop('easyadmin.dashboard.render');
        $this->profileStop('easyadmin.dashboard.index');

        return $response;
    }

    private function loadChangelog(): array
    {
        $file = rtrim((string) $this->getParameter('kernel.project_dir'), '/') . '/config/changelog.yaml';
        if (!file_exists($file)) {
            return [];
        }
        $data = \Symfony\Component\Yaml\Yaml::parseFile($file);
        return $data['entries'] ?? [];
    }

    #[AdminRoute(path: '/journal', name: 'journal', options: ['methods' => ['GET']])]
    public function journal(): Response
    {
        return $this->render('admin/journal/index.html.twig', [
            'changelog' => $this->loadChangelog(),
        ]);
    }

    #[Route('/admin/fahrzeugdatenbank/uebersicht', name: 'admin_vehicle_database_overview', methods: ['GET'])]
    public function vehicleDatabaseOverview(): Response
    {
        $vehicleTypeRepository = $this->entityManager->getRepository(VehicleType::class);
        $vehicleBrandRepository = $this->entityManager->getRepository(VehicleBrand::class);
        $vehicleSeriesRepository = $this->entityManager->getRepository(VehicleSeries::class);
        $vehicleModelRepository = $this->entityManager->getRepository(VehicleModel::class);
        $vehicleEngineRepository = $this->entityManager->getRepository(VehicleEngine::class);
        $vehicleStageRepository = $this->entityManager->getRepository(VehicleStage::class);
        $gearboxTypeRepository = $this->entityManager->getRepository(GearboxType::class);
        $gearboxRepository = $this->entityManager->getRepository(Gearbox::class);
        $ecuRepository = $this->entityManager->getRepository(Ecu::class);
        $ecuOptionRepository = $this->entityManager->getRepository(EcuOption::class);

        $countPoolScoped = function (string $entityClass, ?bool $entityActive = null): int {
            $queryBuilder = $this->entityManager->createQueryBuilder()
                ->select('COUNT(entity.id)')
                ->from($entityClass, 'entity')
                ->leftJoin('entity.vehicleDataPool', 'pool')
                ->where('pool.id IS NULL OR pool.active = true');

            if (null !== $entityActive) {
                $queryBuilder
                    ->andWhere('entity.active = :entityActive')
                    ->setParameter('entityActive', $entityActive);
            }

            return (int) $queryBuilder->getQuery()->getSingleScalarResult();
        };

        $summaryCards = [
            [
                'label' => 'Fahrzeugtypen',
                'icon' => 'fa fa-layer-group',
                'total' => $vehicleTypeRepository->count([]),
                'active' => $vehicleTypeRepository->count(['active' => true]),
            ],
            [
                'label' => 'Marken',
                'icon' => 'fa fa-flag',
                'total' => $countPoolScoped(VehicleBrand::class),
                'active' => $countPoolScoped(VehicleBrand::class, true),
            ],
            [
                'label' => 'Baureihen',
                'icon' => 'fa fa-sitemap',
                'total' => $countPoolScoped(VehicleSeries::class),
                'active' => $countPoolScoped(VehicleSeries::class, true),
            ],
            [
                'label' => 'Modelle',
                'icon' => 'fa fa-list',
                'total' => $countPoolScoped(VehicleModel::class),
                'active' => $countPoolScoped(VehicleModel::class, true),
            ],
            [
                'label' => 'Motoren',
                'icon' => 'fa fa-cog',
                'total' => $countPoolScoped(VehicleEngine::class),
                'active' => $countPoolScoped(VehicleEngine::class, true),
            ],
            [
                'label' => 'Stages',
                'icon' => 'fa fa-tachometer-alt',
                'total' => $countPoolScoped(VehicleStage::class),
                'active' => $countPoolScoped(VehicleStage::class, true),
            ],
            [
                'label' => 'Getriebe-Typen',
                'icon' => 'fa fa-tags',
                'total' => $gearboxTypeRepository->count([]),
                'active' => $gearboxTypeRepository->count(['active' => true]),
            ],
            [
                'label' => 'Getriebe',
                'icon' => 'fa fa-gears',
                'total' => $countPoolScoped(Gearbox::class),
                'active' => $countPoolScoped(Gearbox::class, true),
            ],
            [
                'label' => 'ECUs',
                'icon' => 'fa fa-microchip',
                'total' => $countPoolScoped(Ecu::class),
                'active' => null,
            ],
            [
                'label' => 'ECU-Optionen',
                'icon' => 'fa fa-puzzle-piece',
                'total' => $countPoolScoped(EcuOption::class),
                'active' => $countPoolScoped(EcuOption::class, true),
            ],
        ];

        $enginesWithoutStages = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(VehicleEngine::class, 'e')
            ->leftJoin('e.vehicleDataPool', 'pool')
            ->leftJoin('e.stages', 's')
            ->where('pool.id IS NULL OR pool.active = true')
            ->andWhere('s.id IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $enginesWithoutEcuRelations = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(VehicleEngine::class, 'e')
            ->leftJoin('e.vehicleDataPool', 'pool')
            ->leftJoin('e.ecuRelations', 'er')
            ->where('pool.id IS NULL OR pool.active = true')
            ->andWhere('er.id IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $stagesWithoutEcuSelection = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(VehicleStage::class, 's')
            ->leftJoin('s.vehicleDataPool', 'pool')
            ->where('pool.id IS NULL OR pool.active = true')
            ->andWhere('s.ecu IS NULL OR s.ecu = 0')
            ->getQuery()
            ->getSingleScalarResult();

        $mainEcuOptionsCount = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(EcuOption::class, 'o')
            ->leftJoin('o.vehicleDataPool', 'pool')
            ->where('pool.id IS NULL OR pool.active = true')
            ->andWhere('o.isMainOption = true')
            ->getQuery()
            ->getSingleScalarResult();

        $enginesOverview = $vehicleEngineRepository->createQueryBuilder('e')
            ->leftJoin('e.vehicleModel', 'm')
            ->leftJoin('e.vehicleDataPool', 'pool')
            ->addSelect('m')
            ->where('pool.id IS NULL OR pool.active = true')
            ->orderBy('e.sorting', 'ASC')
            ->addOrderBy('e.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('admin/vehicle_database_overview.html.twig', [
            'summaryCards' => $summaryCards,
            'enginesWithoutStages' => $enginesWithoutStages,
            'enginesWithoutEcuRelations' => $enginesWithoutEcuRelations,
            'stagesWithoutEcuSelection' => $stagesWithoutEcuSelection,
            'mainEcuOptionsCount' => $mainEcuOptionsCount,
            'enginesOverview' => $enginesOverview,
        ]);
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/tools', name: 'vehicle_tools', options: ['methods' => ['GET']])]
    public function vehicleTools(Request $request): Response
    {
        $activeTab = strtolower(trim((string) $request->query->get('tab', 'manufacturer')));
        if (!in_array($activeTab, ['manufacturer', 'methode'], true)) {
            $activeTab = 'manufacturer';
        }

        return $this->render('admin/vehicle_tools.html.twig', [
            'activeTab' => $activeTab,
        ]);
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/ecu', name: 'vehicle_ecu', options: ['methods' => ['GET']])]
    public function vehicleEcu(Request $request): Response
    {
        $activeTab = strtolower(trim((string) $request->query->get('tab', 'manufacturer')));
        if (!in_array($activeTab, ['manufacturer', 'ecu', 'option'], true)) {
            $activeTab = 'manufacturer';
        }

        return $this->render('admin/vehicle_ecu.html.twig', [
            'activeTab' => $activeTab,
        ]);
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/getriebe', name: 'vehicle_gearbox', options: ['methods' => ['GET']])]
    public function vehicleGearbox(Request $request): Response
    {
        $activeTab = strtolower(trim((string) $request->query->get('tab', 'type')));
        if (!in_array($activeTab, ['type', 'gearbox'], true)) {
            $activeTab = 'type';
        }

        return $this->render('admin/vehicle_gearbox.html.twig', [
            'activeTab' => $activeTab,
        ]);
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/statistik', name: 'vehicle_usage_statistics', options: ['methods' => ['GET']])]
    public function vehicleUsageStatistics(Request $request): Response
    {
        $embed = $request->query->getBoolean('embed');
        $brandApplicationFilter = trim((string) $request->query->get('brandApplication', ''));
        $now = new \DateTimeImmutable();
        $start24h = $now->modify('-24 hours');
        $start7d = $now->modify('-7 days');
        $start30d = $now->modify('-30 days');
        $startDaily = $now->setTime(0, 0)->modify('-13 days');

        $events24h = $this->vehicleUsageEventRepository->countSince($start24h);
        $events7d = $this->vehicleUsageEventRepository->countSince($start7d);
        $events30d = $this->vehicleUsageEventRepository->countSince($start30d);

        $uniqueVisitors30d = $this->vehicleUsageEventRepository->countUniqueVisitorsSince($start30d);
        $engineViews30d = $this->vehicleUsageEventRepository->countByEventTypeSince('vdb.engine.details', $start30d);
        $stageSelections30d = $this->vehicleUsageEventRepository->countByEventTypeSince('vdb.client.select_stage', $start30d);

        $eventTypeBreakdown = $this->vehicleUsageEventRepository->getEventTypeBreakdownSince($start30d, 30);
        $topTypes = $this->vehicleUsageEventRepository->getTopVehicleTypesSince($start30d, 10);
        $topApplications = $this->vehicleUsageEventRepository->getTopApplicationsSince($start30d, 15);
        $topBrands = $this->vehicleUsageEventRepository->getTopBrandsSince(
            $start30d,
            10,
            '' !== $brandApplicationFilter ? $brandApplicationFilter : null
        );
        $topSeries = $this->vehicleUsageEventRepository->getTopSeriesSince($start30d, 10);
        $topModels = $this->vehicleUsageEventRepository->getTopModelsSince($start30d, 10);
        $topEngines = $this->vehicleUsageEventRepository->getTopEnginesSince($start30d, 15);
        $topBrandModelEngines = $this->vehicleUsageEventRepository->getTopBrandModelEnginesSince($start30d, 15);
        $topStages = $this->vehicleUsageEventRepository->getTopStagesSince($start30d, 15);
        $dailyCounts = $this->vehicleUsageEventRepository->getDailyCounts($startDaily, 14);
        $latestEvents = $this->vehicleUsageEventRepository->getLatestWithNames(100);

        $dailyLabels = [];
        $dailyValues = [];
        foreach ($dailyCounts as $row) {
            $dailyLabels[] = (string) ($row['label'] ?? '');
            $dailyValues[] = (int) ($row['total'] ?? 0);
        }
        if ([] === $dailyLabels) {
            $dailyLabels = ['Keine Daten'];
            $dailyValues = [0];
        }

        $dailyEventsChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $dailyEventsChart->setData([
            'labels' => $dailyLabels,
            'datasets' => [[
                'label' => 'Events',
                'data' => $dailyValues,
                'borderColor' => '#bbcf4e',
                'backgroundColor' => 'rgba(189, 30, 33, 0.15)',
                'fill' => true,
                'tension' => 0.35,
                'pointRadius' => 3,
            ]],
        ]);
        $dailyEventsChart->setOptions([
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'maintainAspectRatio' => false,
        ]);

        $eventTypeLabels = [];
        $eventTypeValues = [];
        $eventTypeColors = [];
        $palette = ['#bbcf4e', '#111931', '#198754', '#bbcf4e', '#6f42c1', '#20c997', '#ffc107', '#0dcaf0'];
        $maxSegments = 8;
        $othersTotal = 0;
        foreach ($eventTypeBreakdown as $index => $row) {
            $label = (string) ($row['eventType'] ?? '');
            $value = (int) ($row['total'] ?? 0);
            if ($index >= $maxSegments) {
                $othersTotal += $value;
                continue;
            }

            $eventTypeLabels[] = '' !== $label ? $label : '-';
            $eventTypeValues[] = $value;
            $eventTypeColors[] = $palette[$index % count($palette)];
        }
        if ($othersTotal > 0) {
            $eventTypeLabels[] = 'Sonstige';
            $eventTypeValues[] = $othersTotal;
            $eventTypeColors[] = '#6c757d';
        }
        if ([] === $eventTypeLabels) {
            $eventTypeLabels = ['Keine Daten'];
            $eventTypeValues = [0];
            $eventTypeColors = ['#6c757d'];
        }

        $eventTypesChart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $eventTypesChart->setData([
            'labels' => $eventTypeLabels,
            'datasets' => [[
                'data' => $eventTypeValues,
                'backgroundColor' => $eventTypeColors,
                'borderWidth' => 1,
            ]],
        ]);
        $eventTypesChart->setOptions([
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
            'maintainAspectRatio' => false,
        ]);

        $topBrandLabels = [];
        $topBrandValues = [];
        foreach ($topBrands as $row) {
            $topBrandLabels[] = (string) ($row['brandName'] ?? '-');
            $topBrandValues[] = (int) ($row['total'] ?? 0);
        }
        if ([] === $topBrandLabels) {
            $topBrandLabels = ['Keine Daten'];
            $topBrandValues = [0];
        }

        $topBrandsChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $topBrandsChart->setData([
            'labels' => $topBrandLabels,
            'datasets' => [[
                'label' => 'Marken',
                'data' => $topBrandValues,
                'backgroundColor' => '#111931',
            ]],
        ]);
        $topBrandsChart->setOptions([
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'maintainAspectRatio' => false,
        ]);

        $topModelLabels = [];
        $topModelValues = [];
        foreach ($topModels as $row) {
            $topModelLabels[] = (string) ($row['modelName'] ?? '-');
            $topModelValues[] = (int) ($row['total'] ?? 0);
        }
        if ([] === $topModelLabels) {
            $topModelLabels = ['Keine Daten'];
            $topModelValues = [0];
        }

        $topModelsChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $topModelsChart->setData([
            'labels' => $topModelLabels,
            'datasets' => [[
                'label' => 'Modelle',
                'data' => $topModelValues,
                'backgroundColor' => '#198754',
            ]],
        ]);
        $topModelsChart->setOptions([
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'maintainAspectRatio' => false,
        ]);

        $topBrandModelEngineLabels = [];
        $topBrandModelEngineValues = [];
        foreach ($topBrandModelEngines as $row) {
            $brandName = (string) ($row['brandName'] ?? '-');
            $modelName = (string) ($row['modelName'] ?? '-');
            $engineName = (string) ($row['engineName'] ?? '-');
            $topBrandModelEngineLabels[] = [trim($brandName . ' ' . $modelName), $engineName];
            $topBrandModelEngineValues[] = (int) ($row['total'] ?? 0);
        }
        if ([] === $topBrandModelEngineLabels) {
            $topBrandModelEngineLabels = ['Keine Daten'];
            $topBrandModelEngineValues = [0];
        }

        $topBrandModelEnginesChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $topBrandModelEnginesChart->setData([
            'labels' => $topBrandModelEngineLabels,
            'datasets' => [[
                'label' => 'Aufrufe',
                'data' => $topBrandModelEngineValues,
                'backgroundColor' => '#bbcf4e',
            ]],
        ]);
        $topBrandModelEnginesChart->setOptions([
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'maintainAspectRatio' => false,
        ]);

        $template = $embed ? 'admin/statistics/_vehicle_usage.html.twig' : 'admin/vehicle_usage_statistics.html.twig';

        return $this->render($template, [
            'events24h' => $events24h,
            'events7d' => $events7d,
            'events30d' => $events30d,
            'uniqueVisitors30d' => $uniqueVisitors30d,
            'engineViews30d' => $engineViews30d,
            'stageSelections30d' => $stageSelections30d,
            'eventTypeBreakdown' => $eventTypeBreakdown,
            'topTypes' => $topTypes,
            'topBrands' => $topBrands,
            'topSeries' => $topSeries,
            'topModels' => $topModels,
            'topEngines' => $topEngines,
            'topBrandModelEngines' => $topBrandModelEngines,
            'topStages' => $topStages,
            'topApplications' => $topApplications,
            'brandApplicationFilter' => $brandApplicationFilter,
            'dailyCounts' => $dailyCounts,
            'latestEvents' => $latestEvents,
            'dailyEventsChart' => $dailyEventsChart,
            'eventTypesChart' => $eventTypesChart,
            'topBrandsChart' => $topBrandsChart,
            'topModelsChart' => $topModelsChart,
            'topBrandModelEnginesChart' => $topBrandModelEnginesChart,
            'embedded' => $embed,
        ]);
    }

    #[AdminRoute(path: '/support/chats', name: 'support_chats', options: ['methods' => ['GET']])]
    public function supportChats(Request $request): Response
    {
        $this->addFlash('info', 'Chats sind aktuell deaktiviert.');

        return $this->redirectToRoute('admin_support_tickets');

        $firmen = $this->firmaRepository->findBy([], ['name' => 'ASC']);

        $selectedFirmaId = (int) $request->query->get('firma', 0);
        $selectedFirma = $selectedFirmaId > 0 ? $this->firmaRepository->find($selectedFirmaId) : null;
        if (!$selectedFirma && [] !== $firmen) {
            $selectedFirma = $firmen[0];
            $selectedFirmaId = (int) $selectedFirma->getId();
        }

        $selectedKundeId = (int) $request->query->get('kunde', 0);
        $selectedKunde = null;
        if ($selectedFirma && $selectedKundeId > 0) {
            $candidate = $this->kundeRepository->find($selectedKundeId);
            if ($candidate instanceof Kunde && $candidate->getFirma()?->getId() === $selectedFirma->getId()) {
                $selectedKunde = $candidate;
            } else {
                $selectedKundeId = 0;
            }
        } else {
            $selectedKundeId = 0;
        }

        $countRows = $this->chatMessageRepository->createQueryBuilder('m')
            ->select('IDENTITY(m.firma) AS firmaId, COUNT(m.id) AS total, MAX(m.id) AS maxId')
            ->groupBy('m.firma')
            ->getQuery()
            ->getArrayResult();

        $countsByFirma = [];
        $maxIds = [];
        foreach ($countRows as $row) {
            $firmaId = isset($row['firmaId']) ? (int) $row['firmaId'] : 0;
            if ($firmaId <= 0) {
                continue;
            }

            $countsByFirma[$firmaId] = isset($row['total']) ? (int) $row['total'] : 0;
            $maxId = isset($row['maxId']) ? (int) $row['maxId'] : 0;
            if ($maxId > 0) {
                $maxIds[] = $maxId;
            }
        }

        $latestByFirma = [];
        if ([] !== $maxIds) {
            $latestMessages = $this->chatMessageRepository->createQueryBuilder('m')
                ->leftJoin('m.firma', 'f')
                ->addSelect('f')
                ->leftJoin('m.kunde', 'k')
                ->addSelect('k')
                ->andWhere('m.id IN (:ids)')
                ->setParameter('ids', array_values(array_unique($maxIds)))
                ->getQuery()
                ->getResult();

            foreach ($latestMessages as $message) {
                if (!$message instanceof CustomerChatMessage) {
                    continue;
                }

                $firmaId = $message->getFirma()?->getId();
                if (null === $firmaId) {
                    continue;
                }

                $latestByFirma[(int) $firmaId] = $message;
            }
        }

        $firmaItems = [];
        foreach ($firmen as $firma) {
            $firmaId = (int) ($firma->getId() ?? 0);
            $lastMessage = $latestByFirma[$firmaId] ?? null;
            $lastPreview = '';
            $lastAt = null;
            $lastKundeLabel = null;

            if ($lastMessage instanceof CustomerChatMessage) {
                $text = trim($lastMessage->getMessage());
                $text = preg_replace('/\\s+/', ' ', $text ?? '') ?? '';
                $maxLen = 90;
                if (function_exists('mb_substr') && function_exists('mb_strlen')) {
                    $lastPreview = mb_substr($text, 0, $maxLen);
                    if (mb_strlen($text) > $maxLen) {
                        $lastPreview .= '...';
                    }
                } else {
                    $lastPreview = substr($text, 0, $maxLen);
                    if (strlen($text) > $maxLen) {
                        $lastPreview .= '...';
                    }
                }

                $lastAt = $lastMessage->getCreatedAt();
                $lastKundeLabel = $lastMessage->getKunde() ? (string) $lastMessage->getKunde() : null;
            }

            $firmaItems[] = [
                'id' => $firmaId,
                'name' => (string) $firma->getName(),
                'messageCount' => (int) ($countsByFirma[$firmaId] ?? 0),
                'lastPreview' => $lastPreview,
                'lastAt' => $lastAt,
                'lastKundeLabel' => $lastKundeLabel,
            ];
        }

        $messages = [];
        $kundenOptions = [];
        if ($selectedFirma) {
            foreach ($selectedFirma->getKunden() as $kunde) {
                $kundenOptions[] = [
                    'id' => (int) ($kunde->getId() ?? 0),
                    'name' => trim((string) $kunde),
                ];
            }
            usort($kundenOptions, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

            $qb = $this->chatMessageRepository->createQueryBuilder('m')
                ->leftJoin('m.kunde', 'k')
                ->addSelect('k')
                ->andWhere('m.firma = :firma')
                ->setParameter('firma', $selectedFirma)
                ->orderBy('m.createdAt', 'DESC')
                ->setMaxResults(200);

            if ($selectedKunde) {
                $qb
                    ->andWhere('m.kunde = :kunde')
                    ->setParameter('kunde', $selectedKunde);
            }

            $messages = $qb->getQuery()->getResult();
            $messages = array_reverse($messages);
        }

        return $this->render('admin/support/chats.html.twig', [
            'firmaItems' => $firmaItems,
            'selectedFirma' => $selectedFirma,
            'selectedFirmaId' => $selectedFirmaId,
            'selectedKundeId' => $selectedKundeId,
            'kundenOptions' => $kundenOptions,
            'messages' => $messages,
        ]);
    }

    #[AdminRoute(path: '/support/tickets', name: 'support_tickets', options: ['methods' => ['GET']])]
    public function supportTickets(Request $request): Response
    {
        $embed = $request->query->getBoolean('embed');
        $statusFilter = strtolower(trim((string) $request->query->get('status', SupportTicket::STATUS_OPEN)));
        $validStatusFilters = [
            SupportTicket::STATUS_OPEN,
            SupportTicket::STATUS_IN_PROGRESS,
            SupportTicket::STATUS_CLOSED,
            'all',
        ];
        if (!in_array($statusFilter, $validStatusFilters, true)) {
            $statusFilter = SupportTicket::STATUS_OPEN;
        }

        $firmaFilterId = (int) $request->query->get('firma', 0);
        $firmaFilter = $firmaFilterId > 0 ? $this->firmaRepository->find($firmaFilterId) : null;
        if (!$firmaFilter) {
            $firmaFilterId = 0;
        }

        $ticketsEnabled = true;
        try {
            $schemaManager = $this->connection->createSchemaManager();
            $ticketsEnabled = $schemaManager->tablesExist(['support_ticket', 'support_ticket_message']);
        } catch (\Throwable) {
            $ticketsEnabled = true;
        }

        $selectedTicketId = (int) $request->query->get('ticket', 0);
        $selectedTicket = null;
        if ($ticketsEnabled && $selectedTicketId > 0) {
            $selectedTicket = $this->supportTicketRepository->createQueryBuilder('t')
                ->leftJoin('t.firma', 'f')
                ->addSelect('f')
                ->leftJoin('t.kunde', 'k')
                ->addSelect('k')
                ->andWhere('t.id = :id')
                ->setParameter('id', $selectedTicketId)
                ->getQuery()
                ->getOneOrNullResult();
        }

        $tickets = [];
        if ($ticketsEnabled) {
            $ticketQb = $this->supportTicketRepository->createQueryBuilder('t')
                ->leftJoin('t.firma', 'f')
                ->addSelect('f')
                ->leftJoin('t.kunde', 'k')
                ->addSelect('k')
                ->orderBy('t.lastMessageAt', 'DESC')
                ->addOrderBy('t.updatedAt', 'DESC')
                ->setMaxResults(250);

            if ($firmaFilter) {
                $ticketQb
                    ->andWhere('t.firma = :firma')
                    ->setParameter('firma', $firmaFilter);
            }

            if ('all' !== $statusFilter) {
                $ticketQb
                    ->andWhere('t.status = :status')
                    ->setParameter('status', $statusFilter);
            }

            $tickets = $ticketQb->getQuery()->getResult();
        } else {
            $this->addFlash('warning', 'Ticket-System ist noch nicht migriert. Bitte Migration ausführen.');
        }

        $ticketStats = [
            'total' => 0,
            'open' => 0,
            'inProgress' => 0,
            'closed' => 0,
            'messages' => 0,
            'urgent' => 0,
        ];
        $ticketStatsByFirma = [
            'TFD' => [
                'total' => 0,
                'open' => 0,
                'inProgress' => 0,
                'closed' => 0,
            ],
            'CPT' => [
                'total' => 0,
                'open' => 0,
                'inProgress' => 0,
                'closed' => 0,
            ],
        ];
        if ($ticketsEnabled) {
            $statsQb = $this->supportTicketRepository->createQueryBuilder('t')
                ->select('COUNT(t.id) AS totalCount')
                ->addSelect('SUM(CASE WHEN t.status = :statusOpen THEN 1 ELSE 0 END) AS openCount')
                ->addSelect('SUM(CASE WHEN t.status = :statusInProgress THEN 1 ELSE 0 END) AS inProgressCount')
                ->addSelect('SUM(CASE WHEN t.status = :statusClosed THEN 1 ELSE 0 END) AS closedCount')
                ->addSelect('COALESCE(SUM(t.messageCount), 0) AS messageTotal')
                ->addSelect('SUM(CASE WHEN t.priority = :priorityUrgent THEN 1 ELSE 0 END) AS urgentCount')
                ->setParameter('statusOpen', SupportTicket::STATUS_OPEN)
                ->setParameter('statusInProgress', SupportTicket::STATUS_IN_PROGRESS)
                ->setParameter('statusClosed', SupportTicket::STATUS_CLOSED)
                ->setParameter('priorityUrgent', SupportTicket::PRIORITY_URGENT);

            if ($firmaFilter) {
                $statsQb
                    ->andWhere('t.firma = :statsFirma')
                    ->setParameter('statsFirma', $firmaFilter);
            }

            /** @var array<string, mixed> $statsRaw */
            $statsRaw = $statsQb->getQuery()->getSingleResult();

            $ticketStats = [
                'total' => (int) ($statsRaw['totalCount'] ?? 0),
                'open' => (int) ($statsRaw['openCount'] ?? 0),
                'inProgress' => (int) ($statsRaw['inProgressCount'] ?? 0),
                'closed' => (int) ($statsRaw['closedCount'] ?? 0),
                'messages' => (int) ($statsRaw['messageTotal'] ?? 0),
                'urgent' => (int) ($statsRaw['urgentCount'] ?? 0),
            ];

            /** @var array<int, array<string, mixed>> $firmaStatsRows */
            $firmaStatsRows = $this->supportTicketRepository->createQueryBuilder('t')
                ->select('UPPER(COALESCE(f.kuerzel, \'\')) AS kuerzel')
                ->addSelect('COUNT(t.id) AS totalCount')
                ->addSelect('SUM(CASE WHEN t.status = :statusOpenByFirma THEN 1 ELSE 0 END) AS openCount')
                ->addSelect('SUM(CASE WHEN t.status = :statusInProgressByFirma THEN 1 ELSE 0 END) AS inProgressCount')
                ->addSelect('SUM(CASE WHEN t.status = :statusClosedByFirma THEN 1 ELSE 0 END) AS closedCount')
                ->innerJoin('t.firma', 'f')
                ->groupBy('f.id, f.kuerzel')
                ->setParameter('statusOpenByFirma', SupportTicket::STATUS_OPEN)
                ->setParameter('statusInProgressByFirma', SupportTicket::STATUS_IN_PROGRESS)
                ->setParameter('statusClosedByFirma', SupportTicket::STATUS_CLOSED)
                ->getQuery()
                ->getArrayResult();

            foreach ($firmaStatsRows as $row) {
                $kuerzel = strtoupper(trim((string) ($row['kuerzel'] ?? '')));
                if (!isset($ticketStatsByFirma[$kuerzel])) {
                    continue;
                }

                $ticketStatsByFirma[$kuerzel] = [
                    'total' => (int) ($row['totalCount'] ?? 0),
                    'open' => (int) ($row['openCount'] ?? 0),
                    'inProgress' => (int) ($row['inProgressCount'] ?? 0),
                    'closed' => (int) ($row['closedCount'] ?? 0),
                ];
            }
        }

        $messages = [];
        if ($ticketsEnabled && $selectedTicket instanceof SupportTicket) {
            $messages = $this->supportTicketMessageRepository->createQueryBuilder('m')
                ->leftJoin('m.kunde', 'k')
                ->addSelect('k')
                ->andWhere('m.ticket = :ticket')
                ->setParameter('ticket', $selectedTicket)
                ->orderBy('m.createdAt', 'ASC')
                ->getQuery()
                ->getResult();
        }

        $firmen = $this->firmaRepository->findBy([], ['name' => 'ASC']);

        $statusBadgeClasses = [
            SupportTicket::STATUS_OPEN => 'text-bg-info',
            SupportTicket::STATUS_IN_PROGRESS => 'text-bg-warning',
            SupportTicket::STATUS_CLOSED => 'text-bg-secondary',
        ];
        $priorityBadgeClasses = [
            SupportTicket::PRIORITY_LOW => 'text-bg-secondary',
            SupportTicket::PRIORITY_NORMAL => 'text-bg-primary',
            SupportTicket::PRIORITY_HIGH => 'text-bg-warning',
            SupportTicket::PRIORITY_URGENT => 'text-bg-danger',
        ];
        $statusChoices = SupportTicket::getStatusChoices();
        $priorityChoices = SupportTicket::getPriorityChoices();

        return $this->render('admin/support/tickets.html.twig', [
            'embedded' => $embed,
            'ticketsEnabled' => $ticketsEnabled,
            'tickets' => $tickets,
            'selectedTicket' => $selectedTicket,
            'messages' => $messages,
            'firmen' => $firmen,
            'statusFilter' => $statusFilter,
            'firmaFilterId' => $firmaFilterId,
            'statusChoices' => $statusChoices,
            'priorityChoices' => $priorityChoices,
            'statusLabelsByValue' => array_flip($statusChoices),
            'priorityLabelsByValue' => array_flip($priorityChoices),
            'statusBadgeClasses' => $statusBadgeClasses,
            'priorityBadgeClasses' => $priorityBadgeClasses,
            'ticketStats' => $ticketStats,
            'ticketStatsByFirma' => $ticketStatsByFirma,
        ]);
    }

    #[AdminRoute(path: '/support/tickets/customers', name: 'support_ticket_customers', options: ['methods' => ['GET']])]
    public function supportTicketCustomers(Request $request): JsonResponse
    {
        $firmaId = (int) $request->query->get('firma', 0);
        if ($firmaId <= 0) {
            return new JsonResponse(['items' => []]);
        }

        $firma = $this->firmaRepository->find($firmaId);
        if (null === $firma) {
            return new JsonResponse(['items' => []]);
        }

        $kunden = $this->kundeRepository->createQueryBuilder('k')
            ->andWhere('k.firma = :firma')
            ->setParameter('firma', $firma)
            ->orderBy('k.nachname', 'ASC')
            ->addOrderBy('k.vorname', 'ASC')
            ->getQuery()
            ->getResult();

        $items = [];
        foreach ($kunden as $kunde) {
            if (!$kunde instanceof Kunde) {
                continue;
            }

            $id = (int) ($kunde->getId() ?? 0);
            if ($id <= 0) {
                continue;
            }

            $items[] = [
                'id' => $id,
                'name' => (string) $kunde,
            ];
        }

        return new JsonResponse(['items' => $items]);
    }

    #[Route('/admin/support/tickets/new', name: 'admin_support_ticket_new', methods: ['POST'])]
    public function createSupportTicket(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_ticket_new', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_tickets');
        }

        $firmaId = (int) $request->request->get('firmaId', 0);
        $kundeId = (int) $request->request->get('kundeId', 0);
        $subject = trim((string) $request->request->get('subject', ''));
        $messageText = trim((string) $request->request->get('message', ''));
        $priority = trim((string) $request->request->get('priority', SupportTicket::PRIORITY_NORMAL));

        if ($firmaId <= 0 || '' === $subject || '' === $messageText) {
            $this->addFlash('warning', 'Bitte Firma, Betreff und Nachricht angeben.');

            return $this->redirectToRoute('admin_support_tickets');
        }

        $firma = $this->firmaRepository->find($firmaId);
        if (null === $firma) {
            $this->addFlash('danger', 'Firma nicht gefunden.');

            return $this->redirectToRoute('admin_support_tickets');
        }

        $kunde = null;
        if ($kundeId > 0) {
            $candidate = $this->kundeRepository->find($kundeId);
            if ($candidate instanceof Kunde && $candidate->getFirma()?->getId() === $firma->getId()) {
                $kunde = $candidate;
            }
        }

        $ticket = (new SupportTicket())
            ->setFirma($firma)
            ->setKunde($kunde)
            ->setSubject($subject)
            ->setStatus(SupportTicket::STATUS_OPEN)
            ->setPriority($priority);

        $user = $this->getUser();
        $senderName = $user ? (string) $user : 'Admin';

        $message = (new SupportTicketMessage())
            ->setTicket($ticket)
            ->setSenderType(SupportTicketMessage::SENDER_ADMIN)
            ->setSenderName($senderName)
            ->setMessage($messageText);

        $ticket->addMessage($message);
        $ticket->registerNewMessage($messageText, $message->getCreatedAt() ?? new \DateTimeImmutable());

        $this->entityManager->persist($ticket);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $this->addFlash('success', 'Ticket wurde erstellt.');

        return $this->redirectToRoute('admin_support_tickets', [
            'ticket' => (int) ($ticket->getId() ?? 0),
        ]);
    }

    #[Route('/admin/support/tickets/reply', name: 'admin_support_ticket_reply', methods: ['POST'])]
    public function replySupportTicket(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_ticket_reply', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_tickets');
        }

        $ticketId = (int) $request->request->get('ticketId', 0);
        $messageText = trim((string) $request->request->get('message', ''));

        $statusFilter = strtolower(trim((string) $request->request->get('status', SupportTicket::STATUS_OPEN)));
        $firmaFilterId = (int) $request->request->get('firma', 0);

        if ($ticketId <= 0 || '' === $messageText) {
            $this->addFlash('warning', 'Bitte Nachricht angeben.');

            return $this->redirectToRoute('admin_support_tickets', [
                'ticket' => $ticketId,
                'status' => $statusFilter,
                'firma' => $firmaFilterId,
            ]);
        }

        $ticket = $this->supportTicketRepository->find($ticketId);
        if (!$ticket instanceof SupportTicket) {
            $this->addFlash('danger', 'Ticket nicht gefunden.');

            return $this->redirectToRoute('admin_support_tickets');
        }

        $user = $this->getUser();
        $senderName = $user ? (string) $user : 'Admin';

        $message = (new SupportTicketMessage())
            ->setTicket($ticket)
            ->setSenderType(SupportTicketMessage::SENDER_ADMIN)
            ->setSenderName($senderName)
            ->setMessage($messageText);

        $ticket->addMessage($message);
        $ticket->registerNewMessage($messageText, $message->getCreatedAt() ?? new \DateTimeImmutable());

        if (SupportTicket::STATUS_OPEN === $ticket->getStatus()) {
            $ticket->setStatus(SupportTicket::STATUS_IN_PROGRESS);
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Handle file upload (NACH flush, damit Ticket ID existiert)
        error_log('DEBUG: All files: ' . json_encode(array_keys($request->files->all())));
        error_log('DEBUG: Attachment param exists: ' . ($request->files->has('attachment') ? 'ja' : 'nein'));
        
        $uploadedFile = $request->files->get('attachment');
        error_log('DEBUG: uploadedFile is null: ' . (is_null($uploadedFile) ? 'ja' : 'nein'));
        
        if ($uploadedFile) {
            try {
                error_log('DEBUG: Datei vorhanden, Name: ' . $uploadedFile->getClientOriginalName());
                error_log('DEBUG: Temp Path: ' . $uploadedFile->getRealPath());
                error_log('DEBUG: Ist gültig: ' . ($uploadedFile->isValid() ? 'ja' : 'nein'));
                error_log('DEBUG: Error: ' . $uploadedFile->getErrorMessage());
                
                if ($uploadedFile->isValid()) {
                    $description = trim((string) $request->request->get('attachment_description', ''));
                    $this->fileService->uploadFile(
                        $ticket,
                        $uploadedFile,
                        $description ?: null
                    );
                    $this->addFlash('success', 'Nachricht und Datei gespeichert.');
                } else {
                    $this->addFlash('warning', 'Datei ungültig: ' . $uploadedFile->getErrorMessage());
                }
            } catch (\Exception $e) {
                error_log('DEBUG: Exception: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
                $this->addFlash('warning', 'Datei-Upload fehlgeschlagen: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('success', 'Nachricht gespeichert.');
        }

        return $this->redirectToRoute('admin_support_tickets', [
            'ticket' => $ticketId,
            'status' => $statusFilter,
            'firma' => $firmaFilterId,
        ]);
    }

    #[Route('/admin/support/tickets/attachment/{attachmentId}/preview', name: 'admin_support_ticket_preview', methods: ['GET'])]
    public function previewSupportTicketAttachment(int $attachmentId): Response
    {
        $attachment = $this->entityManager->getRepository(\App\Entity\SupportTicketAttachment::class)->find($attachmentId);
        if (!$attachment) {
            throw $this->createNotFoundException('Anhang nicht gefunden.');
        }

        $filePath = $this->fileService->getFilePath($attachment);
        if (!file_exists($filePath) || !is_file($filePath)) {
            throw $this->createNotFoundException('Datei nicht gefunden.');
        }

        return $this->file($filePath, null, \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/admin/support/tickets/attachment/{attachmentId}/download', name: 'admin_support_ticket_download', methods: ['GET'])]
    public function downloadSupportTicketAttachment(int $attachmentId): Response
    {
        $attachment = $this->entityManager->getRepository(\App\Entity\SupportTicketAttachment::class)->find($attachmentId);
        if (!$attachment) {
            throw $this->createNotFoundException('Anhang nicht gefunden.');
        }

        $filePath = $this->fileService->getFilePath($attachment);
        if (!file_exists($filePath) || !is_file($filePath)) {
            throw $this->createNotFoundException('Datei nicht gefunden.');
        }

        return $this->file(
            $filePath,
            $attachment->getOriginalFilename(),
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }

    #[Route('/admin/support/tickets/status', name: 'admin_support_ticket_status', methods: ['POST'])]
    public function updateSupportTicketStatus(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_ticket_status', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_tickets');
        }

        $ticketId = (int) $request->request->get('ticketId', 0);
        $status = strtolower(trim((string) $request->request->get('status', '')));

        $statusFilter = strtolower(trim((string) $request->request->get('statusFilter', SupportTicket::STATUS_OPEN)));
        $firmaFilterId = (int) $request->request->get('firma', 0);

        if ($ticketId <= 0) {
            return $this->redirectToRoute('admin_support_tickets', [
                'status' => $statusFilter,
                'firma' => $firmaFilterId,
            ]);
        }

        $ticket = $this->supportTicketRepository->find($ticketId);
        if (!$ticket instanceof SupportTicket) {
            $this->addFlash('danger', 'Ticket nicht gefunden.');

            return $this->redirectToRoute('admin_support_tickets', [
                'status' => $statusFilter,
                'firma' => $firmaFilterId,
            ]);
        }

        $now = new \DateTimeImmutable();
        if (SupportTicket::STATUS_CLOSED === $status) {
            $ticket
                ->setStatus(SupportTicket::STATUS_CLOSED)
                ->setClosedAt($now)
                ->setUpdatedAt($now);
        } elseif (SupportTicket::STATUS_OPEN === $status) {
            $ticket
                ->setStatus(SupportTicket::STATUS_OPEN)
                ->setClosedAt(null)
                ->setUpdatedAt($now);
        } elseif (SupportTicket::STATUS_IN_PROGRESS === $status) {
            $ticket
                ->setStatus(SupportTicket::STATUS_IN_PROGRESS)
                ->setUpdatedAt($now);
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('admin_support_tickets', [
            'ticket' => $ticketId,
            'status' => $statusFilter,
            'firma' => $firmaFilterId,
        ]);
    }

    #[AdminRoute(path: '/support/email', name: 'support_email', options: ['methods' => ['GET']])]
    public function supportEmail(Request $request): Response
    {
        $embed = $request->query->getBoolean('embed');
        $accounts = $this->emailKontoRepository->findBy(
            ['active' => true],
            ['sorting' => 'ASC', 'name' => 'ASC']
        );

        $accountId = (int) $request->query->get('account', 0);
        $selectedAccount = null;
        if ($accountId > 0) {
            foreach ($accounts as $account) {
                if ($account instanceof EmailKonto && $account->getId() === $accountId) {
                    $selectedAccount = $account;
                    break;
                }
            }
        }
        if (!$selectedAccount instanceof EmailKonto) {
            $selectedAccount = $accounts[0] ?? null;
            $accountId = $selectedAccount instanceof EmailKonto ? (int) ($selectedAccount->getId() ?? 0) : 0;
        }

        $folderFilter = $this->normalizeSupportEmailFolder(
            (string) $request->query->get('folder', SupportEmailMessage::FOLDER_INBOX),
            true
        );
        $searchQuery = trim((string) $request->query->get('q', ''));
        $unreadOnly = '1' === (string) $request->query->get('unread', '0');
        $starredOnly = '1' === (string) $request->query->get('starred', '0');
        $selectedMessageId = (int) $request->query->get('message', 0);

        $messageQb = $this->supportEmailMessageRepository->createQueryBuilder('m')
            ->leftJoin('m.emailKonto', 'k')
            ->addSelect('k')
            ->orderBy('m.receivedAt', 'DESC')
            ->addOrderBy('m.sentAt', 'DESC')
            ->addOrderBy('m.updatedAt', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->setMaxResults(250);

        if ($selectedAccount instanceof EmailKonto) {
            $messageQb
                ->andWhere('m.emailKonto = :account')
                ->setParameter('account', $selectedAccount);
        }

        if ('all' !== $folderFilter) {
            $messageQb
                ->andWhere('m.folder = :folder')
                ->setParameter('folder', $folderFilter);
        }

        if ($unreadOnly) {
            $messageQb
                ->andWhere('m.isRead = :isRead')
                ->setParameter('isRead', false);
        }

        if ($starredOnly) {
            $messageQb
                ->andWhere('m.isStarred = :isStarred')
                ->setParameter('isStarred', true);
        }

        if ('' !== $searchQuery) {
            $searchToken = '%' . mb_strtolower($searchQuery) . '%';
            $messageQb
                ->andWhere(
                    'LOWER(COALESCE(m.subject, \'\')) LIKE :search
                     OR LOWER(COALESCE(m.fromName, \'\')) LIKE :search
                     OR LOWER(COALESCE(m.fromEmail, \'\')) LIKE :search
                     OR LOWER(COALESCE(m.bodyText, \'\')) LIKE :search'
                )
                ->setParameter('search', $searchToken);
        }

        $messages = $messageQb->getQuery()->getResult();

        $selectedMessage = null;
        if ($selectedMessageId > 0) {
            foreach ($messages as $message) {
                if ($message instanceof SupportEmailMessage && $message->getId() === $selectedMessageId) {
                    $selectedMessage = $message;
                    break;
                }
            }
        }
        if (!$selectedMessage instanceof SupportEmailMessage) {
            $selectedMessage = $messages[0] ?? null;
        }

        if ($selectedMessage instanceof SupportEmailMessage && !$selectedMessage->isRead()) {
            $selectedMessage
                ->setIsRead(true)
                ->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        $folderStats = [];
        foreach (SupportEmailMessage::getFolderChoices() as $value) {
            $folderStats[$value] = 0;
        }

        $folderStatsQb = $this->supportEmailMessageRepository->createQueryBuilder('m')
            ->select('m.folder AS folderValue, COUNT(m.id) AS totalCount')
            ->groupBy('m.folder');
        if ($selectedAccount instanceof EmailKonto) {
            $folderStatsQb
                ->andWhere('m.emailKonto = :statsAccount')
                ->setParameter('statsAccount', $selectedAccount);
        }

        /** @var array<int, array<string, mixed>> $folderStatsRows */
        $folderStatsRows = $folderStatsQb->getQuery()->getArrayResult();
        foreach ($folderStatsRows as $row) {
            $folderValue = strtolower(trim((string) ($row['folderValue'] ?? '')));
            if (!array_key_exists($folderValue, $folderStats)) {
                continue;
            }

            $folderStats[$folderValue] = (int) ($row['totalCount'] ?? 0);
        }

        $labelStats = [];
        $labelQb = $this->supportEmailMessageRepository->createQueryBuilder('m')
            ->select('m.labels AS labelsValue')
            ->andWhere('m.labels IS NOT NULL');
        if ($selectedAccount instanceof EmailKonto) {
            $labelQb
                ->andWhere('m.emailKonto = :labelAccount')
                ->setParameter('labelAccount', $selectedAccount);
        }
        /** @var array<int, array<string, mixed>> $labelRows */
        $labelRows = $labelQb->getQuery()->getArrayResult();
        foreach ($labelRows as $row) {
            $labels = $row['labelsValue'] ?? null;
            if (!is_array($labels)) {
                continue;
            }

            foreach ($labels as $label) {
                $value = trim((string) $label);
                if ('' === $value) {
                    continue;
                }
                if (!isset($labelStats[$value])) {
                    $labelStats[$value] = 0;
                }
                ++$labelStats[$value];
            }
        }
        $configuredLabelStats = [];
        if ($selectedAccount instanceof EmailKonto) {
            foreach ($selectedAccount->getLabels() as $labelEntity) {
                if (!$labelEntity instanceof EmailLabel) {
                    continue;
                }

                $name = trim($labelEntity->getName());
                if ('' === $name) {
                    continue;
                }

                $configuredLabelStats[$name] = (int) ($labelStats[$name] ?? 0);
            }
        }

        if ([] !== $configuredLabelStats) {
            $labelStats = $configuredLabelStats;
        } elseif ([] === $labelStats) {
            $labelStats = [
                'Personal' => 0,
                'Work' => 0,
                'Payments' => 0,
                'Invoices' => 0,
                'Accounts' => 0,
                'Forums' => 0,
            ];
        } else {
            arsort($labelStats);
            $labelStats = array_slice($labelStats, 0, 8, true);
        }

        $customerRows = $this->kundeRepository->createQueryBuilder('k')
            ->select('k.email AS email, k.vorname AS vorname, k.nachname AS nachname, k.firmenname AS firmenname')
            ->andWhere('k.email IS NOT NULL')
            ->andWhere('TRIM(k.email) <> :emptyEmail')
            ->setParameter('emptyEmail', '')
            ->orderBy('k.email', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $recipientSuggestions = [];
        $seenEmails = [];
        foreach ($customerRows as $row) {
            $emailRaw = trim((string) ($row['email'] ?? ''));
            if ('' === $emailRaw) {
                continue;
            }

            $emailKey = strtolower($emailRaw);
            if (isset($seenEmails[$emailKey])) {
                continue;
            }
            $seenEmails[$emailKey] = true;

            $fullName = trim(sprintf(
                '%s %s',
                trim((string) ($row['vorname'] ?? '')),
                trim((string) ($row['nachname'] ?? ''))
            ));
            $company = trim((string) ($row['firmenname'] ?? ''));

            $displayName = '';
            if ('' !== $fullName) {
                $displayName = $fullName;
            } elseif ('' !== $company) {
                $displayName = $company;
            }

            $recipientSuggestions[] = [
                'email' => $emailRaw,
                'label' => '' !== $displayName ? sprintf('%s <%s>', $displayName, $emailRaw) : $emailRaw,
            ];
        }

        return $this->render('admin/support/email.html.twig', [
            'embedded' => $embed,
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'folderChoices' => SupportEmailMessage::getFolderChoices(),
            'folderFilter' => $folderFilter,
            'folderStats' => $folderStats,
            'labelStats' => $labelStats,
            'messages' => $messages,
            'selectedMessage' => $selectedMessage,
            'searchQuery' => $searchQuery,
            'unreadOnly' => $unreadOnly,
            'starredOnly' => $starredOnly,
            'recipientSuggestions' => $recipientSuggestions,
        ]);
    }

    #[AdminRoute(path: '/support/kommunikation', name: 'communication', options: ['methods' => ['GET']])]
    public function communicationHub(): Response
    {
        $ticketsAllowed = $this->isPageAllowed(AdminPageRegistry::routeKey('admin_support_tickets'));
        $emailAllowed = $this->isPageAllowed(AdminPageRegistry::routeKey('admin_support_email'));

        return $this->render('admin/communication.html.twig', [
            'ticketsAllowed' => $ticketsAllowed,
            'emailAllowed' => $emailAllowed,
        ]);
    }

    #[Route('/admin/navigation-badges', name: 'admin_navigation_badges', methods: ['GET'])]
    public function navigationBadges(): JsonResponse
    {
        $orderCount = $this->isPageAllowed(AdminPageRegistry::crudKey(AuftragCrudController::class))
            ? $this->auftragRepository->count(['status' => Auftrag::STATUS_RECEIVED]) + $this->auftragRepository->count(['status' => Auftrag::STATUS_IN_PROGRESS]) + $this->auftragRepository->count(['status' => Auftrag::STATUS_PARKED])
            : 0;

        $unreadInboxEmailCount = $this->isPageAllowed(AdminPageRegistry::routeKey('admin_support_email'))
            ? $this->supportEmailMessageRepository->count([
                'folder' => SupportEmailMessage::FOLDER_INBOX,
                'isRead' => false,
            ])
            : 0;

        $openTicketCount = $this->isPageAllowed(AdminPageRegistry::routeKey('admin_support_tickets'))
            ? $this->supportTicketRepository->count(['status' => SupportTicket::STATUS_OPEN])
            : 0;

        $pendingCreditPrepaymentCount = 0;
        if ($this->isPageAllowed(AdminPageRegistry::crudKey(KundeCreditTransactionCrudController::class))) {
            $pendingCreditPrepaymentCount = (int) $this->connection->fetchOne(
                "SELECT COUNT(*)
                 FROM kunde_credit_transaction
                 WHERE payment_method = :paymentMethod
                   AND (description LIKE :pendingA OR description LIKE :pendingB)",
                [
                    'paymentMethod' => 'vorkasse',
                    'pendingA' => '%Status: pending%',
                    'pendingB' => '%Vorkasse Anfrage:%',
                ]
            );
        }

        return $this->json([
            'orders' => [
                'count' => max(0, (int) $orderCount),
            ],
            'communication' => [
                'count' => max(0, (int) $unreadInboxEmailCount) + max(0, (int) $openTicketCount),
                'emails' => max(0, (int) $unreadInboxEmailCount),
                'tickets' => max(0, (int) $openTicketCount),
            ],
            'credits' => [
                'count' => max(0, (int) $pendingCreditPrepaymentCount),
            ],
        ]);
    }

    #[Route('/admin/support/email/fetch', name: 'admin_support_email_fetch', methods: ['POST'])]
    public function supportEmailFetch(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_email_fetch', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_email');
        }

        $accountId = (int) $request->request->get('accountId', 0);
        $limit = (int) $request->request->get('limit', 100);
        $limit = max(1, min(500, $limit));

        $accounts = [];
        if ($accountId > 0) {
            $account = $this->emailKontoRepository->find($accountId);
            if ($account instanceof EmailKonto) {
                $accounts[] = $account;
            }
        } else {
            $accounts = $this->emailKontoRepository->findBy(
                ['active' => true, 'fetchEnabled' => true],
                ['sorting' => 'ASC', 'name' => 'ASC']
            );
        }

        if ([] === $accounts) {
            $this->addFlash('warning', 'Kein aktives E-Mail-Konto mit aktiviertem Abruf gefunden.');

            return $this->redirectToRoute('admin_support_email', [
                'account' => $accountId,
                'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', SupportEmailMessage::FOLDER_INBOX), true),
                'q' => trim((string) $request->request->get('q', '')),
                'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
                'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
            ]);
        }

        $sumFetched = 0;
        $sumCreated = 0;
        $sumUpdated = 0;
        $sumFailed = 0;
        $errorMessages = [];

        foreach ($accounts as $account) {
            if (!$account instanceof EmailKonto) {
                continue;
            }

            $result = $this->supportEmailFetchService->fetchAccount($account, $limit);

            $sumFetched += (int) ($result['fetched'] ?? 0);
            $sumCreated += (int) ($result['created'] ?? 0);
            $sumUpdated += (int) ($result['updated'] ?? 0);
            $sumFailed += (int) ($result['failed'] ?? 0);

            $error = trim((string) ($result['error'] ?? ''));
            if ('' !== $error) {
                $label = trim((string) ($result['accountLabel'] ?? ('Konto #' . (int) ($result['accountId'] ?? 0))));
                $errorMessages[] = sprintf('%s: %s', $label, $error);
            }
        }

        if ([] === $errorMessages) {
            $this->addFlash(
                'success',
                sprintf(
                    'Abruf abgeschlossen. Gelesen: %d, Neu: %d, Aktualisiert: %d, Fehlgeschlagen: %d.',
                    $sumFetched,
                    $sumCreated,
                    $sumUpdated,
                    $sumFailed
                )
            );
        } else {
            $this->addFlash('warning', implode(' | ', $errorMessages));
            $this->addFlash(
                'info',
                sprintf(
                    'Teilweiser Abruf. Gelesen: %d, Neu: %d, Aktualisiert: %d, Fehlgeschlagen: %d.',
                    $sumFetched,
                    $sumCreated,
                    $sumUpdated,
                    $sumFailed
                )
            );
        }

        return $this->redirectToRoute('admin_support_email', [
            'account' => $accountId,
            'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', SupportEmailMessage::FOLDER_INBOX), true),
            'q' => trim((string) $request->request->get('q', '')),
            'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
            'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
            'message' => (int) $request->request->get('selectedMessage', 0),
        ]);
    }

    #[Route('/admin/support/email/compose', name: 'admin_support_email_compose', methods: ['POST'])]
    public function supportEmailCompose(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_email_compose', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_email');
        }

        $accountId = (int) $request->request->get('accountId', 0);
        $account = $accountId > 0 ? $this->emailKontoRepository->find($accountId) : null;
        if (!$account instanceof EmailKonto) {
            $this->addFlash('warning', 'Bitte ein gueltiges E-Mail-Konto auswaehlen.');

            return $this->redirectToRoute('admin_support_email');
        }

        $toEmail = trim((string) $request->request->get('toEmail', ''));
        $subject = trim((string) $request->request->get('subject', ''));
        $body = trim((string) $request->request->get('body', ''));
        $sendNow = '1' === (string) $request->request->get('sendNow', '0');

        if ('' === $toEmail && $sendNow) {
            $this->addFlash('warning', 'Bitte mindestens einen Empfaenger angeben.');

            return $this->redirectToRoute('admin_support_email', ['account' => $accountId]);
        }

        $now = new \DateTimeImmutable();
        $message = (new SupportEmailMessage())
            ->setEmailKonto($account)
            ->setFolder(SupportEmailMessage::FOLDER_DRAFT)
            ->setSubject('' !== $subject ? $subject : '(Ohne Betreff)')
            ->setFromName($account->getFromName() ?: $account->getName())
            ->setFromEmail($account->getEmailAddress())
            ->setToEmail($toEmail)
            ->setCcEmail(trim((string) $request->request->get('ccEmail', '')))
            ->setBccEmail(trim((string) $request->request->get('bccEmail', '')))
            ->setBodyText($body)
            ->setIsRead(true)
            ->setIsDraft(true)
            ->setUpdatedAt($now);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $targetFolder = SupportEmailMessage::FOLDER_DRAFT;
        if ($sendNow) {
            try {
                $this->supportEmailSendService->send($message, $account);

                $message
                    ->setFolder(SupportEmailMessage::FOLDER_SENT)
                    ->setIsDraft(false)
                    ->setSentAt(new \DateTimeImmutable())
                    ->setUpdatedAt(new \DateTimeImmutable());

                $this->entityManager->flush();
                $targetFolder = SupportEmailMessage::FOLDER_SENT;
                $this->addFlash('success', 'E-Mail wurde erfolgreich versendet.');
            } catch (\Throwable $throwable) {
                $message
                    ->setFolder(SupportEmailMessage::FOLDER_DRAFT)
                    ->setIsDraft(true)
                    ->setSentAt(null)
                    ->setUpdatedAt(new \DateTimeImmutable());

                $this->entityManager->flush();
                $this->addFlash('danger', sprintf('Versand fehlgeschlagen: %s', trim($throwable->getMessage())));
            }
        } else {
            $this->addFlash('success', 'Entwurf gespeichert.');
        }

        return $this->redirectToRoute('admin_support_email', [
            'account' => $accountId,
            'folder' => $targetFolder,
            'message' => $message->getId(),
        ]);
    }

    #[Route('/admin/support/email/update', name: 'admin_support_email_update', methods: ['POST'])]
    public function supportEmailUpdate(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_email_update', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_email');
        }

        $messageId = (int) $request->request->get('messageId', 0);
        $message = $this->supportEmailMessageRepository->find($messageId);
        if (!$message instanceof SupportEmailMessage) {
            $this->addFlash('danger', 'E-Mail nicht gefunden.');

            return $this->redirectToRoute('admin_support_email');
        }

        $folder = $this->normalizeSupportEmailFolder((string) $request->request->get('targetFolder', $message->getFolder()), false);
        $labelsRaw = trim((string) $request->request->get('labels', ''));
        $labels = '' === $labelsRaw ? null : array_map('trim', explode(',', $labelsRaw));

        $message
            ->setSubject((string) $request->request->get('subject', $message->getSubject()))
            ->setFolder($folder)
            ->setToEmail((string) $request->request->get('toEmail', $message->getToEmail()))
            ->setCcEmail((string) $request->request->get('ccEmail', $message->getCcEmail()))
            ->setBccEmail((string) $request->request->get('bccEmail', $message->getBccEmail()))
            ->setBodyText((string) $request->request->get('body', $message->getBodyText()))
            ->setIsRead('1' === (string) $request->request->get('isRead', '0'))
            ->setIsStarred('1' === (string) $request->request->get('isStarred', '0'))
            ->setLabels($labels)
            ->setUpdatedAt(new \DateTimeImmutable());

        if (SupportEmailMessage::FOLDER_SENT === $folder && null === $message->getSentAt()) {
            $message->setSentAt(new \DateTimeImmutable());
        }
        $message->setIsDraft(SupportEmailMessage::FOLDER_DRAFT === $folder);

        $this->entityManager->flush();
        $this->addFlash('success', 'E-Mail gespeichert.');

        return $this->redirectToRoute('admin_support_email', [
            'account' => (int) ($request->request->get('account', 0)),
            'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', $folder), true),
            'q' => trim((string) $request->request->get('q', '')),
            'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
            'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
            'message' => $message->getId(),
        ]);
    }

    #[Route('/admin/support/email/reply', name: 'admin_support_email_reply', methods: ['POST'])]
    public function supportEmailReply(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_email_reply', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_email');
        }

        $messageId = (int) $request->request->get('messageId', 0);
        $originalMessage = $this->supportEmailMessageRepository->find($messageId);
        if (!$originalMessage instanceof SupportEmailMessage) {
            $this->addFlash('danger', 'Originalnachricht nicht gefunden.');

            return $this->redirectToRoute('admin_support_email');
        }

        $replyBody = trim((string) $request->request->get('replyBody', ''));
        if ('' === $replyBody) {
            $this->addFlash('warning', 'Bitte eine Antwort eingeben.');

            return $this->redirectToRoute('admin_support_email', [
                'message' => $originalMessage->getId(),
                'account' => $originalMessage->getEmailKonto()?->getId(),
                'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', $originalMessage->getFolder()), true),
            ]);
        }

        $accountId = (int) $request->request->get('accountId', 0);
        $account = $accountId > 0 ? $this->emailKontoRepository->find($accountId) : $originalMessage->getEmailKonto();
        if (!$account instanceof EmailKonto) {
            $this->addFlash('warning', 'Kein gültiges E-Mail-Konto fuer den Versand ausgewaehlt.');

            return $this->redirectToRoute('admin_support_email', [
                'message' => $originalMessage->getId(),
                'account' => $accountId,
            ]);
        }

        $subjectInput = trim((string) $request->request->get('subject', ''));
        $defaultReplySubject = preg_match('/^\s*re:/i', $originalMessage->getSubject())
            ? $originalMessage->getSubject()
            : 'Re: ' . $originalMessage->getSubject();
        $replySubject = '' !== $subjectInput ? $subjectInput : $defaultReplySubject;

        $recipientEmail = trim((string) $request->request->get('toEmail', $originalMessage->getFromEmail() ?? ''));
        if ('' === $recipientEmail) {
            $this->addFlash('warning', 'Kein Empfänger vorhanden.');

            return $this->redirectToRoute('admin_support_email', [
                'message' => $originalMessage->getId(),
                'account' => $accountId,
            ]);
        }

        $now = new \DateTimeImmutable();
        $replyMessage = (new SupportEmailMessage())
            ->setEmailKonto($account)
            ->setFolder(SupportEmailMessage::FOLDER_DRAFT)
            ->setSubject($replySubject)
            ->setFromName($account->getFromName() ?: $account->getName())
            ->setFromEmail($account->getEmailAddress())
            ->setToEmail($recipientEmail)
            ->setBodyText($replyBody)
            ->setIsRead(true)
            ->setIsDraft(true)
            ->setInReplyToMessageId($originalMessage->getExternalMessageId() ?: (string) ($originalMessage->getId() ?? ''))
            ->setUpdatedAt($now);

        $originalMessage
            ->setIsRead(true)
            ->setUpdatedAt($now);

        $this->entityManager->persist($replyMessage);
        $this->entityManager->flush();

        $targetFolder = SupportEmailMessage::FOLDER_DRAFT;
        try {
            $this->supportEmailSendService->send($replyMessage, $account);

            $replyMessage
                ->setFolder(SupportEmailMessage::FOLDER_SENT)
                ->setIsDraft(false)
                ->setSentAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->addFlash('success', 'Antwort wurde erfolgreich versendet.');
            $targetFolder = SupportEmailMessage::FOLDER_SENT;
        } catch (\Throwable $throwable) {
            $replyMessage
                ->setFolder(SupportEmailMessage::FOLDER_DRAFT)
                ->setIsDraft(true)
                ->setSentAt(null)
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->addFlash('danger', sprintf('Antwort konnte nicht versendet werden: %s', trim($throwable->getMessage())));
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('admin_support_email', [
            'account' => (int) ($account->getId() ?? 0),
            'folder' => $targetFolder,
            'q' => trim((string) $request->request->get('q', '')),
            'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
            'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
            'message' => $replyMessage->getId(),
        ]);
    }

    #[Route('/admin/support/email/toggle-read', name: 'admin_support_email_toggle_read', methods: ['POST'])]
    public function supportEmailToggleRead(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_email_toggle_read', $csrfToken)) {
            $this->addFlash('danger', 'Ungültiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_email');
        }

        $messageId = (int) $request->request->get('messageId', 0);
        $message = $this->supportEmailMessageRepository->find($messageId);
        if ($message instanceof SupportEmailMessage) {
            $message
                ->setIsRead(!$message->isRead())
                ->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('admin_support_email', [
            'account' => (int) $request->request->get('account', 0),
            'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', SupportEmailMessage::FOLDER_INBOX), true),
            'q' => trim((string) $request->request->get('q', '')),
            'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
            'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
            'message' => (int) $request->request->get('selectedMessage', 0),
        ]);
    }

    #[Route('/admin/support/email/toggle-star', name: 'admin_support_email_toggle_star', methods: ['POST'])]
    public function supportEmailToggleStar(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_email_toggle_star', $csrfToken)) {
            $this->addFlash('danger', 'Ungültiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_email');
        }

        $messageId = (int) $request->request->get('messageId', 0);
        $message = $this->supportEmailMessageRepository->find($messageId);
        if ($message instanceof SupportEmailMessage) {
            $message
                ->setIsStarred(!$message->isStarred())
                ->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('admin_support_email', [
            'account' => (int) $request->request->get('account', 0),
            'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', SupportEmailMessage::FOLDER_INBOX), true),
            'q' => trim((string) $request->request->get('q', '')),
            'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
            'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
            'message' => (int) $request->request->get('selectedMessage', 0),
        ]);
    }

    #[Route('/admin/support/email/bulk', name: 'admin_support_email_bulk', methods: ['POST'])]
    public function supportEmailBulk(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_support_email_bulk', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_support_email');
        }

        $rawMessageIds = $request->request->all('messageIds');
        if (!is_array($rawMessageIds)) {
            $rawMessageIds = [];
        }

        $messageIds = array_values(array_unique(array_filter(
            array_map(static fn (mixed $value): int => (int) $value, $rawMessageIds),
            static fn (int $id): bool => $id > 0
        )));
        $bulkAction = strtolower(trim((string) $request->request->get('bulkAction', '')));

        if ([] === $messageIds) {
            $this->addFlash('warning', 'Bitte mindestens eine E-Mail auswählen.');

            return $this->redirectToRoute('admin_support_email', [
                'account' => (int) $request->request->get('account', 0),
                'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', SupportEmailMessage::FOLDER_INBOX), true),
                'q' => trim((string) $request->request->get('q', '')),
                'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
                'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
                'message' => (int) $request->request->get('selectedMessage', 0),
            ]);
        }

        $validActions = ['mark_read', 'mark_unread', 'star', 'unstar', 'delete'];
        if (!in_array($bulkAction, $validActions, true)) {
            $this->addFlash('warning', 'Bitte eine gültige Aktion auswaehlen.');

            return $this->redirectToRoute('admin_support_email', [
                'account' => (int) $request->request->get('account', 0),
                'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', SupportEmailMessage::FOLDER_INBOX), true),
                'q' => trim((string) $request->request->get('q', '')),
                'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
                'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
                'message' => (int) $request->request->get('selectedMessage', 0),
            ]);
        }

        $messages = $this->supportEmailMessageRepository->createQueryBuilder('m')
            ->andWhere('m.id IN (:ids)')
            ->setParameter('ids', $messageIds)
            ->getQuery()
            ->getResult();

        if ([] === $messages) {
            $this->addFlash('warning', 'Die ausgewählten E-Mails wurden nicht gefunden.');

            return $this->redirectToRoute('admin_support_email', [
                'account' => (int) $request->request->get('account', 0),
                'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', SupportEmailMessage::FOLDER_INBOX), true),
                'q' => trim((string) $request->request->get('q', '')),
                'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
                'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
                'message' => (int) $request->request->get('selectedMessage', 0),
            ]);
        }

        $now = new \DateTimeImmutable();
        $updatedCount = 0;
        $deletedCount = 0;

        foreach ($messages as $message) {
            if (!$message instanceof SupportEmailMessage) {
                continue;
            }

            switch ($bulkAction) {
                case 'mark_read':
                    if (!$message->isRead()) {
                        $message
                            ->setIsRead(true)
                            ->setUpdatedAt($now);
                        ++$updatedCount;
                    }
                    break;

                case 'mark_unread':
                    if ($message->isRead()) {
                        $message
                            ->setIsRead(false)
                            ->setUpdatedAt($now);
                        ++$updatedCount;
                    }
                    break;

                case 'star':
                    if (!$message->isStarred()) {
                        $message
                            ->setIsStarred(true)
                            ->setUpdatedAt($now);
                        ++$updatedCount;
                    }
                    break;

                case 'unstar':
                    if ($message->isStarred()) {
                        $message
                            ->setIsStarred(false)
                            ->setUpdatedAt($now);
                        ++$updatedCount;
                    }
                    break;

                case 'delete':
                    if (SupportEmailMessage::FOLDER_TRASH === $message->getFolder()) {
                        $this->entityManager->remove($message);
                        ++$deletedCount;
                    } else {
                        $message
                            ->setFolder(SupportEmailMessage::FOLDER_TRASH)
                            ->setIsDraft(false)
                            ->setUpdatedAt($now);
                        ++$updatedCount;
                    }
                    break;
            }
        }

        if ($updatedCount > 0 || $deletedCount > 0) {
            $this->entityManager->flush();
        }

        $bulkLabel = match ($bulkAction) {
            'mark_read' => 'Als gelesen markiert',
            'mark_unread' => 'Als ungelesen markiert',
            'star' => 'Markiert',
            'unstar' => 'Markierung entfernt',
            'delete' => 'In den Papierkorb verschoben / geloescht',
            default => 'Aktion ausgefuehrt',
        };

        $this->addFlash(
            'success',
            sprintf(
                '%s. Aktualisiert: %d, Endgueltig geloescht: %d.',
                $bulkLabel,
                $updatedCount,
                $deletedCount
            )
        );

        return $this->redirectToRoute('admin_support_email', [
            'account' => (int) $request->request->get('account', 0),
            'folder' => $this->normalizeSupportEmailFolder((string) $request->request->get('folder', SupportEmailMessage::FOLDER_INBOX), true),
            'q' => trim((string) $request->request->get('q', '')),
            'unread' => '1' === (string) $request->request->get('unread', '0') ? 1 : 0,
            'starred' => '1' === (string) $request->request->get('starred', '0') ? 1 : 0,
        ]);
    }

    private function normalizeSupportEmailFolder(string $folder, bool $allowAll): string
    {
        $normalized = strtolower(trim($folder));
        if ($allowAll && 'all' === $normalized) {
            return 'all';
        }

        $validFolders = array_values(SupportEmailMessage::getFolderChoices());

        return in_array($normalized, $validFolders, true) ? $normalized : SupportEmailMessage::FOLDER_INBOX;
    }

    #[AdminRoute(path: '/support/newsletter', name: 'support_newsletter', options: ['methods' => ['GET']])]
    public function supportNewsletter(): Response
    {
        return $this->render('admin/support/newsletter.html.twig');
    }

    #[AdminRoute(path: '/ecommerce/statistik', name: 'shop_statistics', options: ['methods' => ['GET']])]
    public function shopStatistics(Request $request): Response
    {
        $embed = $request->query->getBoolean('embed');

        $productRepo = $this->entityManager->getRepository(ShopProduct::class);
        $categoryRepo = $this->entityManager->getRepository(ShopCategory::class);
        $orderRepo = $this->entityManager->getRepository(ShopOrder::class);

        $productsTotal = $productRepo->count(['deleted' => false]);
        $productsActive = $productRepo->count(['deleted' => false, 'hidden' => false, 'isActive' => true]);
        $categoriesTotal = $categoryRepo->count(['deleted' => false]);
        $ordersTotal = $orderRepo->count(['deleted' => false]);

        $todayStart = (new \DateTimeImmutable('today'))->setTime(0, 0);
        $fromDate = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0)->modify('-11 months');
        $fromTimestamp = $fromDate->getTimestamp();
        $last30Timestamp = $todayStart->modify('-29 days')->getTimestamp();

        $monthKeys = [];
        $monthLabels = [];
        $monthOrderCounts = [];
        $monthRevenue = [];
        $cursor = $fromDate;
        for ($i = 0; $i < 12; ++$i) {
            $key = $cursor->format('Y-m');
            $monthKeys[] = $key;
            $monthLabels[] = $cursor->format('m/Y');
            $monthOrderCounts[$key] = 0;
            $monthRevenue[$key] = 0.0;
            $cursor = $cursor->modify('+1 month');
        }

        $ordersLast30 = 0;
        $revenueLast30 = 0.0;

        $rows = $this->connection->fetchAllAssociative(
            'SELECT created_at, total
             FROM tx_tuningtoolshop_domain_model_order
             WHERE deleted = 0 AND hidden = 0 AND created_at >= :fromDate',
            ['fromDate' => $fromTimestamp]
        );

        $timezone = new \DateTimeZone(date_default_timezone_get());

        foreach ($rows as $row) {
            $createdAt = (int) ($row['created_at'] ?? 0);
            if ($createdAt <= 0) {
                continue;
            }

            $total = is_numeric($row['total'] ?? null) ? (float) $row['total'] : 0.0;
            $createdAtDate = (new \DateTimeImmutable('@' . $createdAt))->setTimezone($timezone);
            $monthKey = $createdAtDate->format('Y-m');

            if (isset($monthOrderCounts[$monthKey])) {
                ++$monthOrderCounts[$monthKey];
                $monthRevenue[$monthKey] += $total;
            }

            if ($createdAt >= $last30Timestamp) {
                ++$ordersLast30;
                $revenueLast30 += $total;
            }
        }

        $monthlyOrderValues = [];
        $monthlyRevenueValues = [];
        foreach ($monthKeys as $key) {
            $monthlyOrderValues[] = $monthOrderCounts[$key];
            $monthlyRevenueValues[] = round($monthRevenue[$key], 2);
        }

        $methodRows = $this->connection->fetchAllAssociative(
            'SELECT pm.title AS label, COUNT(o.uid) AS total
             FROM tx_tuningtoolshop_domain_model_order o
             LEFT JOIN tx_tuningtoolshop_domain_model_paymentmethod pm ON pm.uid = o.payment_method
             WHERE o.deleted = 0 AND o.hidden = 0 AND o.created_at >= :fromDate
             GROUP BY pm.title
             ORDER BY total DESC',
            ['fromDate' => $fromTimestamp]
        );

        $methodLabels = [];
        $methodValues = [];
        foreach ($methodRows as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            if ('' === $label) {
                $label = 'Unbekannt';
            }

            $methodLabels[] = $label;
            $methodValues[] = (int) ($row['total'] ?? 0);
        }
        if ([] === $methodLabels) {
            $methodLabels = ['Keine Daten'];
            $methodValues = [0];
        }

        $ordersChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $ordersChart->setData([
            'labels' => $monthLabels,
            'datasets' => [[
                'label' => 'Bestellungen',
                'data' => $monthlyOrderValues,
                'backgroundColor' => '#111931',
            ]],
        ]);
        $ordersChart->setOptions([
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $revenueChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $revenueChart->setData([
            'labels' => $monthLabels,
            'datasets' => [[
                'label' => 'Umsatz',
                'data' => $monthlyRevenueValues,
                'borderColor' => '#198754',
                'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
                'tension' => 0.3,
                'fill' => true,
            ]],
        ]);
        $revenueChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $methodChart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $methodChart->setData([
            'labels' => $methodLabels,
            'datasets' => [[
                'data' => $methodValues,
                'backgroundColor' => ['#111931', '#bbcf4e', '#828c4b', '#6f42c1', '#adb5bd'],
            ]],
        ]);
        $methodChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'maintainAspectRatio' => false,
        ]);

        $kpis = [
            ['label' => 'Produkte gesamt', 'value' => $productsTotal, 'format' => 'int'],
            ['label' => 'Produkte aktiv', 'value' => $productsActive, 'format' => 'int'],
            ['label' => 'Kategorien', 'value' => $categoriesTotal, 'format' => 'int'],
            ['label' => 'Bestellungen gesamt', 'value' => $ordersTotal, 'format' => 'int'],
            ['label' => 'Bestellungen 30 Tage', 'value' => $ordersLast30, 'format' => 'int'],
            ['label' => 'Umsatz 30 Tage', 'value' => $revenueLast30, 'format' => 'money'],
        ];

        $template = $embed ? 'admin/statistics/_ecommerce.html.twig' : 'admin/ecommerce_statistics.html.twig';

        return $this->render($template, [
            'kpis' => $kpis,
            'ordersChart' => $ordersChart,
            'revenueChart' => $revenueChart,
            'methodChart' => $methodChart,
            'embedded' => $embed,
        ]);
    }

    #[AdminRoute(path: '/statistiken/umsatz', name: 'revenue_statistics', options: ['methods' => ['GET']])]
    public function revenueStatistics(Request $request): Response
    {
        $embed = $request->query->getBoolean('embed');

        $period = strtolower(trim((string) $request->query->get('period', 'month')));
        if (!in_array($period, ['day', 'month', 'year'], true)) {
            $period = 'month';
        }

        $bucketKeys = [];
        $bucketLabels = [];
        $bucketStarts = [];
        $bucketEnds = [];

        $timezone = new \DateTimeZone(date_default_timezone_get());

        if ('day' === $period) {
            $rangeStart = (new \DateTimeImmutable('today'))->setTime(0, 0);
            $rangeEnd = $rangeStart->setTime(23, 59, 59);
            for ($hour = 0; $hour < 24; ++$hour) {
                $label = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);
                $bucketKeys[] = $label;
                $bucketLabels[] = $label;
            }
        } elseif ('year' === $period) {
            $year = (int) (new \DateTimeImmutable())->format('Y');
            $rangeStart = (new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $year)));
            $rangeEnd = (new \DateTimeImmutable(sprintf('%d-12-31 23:59:59', $year)));
            for ($month = 1; $month <= 12; ++$month) {
                $date = new \DateTimeImmutable(sprintf('%d-%02d-01', $year, $month));
                $key = $date->format('Y-m');
                $bucketKeys[] = $key;
                $bucketLabels[] = $date->format('m/Y');
                $bucketStarts[$key] = $date->setTime(0, 0);
                $bucketEnds[$key] = $date->modify('last day of this month')->setTime(23, 59, 59);
            }
        } else {
            $rangeStart = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
            $rangeEnd = $rangeStart->modify('last day of this month')->setTime(23, 59, 59);
            $daysInMonth = (int) $rangeStart->format('t');
            for ($day = 1; $day <= $daysInMonth; ++$day) {
                $date = $rangeStart->modify('+' . ($day - 1) . ' days');
                $key = $date->format('Y-m-d');
                $bucketKeys[] = $key;
                $bucketLabels[] = $date->format('d.m');
                $bucketStarts[$key] = $date->setTime(0, 0);
                $bucketEnds[$key] = $date->setTime(23, 59, 59);
            }
        }

        $defaultVatRate = 19.0;

        $abosForRevenue = $this->aboRepository->createQueryBuilder('a')
            ->leftJoin('a.preisstaffel', 'ps')
            ->addSelect('ps')
            ->where('a.startDatum <= :rangeEnd')
            ->andWhere('a.endDatum IS NULL OR a.endDatum >= :rangeStart')
            ->setParameter('rangeStart', $rangeStart)
            ->setParameter('rangeEnd', $rangeEnd)
            ->getQuery()
            ->getResult();

        $aboRevenueMap = array_fill_keys($bucketKeys, 0.0);
        foreach ($abosForRevenue as $abo) {
            $preisstaffel = $abo->getPreisstaffel();
            if (null === $preisstaffel) {
                continue;
            }

            if ('jaehrlich' === $abo->getZahlweise()) {
                $monatlicherPreis = null !== $preisstaffel->getPreisJaehrlich()
                    ? (float) $preisstaffel->getPreisJaehrlich()
                    : (float) ($preisstaffel->getPreisMonatlich() ?? 0);
            } else {
                $monatlicherPreis = null !== $preisstaffel->getPreisMonatlich()
                    ? (float) $preisstaffel->getPreisMonatlich()
                    : (float) ($preisstaffel->getPreisJaehrlich() ?? 0);
            }

            $start = $abo->getStartDatum();
            $ende = $abo->getEndDatum();

            if ('year' === $period) {
                foreach ($bucketKeys as $monthKey) {
                    $monthStart = $bucketStarts[$monthKey];
                    $monthEnd = $bucketEnds[$monthKey];

                    if ($start > $monthEnd) {
                        continue;
                    }
                    if (null !== $ende && $ende < $monthStart) {
                        continue;
                    }

                    $aboRevenueMap[$monthKey] += $monatlicherPreis;
                }
            } elseif ('day' === $period) {
                $daysInMonth = (int) $rangeStart->format('t');
                $dailyShare = $daysInMonth > 0 ? ($monatlicherPreis / $daysInMonth) : 0.0;
                $hourlyShare = $dailyShare / 24;

                if ($start > $rangeEnd) {
                    continue;
                }
                if (null !== $ende && $ende < $rangeStart) {
                    continue;
                }

                foreach ($bucketKeys as $hourKey) {
                    $aboRevenueMap[$hourKey] += $hourlyShare;
                }
            } else {
                $daysInMonth = (int) $rangeStart->format('t');
                $dailyShare = $daysInMonth > 0 ? ($monatlicherPreis / $daysInMonth) : 0.0;

                foreach ($bucketKeys as $dayKey) {
                    $dayStart = $bucketStarts[$dayKey];
                    $dayEnd = $bucketEnds[$dayKey];

                    if ($start > $dayEnd) {
                        continue;
                    }
                    if (null !== $ende && $ende < $dayStart) {
                        continue;
                    }

                    $aboRevenueMap[$dayKey] += $dailyShare;
                }
            }
        }

        $aboRevenueValues = array_map(
            static fn (string $key): float => $aboRevenueMap[$key] ?? 0.0,
            $bucketKeys
        );
        $aboRevenueGrossValues = array_map(
            static fn (float $value): float => $value * (1 + ($defaultVatRate / 100)),
            $aboRevenueValues
        );

        $creditRevenueNetMap = array_fill_keys($bucketKeys, 0.0);
        $creditRevenueGrossMap = array_fill_keys($bucketKeys, 0.0);
        $creditCount = 0;
        $creditRows = $this->connection->fetchAllAssociative(
            'SELECT created_at, price_net, price_gross, tax_rate
             FROM kunde_credit_transaction t
             WHERE t.transaction_type = :type
               AND (t.price_net IS NOT NULL OR t.price_gross IS NOT NULL)
               AND t.created_at >= :startDate
               AND t.created_at <= :endDate
               AND NOT EXISTS (
                   SELECT 1 FROM kunde_credit_transaction r WHERE r.reversal_of_id = t.id
               )
             ORDER BY t.created_at ASC',
            [
                'type' => KundeCreditTransaction::TYPE_CUSTOMER_TOPUP,
                'startDate' => $rangeStart->format('Y-m-d H:i:s'),
                'endDate' => $rangeEnd->format('Y-m-d H:i:s'),
            ]
        );

        foreach ($creditRows as $row) {
            $createdAt = new \DateTimeImmutable((string) $row['created_at']);
            $bucketKey = match ($period) {
                'day' => $createdAt->format('H'),
                'year' => $createdAt->format('Y-m'),
                default => $createdAt->format('Y-m-d'),
            };
            if (!array_key_exists($bucketKey, $creditRevenueNetMap)) {
                continue;
            }

            $net = isset($row['price_net']) ? (float) $row['price_net'] : 0.0;
            $gross = isset($row['price_gross']) ? (float) $row['price_gross'] : 0.0;
            $taxRate = isset($row['tax_rate']) ? (float) $row['tax_rate'] : $defaultVatRate;
            if ($taxRate < 0) {
                $taxRate = $defaultVatRate;
            }

            if ($net <= 0 && $gross > 0) {
                $divider = 1 + ($taxRate / 100);
                $net = $divider > 0 ? $gross / $divider : $gross;
            }
            if ($gross <= 0 && $net > 0) {
                $gross = $net * (1 + ($taxRate / 100));
            }

            if ($net <= 0 && $gross <= 0) {
                continue;
            }

            $creditRevenueNetMap[$bucketKey] += $net;
            $creditRevenueGrossMap[$bucketKey] += $gross;
            ++$creditCount;
        }

        $creditRevenueValues = array_map(
            static fn (string $key): float => $creditRevenueNetMap[$key] ?? 0.0,
            $bucketKeys
        );
        $creditRevenueGrossValues = array_map(
            static fn (string $key): float => $creditRevenueGrossMap[$key] ?? 0.0,
            $bucketKeys
        );

        $ecommerceRevenueNetMap = array_fill_keys($bucketKeys, 0.0);
        $ecommerceRevenueGrossMap = array_fill_keys($bucketKeys, 0.0);
        $ecommerceCount = 0;
        $ecommerceRows = $this->connection->fetchAllAssociative(
            'SELECT created_at, total, tax_amount
             FROM tx_tuningtoolshop_domain_model_order
             WHERE deleted = 0 AND hidden = 0
               AND created_at >= :startDate
               AND created_at <= :endDate',
            [
                'startDate' => $rangeStart->getTimestamp(),
                'endDate' => $rangeEnd->getTimestamp(),
            ]
        );

        foreach ($ecommerceRows as $row) {
            $createdAt = (int) ($row['created_at'] ?? 0);
            if ($createdAt <= 0) {
                continue;
            }

            $createdAtDate = (new \DateTimeImmutable('@' . $createdAt))->setTimezone($timezone);
            $bucketKey = match ($period) {
                'day' => $createdAtDate->format('H'),
                'year' => $createdAtDate->format('Y-m'),
                default => $createdAtDate->format('Y-m-d'),
            };
            if (!array_key_exists($bucketKey, $ecommerceRevenueNetMap)) {
                continue;
            }

            $total = is_numeric($row['total'] ?? null) ? (float) $row['total'] : 0.0;
            $taxAmount = is_numeric($row['tax_amount'] ?? null) ? (float) $row['tax_amount'] : 0.0;
            if ($total <= 0 && $taxAmount <= 0) {
                continue;
            }

            $net = $total;
            $gross = $total;
            if ($taxAmount > 0) {
                if ($total >= $taxAmount) {
                    $net = $total - $taxAmount;
                    $gross = $total;
                } else {
                    $net = $total;
                    $gross = $total + $taxAmount;
                }
            }

            if ($net <= 0 && $gross <= 0) {
                continue;
            }

            $ecommerceRevenueNetMap[$bucketKey] += $net;
            $ecommerceRevenueGrossMap[$bucketKey] += $gross;
            ++$ecommerceCount;
        }

        $ecommerceRevenueValues = array_map(
            static fn (string $key): float => $ecommerceRevenueNetMap[$key] ?? 0.0,
            $bucketKeys
        );
        $ecommerceRevenueGrossValues = array_map(
            static fn (string $key): float => $ecommerceRevenueGrossMap[$key] ?? 0.0,
            $bucketKeys
        );

        $combinedRevenueValues = [];
        foreach ($bucketKeys as $index => $bucketKey) {
            $combinedRevenueValues[] = ($aboRevenueValues[$index] ?? 0.0)
                + ($creditRevenueValues[$index] ?? 0.0)
                + ($ecommerceRevenueValues[$index] ?? 0.0);
        }
        $combinedRevenueGrossValues = [];
        foreach ($bucketKeys as $index => $bucketKey) {
            $combinedRevenueGrossValues[] = ($aboRevenueGrossValues[$index] ?? 0.0)
                + ($creditRevenueGrossValues[$index] ?? 0.0)
                + ($ecommerceRevenueGrossValues[$index] ?? 0.0);
        }

        $revenueChartNet = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $revenueChartNet->setData([
            'labels' => $bucketLabels,
            'datasets' => [
                [
                    'label' => 'Abo-Umsatz (EUR)',
                    'data' => $aboRevenueValues,
                    'backgroundColor' => '#20c997',
                    'stack' => 'revenue',
                ],
                [
                    'label' => 'Credit-Umsatz (EUR)',
                    'data' => $creditRevenueValues,
                    'backgroundColor' => '#111931',
                    'stack' => 'revenue',
                ],
                [
                    'label' => 'eCommerce-Umsatz (EUR)',
                    'data' => $ecommerceRevenueValues,
                    'backgroundColor' => '#0d6efd',
                    'stack' => 'revenue',
                ],
                [
                    'type' => 'line',
                    'label' => 'Gesamt (EUR)',
                    'data' => $combinedRevenueValues,
                    'borderColor' => '#bbcf4e',
                    'backgroundColor' => 'rgba(253, 126, 20, 0.15)',
                    'tension' => 0.3,
                    'stack' => 'total',
                ],
            ],
        ]);
        $revenueChartNet->setOptions([
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['beginAtZero' => true, 'stacked' => true],
            ],
            'maintainAspectRatio' => false,
        ]);

        $revenueChartGross = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $revenueChartGross->setData([
            'labels' => $bucketLabels,
            'datasets' => [
                [
                    'label' => 'Abo-Umsatz (Brutto)',
                    'data' => $aboRevenueGrossValues,
                    'backgroundColor' => '#20c997',
                    'stack' => 'revenue',
                ],
                [
                    'label' => 'Credit-Umsatz (Brutto)',
                    'data' => $creditRevenueGrossValues,
                    'backgroundColor' => '#111931',
                    'stack' => 'revenue',
                ],
                [
                    'label' => 'eCommerce-Umsatz (Brutto)',
                    'data' => $ecommerceRevenueGrossValues,
                    'backgroundColor' => '#0d6efd',
                    'stack' => 'revenue',
                ],
                [
                    'type' => 'line',
                    'label' => 'Gesamt (Brutto)',
                    'data' => $combinedRevenueGrossValues,
                    'borderColor' => '#bbcf4e',
                    'backgroundColor' => 'rgba(253, 126, 20, 0.15)',
                    'tension' => 0.3,
                    'stack' => 'total',
                ],
            ],
        ]);
        $revenueChartGross->setOptions([
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['beginAtZero' => true, 'stacked' => true],
            ],
            'maintainAspectRatio' => false,
        ]);

        $creditChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $creditChart->setData([
            'labels' => $bucketLabels,
            'datasets' => [
                [
                    'label' => 'Netto',
                    'data' => $creditRevenueValues,
                    'borderColor' => '#111931',
                    'backgroundColor' => 'rgba(17, 25, 49, 0.15)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Brutto',
                    'data' => $creditRevenueGrossValues,
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
                    'tension' => 0.3,
                ],
            ],
        ]);
        $creditChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $aboChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $aboChart->setData([
            'labels' => $bucketLabels,
            'datasets' => [
                [
                    'label' => 'Netto',
                    'data' => $aboRevenueValues,
                    'borderColor' => '#20c997',
                    'backgroundColor' => 'rgba(32, 201, 151, 0.18)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Brutto',
                    'data' => $aboRevenueGrossValues,
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
                    'tension' => 0.3,
                ],
            ],
        ]);
        $aboChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $ecommerceChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $ecommerceChart->setData([
            'labels' => $bucketLabels,
            'datasets' => [
                [
                    'label' => 'Netto',
                    'data' => $ecommerceRevenueValues,
                    'borderColor' => '#0d6efd',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.15)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Brutto',
                    'data' => $ecommerceRevenueGrossValues,
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
                    'tension' => 0.3,
                ],
            ],
        ]);
        $ecommerceChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $template = $embed ? 'admin/statistics/_revenue.html.twig' : 'admin/revenue_statistics.html.twig';

        $periodLabel = match ($period) {
            'day' => 'Tag',
            'year' => 'Jahr',
            default => 'Monat',
        };

        return $this->render($template, [
            'revenueChartNet' => $revenueChartNet,
            'revenueChartGross' => $revenueChartGross,
            'creditChart' => $creditChart,
            'aboChart' => $aboChart,
            'ecommerceChart' => $ecommerceChart,
            'revenueAboTotal' => array_sum($aboRevenueValues),
            'revenueCreditTotal' => array_sum($creditRevenueValues),
            'revenueEcommerceTotal' => array_sum($ecommerceRevenueValues),
            'revenueTotal' => array_sum($combinedRevenueValues),
            'revenueAboGrossTotal' => array_sum($aboRevenueGrossValues),
            'revenueCreditGrossTotal' => array_sum($creditRevenueGrossValues),
            'revenueEcommerceGrossTotal' => array_sum($ecommerceRevenueGrossValues),
            'revenueTotalGross' => array_sum($combinedRevenueGrossValues),
            'revenueVatRate' => $defaultVatRate,
            'period' => $period,
            'periodLabel' => $periodLabel,
            'creditCount' => $creditCount,
            'ecommerceCount' => $ecommerceCount,
            'aboCount' => count($abosForRevenue),
            'embedded' => $embed,
        ]);
    }

    #[AdminRoute(path: '/abos/statistik', name: 'abo_statistics', options: ['methods' => ['GET']])]
    public function aboStatistics(Request $request): Response
    {
        $embed = $request->query->getBoolean('embed');

        $rangeStart = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0)->modify('-11 months');
        $rangeEnd = (new \DateTimeImmutable('last day of this month'))->setTime(23, 59, 59);

        $months = [];
        $monthLabels = [];
        $monthStarts = [];
        $monthEnds = [];
        $cursor = $rangeStart;
        for ($i = 0; $i < 12; ++$i) {
            $key = $cursor->format('Y-m');
            $months[] = $key;
            $monthLabels[] = $cursor->format('m/Y');
            $monthStarts[$key] = $cursor;
            $monthEnds[$key] = $cursor->modify('last day of this month')->setTime(23, 59, 59);
            $cursor = $cursor->modify('+1 month');
        }

        $abos = $this->connection->fetchAllAssociative(
            'SELECT a.start_datum, a.end_datum, a.zahlweise, ps.preis_monatlich, ps.preis_jaehrlich, ps.preis_einrichtung
             FROM abo a
             LEFT JOIN abo_auswahl_preisstaffel ps ON ps.id = a.preisstaffel_id
             WHERE a.start_datum <= :rangeEnd
               AND (a.end_datum IS NULL OR a.end_datum >= :rangeStart)',
            [
                'rangeStart' => $rangeStart->format('Y-m-d H:i:s'),
                'rangeEnd' => $rangeEnd->format('Y-m-d H:i:s'),
            ]
        );

        $aboRevenueMap = array_fill_keys($months, 0.0);
        $setupRevenueMap = array_fill_keys($months, 0.0);

        foreach ($abos as $row) {
            $start = new \DateTimeImmutable((string) $row['start_datum']);
            $ende = null !== $row['end_datum'] ? new \DateTimeImmutable((string) $row['end_datum']) : null;
            $zahlweise = (string) ($row['zahlweise'] ?? 'monatlich');

            $preisMonatlich = null !== $row['preis_monatlich'] ? (float) $row['preis_monatlich'] : null;
            $preisJaehrlich = null !== $row['preis_jaehrlich'] ? (float) $row['preis_jaehrlich'] : null;

            if ('jaehrlich' === $zahlweise) {
                $monatlicherPreis = $preisJaehrlich ?? $preisMonatlich ?? 0.0;
            } else {
                $monatlicherPreis = $preisMonatlich ?? $preisJaehrlich ?? 0.0;
            }

            $einrichtungsgebuehr = null !== $row['preis_einrichtung'] ? (float) $row['preis_einrichtung'] : 0.0;

            foreach ($months as $monthKey) {
                $monthStart = $monthStarts[$monthKey];
                $monthEnd = $monthEnds[$monthKey];

                if ($start > $monthEnd) {
                    continue;
                }
                if (null !== $ende && $ende < $monthStart) {
                    continue;
                }

                $aboRevenueMap[$monthKey] += $monatlicherPreis;
            }

            $startKey = $start->format('Y-m');
            if (isset($setupRevenueMap[$startKey])) {
                $setupRevenueMap[$startKey] += $einrichtungsgebuehr;
            }
        }

        $aboRevenueValues = array_map(static fn (string $month): float => $aboRevenueMap[$month], $months);
        $setupRevenueValues = array_map(static fn (string $month): float => $setupRevenueMap[$month], $months);

        $aboRevenueChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $aboRevenueChart->setData([
            'labels' => $monthLabels,
            'datasets' => [[
                'label' => 'Abo Umsatz pro Monat (EUR)',
                'data' => $aboRevenueValues,
                'borderColor' => '#20c997',
                'backgroundColor' => 'rgba(32, 201, 151, 0.18)',
                'fill' => true,
                'tension' => 0.35,
            ]],
        ]);
        $aboRevenueChart->setOptions([
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $setupRevenueChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $setupRevenueChart->setData([
            'labels' => $monthLabels,
            'datasets' => [[
                'label' => 'Einrichtungsgebuehr pro Monat (EUR)',
                'data' => $setupRevenueValues,
                'backgroundColor' => '#fd7e14',
            ]],
        ]);
        $setupRevenueChart->setOptions([
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $template = $embed ? 'admin/statistics/_abo.html.twig' : 'admin/abo_statistics.html.twig';

        return $this->render($template, [
            'aboRevenueChart' => $aboRevenueChart,
            'setupRevenueChart' => $setupRevenueChart,
            'aboRevenueSum' => array_sum($aboRevenueValues),
            'setupRevenueSum' => array_sum($setupRevenueValues),
            'rangeLabel' => 'letzte 12 Monate',
            'embedded' => $embed,
        ]);
    }

    #[AdminRoute(path: '/credits/statistik', name: 'credits_statistics', options: ['methods' => ['GET']])]
    public function creditsStatistics(Request $request): Response
    {
        $embed = $request->query->getBoolean('embed');
        $todayStart = (new \DateTimeImmutable('today'))->setTime(0, 0);
        $fromDate = $todayStart->modify('-12 months');

        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, created_at, price_net, price_gross, payment_method, payment_fee
             FROM kunde_credit_transaction t
             WHERE t.transaction_type = :type
               AND t.price_gross IS NOT NULL
               AND t.created_at >= :fromDate
               AND NOT EXISTS (
                   SELECT 1 FROM kunde_credit_transaction r
                   WHERE r.reversal_of_id = t.id
               )
             ORDER BY t.created_at ASC',
            [
                'type' => KundeCreditTransaction::TYPE_CUSTOMER_TOPUP,
                'fromDate' => $fromDate->format('Y-m-d H:i:s'),
            ]
        );

        $dailyMap = [];
        $dailyLabels = [];
        for ($i = 29; $i >= 0; --$i) {
            $day = $todayStart->modify(sprintf('-%d days', $i));
            $key = $day->format('Y-m-d');
            $dailyLabels[] = $day->format('d.m');
            $dailyMap[$key] = ['net' => 0.0, 'gross' => 0.0];
        }

        $weekStart = (new \DateTimeImmutable('monday this week'))->setTime(0, 0);
        $weeklyLabels = [];
        $weeklyMap = [];
        for ($i = 11; $i >= 0; --$i) {
            $start = $weekStart->modify(sprintf('-%d weeks', $i));
            $key = $start->format('o-W');
            $weeklyLabels[] = 'KW ' . $start->format('W');
            $weeklyMap[$key] = ['net' => 0.0, 'gross' => 0.0];
        }

        $monthStart = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $monthlyLabels = [];
        $monthlyMap = [];
        for ($i = 11; $i >= 0; --$i) {
            $start = $monthStart->modify(sprintf('-%d months', $i));
            $key = $start->format('Y-m');
            $monthlyLabels[] = $start->format('m/Y');
            $monthlyMap[$key] = ['net' => 0.0, 'gross' => 0.0];
        }

        $monthCurrentStart = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $yearCurrentStart = (new \DateTimeImmutable('first day of january this year'))->setTime(0, 0);

        $totals = [
            'day' => ['label' => 'Heute', 'net' => 0.0, 'gross' => 0.0, 'paypalGross' => 0.0, 'paypalNet' => 0.0, 'paypalFee' => 0.0],
            'week' => ['label' => '7 Tage', 'net' => 0.0, 'gross' => 0.0, 'paypalGross' => 0.0, 'paypalNet' => 0.0, 'paypalFee' => 0.0],
            'month' => ['label' => 'Monat', 'net' => 0.0, 'gross' => 0.0, 'paypalGross' => 0.0, 'paypalNet' => 0.0, 'paypalFee' => 0.0],
            'year' => ['label' => 'Jahr', 'net' => 0.0, 'gross' => 0.0, 'paypalGross' => 0.0, 'paypalNet' => 0.0, 'paypalFee' => 0.0],
            'months3' => ['label' => '3 Monate', 'net' => 0.0, 'gross' => 0.0, 'paypalGross' => 0.0, 'paypalNet' => 0.0, 'paypalFee' => 0.0],
            'months6' => ['label' => '6 Monate', 'net' => 0.0, 'gross' => 0.0, 'paypalGross' => 0.0, 'paypalNet' => 0.0, 'paypalFee' => 0.0],
            'months12' => ['label' => '12 Monate', 'net' => 0.0, 'gross' => 0.0, 'paypalGross' => 0.0, 'paypalNet' => 0.0, 'paypalFee' => 0.0],
        ];

        $rangeStarts = [
            'day' => $todayStart,
            'week' => $todayStart->modify('-6 days'),
            'month' => $monthCurrentStart,
            'year' => $yearCurrentStart,
            'months3' => $todayStart->modify('-3 months'),
            'months6' => $todayStart->modify('-6 months'),
            'months12' => $todayStart->modify('-12 months'),
        ];

        $paymentMethods = [];
        foreach ($rows as $row) {
            $createdAt = new \DateTimeImmutable((string) $row['created_at']);
            $net = isset($row['price_net']) ? (float) $row['price_net'] : 0.0;
            $gross = isset($row['price_gross']) ? (float) $row['price_gross'] : 0.0;

            if ($gross <= 0 && $net <= 0) {
                continue;
            }

            $paymentMethod = strtolower(trim((string) ($row['payment_method'] ?? '')));
            $isPaypal = 'paypal' === $paymentMethod;
            $paypalFee = 0.0;
            if ($isPaypal && $gross > 0) {
                $paypalFee = is_numeric($row['payment_fee'] ?? null) ? (float) $row['payment_fee'] : 0.0;
                if ($paypalFee <= 0) {
                    $paypalFee = ($gross * 0.0179) + 0.35;
                }
            }
            $paypalNet = $gross - $paypalFee;
            if ($paypalNet < 0) {
                $paypalNet = 0.0;
            }

            $dayKey = $createdAt->format('Y-m-d');
            if (isset($dailyMap[$dayKey])) {
                $dailyMap[$dayKey]['net'] += $net;
                $dailyMap[$dayKey]['gross'] += $gross;
            }

            $weekKey = $createdAt->modify('monday this week')->format('o-W');
            if (isset($weeklyMap[$weekKey])) {
                $weeklyMap[$weekKey]['net'] += $net;
                $weeklyMap[$weekKey]['gross'] += $gross;
            }

            $monthKey = $createdAt->format('Y-m');
            if (isset($monthlyMap[$monthKey])) {
                $monthlyMap[$monthKey]['net'] += $net;
                $monthlyMap[$monthKey]['gross'] += $gross;
            }

            foreach ($rangeStarts as $key => $start) {
                if ($createdAt >= $start) {
                    $totals[$key]['net'] += $net;
                    $totals[$key]['gross'] += $gross;
                    if ($isPaypal) {
                        $totals[$key]['paypalGross'] += $gross;
                        $totals[$key]['paypalNet'] += $paypalNet;
                        $totals[$key]['paypalFee'] += $paypalFee;
                    }
                }
            }

            if ('' === $paymentMethod) {
                $paymentMethod = 'unbekannt';
            }
            $paymentMethods[$paymentMethod] = ($paymentMethods[$paymentMethod] ?? 0.0) + $gross;
        }

        $dailyNet = array_map(static fn (array $values): float => $values['net'], $dailyMap);
        $dailyGross = array_map(static fn (array $values): float => $values['gross'], $dailyMap);
        $weeklyNet = array_map(static fn (array $values): float => $values['net'], $weeklyMap);
        $weeklyGross = array_map(static fn (array $values): float => $values['gross'], $weeklyMap);
        $monthlyNet = array_map(static fn (array $values): float => $values['net'], $monthlyMap);
        $monthlyGross = array_map(static fn (array $values): float => $values['gross'], $monthlyMap);

        $dailyChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $dailyChart->setData([
            'labels' => $dailyLabels,
            'datasets' => [
                [
                    'label' => 'Netto',
                    'data' => $dailyNet,
                    'borderColor' => '#111931',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.15)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Brutto',
                    'data' => $dailyGross,
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
                    'tension' => 0.3,
                ],
            ],
        ]);
        $dailyChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $weeklyChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $weeklyChart->setData([
            'labels' => $weeklyLabels,
            'datasets' => [
                [
                    'label' => 'Netto',
                    'data' => $weeklyNet,
                    'borderColor' => '#111931',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.15)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Brutto',
                    'data' => $weeklyGross,
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
                    'tension' => 0.3,
                ],
            ],
        ]);
        $weeklyChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $monthlyChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $monthlyChart->setData([
            'labels' => $monthlyLabels,
            'datasets' => [
                [
                    'label' => 'Netto',
                    'data' => $monthlyNet,
                    'borderColor' => '#bbcf4e',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.15)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Brutto',
                    'data' => $monthlyGross,
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
                    'tension' => 0.3,
                ],
            ],
        ]);
        $monthlyChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $methodChart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $methodLabels = array_map(static fn (string $label): string => strtoupper($label), array_keys($paymentMethods));
        $methodValues = array_values($paymentMethods);
        if ([] === $methodLabels) {
            $methodLabels = ['Keine Daten'];
            $methodValues = [0];
        }
        $methodChart->setData([
            'labels' => $methodLabels,
            'datasets' => [[
                'data' => $methodValues,
                'backgroundColor' => ['#111931', '#bbcf4e', '#828c4b', '#6f42c1', '#adb5bd'],
            ]],
        ]);
        $methodChart->setOptions([
            'plugins' => ['legend' => ['position' => 'bottom']],
            'maintainAspectRatio' => false,
        ]);

        $template = $embed ? 'admin/statistics/_credits.html.twig' : 'admin/credits_statistics.html.twig';

        return $this->render($template, [
            'totals' => $totals,
            'dailyChart' => $dailyChart,
            'weeklyChart' => $weeklyChart,
            'monthlyChart' => $monthlyChart,
            'methodChart' => $methodChart,
            'embedded' => $embed,
        ]);
    }

    #[AdminRoute(path: '/statistiken', name: 'statistics', options: ['methods' => ['GET']])]
    public function statisticsHub(): Response
    {
        return $this->render('admin/statistics/index.html.twig');
    }

    #[AdminRoute(path: '/suche', name: 'search', options: ['methods' => ['GET']])]
    public function searchHub(Request $request): Response
    {
        $vehicleTypeRepo = $this->entityManager->getRepository(\App\Entity\VehicleType::class);
        $dataPoolRepo = $this->entityManager->getRepository(\App\Entity\VehicleDataPool::class);

        $vehicleTypes = $vehicleTypeRepo->findBy(['active' => true], ['sorting' => 'ASC', 'name' => 'ASC']);
        $dataPools = $dataPoolRepo->findBy(['active' => true], ['name' => 'ASC']);

        return $this->render('admin/search/index.html.twig', [
            'query' => trim((string) $request->query->get('q', '')),
            'vehicleTypes' => $vehicleTypes,
            'dataPools' => $dataPools,
            'filterVehicleType' => trim((string) $request->query->get('vehicleType', '')),
            'filterDataPool' => trim((string) $request->query->get('dataPool', '')),
        ]);
    }

    #[AdminRoute(path: '/suche/chat', name: 'search_chat', options: ['methods' => ['GET']])]
    public function searchChatHub(): Response
    {
        return $this->render('admin/search/chat.html.twig', [
            'workspace' => trim((string) ($_ENV['MEILISEARCH_CHAT_WORKSPACE'] ?? 'cloud')),
            'model' => trim((string) ($_ENV['MEILISEARCH_CHAT_MODEL'] ?? '')),
            'docsUrl' => 'https://www.meilisearch.com/docs/capabilities/conversational_search/getting_started/setup#find-your-chat-api-key',
        ]);
    }

    #[AdminRoute(path: '/suche/chat/api', name: 'search_chat_api', options: ['methods' => ['POST']])]
    public function searchChatApi(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Ungueltiger JSON-Body.'], Response::HTTP_BAD_REQUEST);
        }

        $messages = $payload['messages'] ?? null;
        if (!is_array($messages) || [] === $messages) {
            return $this->json(['error' => 'messages ist erforderlich.'], Response::HTTP_BAD_REQUEST);
        }

        $model = trim((string) ($payload['model'] ?? ($_ENV['MEILISEARCH_CHAT_MODEL'] ?? '')));
        if ('' === $model) {
            return $this->json(['error' => 'Kein Modell gesetzt. Setze MEILISEARCH_CHAT_MODEL oder sende model im Request.'], Response::HTTP_BAD_REQUEST);
        }

        $workspace = trim((string) ($payload['workspace'] ?? ($_ENV['MEILISEARCH_CHAT_WORKSPACE'] ?? 'cloud')));
        if ('' === $workspace) {
            $workspace = 'cloud';
        }

        $meilisearchUrl = rtrim((string) ($_ENV['MEILISEARCH_URL'] ?? ''), '/');
        $chatApiKey = trim((string) ($_ENV['MEILISEARCH_CHAT_API_KEY'] ?? ''));
        if ('' === $chatApiKey) {
            $chatApiKey = trim((string) ($_ENV['MEILISEARCH_KEY'] ?? ''));
        }

        if ('' === $meilisearchUrl || '' === $chatApiKey) {
            return $this->json(['error' => 'MEILISEARCH_URL und MEILISEARCH_CHAT_API_KEY (oder MEILISEARCH_KEY) muessen gesetzt sein.'], Response::HTTP_BAD_REQUEST);
        }

        $outboundPayload = [
            'model' => $model,
            'stream' => true,
            'messages' => $messages,
        ];
        if (isset($payload['tools']) && is_array($payload['tools'])) {
            $outboundPayload['tools'] = $payload['tools'];
        }

        $endpoint = sprintf('%s/chats/%s/chat/completions', $meilisearchUrl, rawurlencode($workspace));
        $response = new StreamedResponse(function () use ($endpoint, $chatApiKey, $outboundPayload): void {
            $ch = curl_init($endpoint);
            if (false === $ch) {
                echo "data: {\"error\":\"cURL init fehlgeschlagen\"}\n\n";
                flush();

                return;
            }

            curl_setopt_array($ch, [
                \CURLOPT_POST => true,
                \CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $chatApiKey,
                    'Content-Type: application/json',
                    'Accept: text/event-stream',
                ],
                \CURLOPT_POSTFIELDS => json_encode($outboundPayload, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
                \CURLOPT_RETURNTRANSFER => false,
                \CURLOPT_FOLLOWLOCATION => false,
                \CURLOPT_CONNECTTIMEOUT => 5,
                \CURLOPT_TIMEOUT => 120,
                \CURLOPT_WRITEFUNCTION => static function ($curlHandle, string $chunk): int {
                    echo $chunk;
                    flush();

                    return strlen($chunk);
                },
            ]);

            curl_exec($ch);
            if (curl_errno($ch)) {
                $message = curl_error($ch);
                echo 'data: {"error":"' . addslashes((string) $message) . "\"}\n\n";
                flush();
            }
            curl_close($ch);
        });

        $response->headers->set('Content-Type', 'text/event-stream; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[AdminRoute(path: '/suche/api', name: 'search_api', options: ['methods' => ['GET']])]
    public function searchApi(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        if ('' === $query) {
            return $this->json(['results' => []]);
        }

        $meilisearchUrl = rtrim((string) ($_ENV['MEILISEARCH_URL'] ?? ''), '/');
        $apiKey = (string) ($_ENV['MEILISEARCH_KEY'] ?? '');
        $prefix = 'dev_';
        if (isset($_ENV['APP_ENV']) && 'prod' === $_ENV['APP_ENV']) {
            $prefix = 'prod_';
        }
        $hybridEnabled = filter_var((string) ($_ENV['MEILISEARCH_HYBRID_ENABLED'] ?? 'false'), \FILTER_VALIDATE_BOOL);
        $hybridSemanticRatio = max(0.0, min(1.0, (float) ($_ENV['MEILISEARCH_HYBRID_SEMANTIC_RATIO'] ?? 0.35)));
        $hybridEmbedder = trim((string) ($_ENV['MEILISEARCH_HYBRID_EMBEDDER'] ?? 'default'));

        // Fahrzeug-Filter aus Query-Parametern
        $filterVehicleType = trim((string) $request->query->get('vehicleType', ''));
        $filterDataPool = trim((string) $request->query->get('dataPool', ''));

        $vehicleFilter = 'active = true';
        if ('' !== $filterVehicleType) {
            $vehicleFilter .= ' AND vehicleTypeName = ' . json_encode($filterVehicleType);
        }
        if ('' !== $filterDataPool) {
            $vehicleFilter .= ' AND dataPoolKey = ' . json_encode($filterDataPool);
        }

        $indexes = [
            ['key' => 'kunden', 'label' => 'Kunden', 'filter' => null],
            ['key' => 'auftraege', 'label' => 'Aufträge', 'filter' => null],
            ['key' => 'vehicle_types', 'label' => 'Fahrzeugtypen', 'filter' => 'active = true'],
            ['key' => 'vehicle_brands', 'label' => 'Fahrzeugmarken', 'filter' => $vehicleFilter],
            ['key' => 'vehicle_series', 'label' => 'Baureihen', 'filter' => $vehicleFilter],
            ['key' => 'vehicle_models', 'label' => 'Modelle', 'filter' => $vehicleFilter],
            ['key' => 'vehicle_engines', 'label' => 'Motoren', 'filter' => $vehicleFilter],
            ['key' => 'ecu_masters', 'label' => 'ECU-Master', 'filter' => null],
            ['key' => 'ecu_manufacturer_masters', 'label' => 'ECU-Hersteller-Master', 'filter' => null],
            ['key' => 'dtc_codes', 'label' => 'DTC-Codes', 'filter' => 'hidden = false AND deleted = false'],
        ];

        $results = [];
        foreach ($indexes as $index) {
            $indexUid = $prefix . $index['key'];
            $url = $meilisearchUrl . '/indexes/' . $indexUid . '/search';

            $payload = [
                'q' => $query,
                'limit' => 8,
                'showRankingScore' => true,
                'attributesToHighlight' => ['*'],
                'highlightPreTag' => '<mark>',
                'highlightPostTag' => '</mark>',
            ];
            if (is_string($index['filter']) && '' !== $index['filter']) {
                $payload['filter'] = $index['filter'];
            }
            if ($hybridEnabled) {
                $payload['hybrid'] = [
                    'semanticRatio' => $hybridSemanticRatio,
                ];
                if ('' !== $hybridEmbedder) {
                    $payload['hybrid']['embedder'] = $hybridEmbedder;
                }
            }

            $data = $this->postMeilisearchRequest($url, $apiKey, $payload);
            if ($hybridEnabled && $this->shouldFallbackFromHybrid($data)) {
                unset($payload['hybrid']);
                $data = $this->postMeilisearchRequest($url, $apiKey, $payload);
            }
            $hits = is_array($data) && isset($data['hits']) ? $data['hits'] : [];

            if ($hits) {
                usort($hits, static function (array $a, array $b): int {
                    $scoreA = (float) ($a['_rankingScore'] ?? 0.0);
                    $scoreB = (float) ($b['_rankingScore'] ?? 0.0);

                    return $scoreB <=> $scoreA;
                });

                $hitsWithLinks = array_map(function (array $hit) use ($index): array {
                    $hit['_adminUrl'] = $this->buildSearchHitAdminUrl($index['key'], $hit);

                    return $hit;
                }, $hits);

                $results[] = [
                    'label' => $index['label'],
                    'key' => $index['key'],
                    'hits' => $hitsWithLinks,
                ];
            }
        }

        return $this->json(['results' => $results]);
    }

    /** @param array<string, mixed> $hit */
    private function buildSearchHitAdminUrl(string $indexKey, array $hit): ?string
    {
        $entityId = $hit['id'] ?? null;
        if (null === $entityId || '' === (string) $entityId) {
            return null;
        }

        $routeName = match ($indexKey) {
            'kunden' => 'admin_kunde_detail',
            'auftraege' => 'admin_auftrag_detail',
            'vehicle_types' => 'admin_vehicle_type_detail',
            'vehicle_brands' => 'admin_vehicle_brand_detail',
            'vehicle_series' => 'admin_vehicle_series_detail',
            'vehicle_models' => 'admin_vehicle_model_detail',
            'vehicle_engines' => 'admin_vehicle_engine_detail',
            'ecu_masters' => 'admin_ecu_master_detail',
            'ecu_manufacturer_masters' => 'admin_ecu_manufacturer_master_detail',
            'dtc_codes' => 'admin_dtc_code_detail',
            default => null,
        };

        if (null === $routeName) {
            return null;
        }

        return $this->generateUrl($routeName, ['entityId' => $entityId]);
    }

    /** @param array<string, mixed> $payload
     *  @return array<string, mixed>|null
     */
    private function postMeilisearchRequest(string $url, string $apiKey, array $payload): ?array
    {
        $ch = curl_init($url);
        if (false === $ch) {
            return null;
        }

        curl_setopt_array($ch, [
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_CONNECTTIMEOUT => 2,
            \CURLOPT_TIMEOUT => 4,
            \CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            \CURLOPT_POST => true,
            \CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);

        if (!is_string($response)) {
            return null;
        }

        $data = json_decode($response, true);

        return is_array($data) ? $data : null;
    }

    /** @param array<string, mixed>|null $data */
    private function shouldFallbackFromHybrid(?array $data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        $message = strtolower((string) ($data['message'] ?? ''));
        $code = strtolower((string) ($data['code'] ?? ''));

        if ('' === $message && '' === $code) {
            return false;
        }

        return str_contains($message, 'hybrid')
            || str_contains($message, 'semantic')
            || str_contains($message, 'embedder')
            || str_contains($code, 'embedder')
            || str_contains($code, 'hybrid');
    }

    #[Route('/admin/chat/send', name: 'admin_chat_send', methods: ['POST'])]
    public function sendChatMessage(Request $request): RedirectResponse
    {
        $this->addFlash('info', 'Chats sind aktuell deaktiviert.');

        return $this->redirectToRoute('admin_support_tickets');

        $redirectToChats = function (int $firmaId, int $kundeId = 0): RedirectResponse {
            $params = ['firma' => $firmaId];
            if ($kundeId > 0) {
                $params['kunde'] = $kundeId;
            }

            return $this->redirectToRoute('admin_support_chats', $params);
        };

        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_chat_send', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $redirectToChats((int) $request->request->get('firmaId', 0), (int) $request->request->get('kundeId', 0));
        }

        $firmaId = (int) $request->request->get('firmaId', 0);
        $kundeId = (int) $request->request->get('kundeId', 0);
        $message = trim((string) $request->request->get('message', ''));

        if ($firmaId <= 0 || '' === $message) {
            $this->addFlash('warning', 'Bitte Firma und Nachricht angeben.');

            return $redirectToChats($firmaId, $kundeId);
        }

        $firma = $this->firmaRepository->find($firmaId);
        if (null === $firma) {
            $this->addFlash('danger', 'Firma nicht gefunden.');

            return $redirectToChats($firmaId, $kundeId);
        }

        $kunde = null;
        if ($kundeId > 0) {
            $kunde = $this->kundeRepository->find($kundeId);
            if (!$kunde instanceof Kunde || $kunde->getFirma()?->getId() !== $firma->getId()) {
                $this->addFlash('danger', 'Kunde passt nicht zur gewaehlten Firma.');

                return $redirectToChats($firmaId, 0);
            }
        }

        $user = $this->getUser();
        $senderName = $user ? (string) $user : 'Admin';

        $chatMessage = (new CustomerChatMessage())
            ->setFirma($firma)
            ->setKunde($kunde)
            ->setSenderType(CustomerChatMessage::SENDER_ADMIN)
            ->setSenderName($senderName)
            ->setMessage($message);

        $this->entityManager->persist($chatMessage);
        $this->entityManager->flush();

        $this->addFlash('success', 'Nachricht gespeichert.');

        return $redirectToChats($firmaId, $kundeId);
    }

    #[AdminRoute(path: '/media/manager', name: 'media_manager', options: ['methods' => ['GET']])]
    public function mediaManager(Request $request): Response
    {
        $folderRepository = $this->entityManager->getRepository(MediaFolder::class);
        $assetRepository = $this->entityManager->getRepository(MediaAsset::class);
        $this->syncBrandLogoMediaFolder();

        $scope = strtolower(trim((string) $request->query->get('scope', 'all')));
        if (!in_array($scope, ['all', 'unassigned'], true)) {
            $scope = 'all';
        }

        $selectedFolderId = (int) $request->query->get('folder', 0);
        $selectedFolder = ('all' === $scope && $selectedFolderId > 0)
            ? $folderRepository->find($selectedFolderId)
            : null;
        if (!$selectedFolder instanceof MediaFolder) {
            $selectedFolderId = 0;
            $selectedFolder = null;
        }

        $itemsPerPage = 50;
        $currentPage = max(1, (int) $request->query->get('page', 1));

        $folders = $folderRepository->createQueryBuilder('f')
            ->leftJoin('f.parent', 'p')
            ->addSelect('p')
            ->orderBy('f.sorting', 'ASC')
            ->addOrderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();

        $assetsQb = $assetRepository->createQueryBuilder('a')
            ->leftJoin('a.folder', 'f')
            ->addSelect('f')
            ->orderBy('a.sorting', 'ASC')
            ->addOrderBy('a.name', 'ASC');

        if ('unassigned' === $scope) {
            $assetsQb->andWhere('a.folder IS NULL');
        } elseif ($selectedFolder instanceof MediaFolder) {
            $assetsQb
                ->andWhere('a.folder = :folder')
                ->setParameter('folder', $selectedFolder);
        }

        $filteredAssets = (int) (clone $assetsQb)
            ->resetDQLPart('orderBy')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $totalPages = max(1, (int) ceil($filteredAssets / $itemsPerPage));
        $currentPage = min($currentPage, $totalPages);

        $assetsQb
            ->setFirstResult(($currentPage - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $assets = $assetsQb->getQuery()->getResult();

        $totalAssets = (int) $assetRepository->count([]);
        $unassignedAssets = (int) $assetRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.folder IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $folderCountRows = $assetRepository->createQueryBuilder('a')
            ->select('IDENTITY(a.folder) AS folderId, COUNT(a.id) AS total')
            ->andWhere('a.folder IS NOT NULL')
            ->groupBy('a.folder')
            ->getQuery()
            ->getArrayResult();

        $folderCounts = [];
        foreach ($folderCountRows as $row) {
            $folderId = isset($row['folderId']) ? (int) $row['folderId'] : 0;
            if ($folderId <= 0) {
                continue;
            }
            $folderCounts[$folderId] = isset($row['total']) ? (int) $row['total'] : 0;
        }

        $folderTree = $this->buildMediaFolderTree($folders, $selectedFolderId);
        $folderOptions = $this->buildMediaFolderOptions($folderTree);
        $uxIconCatalog = $this->buildUxIconCatalog();

        return $this->render('admin/media_manager.html.twig', [
            'folderTree' => $folderTree,
            'folderOptions' => $folderOptions,
            'folderCounts' => $folderCounts,
            'selectedFolder' => $selectedFolder,
            'selectedFolderId' => $selectedFolderId,
            'currentScope' => $scope,
            'assets' => $assets,
            'filteredAssets' => $filteredAssets,
            'currentPage' => $currentPage,
            'itemsPerPage' => $itemsPerPage,
            'totalPages' => $totalPages,
            'totalAssets' => $totalAssets,
            'unassignedAssets' => $unassignedAssets,
            'uxIconGroups' => $uxIconCatalog['groups'],
            'uxIconTotal' => $uxIconCatalog['total'],
        ]);
    }

    #[AdminRoute(path: '/media/manager/upload', name: 'media_manager_upload', options: ['methods' => ['GET']])]
    public function mediaManagerUpload(): Response
    {
        $this->ensureMediaFolder('Unsortiert');

        return $this->render('admin/media_upload.html.twig', [
            'mediaTypeChoices' => MediaAsset::getMediaTypeChoices(),
            'targetFolderName' => 'Unsortiert',
        ]);
    }

    #[AdminRoute(path: '/media/manager/upload', name: 'media_manager_upload_submit', options: ['methods' => ['POST']])]
    public function mediaManagerUploadSubmit(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('media_manager_upload', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_media_manager_upload');
        }

        $mediaType = MediaAsset::normalizeMediaType((string) $request->request->get('mediaType', MediaAsset::TYPE_IMAGE));
        $files = $request->files->all('files');
        if (!is_array($files)) {
            $files = [];
        }

        $folder = $this->ensureMediaFolder('Unsortiert');
        $created = 0;
        $skipped = 0;

        foreach ($files as $uploadedFile) {
            if (!$uploadedFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                ++$skipped;
                continue;
            }

            $mimeType = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
            $fileSize = $uploadedFile->getSize();
            $path = $this->storeSingleUpload($uploadedFile, 'uploads/media/unsortiert');
            if (null === $path) {
                ++$skipped;
                continue;
            }

            $asset = (new MediaAsset())
                ->setName($this->buildMediaAssetName($uploadedFile))
                ->setFolder($folder)
                ->setMediaType($mediaType)
                ->setFilePath($path)
                ->setMimeType((null !== $mimeType && '' !== trim($mimeType)) ? trim($mimeType) : null)
                ->setFileSize(is_int($fileSize) ? $fileSize : null)
                ->setActive(true);

            $this->entityManager->persist($asset);
            ++$created;
        }

        if ($created > 0) {
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('%d Datei%s in "Unsortiert" hochgeladen.', $created, 1 === $created ? '' : 'en'));
        }

        if ($skipped > 0 || 0 === $created) {
            $this->addFlash(0 === $created ? 'warning' : 'info', sprintf('%d Datei%s wurden uebersprungen.', $skipped, 1 === $skipped ? '' : 'en'));
        }

        return $this->redirectToRoute('admin_media_manager', [
            'scope' => 'all',
            'folder' => $folder->getId(),
        ]);
    }

    #[AdminRoute(path: '/media/manager/upload/ajax', name: 'media_manager_upload_ajax', options: ['methods' => ['POST']])]
    public function mediaManagerUploadAjax(Request $request): JsonResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if ('' === $csrfToken) {
            $csrfToken = (string) $request->headers->get('X-CSRF-TOKEN', $request->headers->get('X-CSRF-Token', ''));
        }
        if (!$this->isCsrfTokenValid('media_manager_upload', $csrfToken)) {
            return new JsonResponse(['error' => 'Ungueltige Anfrage.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            return new JsonResponse(['error' => 'Keine Datei erhalten.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $mediaType = MediaAsset::normalizeMediaType((string) $request->request->get('mediaType', MediaAsset::TYPE_IMAGE));
        $folder = $this->ensureMediaFolder('Unsortiert');

        $mimeType = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
        $fileSize = $uploadedFile->getSize();
        $path = $this->storeSingleUpload($uploadedFile, 'uploads/media/unsortiert');
        if (null === $path) {
            return new JsonResponse(['error' => 'Datei konnte nicht gespeichert werden.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $asset = (new MediaAsset())
            ->setName($this->buildMediaAssetName($uploadedFile))
            ->setFolder($folder)
            ->setMediaType($mediaType)
            ->setFilePath($path)
            ->setMimeType((null !== $mimeType && '' !== trim($mimeType)) ? trim($mimeType) : null)
            ->setFileSize(is_int($fileSize) ? $fileSize : null)
            ->setActive(true);

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'id' => $asset->getId(), 'name' => $asset->getName()]);
    }

    #[AdminRoute(path: '/media/manager/move', name: 'media_manager_move', options: ['methods' => ['POST']])]
    public function moveMediaAsset(Request $request): RedirectResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('media_manager_move', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_media_manager');
        }

        $assetId = (int) $request->request->get('assetId', 0);
        $targetFolderId = (int) $request->request->get('folderId', 0);
        $currentFolderId = (int) $request->request->get('currentFolder', 0);
        $currentPage = max(1, (int) $request->request->get('currentPage', 1));
        $currentScope = strtolower(trim((string) $request->request->get('currentScope', 'all')));
        if (!in_array($currentScope, ['all', 'unassigned'], true)) {
            $currentScope = 'all';
        }

        $redirectParams = ['scope' => $currentScope];
        if ('all' === $currentScope && $currentFolderId > 0) {
            $redirectParams['folder'] = $currentFolderId;
        }
        if ($currentPage > 1) {
            $redirectParams['page'] = $currentPage;
        }

        /** @var MediaAsset|null $asset */
        $asset = $this->entityManager->getRepository(MediaAsset::class)->find($assetId);
        if (!$asset instanceof MediaAsset) {
            $this->addFlash('danger', 'Datei nicht gefunden.');

            return $this->redirectToRoute('admin_media_manager', $redirectParams);
        }

        $targetFolder = null;
        if ($targetFolderId > 0) {
            /** @var MediaFolder|null $targetFolder */
            $targetFolder = $this->entityManager->getRepository(MediaFolder::class)->find($targetFolderId);
            if (!$targetFolder instanceof MediaFolder) {
                $this->addFlash('danger', 'Zielordner nicht gefunden.');

                return $this->redirectToRoute('admin_media_manager', $redirectParams);
            }
        }

        $asset->setFolder($targetFolder);
        $this->entityManager->flush();

        $this->addFlash('success', 'Datei wurde verschoben.');

        return $this->redirectToRoute('admin_media_manager', $redirectParams);
    }

    private function ensureMediaFolder(string $name): MediaFolder
    {
        $folderRepository = $this->entityManager->getRepository(MediaFolder::class);
        $folder = $folderRepository->findOneBy(['name' => $name, 'parent' => null]);
        if ($folder instanceof MediaFolder) {
            return $folder;
        }

        $folder = (new MediaFolder())
            ->setName($name)
            ->setSlug(strtolower(str_replace(' ', '-', $name)))
            ->setActive(true)
            ->setSorting(0);

        $this->entityManager->persist($folder);
        $this->entityManager->flush();

        return $folder;
    }

    private function buildMediaAssetName(\Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile): string
    {
        $clientName = str_replace("\0", '', (string) $uploadedFile->getClientOriginalName());
        $clientName = trim(basename(str_replace('\\', '/', $clientName)));
        if ('' === $clientName) {
            return 'Upload ' . (new \DateTimeImmutable())->format('d.m.Y H:i:s');
        }

        $extension = pathinfo($clientName, \PATHINFO_EXTENSION);
        $baseName = '' !== $extension ? mb_substr($clientName, 0, -1 * (mb_strlen($extension) + 1)) : $clientName;
        $baseName = trim($baseName);

        return '' !== $baseName ? $baseName : $clientName;
    }

    private function syncBrandLogoMediaFolder(): void
    {
        $projectDir = rtrim((string) $this->getParameter('kernel.project_dir'), '/');
        $sourceDir = $projectDir . '/assets/icons/brands-cars/Icons';
        if (!is_dir($sourceDir)) {
            return;
        }

        $folderRepository = $this->entityManager->getRepository(MediaFolder::class);
        $assetRepository = $this->entityManager->getRepository(MediaAsset::class);

        $folder = $folderRepository->createQueryBuilder('f')
            ->andWhere('LOWER(f.name) = :name OR LOWER(f.slug) = :slug')
            ->setParameter('name', 'markenlogos')
            ->setParameter('slug', 'markenlogos')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $folderWasCreated = false;
        if (!$folder instanceof MediaFolder) {
            $folder = (new MediaFolder())
                ->setName('Markenlogos')
                ->setSlug('markenlogos')
                ->setDescription('Verknuepft mit assets/icons/brands-cars/Icons')
                ->setActive(true)
                ->setSorting(0);

            $this->entityManager->persist($folder);
            $folderWasCreated = true;
        }

        $files = glob($sourceDir . '/*.{svg,png,jpg,jpeg,webp,gif,avif}', GLOB_BRACE) ?: [];
        if ([] === $files) {
            $this->entityManager->flush();

            return;
        }

        usort($files, static fn (string $a, string $b): int => strcasecmp(basename($a), basename($b)));

        $relativePaths = [];
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $relativePaths[] = 'assets/icons/brands-cars/Icons/' . basename($file);
        }

        if ([] === $relativePaths) {
            $this->entityManager->flush();

            return;
        }

        if ($folderWasCreated) {
            $this->entityManager->flush();
        } else {
            $linkedAssetCount = (int) $assetRepository->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->andWhere('a.folder = :folder')
                ->andWhere('a.filePath LIKE :pathPrefix')
                ->setParameter('folder', $folder)
                ->setParameter('pathPrefix', 'assets/icons/brands-cars/Icons/%')
                ->getQuery()
                ->getSingleScalarResult();

            if ($linkedAssetCount === count($relativePaths)) {
                return;
            }
        }

        $existingAssets = $assetRepository->createQueryBuilder('a')
            ->andWhere('a.filePath IN (:paths)')
            ->setParameter('paths', $relativePaths)
            ->getQuery()
            ->getResult();

        $assetByPath = [];
        foreach ($existingAssets as $asset) {
            if ($asset instanceof MediaAsset && null !== $asset->getFilePath()) {
                $assetByPath[$asset->getFilePath()] = $asset;
            }
        }

        $sorting = 0;
        foreach ($relativePaths as $relativePath) {
            $absolutePath = $projectDir . '/' . $relativePath;
            $extension = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
            $name = pathinfo($relativePath, PATHINFO_FILENAME);
            $asset = $assetByPath[$relativePath] ?? null;

            if (!$asset instanceof MediaAsset) {
                $asset = (new MediaAsset())
                    ->setName($name)
                    ->setFilePath($relativePath);

                $this->entityManager->persist($asset);
            }

            $asset
                ->setFolder($folder)
                ->setMediaType('svg' === $extension || 'ico' === $extension ? MediaAsset::TYPE_ICON : MediaAsset::TYPE_IMAGE)
                ->setMimeType($this->guessMediaMimeType($extension))
                ->setFileSize(is_file($absolutePath) ? filesize($absolutePath) ?: null : null)
                ->setActive(true)
                ->setSorting($sorting);

            ++$sorting;
        }

        $this->entityManager->flush();
    }

    private function guessMediaMimeType(string $extension): ?string
    {
        return match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'avif' => 'image/avif',
            'ico' => 'image/x-icon',
            default => null,
        };
    }

    public function configureDashboard(): Dashboard
    {
        $logoUrl = $this->packages->getUrl('images/dct_power.png');

        return Dashboard::new()
            ->renderContentMaximized()
            ->setDefaultColorScheme(ColorScheme::DARK)
            ->setFaviconPath($logoUrl)
            ->setTitle(sprintf('<img src="%s" alt="DCT Power" style="height:42px;max-width:100%%;display:block;">', $logoUrl))
            ->setTranslationDomain('admin')
            ->setLocales([
                'de' => 'Deutsch',
            ]);
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateFormat('dd.MM.yyyy')
            ->setTimeFormat('HH:mm')
            ->setDateTimeFormat('dd.MM.yyyy HH:mm')
            ->setTimezone('Europe/Berlin');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css')
            ->addJsFile('https://code.jquery.com/jquery-3.7.1.min.js')
            ->addJsFile('https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js')
            ->addJsFile('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js')
            ->addCssFile('css/admin-toolbar.css')
            ->useCustomIconSet()
            ->addAssetMapperEntry('admin');
    }

    public function configureMenuItems(): iterable
    {
        $this->profileStart('easyadmin.menu');

        try {
        if ($this->isPageAllowed(AdminPageRegistry::DASHBOARD)) {
            yield MenuItem::linkToDashboard('Dashboard', 'ic:round-dashboard');
        }
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_journal'))) {
            yield MenuItem::linkToRoute('Journal', 'mdi:newspaper-variant-outline', 'admin_journal');
        }
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_statistics'))) {
            yield MenuItem::linkToRoute('Statistiken', 'akar-icons:statistic-up', 'admin_statistics');
        }
        $searchItems = [];
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_search'))) {
            $searchItems[] = MenuItem::linkToRoute('Meilisearch', 'material-symbols:search', 'admin_search');
        }
        /*
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_search_chat'))) {
            $searchItems[] = MenuItem::linkToRoute('Chat Suche', 'material-symbols:chat-outline', 'admin_search_chat');
        }*/
        if ($searchItems) {
            yield MenuItem::section('Suche');
            foreach ($searchItems as $item) {
                yield $item;
            }
        }

        $arbeitItems = [];
        if ($this->isPageAllowed(AdminPageRegistry::crudKey(AuftragCrudController::class))) {
            $arbeitItems[] = MenuItem::linkTo(AuftragCrudController::class, 'Aufträge', 'lets-icons:order')
                ->setAction('index')
                ->setCssClass('tfd-nav-item tfd-nav-item--orders');
        }
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_communication'))) {
            $arbeitItems[] = MenuItem::linkToRoute('Kommunikation', 'ic:outline-email', 'admin_communication')
                ->setCssClass('tfd-nav-item tfd-nav-item--communication');
        }
        if ($this->isPageAllowed(AdminPageRegistry::crudKey(KundeCrudController::class))) {
            $arbeitItems[] = MenuItem::linkTo(KundeCrudController::class, 'Kunden', 'raphael:customer')
                ->setAction('index')
                ->setCssClass('tfd-nav-item tfd-nav-item--customers');
        }
        if ($this->isPageAllowed(AdminPageRegistry::crudKey(KundeCreditTransactionCrudController::class))) {
            $arbeitItems[] = MenuItem::linkTo(KundeCreditTransactionCrudController::class, 'Credits', 'tabler:credits')->setAction('index');
        }
        if ($arbeitItems) {
            yield MenuItem::section('ARbeit');
            foreach ($arbeitItems as $item) {
                yield $item;
            }
        }
            /*       
        $leadItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(LeadCrudController::class) => MenuItem::linkTo(LeadCrudController::class, 'Leads', 'mdi:account-convert')->setAction('index'),
        ]);
        if ($leadItems) {
            yield MenuItem::section('Leads');
            foreach ($leadItems as $item) {
                yield $item;
            }
        }
*/
        $fahrzeugeItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_vehicle_database_overview') => MenuItem::linkToRoute('Uebersicht', 'tdesign:summary', 'admin_vehicle_database_overview'),
            AdminPageRegistry::routeKey('admin_vehicle_usage_statistics') => MenuItem::linkToRoute('Statistik', 'akar-icons:statistic-up', 'admin_vehicle_usage_statistics'),
            AdminPageRegistry::crudKey(VehicleDataPoolCrudController::class) => MenuItem::linkTo(VehicleDataPoolCrudController::class, 'Datenbestände', 'mdi:database-cog')->setAction('index'),
            AdminPageRegistry::crudKey(VehicleDataContextCrudController::class) => MenuItem::linkTo(VehicleDataContextCrudController::class, 'Datenkontexte', 'mdi:source-branch')->setAction('index'),
            AdminPageRegistry::crudKey(VehicleTypeCrudController::class) => MenuItem::linkTo(VehicleTypeCrudController::class, 'Fahrzeugtypen', 'gridicons:types')->setAction('index'),
            AdminPageRegistry::crudKey(VehicleBrandCrudController::class) => MenuItem::linkTo(VehicleBrandCrudController::class, 'Marken', 'streamline:apple-remix')->setAction('index'),
            AdminPageRegistry::crudKey(VehicleSeriesCrudController::class) => MenuItem::linkTo(VehicleSeriesCrudController::class, 'Baureihen', 'fluent-mdl2:build-definition')->setAction('index'),
            AdminPageRegistry::crudKey(VehicleModelCrudController::class) => MenuItem::linkTo(VehicleModelCrudController::class, 'Modelle', 'carbon:model-alt')->setAction('index'),
            AdminPageRegistry::crudKey(VehicleEngineCrudController::class) => MenuItem::linkTo(VehicleEngineCrudController::class, 'Motoren', 'line-md:engine-filled')->setAction('index'),
            AdminPageRegistry::crudKey(AntriebsartCrudController::class) => MenuItem::linkTo(AntriebsartCrudController::class, 'Antriebsarten', 'mdi:petrol-pump')->setAction('index'),
            AdminPageRegistry::crudKey(VehicleStageCrudController::class) => MenuItem::linkTo(VehicleStageCrudController::class, 'Stages', 'ix:gaugechart')->setAction('index'),
            AdminPageRegistry::routeKey('admin_vehicle_gearbox') => MenuItem::linkToRoute('Getriebe', 'game-icons:gear-stick', 'admin_vehicle_gearbox'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\GearboxStageOptionCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\GearboxStageOptionCrudController::class, 'Getriebe-Stage-Optionen', 'mdi:table-cog')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\GearboxStageInfoCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\GearboxStageInfoCrudController::class, 'Getriebe-Stage-Infos', 'mdi:text-box-outline')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\GearboxDtcCodeCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\GearboxDtcCodeCrudController::class, 'Getriebe-Fehlercodes', 'mdi:alert-circle-outline')->setAction('index'),
            AdminPageRegistry::routeKey('admin_vehicle_ecu') => MenuItem::linkToRoute('ECU', 'streamline:computer-chip-1', 'admin_vehicle_ecu'),
            AdminPageRegistry::routeKey('admin_vehicle_tools') => MenuItem::linkToRoute('Tools', 'streamline:database-setting-solid', 'admin_vehicle_tools'),
        ]);
        $fahrzeugStammdatenItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(\App\Controller\Admin\BrandMasterCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\BrandMasterCrudController::class, 'Brand-Master', 'mdi:tag-multiple')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\PerformanceBrandCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\PerformanceBrandCrudController::class, 'Sportmarke', 'mdi:speedometer')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\EcuManufacturerMasterCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\EcuManufacturerMasterCrudController::class, 'ECU-Hersteller-Master', 'mdi:factory')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\EcuMasterCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\EcuMasterCrudController::class, 'ECU-Master', 'streamline:computer-chip-1')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\EcuOptionMasterCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\EcuOptionMasterCrudController::class, 'ECU-Option-Master', 'mdi:tune-variant')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\EcuOptionHintCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\EcuOptionHintCrudController::class, 'ECU-Optionen Hinweise', 'mdi:tooltip-text-outline')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\EinbauHinweisCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\EinbauHinweisCrudController::class, 'Einbau-Hinweise', 'mdi:map-marker-radius')->setAction('index'),
        ]);
        if ($fahrzeugeItems) {
            yield MenuItem::section('Fahrzeugdatenbank');
            yield MenuItem::subMenu('Fahrzeuge', 'tdesign:vehicle-filled')->setSubItems($fahrzeugeItems);
        }
        if ($fahrzeugStammdatenItems) {
            yield MenuItem::subMenu('Stammdaten', 'mdi:database-search')->setSubItems($fahrzeugStammdatenItems);
        }

        $scraperItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_scraper_control') => MenuItem::linkToRoute('Scraper', 'mdi:robot-industrial', 'admin_scraper_control'),
            AdminPageRegistry::routeKey('admin_scraper_import') => MenuItem::linkToRoute('Import', 'mdi:file-import', 'admin_scraper_import'),
            AdminPageRegistry::routeKey('admin_scraper_output_import') => MenuItem::linkToRoute('Import Output', 'mdi:file-import-outline', 'admin_scraper_output_import'),
        ]);
        if ($scraperItems) {
            yield MenuItem::section('Scraper');
            foreach ($scraperItems as $item) {
                yield $item;
            }
        }

        $ecuIntelligenceItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_tool_news_control') => MenuItem::linkToRoute('Steuerung', 'mdi:play-circle-outline', 'admin_tool_news_control'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\ToolProviderCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\ToolProviderCrudController::class, 'Tool-Anbieter', 'mdi:domain')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\ToolNewsSourceCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\ToolNewsSourceCrudController::class, 'News-Quellen', 'mdi:rss')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\ToolNewsItemCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\ToolNewsItemCrudController::class, 'Anbieter-News', 'mdi:newspaper-variant-outline')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\DetectedEcuCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\DetectedEcuCrudController::class, 'Erkannte ECUs', 'mdi:chip')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\EcuDetectionPatternCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\EcuDetectionPatternCrudController::class, 'Erkennungsmuster', 'mdi:magnify-scan')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\VehicleCoverageMatchCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\VehicleCoverageMatchCrudController::class, 'Fahrzeug-Matches', 'fa fa-car-side')->setAction('index'),
            AdminPageRegistry::crudKey(\App\Controller\Admin\VehiclePlatformAliasCrudController::class) => MenuItem::linkTo(\App\Controller\Admin\VehiclePlatformAliasCrudController::class, 'Plattform-Aliase', 'mdi:link-variant')->setAction('index'),
        ]);
        if ($ecuIntelligenceItems) {
            yield MenuItem::section('ECU Intelligence');
            foreach ($ecuIntelligenceItems as $item) {
                yield $item;
            }
        }

        $toolsItems = [];
        $profitItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(ProfitCalculatorCrudController::class) => MenuItem::linkTo(ProfitCalculatorCrudController::class, 'Calculator', 'hugeicons:calculator')->setAction('index'),
            AdminPageRegistry::crudKey(ProfitCalculatorVehicleTypeCrudController::class) => MenuItem::linkTo(ProfitCalculatorVehicleTypeCrudController::class, 'Fahrzeugtypen', 'gridicons:types')->setAction('index'),
            AdminPageRegistry::crudKey(ProfitCalculatorOptionCrudController::class) => MenuItem::linkTo(ProfitCalculatorOptionCrudController::class, 'Optionen', 'streamline:controller-1-remix')->setAction('index'),
            AdminPageRegistry::crudKey(ProfitCalculatorPriceTierCrudController::class) => MenuItem::linkTo(ProfitCalculatorPriceTierCrudController::class, 'Preisstaffeln', 'gg:list')->setAction('index'),
        ]);
        if ($profitItems) {
            $toolsItems[] = MenuItem::subMenu('Profitcalculator', 'fluent:money-calculator-20-regular')->setSubItems($profitItems);
        }

        $dynoItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_dyno_stats') => MenuItem::linkToRoute('Statistik', 'la:chart-line', 'admin_dyno_stats'),
            AdminPageRegistry::crudKey(DynoVehicleCrudController::class) => MenuItem::linkTo(DynoVehicleCrudController::class, 'Fahrzeuge', 'f7:car-fill')->setAction('index'),
            AdminPageRegistry::crudKey(DynoCustomerCrudController::class) => MenuItem::linkTo(DynoCustomerCrudController::class, 'Kunden', 'carbon:customer')->setAction('index'),
            AdminPageRegistry::crudKey(DynoTripCrudController::class) => MenuItem::linkTo(DynoTripCrudController::class, 'Fahrten', 'material-symbols-light:your-trips')->setAction('index'),
            AdminPageRegistry::crudKey(DynoSprintCrudController::class) => MenuItem::linkTo(DynoSprintCrudController::class, 'Sprints', 'fluent-mdl2:sprint')->setAction('index'),
            AdminPageRegistry::crudKey(DynoDtcCrudController::class) => MenuItem::linkTo(DynoDtcCrudController::class, 'DTCs', 'material-symbols:error-outline-rounded')->setAction('index'),
        ]);
        if ($dynoItems) {
            $toolsItems[] = MenuItem::subMenu('Dyno', 'cil:gauge')->setSubItems($dynoItems);
        }

        $fehlerItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(DtcCategoryCrudController::class) => MenuItem::linkTo(DtcCategoryCrudController::class, 'Kategorien', 'tdesign:task-error-filled')->setAction('index'),
            AdminPageRegistry::crudKey(DtcCodeCrudController::class) => MenuItem::linkTo(DtcCodeCrudController::class, 'Codes', 'material-symbols:car-crash-rounded')->setAction('index'),
        ]);
        if ($fehlerItems) {
            $toolsItems[] = MenuItem::subMenu('Fehlercodes', 'material-symbols:error-outline-rounded')->setSubItems($fehlerItems);
        }

        if ($this->isPageAllowed(AdminPageRegistry::crudKey(SoftwareCrudController::class))) {
            $toolsItems[] = MenuItem::linkTo(SoftwareCrudController::class, 'Software', 'hugeicons:software')->setAction('index');
        }
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_vin_decoder'))) {
            $toolsItems[] = MenuItem::linkToRoute('VIN-Decoder', 'carbon:array-numbers', 'admin_vin_decoder');
        }
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_vpic_explorer'))) {
            $toolsItems[] = MenuItem::linkToRoute('NHTSA vPIC', 'carbon:globe', 'admin_vpic_explorer');
        }
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_test_actions'))) {
            $toolsItems[] = MenuItem::linkToRoute('TestActions', 'mdi:test-tube', 'admin_test_actions');
        }
            if ($this->isPageAllowed(AdminPageRegistry::routeKey('socialfux_app'))) {
                $toolsItems[] = MenuItem::linkToUrl('SocialFux', 'mdi:palette-outline', '/socialfux')->setLinkTarget('_blank');
            }

            /**
             * Partner Wizard
             * Erstmal direkt einblenden, damit du es sicher im EasyAdmin siehst.
             * Wenn alles läuft, kann man es später wieder über AdminPageRegistry/Rechte filtern.
             */
            $partnerWizardItems = [
                MenuItem::linkTo(PartnerWizardClientCrudController::class, 'Einstellungen', 'mdi:tune')
                    ->setAction('index'),

                MenuItem::linkTo(PartnerWizardStepCrudController::class, 'Schritte', 'mdi:format-list-numbered')
                    ->setAction('index'),

                MenuItem::linkTo(PartnerWizardQuestionCrudController::class, 'Fragen', 'mdi:comment-question-outline')
                    ->setAction('index'),

                MenuItem::linkTo(PartnerWizardQuestionOptionCrudController::class, 'Antwortoptionen', 'mdi:format-list-bulleted')
                    ->setAction('index'),

                MenuItem::linkTo(PartnerWizardToolCrudController::class, 'Tool-Empfehlungen', 'mdi:wrench-cog')
                    ->setAction('index'),

                MenuItem::linkTo(PartnerWizardCombinationCrudController::class, 'Kombinationen', 'mdi:set-merge')
                    ->setAction('index'),

                MenuItem::linkTo(PartnerWizardPackageCrudController::class, 'Pakete', 'mdi:package-variant-closed')
                    ->setAction('index'),

                MenuItem::linkTo(PartnerWizardLeadCrudController::class, 'Anfragen', 'mdi:account-convert')
                    ->setAction('index'),
            ];

            $toolsItems[] = MenuItem::subMenu('Partner Wizard', 'mdi:account-wrench')
                ->setSubItems($partnerWizardItems);

            if ($toolsItems) {
                yield MenuItem::section('Tools');
                foreach ($toolsItems as $item) {
                    yield $item;
                }
            }

        $projectItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_projects_todo') => MenuItem::linkToRoute('ToDo', 'mdi:clipboard-text-outline', 'admin_projects_todo'),
            AdminPageRegistry::routeKey('admin_projects_ideas') => MenuItem::linkToRoute('Ideen', 'mdi:lightbulb-on-outline', 'admin_projects_ideas'),
        ]);
        if ($projectItems) {
            yield MenuItem::section('Projekte');
            foreach ($projectItems as $item) {
                yield $item;
            }
        }
/*
        $aiItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(AiProjectCrudController::class) => MenuItem::linkTo(AiProjectCrudController::class, 'Projekte', 'mdi:folder-outline')->setAction('index'),
            AdminPageRegistry::routeKey('admin_ai_chat') => MenuItem::linkToRoute('Chat', 'mdi:robot', 'admin_ai_chat'),
            AdminPageRegistry::routeKey('admin_ai_agent') => MenuItem::linkToRoute('Agent', 'mdi:robot-outline', 'admin_ai_agent'),
            AdminPageRegistry::routeKey('admin_ai_firecrawl') => MenuItem::linkToRoute('Firecrawl', 'mdi:spider-web', 'admin_ai_firecrawl'),
            AdminPageRegistry::routeKey('admin_ai_dalle') => MenuItem::linkToRoute('DALL-E', 'mdi:image-plus', 'admin_ai_dalle'),
            AdminPageRegistry::routeKey('admin_ai_image_analysis') => MenuItem::linkToRoute('Bildanalyse', 'mdi:image-search', 'admin_ai_image_analysis'),
            AdminPageRegistry::routeKey('admin_ai_pdf_analysis') => MenuItem::linkToRoute('PDF-Analyse', 'mdi:file-pdf-box', 'admin_ai_pdf_analysis'),
            AdminPageRegistry::routeKey('admin_ai_vehicle') => MenuItem::linkToRoute('Fahrzeugdatenbank', 'mdi:car-search', 'admin_ai_vehicle'),
        ]);
        if ($aiItems) {
            yield MenuItem::section('AI');
            foreach ($aiItems as $item) {
                yield $item;
            }
        }
*/
        $ecommerceItems = [];
        $commerceStatsItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_shop_statistics') => MenuItem::linkToRoute('Statistik', 'la:chart-line', 'admin_shop_statistics'),
        ]);
        if ($commerceStatsItems) {
            $ecommerceItems = array_merge($ecommerceItems, $commerceStatsItems);
        }

        $catalogItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(ShopProductCrudController::class) => MenuItem::linkTo(ShopProductCrudController::class, 'Produkte', 'mdi:package-variant-closed')->setAction('index'),
            AdminPageRegistry::crudKey(ShopCategoryCrudController::class) => MenuItem::linkTo(ShopCategoryCrudController::class, 'Kategorien', 'material-symbols:category')->setAction('index'),
            AdminPageRegistry::crudKey(ShopManufacturerCrudController::class) => MenuItem::linkTo(ShopManufacturerCrudController::class, 'Hersteller', 'mdi:factory')->setAction('index'),
            AdminPageRegistry::crudKey(ShopTagCrudController::class) => MenuItem::linkTo(ShopTagCrudController::class, 'Tags', 'mdi:tag-outline')->setAction('index'),
            AdminPageRegistry::crudKey(ShopDownloadCrudController::class) => MenuItem::linkTo(ShopDownloadCrudController::class, 'Downloads', 'material-symbols:download')->setAction('index'),
            AdminPageRegistry::crudKey(ShopVideoCrudController::class) => MenuItem::linkTo(ShopVideoCrudController::class, 'Videos', 'material-symbols:video-library')->setAction('index'),
            AdminPageRegistry::crudKey(ShopProductOptionCrudController::class) => MenuItem::linkTo(ShopProductOptionCrudController::class, 'Optionen', 'material-symbols:settings-input-component')->setAction('index'),
            AdminPageRegistry::crudKey(ShopProductOptionValueCrudController::class) => MenuItem::linkTo(ShopProductOptionValueCrudController::class, 'Optionswerte', 'material-symbols:playlist-add')->setAction('index'),
            AdminPageRegistry::crudKey(ShopProductDeliveryScopeCrudController::class) => MenuItem::linkTo(ShopProductDeliveryScopeCrudController::class, 'Lieferumfang', 'material-symbols:inventory-2')->setAction('index'),
            AdminPageRegistry::crudKey(ShopProductLinkCrudController::class) => MenuItem::linkTo(ShopProductLinkCrudController::class, 'Produktlinks', 'material-symbols:link')->setAction('index'),
        ]);
        if ($catalogItems) {
            $ecommerceItems[] = MenuItem::subMenu('Katalog', 'carbon:catalog')->setSubItems($catalogItems);
        }

        $commerceSettingsItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(ShopTaxCrudController::class) => MenuItem::linkTo(ShopTaxCrudController::class, 'Steuern', 'tabler:receipt-tax')->setAction('index'),
            AdminPageRegistry::crudKey(ShopShippingMethodCrudController::class) => MenuItem::linkTo(ShopShippingMethodCrudController::class, 'Versandarten', 'mdi:truck-fast')->setAction('index'),
            AdminPageRegistry::crudKey(ShopPaymentMethodCrudController::class) => MenuItem::linkTo(ShopPaymentMethodCrudController::class, 'Zahlungsarten', 'mdi:credit-card-outline')->setAction('index'),
        ]);
        if ($commerceSettingsItems) {
            $ecommerceItems[] = MenuItem::subMenu('Einstellungen', 'material-symbols:settings')->setSubItems($commerceSettingsItems);
        }

        $commerceOrderItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(ShopOrderCrudController::class) => MenuItem::linkTo(ShopOrderCrudController::class, 'Bestellungen', 'lets-icons:order')->setAction('index')->setCssClass('tfd-nav-item tfd-nav-item--shop-orders'),
        ]);
        if ($commerceOrderItems) {
            $ecommerceItems[] = MenuItem::subMenu('Bestellungen', 'lets-icons:order')->setSubItems($commerceOrderItems);
        }

        if ($ecommerceItems) {
            yield MenuItem::section('eCommerce');
            foreach ($ecommerceItems as $item) {
                yield $item;
            }
        }
/*
        $marketingItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(MarketingAdSizeCrudController::class) => MenuItem::linkTo(MarketingAdSizeCrudController::class, 'Anzeigen', 'mdi:billboard')->setAction('index'),
            AdminPageRegistry::crudKey(MarketingCategoryCrudController::class) => MenuItem::linkTo(MarketingCategoryCrudController::class, 'Kategorien', 'material-symbols:category')->setAction('index'),
        ]);
        if ($marketingItems) {
            yield MenuItem::section('Marketing');
            foreach ($marketingItems as $item) {
                yield $item;
            }
        }

        yield MenuItem::section('Social Media');
        yield MenuItem::linkToRoute('Instagram Übersicht', 'fa-brands:instagram', 'admin_instagram_index');
        yield MenuItem::linkToRoute('Instagram Accounts', 'fa-solid:users', 'admin_instagram_account_index');
        yield MenuItem::linkToUrl('Instagram verbinden', 'fa-solid:link', '/instagram/connect');
*/
        $mediaItems = [];
        $mediaSubItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_media_manager') => MenuItem::linkToRoute('Manager', 'dashicons:admin-media', 'admin_media_manager'),
            AdminPageRegistry::crudKey(MediaFolderCrudController::class) => MenuItem::linkTo(MediaFolderCrudController::class, 'Ordner', 'material-symbols:folder-rounded')->setAction('index'),
            AdminPageRegistry::crudKey(MediaAssetCrudController::class) => MenuItem::linkTo(MediaAssetCrudController::class, 'Dateien', 'basil:file-solid')->setAction('index'),
        ]);
        if ($mediaSubItems) {
            $mediaItems[] = MenuItem::subMenu('Media', 'material-symbols-light:media-link')->setSubItems($mediaSubItems);
        }
        /*
        $websiteSubItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(TemplateCrudController::class) => MenuItem::linkTo(TemplateCrudController::class, 'Templates', 'eos-icons:templates')->setAction('index'),
            AdminPageRegistry::crudKey(WebsiteSnifferCrudController::class) => MenuItem::linkTo(WebsiteSnifferCrudController::class, 'Sniffer', 'game-icons:sniffing-dog')->setAction('index'),
            AdminPageRegistry::crudKey(DemoCrudController::class) => MenuItem::linkTo(DemoCrudController::class, 'Demos', 'carbon:demo')->setAction('index'),
        ]);

        if ($websiteSubItems) {
            $mediaItems[] = MenuItem::subMenu('Websites', 'icon-park-outline:web-page')->setSubItems($websiteSubItems);
        }*/
        if ($mediaItems) {
            yield MenuItem::section('Media');
            foreach ($mediaItems as $item) {
                yield $item;
            }
        }

        if ($this->isPageAllowed(AdminPageRegistry::crudKey(WebsiteWidgetConfigCrudController::class))) {
            yield MenuItem::section('Website Widget');
            yield MenuItem::linkTo(WebsiteWidgetConfigCrudController::class, 'Website Widget', 'mdi:widgets-outline')->setAction('index');
        }

        $finanzenItems = [];
        $creditsSubItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_credits_statistics') => MenuItem::linkToRoute('Statistik', 'la:chart-line', 'admin_credits_statistics'),
            AdminPageRegistry::crudKey(KundeCreditTransactionCrudController::class) => MenuItem::linkTo(KundeCreditTransactionCrudController::class, 'Historie', 'ic:baseline-history')->setAction('index')->setCssClass('tfd-nav-item tfd-nav-item--credits'),
            AdminPageRegistry::crudKey(CreditPackageCrudController::class) => MenuItem::linkTo(CreditPackageCrudController::class, 'Pakete', 'qlementine-icons:money-16')->setAction('index'),
            AdminPageRegistry::crudKey(CreditCategoryCrudController::class) => MenuItem::linkTo(CreditCategoryCrudController::class, 'Kategorien', 'grommet-icons:money')->setAction('index'),
        ]);
        if ($creditsSubItems) {
            $finanzenItems[] = MenuItem::subMenu('Credits', 'tabler:credits')->setSubItems($creditsSubItems);
        }
        $aboSubItems = $this->filterMenuItems([
            AdminPageRegistry::routeKey('admin_abo_statistics') => MenuItem::linkToRoute('Statistik', 'la:chart-line', 'admin_abo_statistics'),
            AdminPageRegistry::crudKey(AboCrudController::class) => MenuItem::linkTo(AboCrudController::class, 'Aktiv', 'nrk:list-active')->setAction('index'),
            AdminPageRegistry::crudKey(AboAuswahlCrudController::class) => MenuItem::linkTo(AboAuswahlCrudController::class, 'Erstellen', 'material-symbols:add-card')->setAction('index'),
        ]);
        if ($aboSubItems) {
            $finanzenItems[] = MenuItem::subMenu('Abo', 'stash:subscription-list-solid')->setSubItems($aboSubItems);
        }
        if ($this->isPageAllowed(AdminPageRegistry::crudKey(ZahlungsartCrudController::class))) {
            $finanzenItems[] = MenuItem::linkTo(ZahlungsartCrudController::class, 'Zahlungsarten', 'fluent-mdl2:payment-card')->setAction('index');
        }
        if ($this->isPageAllowed(AdminPageRegistry::routeKey('admin_lexoffice'))) {
            $finanzenItems[] = MenuItem::linkToRoute('Lexoffice', 'ph:invoice-duotone', 'admin_lexoffice');
        }
        if ($finanzenItems) {
            yield MenuItem::section('Finanzen');
            foreach ($finanzenItems as $item) {
                yield $item;
            }
        }

        $verwaltungItems = [];
        $einstellungenItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(VehicleFrontendSettingsCrudController::class) => MenuItem::linkTo(VehicleFrontendSettingsCrudController::class, 'Konfigurator', 'streamline-sharp:tune-adjust-volume-solid')->setAction('index'),
            AdminPageRegistry::crudKey(FirmaCrudController::class) => MenuItem::linkTo(FirmaCrudController::class, 'Firmen', 'uil:building')->setAction('index'),
            AdminPageRegistry::crudKey(PortalCrudController::class) => MenuItem::linkTo(PortalCrudController::class, 'Portale', 'mdi:web-box')->setAction('index'),
            AdminPageRegistry::crudKey(PortalPriceListCrudController::class) => MenuItem::linkTo(PortalPriceListCrudController::class, 'Portal-Preislisten', 'mdi:format-list-bulleted-type')->setAction('index'),
            AdminPageRegistry::crudKey(PortalPriceListItemCrudController::class) => MenuItem::linkTo(PortalPriceListItemCrudController::class, 'Portal-Preisliste Positionen', 'mdi:playlist-edit')->setAction('index'),
            AdminPageRegistry::crudKey(WatermarkPresetCrudController::class) => MenuItem::linkTo(WatermarkPresetCrudController::class, 'Wasserzeichen-Vorlagen', 'mdi:watermark')->setAction('index'),
            AdminPageRegistry::routeKey('admin_admin_watermark_areas') => MenuItem::linkToRoute('Wasserzeichen-Bereiche', 'mdi:image-area', 'admin_admin_watermark_areas'),
            AdminPageRegistry::crudKey(TextbausteinCrudController::class) => MenuItem::linkTo(TextbausteinCrudController::class, 'Textbausteine', 'mdi:text-box-multiple')->setAction('index'),
        ]);
        if ($einstellungenItems) {
            $verwaltungItems[] = MenuItem::subMenu('Einstellungen', 'el:adjust-alt')->setSubItems($einstellungenItems);
        }
        $securityItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(ZugangsdatenCrudController::class) => MenuItem::linkTo(ZugangsdatenCrudController::class, 'Zugangsdaten', 'solar:user-id-bold')->setAction('index'),
            AdminPageRegistry::crudKey(ApiTokenCrudController::class) => MenuItem::linkTo(ApiTokenCrudController::class, 'API Tokens', 'oui:token-key')->setAction('index'),
            AdminPageRegistry::crudKey(WebhookEndpointCrudController::class) => MenuItem::linkTo(WebhookEndpointCrudController::class, 'Webhooks', 'ph:webhooks-logo')->setAction('index'),
            AdminPageRegistry::crudKey(AdminPageAccessCrudController::class) => MenuItem::linkTo(AdminPageAccessCrudController::class, 'Seitenrechte', 'mdi:eye-settings')->setAction('index'),
        ]);
        if ($securityItems) {
            $verwaltungItems[] = MenuItem::subMenu('Security', 'mdi:car-security')->setSubItems($securityItems);
        }
        $emailItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(EmailKontoCrudController::class) => MenuItem::linkTo(EmailKontoCrudController::class, 'Konfiguration', 'streamline-sharp:tune-adjust-volume-solid')->setAction('index'),
            AdminPageRegistry::crudKey(EmailLabelCrudController::class) => MenuItem::linkTo(EmailLabelCrudController::class, 'Labels', 'material-symbols:label-rounded')->setAction('index'),
            AdminPageRegistry::routeKey('admin_support_newsletter') => MenuItem::linkToRoute('Newsletter', 'mdi:email-newsletter', 'admin_support_newsletter'),
        ]);
        if ($emailItems) {
            $verwaltungItems[] = MenuItem::subMenu('E-Mail', 'ic:outline-email')->setSubItems($emailItems);
        }
        $userItems = $this->filterMenuItems([
            AdminPageRegistry::crudKey(UserCrudController::class) => MenuItem::linkTo(UserCrudController::class, 'Anzeigen', 'fluent:eye-16-filled')->setAction('index'),
            AdminPageRegistry::crudKey(UserGroupCrudController::class) => MenuItem::linkTo(UserGroupCrudController::class, 'Gruppe anlegen', 'ri:group-line')->setAction('index'),
        ]);
        if ($userItems) {
            $verwaltungItems[] = MenuItem::subMenu('Benutzer', 'carbon:customer')->setSubItems($userItems);
        }
        if ($verwaltungItems) {
            yield MenuItem::section('Verwaltung');
            foreach ($verwaltungItems as $item) {
                yield $item;
            }
        }

        yield MenuItem::section('LINKS');
        yield MenuItem::linkToLogout('Logout', 'mdi:logout');
        } finally {
            $this->profileStop('easyadmin.menu');
        }
    }

    private function isPageAllowed(string $pageKey): bool
    {
        return $this->pageAccessService->isAllowed($pageKey, $this->getUser());
    }

    /**
     * @param array<string, MenuItem> $items
     * @return list<MenuItem>
     */
    private function filterMenuItems(array $items): array
    {
        $allowed = [];
        foreach ($items as $pageKey => $item) {
            if ($this->isPageAllowed($pageKey)) {
                $allowed[] = $item;
            }
        }

        return $allowed;
    }

    private function profileStart(string $name): void
    {
        $this->stopwatch?->start($name, 'easyadmin-dashboard');
    }

    private function profileStop(string $name): void
    {
        if (null === $this->stopwatch || !$this->stopwatch->isStarted($name)) {
            return;
        }

        $this->stopwatch->stop($name);
    }

    /**
     * @return array{
     *   firmen: array<int, array{id:int,name:string,kunden:array<int, array{id:int,name:string}>>>,
     *   messagesByFirma: array<int, array<int, array{
     *      senderType:string,senderName:string,message:string,createdAt:string,kundeName:string|null
     *   }>>
     * }
     */
    private function buildChatData(): array
    {
        $firmen = $this->firmaRepository->findBy([], ['name' => 'ASC']);

        $firmenPayload = [];
        foreach ($firmen as $firma) {
            $kundenPayload = [];
            foreach ($firma->getKunden() as $kunde) {
                $kundenPayload[] = [
                    'id' => (int) $kunde->getId(),
                    'name' => trim((string) $kunde),
                ];
            }

            usort($kundenPayload, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

            $firmenPayload[] = [
                'id' => (int) $firma->getId(),
                'name' => (string) $firma->getName(),
                'kunden' => $kundenPayload,
            ];
        }

        $messages = $this->chatMessageRepository->findBy([], ['createdAt' => 'DESC'], 500);
        $messages = array_reverse($messages);
        $messagesByFirma = [];

        foreach ($messages as $message) {
            $firmaId = $message->getFirma()?->getId();
            if (null === $firmaId) {
                continue;
            }

            $messagesByFirma[$firmaId] ??= [];
            $messagesByFirma[$firmaId][] = [
                'senderType' => $message->getSenderType(),
                'senderName' => $message->getSenderName(),
                'message' => $message->getMessage(),
                'createdAt' => $message->getCreatedAt()?->format('d.m.Y H:i') ?? '',
                'kundeName' => $message->getKunde() ? (string) $message->getKunde() : null,
            ];
        }

        return [
            'firmen' => $firmenPayload,
            'messagesByFirma' => $messagesByFirma,
        ];
    }

    /**
     * @param array<int, MediaFolder> $folders
     * @return array<int, array{
     *   folder: MediaFolder,
     *   children: array<int, mixed>,
     *   containsSelected: bool
     * }>
     */
    private function buildMediaFolderTree(array $folders, int $selectedFolderId): array
    {
        $byParent = [];
        foreach ($folders as $folder) {
            $parentId = $folder->getParent()?->getId() ?? 0;
            $byParent[$parentId] ??= [];
            $byParent[$parentId][] = $folder;
        }

        $build = function (int $parentId) use (&$build, $byParent, $selectedFolderId): array {
            $nodes = [];
            foreach ($byParent[$parentId] ?? [] as $folder) {
                $children = $build((int) $folder->getId());
                $containsSelected = (int) $folder->getId() === $selectedFolderId;

                if (!$containsSelected) {
                    foreach ($children as $childNode) {
                        if (($childNode['containsSelected'] ?? false) === true) {
                            $containsSelected = true;
                            break;
                        }
                    }
                }

                $nodes[] = [
                    'folder' => $folder,
                    'children' => $children,
                    'containsSelected' => $containsSelected,
                ];
            }

            return $nodes;
        };

        return $build(0);
    }

    /**
     * @param array<int, array{folder: MediaFolder, children: array<int, mixed>}> $tree
     * @return array<int, array{id: int, label: string}>
     */
    private function buildMediaFolderOptions(array $tree, int $depth = 0): array
    {
        $options = [];

        foreach ($tree as $node) {
            $folder = $node['folder'];
            $prefix = $depth > 0 ? str_repeat('— ', $depth) : '';

            $options[] = [
                'id' => (int) $folder->getId(),
                'label' => $prefix . $folder->getName(),
            ];

            $childOptions = $this->buildMediaFolderOptions($node['children'], $depth + 1);
            foreach ($childOptions as $childOption) {
                $options[] = $childOption;
            }
        }

        return $options;
    }

    /**
     * @return array{
     *   groups: array<int, array{
     *     collection: string,
     *     count: int,
     *     icons: array<int, array{id: string, name: string}>
     *   }>,
     *   total: int
     * }
     */
    private function buildUxIconCatalog(): array
    {
        $basePath = rtrim((string) $this->getParameter('kernel.project_dir'), '/')
            . '/assets/icons';
        if (!is_dir($basePath)) {
            return [
                'groups' => [],
                'total' => 0,
            ];
        }

        $groups = [];
        $collectionDirs = glob($basePath . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($collectionDirs as $collectionDir) {
            $collection = basename($collectionDir);
            if ('' === $collection) {
                continue;
            }

            $files = glob($collectionDir . '/*.svg') ?: [];
            if ([] === $files) {
                continue;
            }

            $icons = [];
            foreach ($files as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                if ('' === $name) {
                    continue;
                }
                $icons[] = [
                    'id' => $collection . ':' . $name,
                    'name' => $name,
                ];
            }

            if ([] === $icons) {
                continue;
            }

            usort($icons, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

            $groups[] = [
                'collection' => $collection,
                'count' => count($icons),
                'icons' => $icons,
            ];
        }

        usort($groups, static fn (array $a, array $b): int => strcasecmp($a['collection'], $b['collection']));

        $total = 0;
        foreach ($groups as $group) {
            $total += (int) $group['count'];
        }

        return [
            'groups' => $groups,
            'total' => $total,
        ];
    }
}
