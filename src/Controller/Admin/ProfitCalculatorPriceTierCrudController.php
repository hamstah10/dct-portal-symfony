<?php

namespace App\Controller\Admin;

use App\Entity\ProfitCalculatorPriceTier;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

#[AdminCrud(routePath: '/chiptuning/profitcalculator/preisstufen', routeName: 'admin_profit_calculator_price_tiers')]
final class ProfitCalculatorPriceTierCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProfitCalculatorPriceTier::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Preisstaffel')
            ->setEntityLabelInPlural('Preisstaffeln')
            ->setDefaultSort(['credits' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            IntegerField::new('credits', 'Credits'),
            MoneyField::new('purchasePrice', 'EK')->setCurrency('EUR')->setStoredAsCents(false),
            MoneyField::new('pricePerFile', 'Preis pro File')->setCurrency('EUR')->setStoredAsCents(false),
            BooleanField::new('isDefaultPrice', 'Standard'),
        ];
    }
}
