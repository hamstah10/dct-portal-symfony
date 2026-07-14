<?php

namespace App\Controller\Admin;

use App\Entity\GearboxStageInfo;
use App\Form\GearboxStageInfoFeatureType;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/gearbox-stage-infos', routeName: 'admin_gearbox_stage_info')]
final class GearboxStageInfoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GearboxStageInfo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Getriebe-Stage-Info')
            ->setEntityLabelInPlural('Getriebe-Stage-Infos')
            ->setDefaultSort(['sorting' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('name', 'Stage'),
            TextareaField::new('description', 'Beschreibung')
                ->hideOnIndex()
                ->setRequired(false),
            CollectionField::new('features', 'Features')
                ->setEntryType(GearboxStageInfoFeatureType::class)
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded()
                ->onlyOnForms(),
            \EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField::new('features', 'Features')->hideOnForm(),
            IntegerField::new('sorting', 'Sortierung'),
            BooleanField::new('active', 'Aktiv'),
        ];
    }
}
