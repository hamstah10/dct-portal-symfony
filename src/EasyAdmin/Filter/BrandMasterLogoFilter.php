<?php

namespace App\EasyAdmin\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\NullFilterType;
use Symfony\Contracts\Translation\TranslatableInterface;

final class BrandMasterLogoFilter implements FilterInterface
{
    use FilterTrait;

    private const VALUE_WITHOUT_LOGO = 'without_logo';
    private const VALUE_WITH_LOGO = 'with_logo';

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(NullFilterType::class)
            ->setFormTypeOption('choices', [
                'Ohne Logo' => self::VALUE_WITHOUT_LOGO,
                'Mit Logo' => self::VALUE_WITH_LOGO,
            ]);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $alias = $filterDataDto->getEntityAlias();

        if (self::VALUE_WITHOUT_LOGO === $filterDataDto->getValue()) {
            $queryBuilder->andWhere(sprintf(
                '(%1$s.image IS NULL OR %1$s.image = \'\') AND %1$s.imageMediaAsset IS NULL',
                $alias
            ));

            return;
        }

        $queryBuilder->andWhere(sprintf(
            '(%1$s.image IS NOT NULL AND %1$s.image <> \'\') OR %1$s.imageMediaAsset IS NOT NULL',
            $alias
        ));
    }
}
