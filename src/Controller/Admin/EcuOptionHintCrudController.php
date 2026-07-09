<?php

namespace App\Controller\Admin;

use App\Entity\EcuOptionHint;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/ecu-option-hints', routeName: 'admin_ecu_option_hint')]
final class EcuOptionHintCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EcuOptionHint::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ECU-Option Hinweis')
            ->setEntityLabelInPlural('ECU-Optionen Hinweise')
            ->setDefaultSort([
                'moduleName' => 'ASC',
                'brandName' => 'ASC',
                'manufacturer' => 'ASC',
                'model' => 'ASC',
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('moduleName')
            ->add('brandName')
            ->add('manufacturer')
            ->add('active');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            BooleanField::new('active', 'Aktiv'),
            TextField::new('moduleName', 'Modul'),
            TextField::new('brandName', 'Marke'),
            TextField::new('manufacturer', 'Hersteller'),
            TextField::new('model', 'Modell'),
            TextField::new('notesPreview', 'Hinweis')->onlyOnIndex(),
            TextareaField::new('notesDe', 'Hinweis DE')->hideOnIndex(),
            TextareaField::new('notes', 'Hinweis EN')->hideOnIndex(),
            TextareaField::new('raw', 'Raw')->hideOnIndex(),
            TextField::new('moduleSlug')->hideOnIndex(),
            TextField::new('moduleUrl')->hideOnIndex(),
            TextField::new('brandSlug')->hideOnIndex(),
            TextField::new('sourceFile')->hideOnIndex(),
            TextField::new('signatureHash')->hideOnIndex(),
            DateTimeField::new('scrapedAt', 'Scraped')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Erstellt')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert')->hideOnForm(),
        ];
    }
}
