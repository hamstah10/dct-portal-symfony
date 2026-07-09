<?php

namespace App\Controller\Admin;

use App\Entity\VehicleFrontendSettings;
use App\Repository\VehicleFrontendSettingsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/frontend-einstellungen', routeName: 'admin_vehicle_frontend_settings')]
final class VehicleFrontendSettingsCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly VehicleFrontendSettingsRepository $settingsRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return VehicleFrontendSettings::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Frontend-Einstellung')
            ->setEntityLabelInPlural('Frontend-Einstellungen')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('vehicleDataPool', 'Datenbestand')
                ->setRequired(false),
            TextField::new('name', 'Name')
                ->setRequired(false),
            BooleanField::new('active', 'Aktiv'),
            BooleanField::new('layoutSwitcherEnabled', 'Layout-Switcher aktiv')
                ->setHelp('Aktiv: Im Frontend kann das Layout direkt in der Ergebnisansicht umgeschaltet werden.'),
            ChoiceField::new('templateVariant', 'Standard Layout')
                ->setChoices(VehicleFrontendSettings::getTemplateChoices())
                ->renderExpanded(false)
                ->setHelp('Waehlt das visuelle Frontend-Layout fuer die Fahrzeugdatenbank.'),
            FormField::addFieldset('Performance (Standard)'),
            BooleanField::new('performanceShowLogo', 'Logo anzeigen (TFD)')
                ->setHelp('Aktiv: Das TFD-Logo wird im Header angezeigt.'),
            BooleanField::new('performanceGlbAutorotate', 'GLB Autorotate')
                ->setHelp('Aktiv: 3D Modelle rotieren automatisch.'),
            BooleanField::new('performanceShowPrices', 'Preise anzeigen')
                ->setHelp('Aktiv: Preise fuer Stage/Optionen werden im Frontend eingeblendet.'),
            BooleanField::new('performanceShowOnlyStage1', 'Nur Stage 1')
                ->setHelp('Aktiv: Es wird pro Motor nur Stage 1 ausgegeben.'),
            FormField::addFieldset('Minimal'),
            BooleanField::new('minimalShowLogo', 'Logo anzeigen (TFD)')->hideOnIndex(),
            BooleanField::new('minimalGlbAutorotate', 'GLB Autorotate')->hideOnIndex(),
            BooleanField::new('minimalShowPrices', 'Preise anzeigen')->hideOnIndex(),
            BooleanField::new('minimalShowOnlyStage1', 'Nur Stage 1')->hideOnIndex(),
            FormField::addFieldset('Contrast (Layout2)'),
            BooleanField::new('contrastShowLogo', 'Logo anzeigen (TFD)')->hideOnIndex(),
            BooleanField::new('contrastGlbAutorotate', 'GLB Autorotate')->hideOnIndex(),
            BooleanField::new('contrastShowPrices', 'Preise anzeigen')->hideOnIndex(),
            BooleanField::new('contrastShowOnlyStage1', 'Nur Stage 1')->hideOnIndex(),
            FormField::addFieldset('V3 (Sidebar)'),
            BooleanField::new('layout3ShowLogo', 'Logo anzeigen (TFD)')->hideOnIndex(),
            BooleanField::new('layout3GlbAutorotate', 'GLB Autorotate')->hideOnIndex(),
            BooleanField::new('layout3ShowPrices', 'Preise anzeigen')->hideOnIndex(),
            BooleanField::new('layout3ShowOnlyStage1', 'Nur Stage 1')->hideOnIndex(),
            FormField::addFieldset('V4 (Download)'),
            BooleanField::new('layout4ShowLogo', 'Logo anzeigen (TFD)')->hideOnIndex(),
            BooleanField::new('layout4GlbAutorotate', 'GLB Autorotate')->hideOnIndex(),
            BooleanField::new('layout4ShowPrices', 'Preise anzeigen')->hideOnIndex(),
            BooleanField::new('layout4ShowOnlyStage1', 'Nur Stage 1')->hideOnIndex(),
        ];
    }
}
