<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Portal;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/verwaltung/portale', routeName: 'admin_portal')]
final class PortalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Portal::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Portal')
            ->setEntityLabelInPlural('Portale')
            ->setDefaultSort(['sortOrder' => 'ASC', 'name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $a) => $a->renderAsLink()->setCssClass('btn btn-sm btn-outline-primary me-1'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $a) => $a->renderAsLink()->setCssClass('btn btn-sm btn-outline-danger'))
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static fn (Action $a) => $a->renderAsLink()->setCssClass('btn btn-sm btn-outline-secondary me-1'))
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('portalKey', 'Key')
                ->setHelp('Stabiler technischer Key, z. B. chiptuningfile.'),
            ChoiceField::new('portalType', 'Typ')
                ->setChoices(Portal::getPortalTypeChoices()),
            BooleanField::new('active', 'Aktiv'),
            BooleanField::new('portalEnabled', 'Portal aktiv'),
            BooleanField::new('winolsEnabled', 'WinOLS aktiv'),
            BooleanField::new('websiteEnabled', 'Website aktiv'),
            BooleanField::new('customerRegistrationEnabled', 'Registrierung aktiv'),
            IntegerField::new('sortOrder', 'Sortierung'),
            AssociationField::new('firma', 'Firma')->autocomplete(),
            AssociationField::new('vehicleDataPool', 'Fahrzeugdaten-Pool')
                ->setRequired(false)
                ->autocomplete()
                ->setHelp('Optionaler Standard-Datenpool fuer die Fahrzeugdatenbank dieses Portals.'),
            AssociationField::new('zahlungsarten', 'Zahlungsarten')
                ->setHelp('Nur aktive Zahlungsarten stehen hier zur Auswahl.')
                ->autocomplete()
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('multiple', true)
                ->setQueryBuilder(static function (QueryBuilder $queryBuilder): QueryBuilder {
                    $rootAlias = (string) (current($queryBuilder->getRootAliases()) ?: 'entity');

                    return $queryBuilder
                        ->resetDQLPart('orderBy')
                        ->andWhere(sprintf('%s.aktiv = :aktiv', $rootAlias))
                        ->setParameter('aktiv', true)
                        ->orderBy(sprintf('%s.name', $rootAlias), 'ASC');
                }),
            TextField::new('portalHost', 'Portal-Domain')
                ->setHelp('Nur Hostname, z. B. portal.chiptuningfile.de'),
            TextField::new('websiteHost', 'Website-Domain')
                ->setHelp('Nur Hostname, z. B. chiptuningfile.de'),
            TextField::new('portalUrl', 'Portal-URL')->hideOnForm(),
            TextField::new('websiteUrl', 'Website-URL')->hideOnForm(),
            ArrayField::new('enabledModules', 'Module')
                ->onlyOnIndex()
                ->formatValue(static function (mixed $value): string {
                    return is_array($value) ? implode(', ', $value) : '';
                }),
            ChoiceField::new('enabledModules', 'Module')
                ->setChoices(Portal::getModuleChoices())
                ->allowMultipleChoices()
                ->onlyOnForms(),
            ChoiceField::new('themePreset', 'Theme Auswahl')
                ->setChoices(Portal::getThemePresetChoices())
                ->setHelp('Vordefiniertes Komplett-Theme fuer das Portal.'),
            ChoiceField::new('headingFont', 'Ueberschrift Schrift')
                ->setChoices(Portal::getFontChoices())
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Theme-Standard')
                ->setHelp('Schrift fuer Headlines im Portal.'),
            ChoiceField::new('bodyFont', 'Text Schrift')
                ->setChoices(Portal::getFontChoices())
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Theme-Standard')
                ->setHelp('Schrift fuer Fliesstext und UI im Portal.'),
            BooleanField::new('customDesignEnabled', 'Eigenes Design aktiv')
                ->setHelp('Aus = Theme-Auswahl oben verwenden, An = eigene Farben aus den Feldern darunter.'),
            ColorField::new('primaryColor', 'Primaerfarbe'),
            ColorField::new('secondaryColor', 'Sekundaerfarbe'),
            ColorField::new('menuBackgroundColor', 'Menue Hintergrund')
                ->setHelp('Hintergrundfarbe der linken Navigation im Portal.'),
            ColorField::new('contentBackgroundColor', 'Content Hintergrund')
                ->setHelp('Hintergrundfarbe des Hauptbereichs im Portal.'),
            TextField::new('logoPath', 'Logo-Pfad')
                ->setHelp('Optionaler relativer Pfad oder Asset-Pfad zum Logo.'),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Erstellt')->hideOnForm(),
        ];
    }
}
