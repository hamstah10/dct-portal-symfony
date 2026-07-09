<?php

namespace App\Controller\Admin;

use App\Entity\EmailLabel;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/verwaltung/email-labels', routeName: 'admin_email_label')]
final class EmailLabelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EmailLabel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('E-Mail Label')
            ->setEntityLabelInPlural('E-Mail Labels')
            ->setDefaultSort(['sorting' => 'ASC', 'name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('color', 'Farbe')
                ->setRequired(false)
                ->setHelp('Optional, z.B. #ff5ca8'),
            IntegerField::new('sorting', 'Sortierung'),
            AssociationField::new('emailKonten', 'E-Mail Konfigurationen')
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false)
                ->autocomplete()
                ->setRequired(false)
                ->onlyOnForms(),
        ];
    }
}

