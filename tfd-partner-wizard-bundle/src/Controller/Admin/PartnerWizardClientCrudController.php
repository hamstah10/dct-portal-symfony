<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

use App\Entity\SuperadminCalendarCategory;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;

final class PartnerWizardClientCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

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
        yield TextareaField::new('allowedDomainsText', 'Partner-Domain(s) für Einbettung')
            ->setHelp('Domain der Partner-Webseite, auf der das Widget eingebaut wird, z. B. partner.de oder *.partner.de. Eine pro Zeile. Leer = alle Domains erlaubt.')
            ->setNumOfRows(3)
            ->hideOnIndex();
        yield TextField::new('apiTokenHint', 'API-Token')
            ->hideOnForm()
            ->renderAsHtml()
            ->formatValue(fn (mixed $value, ?PartnerWizardClient $client): string => $this->renderTokenStatus($client));
        yield BooleanField::new('apiTokenRequired', 'Nur mit API-Token')
            ->setHelp('Ein: Jede Anfrage braucht den Token, auch aus dem Browser (nur für reinen Server-Zugriff). Aus: Browser-Aufrufe laufen über die Domain-Prüfung, Server-Aufrufe ohne Origin brauchen den Token, sobald einer erzeugt wurde. Token erzeugen über die Aktion „API-Token erzeugen".')
            ->renderAsSwitch(false)
            ->hideOnIndex();
        yield BooleanField::new('textApiEnabled', 'Inhalte per API bearbeiten')
            ->setHelp('Ein: Der Partner darf mit seinem API-Token Texte ändern sowie Fragen und Antwortoptionen anlegen, ändern und löschen (/api/tfd-partner-wizard/texts, /questions, /options – nur Server-Zugriff mit Token, nie über „Erlaubte Domains"). Aus: nur Admins.')
            ->renderAsSwitch(false)
            ->hideOnIndex();
        yield AssociationField::new('enabledVehicleCategories', 'Aktive Fahrzeugarten')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex();
        yield AssociationField::new('enabledCurrentTools', 'Tools im Wizard')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex();
        yield AssociationField::new('recommendedProducts', 'Produktempfehlungen')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex();
        yield ArrayField::new('enabledServices', 'Aktive Leistungen')->hideOnIndex();
        // Nicht als ArrayField im Formular: das würde die Text-Schlüssel beim Speichern zu 0,1,2… umnummerieren.
        yield TextField::new('customLabels', 'Eigene UI-Texte')
            ->onlyOnDetail()
            ->formatValue(static fn (mixed $value): string => sprintf('%d überschrieben – bearbeiten über „Wizard bearbeiten“', is_array($value) ? count($value) : 0));
        yield TextareaField::new('privacyNotice', 'DSGVO Hinweis')->hideOnIndex();
        yield TextareaField::new('toolDisclaimer', 'Tool Disclaimer')->hideOnIndex();
        yield TextField::new('notificationEmail', 'Benachrichtigung an');
        yield BooleanField::new('sendNotificationEmail', 'E-Mail senden');
        yield BooleanField::new('active', 'Aktiv');

        yield FormField::addFieldset('Beratungstermine')
            ->setHelp('Freie Termine kommen aus dem Superadmin-Kalender. Alles, was dort im gewählten Kalender eingetragen ist, gilt als belegt. Gebuchte Termine landen automatisch dort.')
            ->hideOnIndex();
        yield BooleanField::new('appointmentEnabled', 'Terminbuchung im Wizard');
        yield BooleanField::new('appointmentRequired', 'Termin ist Pflicht')->hideOnIndex();
        yield TextField::new('appointmentTitle', 'Termin-Titel')->hideOnIndex();
        yield ChoiceField::new('appointmentCalendarId', 'Kalender')
            ->setChoices($this->calendarCategoryChoices())
            ->hideOnIndex();
        yield IntegerField::new('appointmentDurationMinutes', 'Dauer (Minuten)')->hideOnIndex();
        yield IntegerField::new('appointmentBufferMinutes', 'Puffer vor/nach belegten Terminen (Minuten)')->hideOnIndex();
        yield IntegerField::new('appointmentMinNoticeHours', 'Vorlauf (Stunden)')->hideOnIndex();
        yield IntegerField::new('appointmentDaysAhead', 'Buchbar für (Tage)')->hideOnIndex();
        yield ChoiceField::new('appointmentWeekdays', 'Wochentage')
            ->setChoices(['Montag' => 1, 'Dienstag' => 2, 'Mittwoch' => 3, 'Donnerstag' => 4, 'Freitag' => 5, 'Samstag' => 6, 'Sonntag' => 7])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->hideOnIndex();
        yield TextField::new('appointmentDayStart', 'Von (HH:MM)')->hideOnIndex();
        yield TextField::new('appointmentDayEnd', 'Bis (HH:MM)')->hideOnIndex();
        yield TextField::new('appointmentTimezone', 'Zeitzone')->hideOnIndex();
        yield TextField::new('appointmentLocation', 'Ort / Link')->setHelp('z. B. „Telefon“ oder ein fester Meet-/Teams-Link')->hideOnIndex();

        yield TextField::new('clientId', 'Vorschau')
            ->onlyOnIndex()
            ->renderAsHtml()
            ->formatValue(fn (mixed $value, ?PartnerWizardClient $client): string => $this->renderPreviewButton($client));
    }

    /** @return array<string, string> Kategorien aus dem Superadmin-Kalender (Name => Key) */
    private function calendarCategoryChoices(): array
    {
        $choices = [];
        foreach ($this->entityManager->getRepository(SuperadminCalendarCategory::class)->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']) as $category) {
            $choices[$category->getLabel()] = $category->getKey();
        }

        return $choices !== [] ? $choices : ['Support' => 'support'];
    }

    public function configureActions(Actions $actions): Actions
    {
        $generate = Action::new('generateApiToken', 'API-Token erzeugen')
            ->setIcon('fa fa-key')
            ->linkToCrudAction('generateApiToken');

        $revoke = Action::new('revokeApiToken', 'API-Token löschen')
            ->setIcon('fa fa-ban')
            ->setCssClass('text-danger')
            ->linkToCrudAction('revokeApiToken')
            ->displayIf(static fn (PartnerWizardClient $client): bool => $client->hasApiToken());

        $editTexts = Action::new('editWizardTexts', 'Wizard bearbeiten')
            ->setIcon('fa fa-pen')
            ->linkToUrl(fn (PartnerWizardClient $client): string => $this->generateUrl(
                'tfd_partner_wizard_preview',
                ['clientId' => $client->getClientId(), 'edit' => 1],
            ))
            ->setHtmlAttributes(['target' => '_blank', 'rel' => 'noopener']);

        return $actions
            ->add(Crud::PAGE_INDEX, $editTexts)
            ->add(Crud::PAGE_INDEX, $generate)
            ->add(Crud::PAGE_INDEX, $revoke)
            ->add(Crud::PAGE_EDIT, $generate)
            ->add(Crud::PAGE_EDIT, $revoke);
    }

    #[AdminRoute(path: '/{entityId}/generate-api-token', name: 'generate_api_token', options: ['methods' => ['GET']])]
    public function generateApiToken(AdminContext $context): Response
    {
        $client = $context->getEntity()->getInstance();
        if (!$client instanceof PartnerWizardClient) {
            $this->addFlash('danger', 'Client nicht gefunden.');

            return $this->redirectToIndex();
        }

        $replaced = $client->hasApiToken();
        $token = $client->generateApiToken();
        $this->entityManager->flush();

        $this->addFlash('warning', sprintf(
            'Neuer API-Token für „%s"%s – <strong>jetzt kopieren, er wird nicht noch einmal angezeigt:</strong><br><code style="user-select:all;font-size:14px">%s</code>',
            htmlspecialchars($client->getName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $replaced ? ' (alter Token ist ab sofort ungültig)' : '',
            htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        ));

        return $this->redirectToIndex();
    }

    #[AdminRoute(path: '/{entityId}/revoke-api-token', name: 'revoke_api_token', options: ['methods' => ['GET']])]
    public function revokeApiToken(AdminContext $context): Response
    {
        $client = $context->getEntity()->getInstance();
        if ($client instanceof PartnerWizardClient) {
            $client->clearApiToken();
            $this->entityManager->flush();
            $this->addFlash('success', sprintf('API-Token für „%s" wurde gelöscht.', $client->getName()));
        }

        return $this->redirectToIndex();
    }

    private function redirectToIndex(): Response
    {
        return $this->redirect($this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }

    private function renderTokenStatus(?PartnerWizardClient $client): string
    {
        if (!$client instanceof PartnerWizardClient || !$client->hasApiToken()) {
            return '<span class="badge badge-secondary">kein Token</span>';
        }

        return sprintf(
            '<code>%s…</code>%s<br><small class="text-muted">seit %s</small>',
            htmlspecialchars((string) $client->getApiTokenHint(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $client->isApiTokenRequired() ? ' <span class="badge badge-warning">Pflicht</span>' : '',
            $client->getApiTokenCreatedAt()?->format('d.m.Y H:i') ?? '–'
        );
    }

    private function renderPreviewButton(?PartnerWizardClient $client): string
    {
        if (!$client instanceof PartnerWizardClient || null === $client->getId()) {
            return '';
        }

        $url = $this->generateUrl('tfd_partner_wizard_preview', ['clientId' => $client->getClientId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $embed = $this->generateUrl('tfd_partner_wizard_embed', ['clientId' => $client->getClientId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $edit = $this->generateUrl('tfd_partner_wizard_preview', ['clientId' => $client->getClientId(), 'edit' => 1], UrlGeneratorInterface::ABSOLUTE_URL);

        return sprintf(
            '<a href="%s" class="btn btn-secondary btn-sm" target="_blank" rel="noopener"><i class="fa fa-eye me-1" aria-hidden="true"></i> Vorschau</a> '
            . '<a href="%s" class="btn btn-secondary btn-sm" target="_blank" rel="noopener"><i class="fa fa-pen me-1" aria-hidden="true"></i> Wizard bearbeiten (Texte, Fragen, Antworten)</a> '
            . '<a href="%s" class="btn btn-primary btn-sm" target="_blank" rel="noopener"><i class="fa fa-code me-1" aria-hidden="true"></i> Einbettungscode</a>',
            htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($edit, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($embed, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }
}
