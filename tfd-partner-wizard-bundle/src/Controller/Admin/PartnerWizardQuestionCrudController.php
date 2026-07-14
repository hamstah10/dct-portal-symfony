<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestion;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardStep;

final class PartnerWizardQuestionCrudController extends AbstractCrudController
{
    private ?int $lastRenderedStepId = null;

    public static function getEntityFqcn(): string
    {
        return PartnerWizardQuestion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Frage')
            ->setEntityLabelInPlural('Fragen');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $request = $this->getContext()?->getRequest();
        if (null === $request || [] !== $request->query->all('sort')) {
            return $queryBuilder;
        }

        $rootAlias = (string) ($queryBuilder->getRootAliases()[0] ?? 'entity');
        $queryBuilder
            ->leftJoin($rootAlias . '.step', 'stepSort')
            ->resetDQLPart('orderBy')
            ->addOrderBy('stepSort.priority', 'ASC')
            ->addOrderBy('stepSort.id', 'ASC')
            ->addOrderBy($rootAlias . '.priority', 'ASC');

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('step', 'Schritt')
            ->onlyOnIndex()
            ->formatValue(fn (mixed $value, PartnerWizardQuestion $question): string => $this->formatStepGroup($question));
        yield AssociationField::new('step', 'Schritt')->hideOnIndex();
        yield TextField::new('title', 'Titel');
        yield TextareaField::new('helpText', 'Hinweistext')->hideOnIndex();
        yield TextField::new('answerKey', 'Antwort-Key')->setHelp('z.B. "goal", "experience", "vehicles", "tool", "services", "volume" oder ein neuer freier Key.');
        yield ChoiceField::new('questionType', 'Typ')->setChoices([
            'Generisch (eigene Optionen)' => 'generic',
            'Fahrzeugarten (aus Client-Einstellungen)' => 'vehicleType',
            'Tool-Hersteller (aus Client-Einstellungen)' => 'toolManufacturer',
            'Volumen (mit Zahlen-Wert je Option)' => 'volume',
        ])->setHelp('Nur relevant für Fragen in einem "Fragen"-Schritt.');
        yield BooleanField::new('multiSelect', 'Mehrfachauswahl')
            ->setHelp('Bei Feldtyp "Checkbox" im Kontakt-Schritt: aktiviert eine Checkbox-Gruppe mit mehreren Antwortoptionen statt einer einzelnen Checkbox (z.B. Datenschutz).');
        yield ChoiceField::new('fieldType', 'Feldtyp')->setChoices([
            'Text' => 'text',
            'Textarea' => 'textarea',
            'E-Mail' => 'email',
            'Auswahl (Select)' => 'select',
            'Checkbox' => 'checkbox',
            'Radio' => 'radio',
        ])
            ->setFormTypeOption('required', false)
            ->setFormTypeOption('placeholder', 'Keine Angabe')
            ->setHelp('Nur relevant für Fragen in einem "Kontakt"-Schritt. Bei "Auswahl (Select)"/"Radio" sowie "Checkbox" + Mehrfachauswahl die Antwortoptionen unten anlegen.');
        yield BooleanField::new('required', 'Pflichtfeld')->setHelp('Nur relevant für Fragen in einem "Kontakt"-Schritt.');
        yield TextField::new('dependsOnAnswerKey', 'Abhängig von Antwort-Key')->hideOnIndex()
            ->setHelp('Answer-Key der auslösenden Frage, z. B. "tool". Leer lassen = immer sichtbar.');
        yield TextField::new('dependsOnValue', 'Abhängig von Wert')->hideOnIndex()
            ->setHelp('Wert, bei dem diese Frage erscheint, z. B. "other".');
        yield IntegerField::new('priority', 'Priorität');
        yield BooleanField::new('active', 'Aktiv');
    }

    private function formatStepGroup(PartnerWizardQuestion $question): string
    {
        $step = $question->getStep();
        if (!$step instanceof PartnerWizardStep) {
            return '';
        }

        if ($this->lastRenderedStepId === $step->getId()) {
            return '';
        }

        $this->lastRenderedStepId = $step->getId();

        return $step->getLabel();
    }
}
