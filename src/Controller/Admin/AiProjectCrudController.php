<?php

namespace App\Controller\Admin;

use App\Entity\AiProject;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ai/project', routeName: 'admin_ai_project')]
final class AiProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AiProject::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('AI Projekt')
            ->setEntityLabelInPlural('AI Projekte')
            ->setDefaultSort(['updatedAt' => 'DESC', 'id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $workspace = Action::new('workspace', 'Workspace', 'mdi:folder-star')
            ->linkToRoute('admin_ai_project_workspace', static fn (AiProject $project): array => [
                'id' => $project->getId(),
            ]);

        return $actions
            ->add(Crud::PAGE_INDEX, $workspace)
            ->add(Crud::PAGE_EDIT, $workspace)
            ->add(Crud::PAGE_DETAIL, $workspace)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextareaField::new('description', 'Beschreibung')
                ->hideOnIndex(),
            TextareaField::new('systemPrompt', 'System-Prompt')
                ->hideOnIndex()
                ->setHelp('Wird fuer projektbezogenen Chat und Agent standardmaessig verwendet.'),
            BooleanField::new('active', 'Aktiv'),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm(),
        ];
    }
}
