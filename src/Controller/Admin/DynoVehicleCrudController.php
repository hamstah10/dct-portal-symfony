<?php

namespace App\Controller\Admin;

use App\Entity\DynoVehicle;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/dyno/vehicles', routeName: 'admin_dyno_vehicle')]
final class DynoVehicleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DynoVehicle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Dyno Fahrzeug')
            ->setEntityLabelInPlural('Dyno Fahrzeuge')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncAction = Action::new('sync', 'Daten abrufen', 'raphael:clouddown')
            ->linkToRoute('admin_dyno_sync', ['type' => 'vehicles'])
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $syncAction);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('make', 'Marke')->hideOnIndex(),
            TextField::new('model', 'Modell')->hideOnIndex(),
            IntegerField::new('year', 'Baujahr')->hideOnIndex(),
            TextField::new('stage', 'Stage')->hideOnIndex(),
            IntegerField::new('maxPs', 'Max PS')->hideOnIndex(),
            IntegerField::new('maxNm', 'Max Nm')->hideOnIndex(),
            IntegerField::new('maxRpm', 'Max RPM')->hideOnIndex(),
            IntegerField::new('idleRpm', 'Idle RPM')->hideOnIndex(),
            IntegerField::new('engineCc', 'Hubraum')->hideOnIndex(),
            IntegerField::new('cylinders', 'Zylinder')->hideOnIndex(),
            TextField::new('fuelType', 'Kraftstoff')->hideOnIndex(),
            BooleanField::new('isActive', 'Aktiv'),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
        ];
    }
}
