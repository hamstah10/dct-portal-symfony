<?php

namespace App\Controller\Admin;

use App\Entity\VehicleDataContext;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/data-contexts', routeName: 'admin_vehicle_data_context')]
final class VehicleDataContextCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehicleDataContext::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Datenkontext')
            ->setEntityLabelInPlural('Datenkontexte');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('name'),
            TextField::new('contextKey', 'Key'),
            ChoiceField::new('usageType', 'Nutzung')
                ->setChoices(VehicleDataContext::getUsageTypeChoices()),
            TextareaField::new('description')->setRequired(false),
            AssociationField::new('defaultVehicleDataPool', 'Standard-Datenbestand')
                ->setRequired(false),
            AssociationField::new('allowedDataPools', 'Erlaubte Datenbestände'),
            BooleanField::new('active'),

            FormField::addFieldset('Sichtbare Inhalte')
                ->setHelp('Steuert, welche Inhalte in der API-Antwort dieses Datenkontexts enthalten sind.'),
            BooleanField::new('showPrices', 'Preise anzeigen'),
            BooleanField::new('showHints', 'Hinweise anzeigen'),
            BooleanField::new('showGearbox', 'Getriebe anzeigen'),
            BooleanField::new('showDyno', 'Dyno-Chart anzeigen'),
            BooleanField::new('showEcoStage', 'Eco-Stage anzeigen'),
            BooleanField::new('showOptionCategories', 'Tuning-Option Kategorie anzeigen'),
            BooleanField::new('allowVehicleEditing', 'Fahrzeugdaten bearbeiten')
                ->setHelp('Schaltet die Edit-Funktionen in der Fahrzeugdatenbank frei.'),
        ];
    }
}
