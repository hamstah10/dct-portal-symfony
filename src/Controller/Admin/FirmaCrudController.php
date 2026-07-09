<?php

namespace App\Controller\Admin;

use App\Entity\Firma;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/firmen', routeName: 'admin_firma')]
final class FirmaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Firma::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Firma')
            ->setEntityLabelInPlural('Firmen')
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
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            ImageField::new('logo')
                ->setBasePath('/uploads/firmen')
                ->setUploadDir('public/uploads/firmen')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[slug]-[contenthash].[extension]')
                ->setRequired(false),
            TextField::new('name', 'Firmenname'),
            TextField::new('kuerzel', 'Kuerzel'),
          
                
         
            TextField::new('rechtsform')->hideOnIndex(),
            TextField::new('ustId', 'USt-Id.')->hideOnIndex(),
            TextField::new('steuernummer')->hideOnIndex(),
            TextField::new('strasse'),
            TextField::new('hausnummer'),
            TextField::new('plz'),
            TextField::new('stadt'),
            TextField::new('land')->hideOnIndex(),
            EmailField::new('email'),
            TextField::new('telefon')->hideOnIndex(),
            TextField::new('website')->hideOnIndex(),
            TextField::new('iban')->hideOnIndex(),
            TextField::new('bic')->hideOnIndex(),
            TextField::new('bankname')->hideOnIndex(),
            TextareaField::new('zahlungsbedingungen')->hideOnIndex(),
            BooleanField::new('aktiv'),
            DateTimeField::new('createdAt')->hideOnForm()->hideOnIndex(),
        ];
    }
}
