<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;

final class PartnerWizardClientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PartnerWizardClient::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
        yield TextField::new('clientId', 'Client-ID');
        yield TextField::new('headline', 'Headline');
        yield TextareaField::new('introText', 'Introtext');
        yield TextField::new('primaryColor', 'Primärfarbe');
        yield ChoiceField::new('theme', 'Theme')->setChoices(['Dark' => 'dark', 'Light' => 'light']);
        yield BooleanField::new('toolRequired', 'Tool erforderlich');
        yield IntegerField::new('defaultVehiclePrice', 'Ø Verkaufspreis je Fahrzeug');
        yield ArrayField::new('allowedDomains', 'Erlaubte Domains')->hideOnIndex();
        yield AssociationField::new('enabledVehicleCategories', 'Aktive Fahrzeugarten')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex();
        yield AssociationField::new('enabledCurrentTools', 'Tools im Wizard')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex();
        yield AssociationField::new('recommendedProducts', 'Produktempfehlungen')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex();
        yield ArrayField::new('enabledServices', 'Aktive Leistungen')->hideOnIndex();
        yield ArrayField::new('customLabels', 'Eigene Labels')->hideOnIndex();        yield TextareaField::new('privacyNotice', 'DSGVO Hinweis')->hideOnIndex();
        yield TextareaField::new('toolDisclaimer', 'Tool Disclaimer')->hideOnIndex();
        yield TextField::new('notificationEmail', 'Benachrichtigung an');
        yield BooleanField::new('sendNotificationEmail', 'E-Mail senden');
        yield BooleanField::new('active', 'Aktiv');
        yield TextField::new('clientId', 'Vorschau')
            ->onlyOnIndex()
            ->renderAsHtml()
            ->formatValue(fn (mixed $value, ?PartnerWizardClient $client): string => $this->renderPreviewButton($client));
    }

    private function renderPreviewButton(?PartnerWizardClient $client): string
    {
        if (!$client instanceof PartnerWizardClient || null === $client->getId()) {
            return '';
        }

        $url = $this->generateUrl('tfd_partner_wizard_preview', ['clientId' => $client->getClientId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return sprintf(
            '<a href="%s" class="btn btn-secondary btn-sm" target="_blank" rel="noopener"><i class="fa fa-eye me-1" aria-hidden="true"></i> Vorschau</a>',
            htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }
}
