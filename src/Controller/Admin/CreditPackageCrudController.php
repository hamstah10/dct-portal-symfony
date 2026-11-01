<?php

namespace App\Controller\Admin;

use App\Entity\CreditPackage;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

#[AdminCrud(routePath: '/credits/pakete', routeName: 'admin_credit_package')]
final class CreditPackageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CreditPackage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Paket')
            ->setEntityLabelInPlural('Pakete')
            ->setDefaultSort(['credits' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        $statusField = TextField::new('lexwareArticleId', 'Lexoffice Status')
            ->onlyOnIndex();

        if ('index' === $pageName) {
            $statusField->setTemplatePath('admin/field/lexoffice_status.html.twig');
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titel'),
            IntegerField::new('credits', 'Credits'),
            MoneyField::new('price', 'Preis')->setCurrency('EUR')->setStoredAsCents(false),
            MoneyField::new('pricePerFile', 'Preis pro File')->setCurrency('EUR')->setStoredAsCents(false),
            TextField::new('paypalFee', 'PayPal Gebühr')->onlyOnIndex()->setHelp('1,99 % + 0,35 €'),
            TextField::new('paypalNet', 'Netto nach PayPal')->onlyOnIndex(),
            AssociationField::new('category', 'Kategorie')->autocomplete(),
            AssociationField::new('firmas', 'Firmen')->autocomplete(),
            $statusField,
            FormField::new('lexoffice_section', 'Lexoffice Synchronisation')->onlyOnDetail(),
            TextField::new('lexwareArticleId', 'Lexoffice Artikel-ID')
                ->setHelp('Automatisch gefüllt bei Synchronisation')
                ->onlyOnDetail(),
        ];
    }
}

