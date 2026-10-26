<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PerformanceFormula;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

final class PerformanceFormulaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PerformanceFormula::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Technischer Name')->setHelp('z.B. hp_per_liter');
        yield TextField::new('label', 'Beschreibung')->setHelp('z.B. PS pro Liter');
        yield TextEditorField::new('formula', 'Formel')
            ->setHelp('Verwende {field_name} für Motorfelder, z.B. {horsepower} / {displacement}');
        yield TextField::new('resultUnit', 'Einheit')->setHelp('z.B. PS/L oder kW/L');
        yield ArrayField::new('requiredFields', 'Erforderliche Felder')
            ->setHelp('Komma-getrennte Liste: horsepower,displacement');
        yield IntegerField::new('sorting', 'Sortierung');
        yield BooleanField::new('active', 'Aktiv');
    }
}
