<?php

namespace App\Twig\Components;

use App\Controller\Admin\VehicleStageCrudController;
use App\Entity\VehicleStage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('vehicle_stage_search', template: 'components/vehicle_stage_search.html.twig')]
final class VehicleStageSearch
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

        $items = $this->entityManager->createQueryBuilder()
            ->select('s', 'e', 'm', 'vs', 'b')
            ->from(VehicleStage::class, 's')
            ->leftJoin('s.vehicleEngine', 'e')
            ->leftJoin('e.vehicleModel', 'm')
            ->leftJoin('m.vehicleSeries', 'vs')
            ->leftJoin('vs.vehicleBrand', 'b')
            ->andWhere('LOWER(s.name) LIKE :term OR LOWER(COALESCE(e.name, \'\')) LIKE :term OR LOWER(COALESCE(e.engineCode, \'\')) LIKE :term OR LOWER(COALESCE(m.name, \'\')) LIKE :term OR LOWER(COALESCE(vs.name, \'\')) LIKE :term OR LOWER(COALESCE(b.name, \'\')) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('s.name', 'ASC')
            ->setMaxResults(max(1, $this->limit))
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $stage) {
            if (!$stage instanceof VehicleStage) {
                continue;
            }

            $label = trim($stage->getName());
            if ('' === $label) {
                $label = 'Stage #' . (string) ($stage->getId() ?? '');
            }

            $subParts = [];
            $engineName = $stage->getVehicleEngine()?->getName();
            if (null !== $engineName && '' !== trim($engineName)) {
                $subParts[] = $engineName;
            }
            $hierarchy = $this->formatHierarchy(
                $stage->getVehicleEngine()?->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand()?->getName(),
                $stage->getVehicleEngine()?->getVehicleModel()?->getVehicleSeries()?->getName(),
                $stage->getVehicleEngine()?->getVehicleModel()?->getName()
            );
            if (null !== $hierarchy) {
                $subParts[] = $hierarchy;
            }

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(VehicleStageCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($stage->getId())
                    ->generateUrl(),
                'label' => $label,
                'subLabel' => $subParts ? implode(' · ', $subParts) : null,
                'active' => $stage->isActive(),
            ];
        }

        return $results;
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
