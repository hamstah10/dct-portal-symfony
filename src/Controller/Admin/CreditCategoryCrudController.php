<?php

namespace App\Controller\Admin;

use App\Entity\CreditCategory;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/credits/kategorien', routeName: 'admin_credit_category')]
final class CreditCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CreditCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Kategorie')
            ->setEntityLabelInPlural('Kategorien')
            ->setDefaultSort(['title' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titel'),
        ];
    }
}

