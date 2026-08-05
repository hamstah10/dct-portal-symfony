<?php

namespace App\Controller\Admin;

use App\Entity\AdminIssue;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

#[AdminCrud(routePath: '/support/issues', routeName: 'admin_issues')]
final class AdminIssueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AdminIssue::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Meldung')
            ->setEntityLabelInPlural('Meldungen')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle(Crud::PAGE_INDEX, 'Admin-Meldungen')
            ->setPageTitle(Crud::PAGE_NEW, 'Neue Meldung')
            ->setPageTitle(Crud::PAGE_EDIT, 'Meldung bearbeiten');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status'))
            ->add(ChoiceFilter::new('priority'))
            ->add(ChoiceFilter::new('category'))
            ->add('createdAt');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        $statusChoices = [
            'open' => 'Offen',
            'in_progress' => 'In Bearbeitung',
            'resolved' => 'Gelöst',
            'rejected' => 'Abgelehnt',
        ];

        $priorityChoices = [
            'low' => 'Niedrig',
            'normal' => 'Normal',
            'high' => 'Hoch',
            'critical' => 'Kritisch',
        ];

        $categoryChoices = [
            'incorrect_data' => 'Fehlerhafte Daten',
            'missing_data' => 'Fehlende Daten',
            'translation' => 'Übersetzung',
            'layout' => 'Layout',
            'technical_error' => 'Technischer Fehler',
            'other' => 'Sonstiges',
        ];

        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titel'),
            TextEditorField::new('description', 'Beschreibung'),
            ChoiceField::new('category', 'Kategorie')
                ->setChoices($categoryChoices),
            ChoiceField::new('status', 'Status')
                ->setChoices($statusChoices),
            ChoiceField::new('priority', 'Priorität')
                ->setChoices($priorityChoices),
            ArrayField::new('labels', 'Labels')
                ->hideOnIndex(),
            TextField::new('createdByName', 'Erstellt von')
                ->onlyOnIndex(),
            TextField::new('assignedToName', 'Zugewiesen an')
                ->hideOnIndex(),
            DateTimeField::new('createdAt', 'Erstellt am')
                ->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')
                ->hideOnForm(),
            DateTimeField::new('resolvedAt', 'Gelöst am')
                ->hideOnForm(),
        ];

        if (Crud::PAGE_DETAIL === $pageName || Crud::PAGE_EDIT === $pageName) {
            $fields[] = TextField::new('pageUrl', 'Seiten-URL')
                ->hideOnIndex();
            $fields[] = TextField::new('pageTitle', 'Seitentitel')
                ->hideOnIndex();
            $fields[] = TextField::new('selector', 'HTML-Selector')
                ->hideOnIndex();
            $fields[] = TextField::new('entityType', 'Entitätstyp')
                ->hideOnIndex();
            $fields[] = TextField::new('entityId', 'Entitäts-ID')
                ->hideOnIndex();
            $fields[] = TextField::new('entityLabel', 'Entitätsbezeichnung')
                ->hideOnIndex();
            $fields[] = AssociationField::new('vehicle', 'Fahrzeug')
                ->hideOnIndex();
            $fields[] = AssociationField::new('engine', 'Motor')
                ->hideOnIndex();
            $fields[] = TextField::new('stageId', 'Stage-ID')
                ->hideOnIndex();
            $fields[] = TextField::new('field', 'Feld')
                ->hideOnIndex();
            $fields[] = TextField::new('currentValue', 'Aktueller Wert')
                ->hideOnIndex();
            $fields[] = TextField::new('screenshotDataUrl', 'Screenshot (Data URL)')
                ->hideOnIndex();
            $fields[] = TextField::new('audioUrl', 'Audio-URL')
                ->hideOnIndex();
        }

        return $fields;
    }
}
