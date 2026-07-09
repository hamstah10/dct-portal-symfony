<?php

namespace App\Controller\Admin;

use App\Entity\Antriebsart;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/antriebsart', routeName: 'admin_antriebsart')]
final class AntriebsartCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Antriebsart::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Antriebsart')
            ->setEntityLabelInPlural('Antriebsarten');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('icon', 'Icon')
                ->setRequired(false)
                ->setHelp('Pfad oder URL zum Icon.'),
        ];
    }
}

