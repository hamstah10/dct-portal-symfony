<?php

namespace App\Controller\Admin;

use App\Entity\GearboxStageOption;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

#[AdminCrud(routePath: '/fahrzeugdatenbank/gearbox-stage-options', routeName: 'admin_gearbox_stage_option')]
final class GearboxStageOptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GearboxStageOption::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Getriebe-Stage-Option')
            ->setEntityLabelInPlural('Getriebe-Stage-Optionen')
            ->setDefaultSort(['gearboxName' => 'ASC', 'sorting' => 'ASC'])
            ->setPaginatorPageSize(50);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('gearboxName', 'Getriebe')->setChoices([
                'DQ200' => 'DQ200',
                'DQ250' => 'DQ250',
                'DQ380 / 381' => 'DQ380 / 381',
                'DQ500' => 'DQ500',
                'DL382' => 'DL382',
                'DL501' => 'DL501',
            ]))
            ->add(ChoiceFilter::new('category', 'Kategorie')->setChoices([
                'Verbesserte Fahrbarkeit' => 'Verbesserte Fahrbarkeit',
                'Optionen zur Lebensdauerverlängerung' => 'Optionen zur Lebensdauerverlängerung',
                'Performance-Optionen' => 'Performance-Optionen',
                'Launch Control' => 'Launch Control',
                'Zusätzliche Optionen' => 'Zusätzliche Optionen',
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('gearboxName', 'Getriebe'),
            TextField::new('category', 'Kategorie'),
            TextareaField::new('categoryDescription', 'Kategorie-Beschreibung')
                ->hideOnIndex()
                ->setRequired(false),
            TextField::new('name', 'Option'),
            TextField::new('nameEn', 'Option (EN)')->hideOnIndex(),
            TextField::new('fahrbarkeit', 'Fahrbarkeit')->setRequired(false),
            TextField::new('stage1', 'Stage 1')->setRequired(false),
            TextField::new('stage2', 'Stage 2')->setRequired(false),
            TextField::new('stage2Plus', 'Stage 2+')->setRequired(false),
            TextField::new('stage3', 'Stage 3')->setRequired(false),
            TextField::new('stage4', 'Stage 4')->setRequired(false),
            IntegerField::new('sorting', 'Sortierung')->hideOnIndex(),
            BooleanField::new('active', 'Aktiv'),
        ];
    }
}
