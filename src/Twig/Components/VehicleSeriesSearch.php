<?php

namespace App\Twig\Components;

use App\Controller\Admin\VehicleSeriesCrudController;
use App\Entity\VehicleSeries;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('vehicle_series_search', template: 'components/vehicle_series_search.html.twig')]
final class VehicleSeriesSearch
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
            ->select('s', 'b')
            ->from(VehicleSeries::class, 's')
            ->leftJoin('s.vehicleBrand', 'b')
            ->andWhere('LOWER(s.name) LIKE :term OR LOWER(COALESCE(b.name, \'\')) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('s.name', 'ASC')
            ->setMaxResults(max(1, $this->limit))
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $series) {
            if (!$series instanceof VehicleSeries) {
                continue;
            }

            $label = trim($series->getName());
            if ('' === $label) {
                $label = 'Baureihe #' . (string) ($series->getId() ?? '');
            }

            $brand = $series->getVehicleBrand()?->getName();
            $subLabel = null;
            if (null !== $brand && '' !== trim($brand)) {
                $subLabel = $brand;
            }

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(VehicleSeriesCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($series->getId())
                    ->generateUrl(),
                'label' => $label,
                'subLabel' => $subLabel,
                'active' => $series->isActive(),
            ];
        }

        return $results;
    }
}
