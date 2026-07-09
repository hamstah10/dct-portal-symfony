<?php

namespace App\Controller\Admin;

use App\Entity\Zahlungsart;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/zahlungsarten', routeName: 'admin_zahlungsart')]
final class ZahlungsartCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Zahlungsart::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Zahlungsart')
            ->setEntityLabelInPlural('Zahlungsarten');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Zahlungsart'),
            ImageField::new('logo')
                ->setBasePath('/uploads/zahlungsarten')
                ->setUploadDir('public/uploads/zahlungsarten')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[slug]-[contenthash].[extension]')
                ->setRequired(false),
            TextField::new('link'),
            TextField::new('apiLink', 'Link zur API'),
            TextField::new('dokuLink', 'Link zur Doku'),
            TextareaField::new('zugangsdaten')
                ->setHelp('Beispiel: Benutzername, Passwort, Hinweise usw.'),
            TextField::new('apiKeySandbox', 'API Key Sandbox'),
            TextField::new('apiKeyLive', 'API Key Live'),
            BooleanField::new('aktiv'),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }
}
