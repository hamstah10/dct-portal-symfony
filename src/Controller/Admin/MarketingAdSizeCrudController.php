<?php

namespace App\Controller\Admin;

use App\Entity\MarketingAdSize;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/marketing/anzeigen', routeName: 'admin_marketing_ad_size')]
final class MarketingAdSizeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MarketingAdSize::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Anzeige')
            ->setEntityLabelInPlural('Anzeigen')
            ->setDefaultSort(['title' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Name'),
            IntegerField::new('width', 'Breite (px)'),
            IntegerField::new('height', 'Höhe (px)'),
            AssociationField::new('category', 'Kategorie'),
        ];
    }
}
