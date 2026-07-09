<?php

namespace App\Controller\Admin;

use App\Entity\ProfitCalculator;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[AdminCrud(routePath: '/chiptuning/profitcalculator/calculator', routeName: 'admin_profit_calculator')]
final class ProfitCalculatorCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly string $frontendUrl
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ProfitCalculator::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Profit-Calculator')
            ->setEntityLabelInPlural('Profit-Calculator');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titel'),
            NumberField::new('vatRate', 'MwSt %')->setNumDecimals(2),
            BooleanField::new('purchasePriceIncludeVat', 'EK inkl. MwSt'),
            BooleanField::new('endPriceIncludeVat', 'VK inkl. MwSt'),
            TextField::new('optionsTemplate', 'Options-Template')->hideOnIndex(),
        ];
    }

  public function configureActions(Actions $actions): Actions
{
    $frontendButton = Action::new('frontend', 'Frontend öffnen')
        ->linkToUrl($this->generateUrl('app_profit_calculator'))
        ->createAsGlobalAction()
        ->setIcon('hugeicons:calculator')
        ->addCssClass('btn btn-primary')
        ->setHtmlAttributes([
                'target' => '_blank'
            ]);

    return $actions
        ->add(Crud::PAGE_INDEX, $frontendButton);
}
}
