<?php

namespace App\Controller\Admin;

use App\Entity\VehiclePlatformAlias;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecu-intelligence/vehicle-platform-aliases', routeName: 'admin_vehicle_platform_aliases')]
final class VehiclePlatformAliasCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehiclePlatformAlias::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Plattform-Alias')
            ->setEntityLabelInPlural('Plattform-Aliase')
            ->setDefaultSort(['sourceBrand' => 'ASC', 'aliasBrand' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('sourceBrand')
            ->add('aliasBrand')
            ->add('isActive');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnDetail(),
            TextField::new('name', 'Name')
                ->setHelp('Bezeichnung der Plattform, z.B. "Sevel Transporter" oder "VAG MQB".'),
            TextField::new('sourceBrand', 'Quell-Marke')->setRequired(false),
            TextField::new('sourceModel', 'Quell-Modell')->setRequired(false),
            TextField::new('aliasBrand', 'Alias-Marke'),
            TextField::new('aliasModel', 'Alias-Modell')->setRequired(false),
            NumberField::new('scoreWeight', 'Gewichtung')
                ->setNumDecimals(2)
                ->setHelp('Multiplikator (0.0-1.0) fuer den Score-Anteil bei einem Alias-Treffer. Alias-Treffer sind grundsaetzlich schwaecher als direkte Treffer.'),
            BooleanField::new('isActive', 'Aktiv'),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm()->onlyOnDetail(),
        ];
    }
}
