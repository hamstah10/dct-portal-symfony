<?php

namespace App\Twig\Components;

use App\Controller\Admin\VehicleEngineCrudController;
use App\Entity\VehicleEngine;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('vehicle_engine_search', template: 'components/vehicle_engine_search.html.twig')]
final class VehicleEngineSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public ?string $vehicleDataPoolId = null;

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
     *   active:?bool
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
        $vehicleDataPoolId = $this->getVehicleDataPoolId();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e', 'm', 's', 'b')
            ->from(VehicleEngine::class, 'e')
            ->leftJoin('e.vehicleModel', 'm')
            ->leftJoin('m.vehicleSeries', 's')
            ->leftJoin('s.vehicleBrand', 'b')
            ->andWhere('LOWER(e.name) LIKE :term OR LOWER(COALESCE(e.engineCode, \'\')) LIKE :term OR LOWER(COALESCE(e.fuel, \'\')) LIKE :term OR LOWER(COALESCE(m.name, \'\')) LIKE :term OR LOWER(COALESCE(s.name, \'\')) LIKE :term OR LOWER(COALESCE(b.name, \'\')) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('b.name', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->addOrderBy('m.name', 'ASC')
            ->addOrderBy('e.name', 'ASC');

        if (null !== $vehicleDataPoolId) {
            $queryBuilder
                ->andWhere('IDENTITY(e.vehicleDataPool) = :vehicleDataPoolId')
                ->setParameter('vehicleDataPoolId', $vehicleDataPoolId);
        }

        $items = $queryBuilder
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $engine) {
            if (!$engine instanceof VehicleEngine) {
                continue;
            }

            $label = trim($engine->getName());
            $engineCode = trim((string) ($engine->getEngineCode() ?? ''));
            if ('' !== $engineCode) {
                $label = '' !== $label ? $label . ' (' . $engineCode . ')' : $engineCode;
            }
            if ('' === $label) {
                $label = 'Motor #' . (string) ($engine->getId() ?? '');
            }

            $hierarchy = $this->formatHierarchy(
                $engine->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand()?->getName(),
                $engine->getVehicleModel()?->getVehicleSeries()?->getName(),
                $engine->getVehicleModel()?->getName()
            );

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(VehicleEngineCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($engine->getId())
                    ->generateUrl(),
                'label' => $label,
                'subLabel' => $hierarchy,
                'active' => $engine->isActive(),
            ];
        }

        return $results;
    }

    public function hasVehicleDataPoolFilter(): bool
    {
        return null !== $this->getVehicleDataPoolId();
    }

    private function getVehicleDataPoolId(): ?int
    {
        $id = (int) trim((string) ($this->vehicleDataPoolId ?? ''));

        return $id > 0 ? $id : null;
    }

    private function formatHierarchy(?string $brand, ?string $series, ?string $model): ?string
    {
        $parts = [];
        if (null !== $brand && '' !== trim($brand)) {
            $parts[] = $brand;
        }
        if (null !== $series && '' !== trim($series)) {
            $parts[] = $series;
        }
        if (null !== $model && '' !== trim($model)) {
            $parts[] = $model;
        }

        return $parts ? implode(' / ', $parts) : null;
    }
}
