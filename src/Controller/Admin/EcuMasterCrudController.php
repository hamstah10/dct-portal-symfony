<?php

namespace App\Controller\Admin;

use App\Entity\EcuManufacturerMaster;
use App\Entity\EcuMaster;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/ecu-master', routeName: 'admin_ecu_master')]
final class EcuMasterCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return EcuMaster::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ECU-Master')
            ->setEntityLabelInPlural('ECU-Master')
            ->setSearchFields(['name', 'slug', 'type', 'controlUnitKind', 'manufacturerMaster.name'])
            ->overrideTemplate('crud/index', 'admin/ecu_master/index.html.twig');
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        if (Crud::PAGE_INDEX !== (string) $responseParameters->get('pageName')) {
            return $responseParameters;
        }

        $responseParameters->set('ecuMasterManufacturerOptions', $this->loadManufacturerOptions());
        $responseParameters->set('ecuMasterTypeOptions', $this->loadTypeOptions());

        return $responseParameters;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $request = $this->getContext()?->getRequest();
        if (null === $request) {
            return $queryBuilder;
        }

        $rootAlias = (string) (current($queryBuilder->getRootAliases()) ?: 'entity');
        $manufacturerId = trim((string) $request->query->get('ecuMasterManufacturer', ''));
        if ('' !== $manufacturerId && ctype_digit($manufacturerId)) {
            $queryBuilder
                ->leftJoin($rootAlias . '.manufacturerMaster', 'ecuMasterManufacturerInlineFilter')
                ->andWhere('ecuMasterManufacturerInlineFilter.id = :ecuMasterManufacturerInlineFilterId')
                ->setParameter('ecuMasterManufacturerInlineFilterId', (int) $manufacturerId);
        }

        $type = trim((string) $request->query->get('ecuMasterType', ''));
        if ('' !== $type) {
            $queryBuilder
                ->andWhere($rootAlias . '.type = :ecuMasterInlineType')
                ->setParameter('ecuMasterInlineType', $type);
        }

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('manufacturerMaster', 'Hersteller-Master'),
            TextField::new('name'),
            TextField::new('slug'),
            TextField::new('type'),
            TextField::new('controlUnitKind', 'TCU/MCU'),
            TextField::new('image')->hideOnIndex(),
        ];
    }

    /**
     * @return list<EcuManufacturerMaster>
     */
    private function loadManufacturerOptions(): array
    {
        return $this->entityManager
            ->getRepository(EcuManufacturerMaster::class)
            ->createQueryBuilder('manufacturer')
            ->orderBy('manufacturer.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<string>
     */
    private function loadTypeOptions(): array
    {
        $rows = $this->entityManager
            ->getRepository(EcuMaster::class)
            ->createQueryBuilder('ecu')
            ->select('DISTINCT ecu.type AS type')
            ->andWhere('ecu.type IS NOT NULL')
            ->andWhere("TRIM(ecu.type) <> ''")
            ->orderBy('ecu.type', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_values(array_filter(array_map(
            static fn (array $row): string => trim((string) ($row['type'] ?? '')),
            $rows
        )));
    }
}
