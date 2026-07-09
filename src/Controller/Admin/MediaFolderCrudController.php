<?php

namespace App\Controller\Admin;

use App\Entity\MediaFolder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/media/folder', routeName: 'admin_media_folder')]
final class MediaFolderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MediaFolder::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Medienordner')
            ->setEntityLabelInPlural('Medienordner');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('parent', 'Elternordner')
                ->autocomplete(),
            TextField::new('name', 'Ordnername'),
            TextField::new('slug', 'Slug'),
            TextareaField::new('description'),
            BooleanField::new('active'),
            IntegerField::new('sorting'),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
        ];
    }
}
