<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardLead;

final class PartnerWizardLeadCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PartnerWizardLead::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setEntityLabelInSingular('Partner Lead')
            ->setEntityLabelInPlural('Partner Leads');
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Datum')->hideOnForm();
        yield AssociationField::new('client', 'Client');
        yield ChoiceField::new('status', 'Lead Status')->setChoices([
            'Neu' => 'new',
            'Kontaktiert' => 'contacted',
            'Tool-Beratung' => 'tool_consulting',
            'Händlerprüfung' => 'dealer_review',
            'Gewonnen' => 'won',
            'Verloren' => 'lost',
        ]);
        yield TextField::new('name', 'Name');
        yield TextField::new('company', 'Firma');
        yield TextField::new('email', 'E-Mail');
        yield TextField::new('phone', 'Telefon');
        yield TextField::new('website', 'Website')->hideOnIndex();
        yield DateTimeField::new('appointmentStartsAt', 'Termin')->setFormat('dd.MM.yyyy HH:mm')->setTimezone('Europe/Berlin')->hideOnForm();
        yield TextField::new('originDomain', 'Domain')->hideOnForm();
        yield TextField::new('currentTool', 'Tool');
        yield TextField::new('recommendationStatus', 'Empfehlungsstatus');
        yield TextField::new('recommendedPackageTitle', 'Empfohlenes Paket');
        yield IntegerField::new('monthlyVehicleCount', 'Fahrzeuge / Monat');
        yield IntegerField::new('estimatedMonthlyRevenue', 'Umsatzpotenzial');
        yield AssociationField::new('vehicleCategories', 'Fahrzeugarten')->autocomplete()->hideOnIndex();
        yield AssociationField::new('mainVehicleType', 'Haupt-Fahrzeugtyp')->hideOnIndex();
        yield ArrayField::new('services', 'Leistungen')->hideOnIndex();
        yield ArrayField::new('recommendedTools', 'Empfohlene Tools')->hideOnIndex();
        yield TextareaField::new('recommendedPackageText', 'Pakettext')->hideOnIndex();
        yield TextareaField::new('toolDirection', 'Tool-Empfehlung')->hideOnIndex();
        yield TextField::new('nextStep', 'Nächster Schritt')->hideOnIndex();
        yield ArrayField::new('payload', 'Payload')->hideOnIndex();
    }
}
