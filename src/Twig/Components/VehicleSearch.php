<?php

namespace App\Twig\Components;

use App\Controller\Admin\AntriebsartCrudController;
use App\Controller\Admin\EcuCrudController;
use App\Controller\Admin\EcuManufacturerCrudController;
use App\Controller\Admin\EcuOptionCrudController;
use App\Controller\Admin\GearboxCrudController;
use App\Controller\Admin\GearboxTypeCrudController;
use App\Controller\Admin\ToolManufacturerCrudController;
use App\Controller\Admin\ToolMethodeCrudController;
use App\Controller\Admin\VehicleBrandCrudController;
use App\Controller\Admin\VehicleEngineCrudController;
use App\Controller\Admin\VehicleModelCrudController;
use App\Controller\Admin\VehicleSeriesCrudController;
use App\Controller\Admin\VehicleStageCrudController;
use App\Controller\Admin\VehicleTypeCrudController;
use App\Entity\Antriebsart;
use App\Entity\Ecu;
use App\Entity\EcuManufacturer;
use App\Entity\EcuOption;
use App\Entity\Gearbox;
use App\Entity\GearboxType;
use App\Entity\ToolManufacturer;
use App\Entity\ToolMethode;
use App\Entity\VehicleBrand;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('vehicle_search', template: 'components/vehicle_search.html.twig')]
final class VehicleSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public int $limit = 120;

    #[LiveProp]
    public int $perTypeLimit = 30;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * @return array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}>
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
        $results = [];

        $this->addVehicleTypes($results, $term);
        $this->addVehicleBrands($results, $term);
        $this->addVehicleSeries($results, $term);
        $this->addVehicleModels($results, $term);
        $this->addVehicleEngines($results, $term);
        $this->addVehicleStages($results, $term);
        $this->addGearboxTypes($results, $term);
        $this->addGearboxes($results, $term);
        $this->addAntriebsarten($results, $term);
        $this->addEcuManufacturers($results, $term);
        $this->addEcus($results, $term);
        $this->addEcuOptions($results, $term);
        $this->addToolManufacturers($results, $term);
        $this->addToolMethodes($results, $term);

        if (\count($results) > $this->limit) {
            $results = \array_slice($results, 0, $this->limit);
        }

        return $results;
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addVehicleTypes(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(VehicleType::class, 't')
            ->andWhere('LOWER(t.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('t.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $this->pushResult(
                $results,
                'Fahrzeugtyp',
                $item->getName(),
                null,
                VehicleTypeCrudController::class,
                $item->getId(),
                $item->isActive()
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addVehicleBrands(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('b', 't')
            ->from(VehicleBrand::class, 'b')
            ->leftJoin('b.vehicleType', 't')
            ->andWhere('LOWER(b.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('b.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $sub = $item->getVehicleType()?->getName();
            $this->pushResult(
                $results,
                'Marke',
                $item->getName(),
                $sub,
                VehicleBrandCrudController::class,
                $item->getId(),
                $item->isActive()
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addVehicleSeries(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('s', 'b')
            ->from(VehicleSeries::class, 's')
            ->leftJoin('s.vehicleBrand', 'b')
            ->andWhere('LOWER(s.name) LIKE :term OR LOWER(b.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('s.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $sub = $item->getVehicleBrand()?->getName();
            $this->pushResult(
                $results,
                'Baureihe',
                $item->getName(),
                $sub,
                VehicleSeriesCrudController::class,
                $item->getId(),
                $item->isActive()
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addVehicleModels(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('m', 's', 'b')
            ->from(VehicleModel::class, 'm')
            ->leftJoin('m.vehicleSeries', 's')
            ->leftJoin('s.vehicleBrand', 'b')
            ->andWhere('LOWER(m.name) LIKE :term OR LOWER(s.name) LIKE :term OR LOWER(b.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('m.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $sub = $this->formatHierarchy($item->getVehicleSeries()?->getVehicleBrand(), $item->getVehicleSeries(), null);
            $this->pushResult(
                $results,
                'Modell',
                $item->getName(),
                $sub,
                VehicleModelCrudController::class,
                $item->getId(),
                $item->isActive()
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addVehicleEngines(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('e', 'm', 's', 'b')
            ->from(VehicleEngine::class, 'e')
            ->leftJoin('e.vehicleModel', 'm')
            ->leftJoin('m.vehicleSeries', 's')
            ->leftJoin('s.vehicleBrand', 'b')
            ->andWhere('LOWER(e.name) LIKE :term OR LOWER(e.engineCode) LIKE :term OR LOWER(e.fuel) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('e.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $sub = $this->formatHierarchy(
                $item->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand(),
                $item->getVehicleModel()?->getVehicleSeries(),
                $item->getVehicleModel()
            );
            $this->pushResult(
                $results,
                'Motor',
                $item->getName(),
                $sub,
                VehicleEngineCrudController::class,
                $item->getId(),
                $item->isActive()
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addVehicleStages(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('s', 'e', 'm', 'vs', 'b')
            ->from(VehicleStage::class, 's')
            ->leftJoin('s.vehicleEngine', 'e')
            ->leftJoin('e.vehicleModel', 'm')
            ->leftJoin('m.vehicleSeries', 'vs')
            ->leftJoin('vs.vehicleBrand', 'b')
            ->andWhere('LOWER(s.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('s.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $engine = $item->getVehicleEngine();
            $subParts = [];
            if ($engine?->getName()) {
                $subParts[] = $engine->getName();
            }
            $subHierarchy = $this->formatHierarchy(
                $engine?->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand(),
                $engine?->getVehicleModel()?->getVehicleSeries(),
                $engine?->getVehicleModel()
            );
            if (null !== $subHierarchy) {
                $subParts[] = $subHierarchy;
            }
            $sub = $subParts ? implode(' · ', $subParts) : null;

            $this->pushResult(
                $results,
                'Stage',
                $item->getName(),
                $sub,
                VehicleStageCrudController::class,
                $item->getId(),
                $item->isActive()
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addGearboxTypes(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('g')
            ->from(GearboxType::class, 'g')
            ->andWhere('LOWER(g.title) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('g.title', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $this->pushResult(
                $results,
                'Getriebe-Typ',
                $item->getTitle(),
                null,
                GearboxTypeCrudController::class,
                $item->getId(),
                $item->isActive()
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addGearboxes(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('g', 't')
            ->from(Gearbox::class, 'g')
            ->leftJoin('g.gearboxType', 't')
            ->andWhere('LOWER(g.title) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('g.title', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $sub = $item->getGearboxType()?->getTitle();
            $this->pushResult(
                $results,
                'Getriebe',
                $item->getTitle(),
                $sub,
                GearboxCrudController::class,
                $item->getId(),
                $item->isActive()
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addAntriebsarten(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Antriebsart::class, 'a')
            ->andWhere('LOWER(a.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('a.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $this->pushResult(
                $results,
                'Antriebsart',
                $item->getName(),
                null,
                AntriebsartCrudController::class,
                $item->getId(),
                null
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addEcuManufacturers(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('m')
            ->from(EcuManufacturer::class, 'm')
            ->andWhere('LOWER(m.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('m.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $this->pushResult(
                $results,
                'ECU-Hersteller',
                $item->getName(),
                null,
                EcuManufacturerCrudController::class,
                $item->getId(),
                null
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addEcus(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('e', 'm')
            ->from(Ecu::class, 'e')
            ->leftJoin('e.manufacturer', 'm')
            ->andWhere('LOWER(e.name) LIKE :term OR LOWER(e.type) LIKE :term OR LOWER(e.controlUnitKind) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('e.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $subParts = [];
            if ($item->getManufacturer()?->getName()) {
                $subParts[] = $item->getManufacturer()->getName();
            }
            if ($item->getType()) {
                $subParts[] = $item->getType();
            }
            if ($item->getControlUnitKind()) {
                $subParts[] = $item->getControlUnitKind();
            }
            $sub = $subParts ? implode(' · ', $subParts) : null;

            $this->pushResult(
                $results,
                'ECU',
                $item->getName(),
                $sub,
                EcuCrudController::class,
                $item->getId(),
                null
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addEcuOptions(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('o')
            ->from(EcuOption::class, 'o')
            ->andWhere('LOWER(o.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('o.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $sub = $item->isMainOption() ? 'Main-Option' : null;
            $this->pushResult(
                $results,
                'ECU-Option',
                $item->getName(),
                $sub,
                EcuOptionCrudController::class,
                $item->getId(),
                null
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addToolManufacturers(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(ToolManufacturer::class, 't')
            ->andWhere('LOWER(t.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('t.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $this->pushResult(
                $results,
                'Tool-Hersteller',
                $item->getName(),
                null,
                ToolManufacturerCrudController::class,
                $item->getId(),
                null
            );
        }
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function addToolMethodes(array &$results, string $term): void
    {
        $items = $this->entityManager->createQueryBuilder()
            ->select('t', 'm')
            ->from(ToolMethode::class, 't')
            ->leftJoin('t.toolManufacturers', 'm')
            ->andWhere('LOWER(t.name) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('t.name', 'ASC')
            ->setMaxResults($this->perTypeLimit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $sub = $item->getToolManufacturer()?->getName();
            $this->pushResult(
                $results,
                'Tool-Methode',
                $item->getName(),
                $sub,
                ToolMethodeCrudController::class,
                $item->getId(),
                null
            );
        }
    }

    private function formatHierarchy(?VehicleBrand $brand, ?VehicleSeries $series, ?VehicleModel $model): ?string
    {
        $parts = [];
        if ($brand?->getName()) {
            $parts[] = $brand->getName();
        }
        if ($series?->getName()) {
            $parts[] = $series->getName();
        }
        if ($model?->getName()) {
            $parts[] = $model->getName();
        }

        return $parts ? implode(' / ', $parts) : null;
    }

    /**
     * @param array<int, array{type:string,label:string,subLabel:?string,url:string,active:?bool}> $results
     */
    private function pushResult(
        array &$results,
        string $type,
        string $label,
        ?string $subLabel,
        string $controllerFqcn,
        ?int $id,
        ?bool $active
    ): void {
        if (null === $id) {
            return;
        }

        $normalizedLabel = trim($label);
        if ('' === $normalizedLabel) {
            $normalizedLabel = sprintf('%s #%d', $type, $id);
        }

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController($controllerFqcn)
            ->setAction(Action::DETAIL)
            ->setEntityId($id)
            ->generateUrl();

        $results[] = [
            'type' => $type,
            'label' => $normalizedLabel,
            'subLabel' => $subLabel,
            'url' => $url,
            'active' => $active,
        ];
    }
}
