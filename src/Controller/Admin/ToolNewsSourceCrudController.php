<?php

namespace App\Controller\Admin;

use App\Entity\ToolNewsSource;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

#[AdminCrud(routePath: '/ecu-intelligence/tool-news-sources', routeName: 'admin_tool_news_sources')]
final class ToolNewsSourceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ToolNewsSource::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('News-Quelle')
            ->setEntityLabelInPlural('News-Quellen')
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
            AssociationField::new('provider', 'Anbieter'),
            TextField::new('name', 'Name'),
            UrlField::new('url', 'URL'),
            ChoiceField::new('type', 'Typ')
                ->setChoices([
                    'News' => ToolNewsSource::TYPE_NEWS,
                    'Blog' => ToolNewsSource::TYPE_BLOG,
                    'Forum' => ToolNewsSource::TYPE_FORUM,
                    'Sonstiges' => ToolNewsSource::TYPE_OTHER,
                ]),
            TextField::new('spiderName', 'Spider-Name')
                ->setHelp('Muss dem Scrapy-Spider "name"-Attribut entsprechen, z.B. "autotuner_news".'),
            BooleanField::new('isActive', 'Aktiv'),
            DateTimeField::new('lastRunAt', 'Letzter Lauf')->hideOnForm(),
            DateTimeField::new('lastSuccessAt', 'Letzter Erfolg')->hideOnForm(),
            TextareaField::new('lastError', 'Letzter Fehler')->hideOnForm()->onlyOnDetail(),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm(),
        ];
    }
}
