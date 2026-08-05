<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\WinolsConfiguration;
use App\Form\WinolsConfigurationRuleType;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

#[AdminCrud(routePath: '/verwaltung/winols', routeName: 'admin_winols_configuration')]
final class WinolsConfigurationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WinolsConfiguration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('WinOLS')
            ->setEntityLabelInPlural('WinOLS')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $isForm = in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT], true);

        if (!$isForm) {
            yield IdField::new('id')->hideOnForm();
            yield TextField::new('name', 'Name');
            yield TextField::new('apiBaseUrl', 'API-URL');
            yield TextField::new('portalSummary', 'Portale')->onlyOnIndex();
            yield BooleanField::new('active', 'Aktiv');
            yield IntegerField::new('ruleCount', 'Regeln');
            yield TextField::new('ruleSummary', 'Regel-Übersicht')->onlyOnDetail();

            return;
        }

        yield FormField::addTab('Allgemein');
        yield TextField::new('name', 'Name');
        yield TextField::new('apiBaseUrl', 'API-URL')
            ->setHelp('Nur die Domain bzw. Basis-URL, ohne Pfad, z. B. https://winols.example.com');
        yield TextField::new('apiToken', 'API-Token')
            ->setHelp('Statischer Token für die WinOLS-Anbindung.');
        yield AssociationField::new('portals', 'Portale')
            ->setFormTypeOptions([
                'multiple' => true,
                'by_reference' => false,
            ])
            ->setHelp('Für welche Portale diese WinOLS-Konfiguration gilt.');
        yield BooleanField::new('active', 'Aktiv');
        yield TextareaField::new('description', 'Beschreibung')
            ->setRequired(false);

        yield FormField::addTab('Regeln');
        yield CollectionField::new('rules', 'Verknüpfungen')
            ->setEntryType(WinolsConfigurationRuleType::class)
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded()
            ->setFormTypeOption('by_reference', false)
            ->setHelp('Jede Verknüpfung definiert Hersteller, Slave/Master und erlaubte Dateiendungen.');
    }
}
