<?php

namespace App\Controller\Admin;

use App\Entity\DynoCustomer;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/dyno/customers', routeName: 'admin_dyno_customer')]
final class DynoCustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DynoCustomer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Dyno Kunde')
            ->setEntityLabelInPlural('Dyno Kunden')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncAction = Action::new('sync', 'Daten abrufen', 'raphael:clouddown')
            ->linkToRoute('admin_dyno_sync', ['type' => 'customers'])
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $syncAction);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('email', 'E-Mail'),
            TextField::new('phone', 'Telefon')->hideOnIndex(),
            AssociationField::new('vehicle', 'Fahrzeug')->autocomplete(),
            TextareaField::new('notes', 'Notizen')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
        ];
    }
}
