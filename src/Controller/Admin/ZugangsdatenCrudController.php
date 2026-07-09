<?php

namespace App\Controller\Admin;

use App\Entity\Zugangsdaten;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

#[AdminCrud(routePath: '/zugangsdaten', routeName: 'admin_zugangsdaten')]
final class ZugangsdatenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Zugangsdaten::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Zugangsdaten')
            ->setEntityLabelInPlural('Zugangsdaten')
            ->setDefaultSort(['sorting' => 'ASC', 'name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('benutzer', 'Benutzer'),
            TextField::new('passwort', 'Passwort')
                ->setFormTypeOption('attr', ['type' => 'password']),
            UrlField::new('url', 'URL'),
            TextareaField::new('notizen', 'Notizen')
                ->setFormTypeOption('attr', ['rows' => 4]),
            BooleanField::new('active', 'Aktiv'),
            IntegerField::new('sorting', 'Sortierung')
                ->hideOnIndex(),
        ];
    }
}
