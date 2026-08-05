<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SuperadminDashboardLink;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

#[AdminCrud(routePath: '/verwaltung/superadmin-dashboard-links', routeName: 'admin_superadmin_dashboard_links')]
final class SuperadminDashboardLinkCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SuperadminDashboardLink::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Dashboard-Link')
            ->setEntityLabelInPlural('Dashboard-Links')
            ->setDefaultSort(['sortOrder' => 'ASC', 'title' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titel'),
            UrlField::new('url', 'URL'),
            TextareaField::new('description', 'Beschreibung')
                ->setRequired(false)
                ->hideOnIndex(),
            TextField::new('category', 'Kategorie')
                ->setRequired(false)
                ->setHelp('Optional, z. B. Tools, Monitoring, Abrechnung.'),
            TextField::new('iconName', 'Icon')
                ->setRequired(false)
                ->setHelp('Optionaler Anzeigename fuer das Dashboard, z. B. API, Docs, Shop.'),
            IntegerField::new('sortOrder', 'Sortierung'),
            BooleanField::new('active', 'Aktiv'),
            BooleanField::new('openInNewTab', 'Neuer Tab'),
            DateTimeField::new('createdAt', 'Erstellt')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert')->hideOnForm(),
        ];
    }
}
