<?php

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

trait ActiveVehicleDataPoolIndexFilterTrait
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $rootAlias = (string) (current($queryBuilder->getRootAliases()) ?: 'entity');
        $poolAlias = $rootAlias . '_activePool';

        if (!in_array($poolAlias, $queryBuilder->getAllAliases(), true)) {
            $queryBuilder->leftJoin(sprintf('%s.vehicleDataPool', $rootAlias), $poolAlias);
        }

        return $queryBuilder
            ->andWhere(sprintf('(%s.id IS NULL OR %s.active = :activeVehicleDataPool)', $poolAlias, $poolAlias))
            ->setParameter('activeVehicleDataPool', true);
    }
}
