<?php

namespace App\Controller\Admin;

use App\Entity\EcuManufacturerMaster;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/ecu-manufacturer-master', routeName: 'admin_ecu_manufacturer_master')]
final class EcuManufacturerMasterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EcuManufacturerMaster::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ECU-Hersteller-Master')
            ->setEntityLabelInPlural('ECU-Hersteller-Master');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('slug'),
            TextField::new('logo')->hideOnIndex(),
            TextareaField::new('description')->hideOnIndex(),
        ];
    }
}
