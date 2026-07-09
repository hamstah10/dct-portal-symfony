<?php

namespace App\Controller\Admin;

use App\Entity\DynoDtc;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/dyno/dtcs', routeName: 'admin_dyno_dtc')]
final class DynoDtcCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DynoDtc::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('DTC')
            ->setEntityLabelInPlural('DTCs')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncAction = Action::new('sync', 'Daten abrufen', 'raphael:clouddown')
            ->linkToRoute('admin_dyno_sync', ['type' => 'dtcs'])
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $syncAction);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('code', 'Code'),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
        ];
    }
}
