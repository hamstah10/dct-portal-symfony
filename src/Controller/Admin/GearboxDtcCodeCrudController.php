<?php

namespace App\Controller\Admin;

use App\Entity\GearboxDtcCode;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/gearbox-dtc-codes', routeName: 'admin_gearbox_dtc_code')]
final class GearboxDtcCodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GearboxDtcCode::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Getriebe-Fehlercode')
            ->setEntityLabelInPlural('Getriebe-Fehlercodes')
            ->setDefaultSort(['sorting' => 'ASC'])
            ->setPaginatorPageSize(50);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('obdCode', 'OBDII-Code'),
            TextField::new('vagCode', 'VAG-Code')->setRequired(false),
            TextField::new('description', 'Beschreibung'),
            IntegerField::new('sorting', 'Sortierung')->hideOnIndex(),
            BooleanField::new('active', 'Aktiv'),
        ];
    }
}
