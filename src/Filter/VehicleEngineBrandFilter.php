<?php

namespace App\Filter;

use App\Entity\VehicleBrand;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\EntityFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use Symfony\Contracts\Translation\TranslatableInterface;

final class VehicleEngineBrandFilter implements FilterInterface
{
    use FilterTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName = 'vehicleBrand', $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label ?? 'Hersteller')
            ->setFormType(EntityFilterType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle')
            ->setFormTypeOption('value_type_options.class', VehicleBrand::class)
            ->setFormTypeOption('value_type_options.multiple', false);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $rootAlias = $filterDataDto->getEntityAlias();
        $comparison = $filterDataDto->getComparison();
        $parameterName = $filterDataDto->getParameterName();
        $value = $filterDataDto->getValue();

        $modelAlias = 'ea_vehicle_engine_model_' . $parameterName;
        $seriesAlias = 'ea_vehicle_engine_series_' . $parameterName;

        $this->joinOnce($queryBuilder, $rootAlias, 'vehicleModel', $modelAlias);
        $this->joinOnce($queryBuilder, $modelAlias, 'vehicleSeries', $seriesAlias);

        if (null === $value || ($value instanceof Collection && 0 === $value->count())) {
            $queryBuilder->andWhere(sprintf('%s.vehicleBrand %s', $seriesAlias, $comparison));

            return;
        }

        $orX = new Orx();
        $orX->add(sprintf('%s.vehicleBrand %s (:%s)', $seriesAlias, $comparison, $parameterName));
        if (ComparisonType::NEQ === $comparison || 'NOT IN' === $comparison) {
            $orX->add(sprintf('%s.vehicleBrand IS NULL', $seriesAlias));
        }

        $queryBuilder
            ->andWhere($orX)
            ->setParameter($parameterName, $value);
    }

    private function joinOnce(QueryBuilder $queryBuilder, string $parentAlias, string $association, string $joinAlias): void
    {
        foreach ($queryBuilder->getDQLPart('join')[$parentAlias] ?? [] as $join) {
            if ($join->getAlias() === $joinAlias) {
                return;
            }
        }

        $queryBuilder->leftJoin(sprintf('%s.%s', $parentAlias, $association), $joinAlias);
    }
}
