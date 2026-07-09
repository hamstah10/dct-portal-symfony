<?php

namespace App\Controller\Admin;

use App\Entity\EcuDetectionPattern;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecu-intelligence/ecu-detection-patterns', routeName: 'admin_ecu_detection_patterns')]
final class EcuDetectionPatternCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EcuDetectionPattern::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Erkennungsmuster')
            ->setEntityLabelInPlural('Erkennungsmuster')
            ->setDefaultSort(['type' => 'ASC', 'name' => 'ASC'])
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
            ChoiceField::new('type', 'Typ')
                ->setChoices([
                    'Hersteller' => EcuDetectionPattern::TYPE_MANUFACTURER,
                    'ECU-Familie' => EcuDetectionPattern::TYPE_ECU_FAMILY,
                    'ECU-Name' => EcuDetectionPattern::TYPE_ECU_NAME,
                    'MCU' => EcuDetectionPattern::TYPE_MCU,
                    'Lese-/Schreibmethode' => EcuDetectionPattern::TYPE_READ_METHOD,
                    'Protokoll/Typ' => EcuDetectionPattern::TYPE_PROTOCOL,
                    'Fahrzeug-Hinweis' => EcuDetectionPattern::TYPE_VEHICLE_HINT,
                ]),
            TextField::new('pattern', 'Muster')
                ->setHelp('Regex (z.B. /MD1CS069/i) oder einfacher Suchtext, je nach "Ist Regex".'),
            BooleanField::new('isRegex', 'Ist Regex'),
            TextField::new('normalizedValue', 'Normalisierter Wert')
                ->setRequired(false)
                ->setHelp('Optional: Wert, der bei einem Treffer gespeichert wird, statt des rohen Textes.'),
            NumberField::new('scoreWeight', 'Gewichtung')->setNumDecimals(2),
            BooleanField::new('isActive', 'Aktiv'),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm()->onlyOnDetail(),
        ];
    }
}
