<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardPackage;

final class PartnerWizardPackageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PartnerWizardPackage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('client', 'Client');
        yield TextField::new('packageKey', 'Paket Key');
        yield TextField::new('title', 'Titel');
        yield TextareaField::new('description', 'Beschreibung');
        yield ChoiceField::new('requiredStatus', 'Benötigter Status')->setChoices([
            'Egal' => 'any',
            'Tool erforderlich' => 'tool_required',
            'Startklar' => 'ready',
        ]);
        yield IntegerField::new('minVehiclesPerMonth', 'Min. Fahrzeuge / Monat');
        yield IntegerField::new('maxVehiclesPerMonth', 'Max. Fahrzeuge / Monat');
        yield ArrayField::new('requiredExperience', 'Erfahrung')->hideOnIndex();
        yield ArrayField::new('requiredGoals', 'Ziele')->hideOnIndex();
        yield AssociationField::new('recommendedVehicleCategories', 'Fahrzeugarten')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex();
        yield ArrayField::new('features', 'Features')->hideOnIndex();
        yield TextField::new('ctaLabel', 'CTA Label');
        yield IntegerField::new('priority', 'Priorität');
        yield BooleanField::new('active', 'Aktiv');
    }
}
