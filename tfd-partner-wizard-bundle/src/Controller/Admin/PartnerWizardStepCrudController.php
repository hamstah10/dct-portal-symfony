<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardStep;

final class PartnerWizardStepCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return PartnerWizardStep::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Schritt')
            ->setEntityLabelInPlural('Schritte')
            ->setDefaultSort(['client' => 'ASC', 'priority' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('stepKey', 'Nr.')
            ->onlyOnIndex()
            ->formatValue(fn (mixed $value, PartnerWizardStep $step): string => $this->formatStepNumber($step));
        yield AssociationField::new('client', 'Client');
        yield TextField::new('stepKey', 'Step-Key')->setHelp('Interner Schlüssel, z.B. "entry". Wird vom Frontend zur Wiedererkennung von Empfehlung/Kontakt genutzt.');
        yield TextField::new('label', 'Label');
        yield ChoiceField::new('type', 'Typ')->setChoices([
            'Fragen' => 'questions',
            'Empfehlung (berechnet)' => 'recommendation',
            'Kontakt (Formular)' => 'contact',
        ]);
        yield IntegerField::new('priority', 'Priorität');
        yield BooleanField::new('active', 'Aktiv');
    }

    private function formatStepNumber(PartnerWizardStep $step): string
    {
        $client = $step->getClient();
        if (null === $client) {
            return '';
        }

        $siblings = $this->entityManager->getRepository(PartnerWizardStep::class)->findBy(
            ['client' => $client],
            ['priority' => 'ASC', 'id' => 'ASC']
        );

        foreach ($siblings as $index => $sibling) {
            if ($sibling->getId() === $step->getId()) {
                return sprintf('Schritt %d', $index + 1);
            }
        }

        return '';
    }
}
