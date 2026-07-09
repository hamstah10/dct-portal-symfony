<?php

namespace App\Portals\No1tuningfiles\Service;

use App\Entity\Auftrag;
use App\Entity\Kunde;
use App\Portals\No1tuningfiles\Entity\PortalUploadJob;
use App\Portals\No1tuningfiles\Repository\PortalUploadJobRepository;
use App\Repository\AuftragRepository;

final class PortalDashboardBuilder
{
    public function __construct(
        private readonly PortalUploadJobRepository $jobRepository,
        private readonly AuftragRepository $auftragRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Kunde $customer): array
    {
        $jobs = $this->jobRepository->findRecentForCustomer($customer, 6);
        $timelineJobs = $this->jobRepository->findCreatedSinceForCustomer(
            $customer,
            new \DateTimeImmutable('first day of -11 months 00:00:00')
        );
        $statusTotals = $this->jobRepository->countByStatusForCustomer($customer);
        $totalJobs = $this->jobRepository->countForCustomer($customer);
        $jobsThisMonth = $this->jobRepository->countCreatedSinceForCustomer(
            $customer,
            new \DateTimeImmutable('first day of this month 00:00:00')
        );
        $chartData = $this->buildChartData($timelineJobs, $customer->getCredits());

        $openJobs = ($statusTotals[PortalUploadJob::STATUS_UPLOADED] ?? 0) + ($statusTotals[PortalUploadJob::STATUS_IN_PROGRESS] ?? 0);
        $completedJobs = $statusTotals[PortalUploadJob::STATUS_COMPLETED] ?? 0;
        $failedJobs = $statusTotals[PortalUploadJob::STATUS_FAILED] ?? 0;
        $lastJob = $jobs[0] ?? null;
        $lastCompletedJob = null;

        foreach ($jobs as $job) {
            if ($job->getStatus() === PortalUploadJob::STATUS_COMPLETED) {
                $lastCompletedJob = $job;
                break;
            }
        }

        $orderedJobsForCards = $this->orderJobsForCards($jobs);
        $lastVisibleJob = $orderedJobsForCards[0] ?? null;

        return [
            'customer' => $customer,
            'credits' => $customer->getCredits(),
            'recentJobs' => $jobs,
            'jobCount' => $totalJobs,
            'jobsThisMonth' => $jobsThisMonth,
            'statusTotals' => $statusTotals,
            'openJobs' => $openJobs,
            'completedJobs' => $completedJobs,
            'failedJobs' => $failedJobs,
            'lastJobAt' => $lastVisibleJob?->getCreatedAt() ?? $lastJob?->getCreatedAt(),
            'lastCompletedAt' => $lastCompletedJob?->getProcessedAt() ?? $lastCompletedJob?->getReturnedAt() ?? $lastCompletedJob?->getUpdatedAt(),
            'recentJobCards' => array_map(fn (PortalUploadJob $job): array => $this->buildRecentJobCard($job), array_slice($orderedJobsForCards, 0, 6)),
            'kpis' => [
                [
                    'label' => 'Aktive Auftraege',
                    'value' => $openJobs,
                    'caption' => 'Offene Faelle und laufende Bearbeitung',
                    'icon' => 'ri-loader-2-line',
                    'avatarClass' => 'bg-primary',
                    'changeLabel' => $openJobs > 0 ? 'In Bearbeitung' : 'Aktuell leer',
                    'changeClass' => $openJobs > 0 ? 'text-primary' : 'text-muted',
                    'sparkline' => $chartData['sparklineOpen'],
                ],
                [
                    'label' => 'Abgeschlossen',
                    'value' => $completedJobs,
                    'caption' => 'Fertig bearbeitete Auftraege',
                    'icon' => 'ri-checkbox-circle-line',
                    'avatarClass' => 'bg-success',
                    'changeLabel' => $this->formatOptionalDate($lastCompletedJob?->getProcessedAt() ?? $lastCompletedJob?->getReturnedAt() ?? $lastCompletedJob?->getUpdatedAt(), 'Zuletzt %s'),
                    'changeClass' => 'text-success',
                    'sparkline' => $chartData['sparklineCompleted'],
                ],
                [
                    'label' => 'Credits',
                    'value' => $customer->getCredits(),
                    'caption' => 'Verfuegbar fuer neue Auftraege',
                    'icon' => 'ri-coins-line',
                    'avatarClass' => 'bg-primary2',
                    'changeLabel' => $customer->getCredits() > 0 ? 'Bereit fuer den naechsten Auftrag' : 'Bitte Credits aufladen',
                    'changeClass' => $customer->getCredits() > 0 ? 'text-success' : 'text-danger',
                    'sparkline' => $chartData['sparklineCredits'],
                ],
                [
                    'label' => 'Diesen Monat',
                    'value' => $jobsThisMonth,
                    'caption' => 'Neu angelegte Auftraege',
                    'icon' => 'ri-calendar-check-line',
                    'avatarClass' => 'bg-primary3',
                    'changeLabel' => $totalJobs > 0 ? sprintf('%d gesamt im Portal', $totalJobs) : 'Noch keine Historie',
                    'changeClass' => $totalJobs > 0 ? 'text-muted' : 'text-warning',
                    'sparkline' => $chartData['sparklineCreated'],
                ],
            ],
            'statusPanels' => [
                $this->buildStatusPanel('Eingegangen', $statusTotals[PortalUploadJob::STATUS_UPLOADED] ?? 0, 'Neu angelegt und wartet auf Start', 'ri-inbox-archive-line', 'warning'),
                $this->buildStatusPanel('In Bearbeitung', $statusTotals[PortalUploadJob::STATUS_IN_PROGRESS] ?? 0, 'Aktuell beim Tuning-Team', 'ri-settings-4-line', 'primary'),
                $this->buildStatusPanel('Abgeschlossen', $completedJobs, 'Bereits rueckgeliefert oder verarbeitet', 'ri-check-double-line', 'success'),
                $this->buildStatusPanel('Problemfaelle', $failedJobs, 'Benoetigen Rueckfrage oder eine Nacharbeit', 'ri-error-warning-line', 'danger'),
            ],
            'charts' => $chartData,
            'parkedOrders' => $this->buildParkedOrders($customer),
        ];
}

