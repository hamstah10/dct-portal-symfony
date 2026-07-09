<?php

namespace App\Controller\Admin;

use App\Entity\ToolProvider;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

#[AdminCrud(routePath: '/ecu-intelligence/tool-providers', routeName: 'admin_tool_providers')]
final class ToolProviderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ToolProvider::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tool-Anbieter')
            ->setEntityLabelInPlural('Tool-Anbieter')
            ->setDefaultSort(['name' => 'ASC'])
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
            IdField::new('id')->onlyOnDetail(),
            TextField::new('name', 'Name'),
            TextField::new('slug', 'Slug')
                ->setHelp('Eindeutiger Bezeichner, z.B. "autotuner" oder "magicmotorsport".'),
            UrlField::new('websiteUrl', 'Website')->setRequired(false),
            BooleanField::new('isActive', 'Aktiv'),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm(),
        ];
    }
}
