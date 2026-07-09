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
use Tfd\PartnerWizardBundle\Entity\PartnerWizardTool;

final class PartnerWizardToolCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PartnerWizardTool::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('client', 'Client');
        yield AssociationField::new('shopProduct', 'Katalog-Produkt')->autocomplete()->hideOnIndex();
        yield TextField::new('imageUrl', 'Bild-URL')->hideOnIndex();
        yield TextField::new('toolKey', 'Tool Key');
        yield TextField::new('name', 'Name');
        yield TextareaField::new('description', 'Beschreibung');
        yield ChoiceField::new('level', 'Level')->setChoices([
            'Slave' => 'slave',
            'Master' => 'master',
            'Universal' => 'universal',
            'Spezial' => 'special',
        ]);
        yield AssociationField::new('recommendedForVehicleCategories', 'Empfohlen für Fahrzeugarten')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex();
        yield ArrayField::new('recommendedForServices', 'Empfohlen für Leistungen')->hideOnIndex();
        yield IntegerField::new('priority', 'Priorität');
        yield BooleanField::new('active', 'Aktiv');
    }
}