    private function buildRecentJobCard(PortalUploadJob $job): array
    {
        [$statusLabel, $badgeClass] = $this->mapStatus($job->getStatus());

        return [
            'id' => $job->getId(),
            'filename' => $job->getOriginalFilename(),
            'status' => $job->getStatus(),
            'statusLabel' => $statusLabel,
            'badgeClass' => $badgeClass,
            'notes' => $job->getNotes(),
            'sizeLabel' => $this->formatBytes($job->getSizeBytes()),
            'createdAt' => $job->getCreatedAt(),
            'updatedAt' => $job->getUpdatedAt(),
            'processedAt' => $job->getProcessedAt(),
            'returnedAt' => $job->getReturnedAt(),
            'timelineLabel' => $this->buildTimelineLabel($job),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildParkedOrders(Kunde $customer, int $limit = 5): array
    {
        $queryBuilder = $this->auftragRepository->createQueryBuilder('a')
            ->andWhere('a.kunde = :customer')
            ->andWhere('a.status = :status')
            ->setParameter('customer', $customer)
            ->setParameter('status', Auftrag::STATUS_PARKED)
            ->orderBy('a.updatedAt', 'DESC')
            ->setMaxResults($limit);

        $orders = $queryBuilder->getQuery()->getResult();

        return array_map(
            fn (Auftrag $order): array => [
                'id' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
                'vehicleLabel' => $order->getVehicleDisplay() ?: ($order->getManufacturer() ?: '—'),
                'stageLabel' => $order->getStageLabel(),
                'updatedAt' => $order->getUpdatedAt()?->format(\DATE_ATOM) ?? '',
            ],
            $orders
        );
    }

    /**
     * @return array{0:string,1:string}
     */
    private function mapStatus(string $status): array
    {
        return match ($status) {
            PortalUploadJob::STATUS_IN_PROGRESS => ['In Bearbeitung', 'bg-primary-transparent text-primary'],
            PortalUploadJob::STATUS_COMPLETED => ['Abgeschlossen', 'bg-success-transparent text-success'],
            PortalUploadJob::STATUS_FAILED => ['Rueckfrage', 'bg-danger-transparent text-danger'],
            default => ['Eingegangen', 'bg-warning-transparent text-warning'],
        };
    }

    /**
     * @param list<PortalUploadJob> $jobs
     *
     * @return list<PortalUploadJob>
     */
    private function orderJobsForCards(array $jobs): array
    {
        $visible = array_values(array_filter(
            $jobs,
            static fn (PortalUploadJob $job): bool => $job->getStatus() !== PortalUploadJob::STATUS_FAILED
        ));

        if ([] === $visible) {
            return $jobs;
        }

        $failed = array_values(array_filter(
            $jobs,
            static fn (PortalUploadJob $job): bool => $job->getStatus() === PortalUploadJob::STATUS_FAILED
        ));

        return array_merge($visible, $failed);
    }

    /**
     * @return array{label:string,value:int,description:string,icon:string,tone:string}
     */
    private function buildStatusPanel(string $label, int $value, string $description, string $icon, string $tone): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'description' => $description,
            'icon' => $icon,
            'tone' => $tone,
        ];
    }

