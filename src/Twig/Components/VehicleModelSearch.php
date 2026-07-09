<?php

namespace App\Twig\Components;

use App\Controller\Admin\VehicleModelCrudController;
use App\Entity\VehicleModel;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('vehicle_model_search', template: 'components/vehicle_model_search.html.twig')]
final class VehicleModelSearch
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
            ->select('m', 's', 'b')
            ->from(VehicleModel::class, 'm')
            ->leftJoin('m.vehicleSeries', 's')
            ->leftJoin('s.vehicleBrand', 'b')
            ->andWhere('LOWER(m.name) LIKE :term OR LOWER(COALESCE(s.name, \'\')) LIKE :term OR LOWER(COALESCE(b.name, \'\')) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('m.name', 'ASC')
            ->setMaxResults(max(1, $this->limit))
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $model) {
            if (!$model instanceof VehicleModel) {
                continue;
            }

            $label = trim($model->getName());
            if ('' === $label) {
                $label = 'Modell #' . (string) ($model->getId() ?? '');
            }

            $hierarchy = $this->formatHierarchy(
                $model->getVehicleSeries()?->getVehicleBrand()?->getName(),
                $model->getVehicleSeries()?->getName()
            );

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(VehicleModelCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($model->getId())
                    ->generateUrl(),
                'label' => $label,
                'subLabel' => $hierarchy,
                'active' => $model->isActive(),
            ];
        }

        return $results;
    }

    private function formatHierarchy(?string $brand, ?string $series): ?string
    {
        $parts = [];
        if (null !== $brand && '' !== trim($brand)) {
            $parts[] = $brand;
        }
        if (null !== $series && '' !== trim($series)) {
            $parts[] = $series;
        }

        return $parts ? implode(' / ', $parts) : null;
    }
}
