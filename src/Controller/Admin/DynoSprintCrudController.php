<?php

namespace App\Controller\Admin;

use App\Entity\DynoSprint;
use App\Repository\DynoTripRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/dyno/sprints', routeName: 'admin_dyno_sprint')]
final class DynoSprintCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DynoTripRepository $tripRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return DynoSprint::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Dyno Sprint')
            ->setEntityLabelInPlural('Dyno Sprints')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->overrideTemplate('crud/detail', 'admin/dyno/sprint_detail.html.twig')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncAction = Action::new('sync', 'Daten abrufen', 'raphael:clouddown')
            ->linkToRoute('admin_dyno_sync', ['type' => 'sprints'])
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $syncAction)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('id')->hideOnForm(),
            AssociationField::new('vehicle', 'Fahrzeug')->autocomplete(),
            TextField::new('vehicleName', 'Fahrzeugname')->hideOnIndex(),
            TextField::new('sprintType', 'Sprint Typ'),
            NumberField::new('timeSeconds', 'Zeit (s)')->setNumDecimals(2),
            TextField::new('tripId', 'Trip-ID')->hideOnIndex(),
            NumberField::new('distanceMeters', 'Wegstrecke (m)')->setNumDecimals(2)->hideOnIndex(),
            IntegerField::new('maxRpm', 'Max RPM')->hideOnIndex(),
            IntegerField::new('maxPs', 'Max PS')->hideOnIndex(),
            IntegerField::new('maxNm', 'Max Nm')->hideOnIndex(),
            TextField::new('driveMode', 'Modus')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
        ];
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_DETAIL !== $pageName) {
            return $responseParameters;
        }

        $entityDto = $responseParameters->get('entity');
        $sprint = null;
        if (is_object($entityDto) && method_exists($entityDto, 'getInstance')) {
            $instance = $entityDto->getInstance();
            if ($instance instanceof DynoSprint) {
                $sprint = $instance;
            }
        }

        if (!$sprint instanceof DynoSprint) {
            return $responseParameters;
        }

        $tripDistanceMeters = null;
        $tripId = $sprint->getTripId();
        if (null !== $tripId && '' !== trim($tripId)) {
            $trip = $this->tripRepository->find($tripId);
            if (null !== $trip) {
                $tripDistanceMeters = (float) $trip->getDistanceKm() * 1000;
            }
        }

        $responseParameters->set('tripDistanceMeters', $tripDistanceMeters);

        return $responseParameters;
    }
}
