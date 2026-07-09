<?php

namespace App\Controller\Admin;

use App\Entity\DynoTrip;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/dyno/trips', routeName: 'admin_dyno_trip')]
final class DynoTripCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DynoTrip::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Dyno Fahrt')
            ->setEntityLabelInPlural('Dyno Fahrten')
            ->setDefaultSort(['startTime' => 'DESC'])
            ->overrideTemplate('crud/detail', 'admin/dyno/trip_detail.html.twig')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncAction = Action::new('sync', 'Daten abrufen', 'raphael:clouddown')
            ->linkToRoute('admin_dyno_sync', ['type' => 'trips'])
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $syncAction)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('id')->hideOnForm()->hideOnIndex(),
            DateTimeField::new('startTime', 'Start')->onlyOnIndex(),
            TextField::new('vehicleName', 'Fahrzeug')->onlyOnIndex(),
            TextField::new('driveMode', 'Modus')->onlyOnIndex(),
            AssociationField::new('vehicle', 'Fahrzeug')->autocomplete()->hideOnIndex(),
            TextField::new('vehicleName', 'Fahrzeugname')->hideOnIndex(),
            DateTimeField::new('startTime', 'Start')->hideOnIndex(),
            DateTimeField::new('endTime', 'Ende')->hideOnIndex(),
            IntegerField::new('durationSeconds', 'Dauer (s)')->hideOnIndex(),
            NumberField::new('distanceKm', 'Distanz (km)')->setNumDecimals(2)->hideOnIndex(),
            NumberField::new('maxSpeedKmh', 'Max km/h')->setNumDecimals(1)->hideOnIndex(),
            NumberField::new('avgSpeedKmh', 'Ø km/h')->setNumDecimals(1)->hideOnIndex(),
            IntegerField::new('maxRpm', 'Max RPM')->hideOnIndex(),
            IntegerField::new('maxNm', 'Max Nm')->hideOnIndex(),
            IntegerField::new('maxPs', 'Max PS')->hideOnIndex(),
            TextField::new('driveMode', 'Modus')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
        ];
    }
}