    private function buildTimelineLabel(PortalUploadJob $job): string
    {
        if ($job->getReturnedAt() instanceof \DateTimeImmutable) {
            return sprintf('Rueckgabe %s', $job->getReturnedAt()->format('d.m.Y H:i'));
        }

        if ($job->getProcessedAt() instanceof \DateTimeImmutable) {
            return sprintf('Bearbeitet %s', $job->getProcessedAt()->format('d.m.Y H:i'));
        }

        if ($job->getUpdatedAt() instanceof \DateTimeImmutable && $job->getUpdatedAt() != $job->getCreatedAt()) {
            return sprintf('Update %s', $job->getUpdatedAt()->format('d.m.Y H:i'));
        }

        return sprintf('Eingang %s', $job->getCreatedAt()->format('d.m.Y H:i'));
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $value = (float) $bytes;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            ++$unitIndex;
        }

        $precision = $unitIndex === 0 ? 0 : 1;

        return number_format($value, $precision, ',', '.') . ' ' . $units[$unitIndex];
    }

    private function formatOptionalDate(?\DateTimeImmutable $date, string $pattern): string
    {
        if (!$date instanceof \DateTimeImmutable) {
            return 'Noch kein Abschluss';
        }

        return sprintf($pattern, $date->format('d.m.Y'));
    }

    /**
     * @param list<PortalUploadJob> $jobs
     * @return array<string, mixed>
     */
    private function buildChartData(array $jobs, int $credits): array
    {
        $months = [];
        $labels = [];

        for ($index = 11; $index >= 0; --$index) {
            $month = new \DateTimeImmutable(sprintf('first day of -%d months 00:00:00', $index));
            $key = $month->format('Y-m');
            $months[$key] = [
                'created' => 0,
                'completed' => 0,
                'failed' => 0,
            ];
            $labels[] = $this->formatMonthLabel($month);
        }

        foreach ($jobs as $job) {
            $createdKey = $job->getCreatedAt()->format('Y-m');
            if (isset($months[$createdKey])) {
                ++$months[$createdKey]['created'];
            }

            $completedAt = $job->getProcessedAt() ?? $job->getReturnedAt();
            if ($completedAt instanceof \DateTimeImmutable) {
                $completedKey = $completedAt->format('Y-m');
                if (isset($months[$completedKey]) && $job->getStatus() === PortalUploadJob::STATUS_COMPLETED) {
                    ++$months[$completedKey]['completed'];
                }
            }

            if ($job->getStatus() === PortalUploadJob::STATUS_FAILED) {
                $failedKey = ($job->getUpdatedAt() ?? $job->getCreatedAt())->format('Y-m');
                if (isset($months[$failedKey])) {
                    ++$months[$failedKey]['failed'];
                }
            }
        }

        $createdSeries = array_map(static fn (array $month): int => $month['created'], array_values($months));
        $completedSeries = array_map(static fn (array $month): int => $month['completed'], array_values($months));
        $failedSeries = array_map(static fn (array $month): int => $month['failed'], array_values($months));
        $activeSeries = [];
        $creditSeries = [];
        $rollingOpen = 0;

        foreach (array_keys($months) as $position => $key) {
            $rollingOpen = max(0, $rollingOpen + $months[$key]['created'] - $months[$key]['completed'] - $months[$key]['failed']);
            $activeSeries[] = $rollingOpen;
            $creditSeries[] = max(0, $credits - (($position + 1) * 2) + ($months[$key]['completed'] * 2));
        }

        return [
            'labels' => $labels,
            'createdSeries' => $createdSeries,
            'completedSeries' => $completedSeries,
            'failedSeries' => $failedSeries,
            'activeSeries' => $activeSeries,
            'creditSeries' => $creditSeries,
            'sparklineOpen' => array_slice($activeSeries, -8),
            'sparklineCompleted' => array_slice($completedSeries, -8),
            'sparklineCredits' => array_slice($creditSeries, -8),
            'sparklineCreated' => array_slice($createdSeries, -8),
            'statusDonut' => [
                $months[array_key_last($months)]['created'],
                $months[array_key_last($months)]['completed'],
                $months[array_key_last($months)]['failed'],
            ],
        ];
    }

    private function formatMonthLabel(\DateTimeImmutable $month): string
    {
        $map = [
            'Jan' => 'Jan',
            'Feb' => 'Feb',
            'Mar' => 'Mrz',
            'Apr' => 'Apr',
            'May' => 'Mai',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Aug' => 'Aug',
            'Sep' => 'Sep',
            'Oct' => 'Okt',
            'Nov' => 'Nov',
            'Dec' => 'Dez',
        ];

        return $map[$month->format('M')] ?? $month->format('m');
    }
}
