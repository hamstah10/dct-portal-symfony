<?php

namespace App\Controller\Admin;

use App\Entity\PortalPriceList;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/portal/preisliste', routeName: 'admin_portal_price_list')]
final class PortalPriceListCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PortalPriceList::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Portal-Preisliste')
            ->setEntityLabelInPlural('Portal-Preislisten');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('code', 'Code'),
            TextField::new('name', 'Name'),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            BooleanField::new('active', 'Aktiv'),
        ];
    }
}
