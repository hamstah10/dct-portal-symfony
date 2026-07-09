<?php

namespace App\Filter;

use App\Entity\Firma;
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

final class AuftragFirmaFilter implements FilterInterface
{
    use FilterTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName = 'firma', $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label ?? 'Firma')
            ->setFormType(EntityFilterType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle')
            ->setFormTypeOption('value_type_options.class', Firma::class)
            ->setFormTypeOption('value_type_options.multiple', false);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $alias = $filterDataDto->getEntityAlias();
        $comparison = $filterDataDto->getComparison();
        $parameterName = $filterDataDto->getParameterName();
        $value = $filterDataDto->getValue();
        $joinAlias = 'ea_kunde_' . $parameterName;

        $this->joinKundeOnce($queryBuilder, $alias, $joinAlias);

        if (null === $value || ($value instanceof Collection && 0 === $value->count())) {
            $queryBuilder->andWhere(sprintf('%s.firma %s', $joinAlias, $comparison));

            return;
        }

        $orX = new Orx();
        $orX->add(sprintf('%s.firma %s (:%s)', $joinAlias, $comparison, $parameterName));
        if (ComparisonType::NEQ === $comparison || 'NOT IN' === $comparison) {
            $orX->add(sprintf('%s.firma IS NULL', $joinAlias));
        }

        $queryBuilder
            ->andWhere($orX)
            ->setParameter($parameterName, $value);
    }

    private function joinKundeOnce(QueryBuilder $queryBuilder, string $rootAlias, string $joinAlias): void
    {
        foreach ($queryBuilder->getDQLPart('join')[$rootAlias] ?? [] as $join) {
            if ($join->getAlias() === $joinAlias) {
                return;
            }
        }

        $queryBuilder->leftJoin(sprintf('%s.kunde', $rootAlias), $joinAlias);
    }
}
