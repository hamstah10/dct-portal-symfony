<?php

namespace App\Twig\Components;

use App\Controller\Admin\AuftragCrudController;
use App\Entity\Auftrag;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('auftrag_search', template: 'components/auftrag_search.html.twig')]
final class AuftragSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public int $limit = 40;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * @return array<int, array{
     *   url:string,
     *   label:string,
     *   subLabel:?string,
     *   status:string,
     *   statusTone:string,
     *   createdAt:?string
     * }>
     */
    public function getResults(): array
    {
        $query = trim($this->query);
        $length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);
        if ($length < 2) {
            return [];
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($query) : strtolower($query);
        $term = '%' . $normalized . '%';
        $orderId = $this->extractOrderId($normalized);

        $qb = $this->entityManager->createQueryBuilder()
            ->select('a', 'k', 'f')
            ->from(Auftrag::class, 'a')
            ->leftJoin('a.kunde', 'k')
            ->leftJoin('k.firma', 'f')
            ->setParameter('term', $term)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(max(1, $this->limit))
        ;

        $expr = $qb->expr()->orX(
            $qb->expr()->like('LOWER(COALESCE(a.vehicleDisplay, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.vehicleType, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.manufacturer, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.model, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.engine, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.built, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.tuningTool, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.tuningMethod, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.stageLabel, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.deviceName, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.slaveOrMaster, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.customerEmail, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(a.fileName, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.kundennummer, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.vorname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.nachname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.firmenname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(f.name, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(f.kuerzel, \'\'))', ':term')
        );

        if (null !== $orderId) {
            $expr->add($qb->expr()->eq('a.id', ':orderId'));
            $qb->setParameter('orderId', $orderId);
        }

        $items = $qb
            ->andWhere($expr)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $auftrag) {
            if (!$auftrag instanceof Auftrag) {
                continue;
            }

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(AuftragCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($auftrag->getId())
                    ->generateUrl(),
                'label' => $this->buildLabel($auftrag),
                'subLabel' => $this->buildSubLabel($auftrag),
                'status' => $auftrag->getStatus(),
                'statusTone' => $this->statusTone($auftrag->getStatus()),
                'createdAt' => $auftrag->getCreatedAt()?->format('d.m.Y H:i'),
            ];
        }

        return $results;
    }

    private function extractOrderId(string $query): ?int
    {
        if ('' === $query) {
            return null;
        }

        if (preg_match('/^(a-?)?(\\d+)$/i', $query, $matches)) {
            return (int) $matches[2];
        }

        return null;
    }

    private function buildLabel(Auftrag $auftrag): string
    {
        $parts = [];
        $parts[] = $auftrag->getOrderNumber();
        $kunde = $auftrag->getKunde();
        if ($kunde) {
            $name = trim(sprintf('%s %s', (string) $kunde->getVorname(), (string) $kunde->getNachname()));
            $firmaName = trim((string) ($kunde->getFirma()?->getName() ?? ''));
            $firmaDisplay = trim((string) ($kunde->getFirmenname() ?? ''));

            if ('' !== $firmaDisplay) {
                $parts[] = $firmaDisplay;
            } elseif ('' !== $firmaName) {
                $parts[] = $firmaName;
            } elseif ('' !== $name) {
                $parts[] = $name;
            }
        }

        return implode(' · ', array_values(array_filter($parts, static fn (string $value): bool => '' !== $value)));
    }

    private function buildSubLabel(Auftrag $auftrag): ?string
    {
        $subParts = [];

        $kundennummer = trim((string) ($auftrag->getKunde()?->getKundennummer() ?? ''));
        if ('' !== $kundennummer) {
            $subParts[] = 'Kundennr. ' . $kundennummer;
        }

        $vehicle = $this->buildVehicleLabel($auftrag);
        if (null !== $vehicle) {
            $subParts[] = $vehicle;
        }

        $tuning = $this->buildTuningLabel($auftrag);
        if (null !== $tuning) {
            $subParts[] = $tuning;
        }

        return $subParts ? implode(' | ', $subParts) : null;
    }

    private function buildVehicleLabel(Auftrag $auftrag): ?string
    {
        $vehicleDisplay = trim((string) ($auftrag->getVehicleDisplay() ?? ''));
        if ('' !== $vehicleDisplay) {
            return $vehicleDisplay;
        }

        $parts = [
            trim((string) ($auftrag->getManufacturer() ?? '')),
            trim((string) ($auftrag->getModel() ?? '')),
            trim((string) ($auftrag->getEngine() ?? '')),
            trim((string) ($auftrag->getBuilt() ?? '')),
        ];

        $parts = array_values(array_filter($parts, static fn (string $value): bool => '' !== $value));
        return $parts ? implode(' ', $parts) : null;
    }

    private function buildTuningLabel(Auftrag $auftrag): ?string
    {
        $parts = [
            trim((string) ($auftrag->getTuningTool() ?? '')),
            trim((string) ($auftrag->getTuningMethod() ?? '')),
            trim((string) ($auftrag->getStageLabel() ?? '')),
            trim((string) ($auftrag->getDeviceName() ?? '')),
            trim((string) ($auftrag->getSlaveOrMaster() ?? '')),
        ];

        $parts = array_values(array_filter($parts, static fn (string $value): bool => '' !== $value));
        return $parts ? implode(' · ', $parts) : null;
    }

    private function statusTone(string $status): string
    {
        return match ($status) {
            Auftrag::STATUS_RECEIVED => 'info',
            Auftrag::STATUS_IN_PROGRESS => 'warning',
            Auftrag::STATUS_COMPLETED => 'success',
            Auftrag::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }
}
