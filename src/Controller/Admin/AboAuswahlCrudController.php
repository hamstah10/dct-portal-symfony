<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\AboAuswahl;
use App\Form\AboAuswahlPreisstaffelType;
use App\Form\SoftwareAutocompleteField;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/abo-auswahl', routeName: 'admin_abo_auswahl')]
final class AboAuswahlCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AboAuswahl::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Abo-Auswahl')
            ->setEntityLabelInPlural('Abo-Auswahlen');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Abo Name'),
            CkEditor5Field::new('beschreibung')->setRequired(false),
            ChoiceField::new('art', 'Art')->setChoices([
                'Trial' => 'trial',
                'Aktiv' => 'aktiv',
            ]),
            CollectionField::new('preisstaffeln', 'Preisstaffel')
                ->setEntryType(AboAuswahlPreisstaffelType::class)
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOption('by_reference', false)
                ->setHelp('Beispiel: 3 Monate -> 39.00 monatlich, 12 Monate -> 29.00 monatlich; Jahreszahlung optional pro Laufzeit.'),
            AssociationField::new('software', 'Software')
                ->setRequired(false)
                ->setFormType(SoftwareAutocompleteField::class)
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false)
                ->setHelp('Mehrfachauswahl moeglich.'),
            BooleanField::new('aktiv'),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }
}
