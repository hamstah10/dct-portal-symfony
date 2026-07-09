<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardCombination;

final class PartnerWizardCombinationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PartnerWizardCombination::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('client', 'Client');
        yield TextField::new('name', 'Name');
        yield AssociationField::new('vehicleCategories', 'Fahrzeugarten (alle erforderlich)')->autocomplete()->setFormTypeOption('by_reference', false);
        yield ArrayField::new('services', 'Leistungen (alle erforderlich)')->hideOnIndex();
        yield AssociationField::new('tools', 'Tools')->autocomplete()->setFormTypeOption('by_reference', false);
        yield IntegerField::new('priority', 'Priorität');
        yield BooleanField::new('active', 'Aktiv');
    }
}
