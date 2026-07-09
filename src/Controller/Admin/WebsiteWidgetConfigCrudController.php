<?php

namespace App\Controller\Admin;

use App\Entity\WebsiteWidgetConfig;
use App\Service\WebsiteWidgetConfigManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AdminCrud(routePath: '/website-widget', routeName: 'admin_website_widget')]
final class WebsiteWidgetConfigCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly WebsiteWidgetConfigManager $widgetManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return WebsiteWidgetConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Website Widget')
            ->setEntityLabelInPlural('Website Widgets')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined()
            ->overrideTemplate('crud/index', 'admin/website_widget/index.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $previewAction = Action::new('previewWidget', 'Vorschau')
            ->setIcon('mdi:eye-outline')
            ->linkToUrl(fn (WebsiteWidgetConfig $widget): string => $this->buildWidgetUrl($widget))
            ->setHtmlAttributes(['target' => '_blank', 'rel' => 'noopener'])
            ->displayIf(static fn (WebsiteWidgetConfig $widget): bool => null !== $widget->getId());

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $previewAction)
            ->add(Crud::PAGE_DETAIL, $previewAction);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('Einbettungs-Code')->onlyOnDetail(),
            TextareaField::new('javascriptSnippet', 'JavaScript-Snippet (empfohlen)')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?WebsiteWidgetConfig $widget): string => $this->renderSnippet($widget, 'js')),
            TextareaField::new('iframeSnippet', 'iFrame-Code (alternativ)')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?WebsiteWidgetConfig $widget): string => $this->renderSnippet($widget, 'iframe')),
            TextField::new('widgetPreview', 'Widget-Vorschau')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?WebsiteWidgetConfig $widget): string => $this->renderPreview($widget)),

            FormField::addFieldset('Allgemein'),
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            BooleanField::new('active', 'Widget aktiviert'),
            AssociationField::new('kunde', 'Kunde')
                ->autocomplete()
                ->setRequired(true),
            AssociationField::new('apiToken', 'API-Key')
                ->autocomplete()
                ->setRequired(false)
                ->setHelp('Leer lassen, um beim Speichern automatisch einen API-Key fuer den Kunden zu erzeugen.'),
            AssociationField::new('vehicleDataPool', 'Datenpool')
                ->setRequired(false)
                ->setHelp('Optional: legt fest, welcher Fahrzeugdatenbestand im Widget verwendet wird. Leer = API-Kontext/Standard.'),
            AssociationField::new('vehicleType', 'Angezeigter Fahrzeugtyp')
                ->autocomplete()
                ->setRequired(false)
                ->setHelp('Optional: begrenzt das Widget auf diesen Fahrzeugtyp. Nur im Admin sichtbar.'),
            ChoiceField::new('recipientEmailMode', 'Anfrage-Empfaenger')
                ->setChoices(WebsiteWidgetConfig::getRecipientEmailModeChoices())
                ->setHelp('Legt fest, wohin Widget-Anfragen gesendet werden.')->hideOnIndex(),
            TextField::new('customRecipientEmail', 'Eigene Empfaenger-E-Mail')
                ->setRequired(false)
                ->setHelp('Nur ausfuellen, wenn "Eigene E-Mail-Adresse" gewaehlt ist.')->hideOnIndex(),
            TextField::new('embedKey', 'Widget-Key')
                ->setRequired(false)
                ->hideOnDetail()
                ->setHelp('Leer lassen, um automatisch einen oeffentlichen Key fuer den Einbettungs-Code zu erzeugen.'),
            TextField::new('title', 'Titel')->hideOnIndex(),
            TextField::new('subtitle', 'Untertitel')->setRequired(false)->hideOnIndex(),
            TextField::new('buttonText', 'Button-Text')->hideOnIndex(),
            TextField::new('successTitle', 'Erfolgs-Titel')->hideOnIndex(),
            ChoiceField::new('templateVariant', 'Layout')
                ->setChoices(WebsiteWidgetConfig::getTemplateChoices())->hideOnIndex(),
            TextareaField::new('successMessage', 'Erfolgs-Nachricht')
                ->setRequired(false)
                ->hideOnIndex(),
            IntegerField::new('iframeHeight', 'iFrame-Hoehe')
                ->hideOnIndex(),

            FormField::addFieldset('Branding & Farben'),
            TextField::new('primaryColor', 'Primaerfarbe')
                ->hideOnIndex()
                ->setHelp('Hex-Farbe, z. B. #5a2116.'),
            TextField::new('backgroundColor', 'Hintergrundfarbe')
                ->hideOnIndex(),
            TextField::new('textColor', 'Textfarbe')
                ->hideOnIndex(),
            TextField::new('selectBackgroundColor', 'Selectbox Hintergrundfarbe')
                ->hideOnIndex()
                ->setHelp('Hex-Farbe fuer Auswahlfelder im Widget, z. B. #ffffff.'),
            TextField::new('selectOpenBackgroundColor', 'Selectbox offen/Hover Hintergrundfarbe')
                ->hideOnIndex()
                ->setHelp('Hex-Farbe fuer geoeffnete Dropdowns, Hover-Zeilen und Suchfeld, z. B. #f3f4f6.'),
            TextareaField::new('customCss', 'Custom CSS')
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp('Wird am Ende der Widget-Styles eingefuegt. Selector .vdb-page verwenden.'),

            FormField::addFieldset('Datenerfassung'),
            BooleanField::new('showPhone', 'Telefon anzeigen')->hideOnIndex(),
            BooleanField::new('showEmail', 'E-Mail anzeigen')->hideOnIndex(),
            BooleanField::new('showFinVin', 'FIN/VIN anzeigen')->hideOnIndex(),
            BooleanField::new('showLicensePlate', 'Kennzeichen anzeigen')->hideOnIndex(),
            BooleanField::new('showMileage', 'Kilometerstand anzeigen')->hideOnIndex(),
            BooleanField::new('phoneRequired', 'Telefon Pflicht')->hideOnIndex(),
            BooleanField::new('emailRequired', 'E-Mail Pflicht')->hideOnIndex(),

            FormField::addFieldset('Datenschutz'),
            BooleanField::new('privacyRequired', 'Datenschutz-Checkbox erforderlich')->hideOnIndex(),
            TextField::new('privacyUrl', 'Datenschutz-URL')->setRequired(false)->hideOnIndex(),
            TextField::new('redirectAfterSubmit', 'Weiterleitung nach Absenden')->setRequired(false)->hideOnIndex(),
            TextareaField::new('privacyTextHtml', 'Datenschutz-Text (HTML)')
                ->setRequired(false)
                ->hideOnIndex(),

            FormField::addFieldset('Sicherheit & Domain-Schutz'),
            TextareaField::new('allowedDomains', 'Erlaubte Domains')
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp('Eine Domain pro Zeile. Leer lassen = alle Domains erlaubt. Wildcard: *.domain.de'),
            BooleanField::new('sendTrackingEvents', 'Tracking-Events an Fahrzeugdaten-API senden')
                ->hideOnIndex(),
            BooleanField::new('showPerformanceChart', 'Diagramm anzeigen')
                ->hideOnIndex()
                ->setHelp('Nur Admin-Einstellung: blendet das Leistungs-/Drehmoment-Diagramm im Widget ein oder aus.'),

            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof WebsiteWidgetConfig) {
            $this->widgetManager->prepareForSave($entityInstance, $entityManager, true);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof WebsiteWidgetConfig) {
            $this->widgetManager->prepareForSave($entityInstance, $entityManager, false);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function renderSnippet(?WebsiteWidgetConfig $widget, string $mode): string
    {
        if (!$widget instanceof WebsiteWidgetConfig || null === $widget->getEmbedKey()) {
            return '<span class="text-muted">Nach dem Speichern verfuegbar.</span>';
        }

        $snippet = 'iframe' === $mode
            ? $this->buildIframeSnippet($widget)
            : $this->buildJavascriptSnippet($widget);

        $escaped = htmlspecialchars($snippet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<div class="d-flex align-items-start gap-2"><pre class="m-0 p-2 border rounded bg-dark text-light flex-grow-1" style="white-space:pre-wrap">%s</pre><button type="button" class="btn btn-link p-0 text-primary" title="Code kopieren" aria-label="Code kopieren" data-copy-clipboard="%s"><i class="fa fa-copy fs-5" aria-hidden="true"></i></button></div>',
            $escaped,
            $escaped
        );
    }

    private function buildJavascriptSnippet(WebsiteWidgetConfig $widget): string
    {
        return sprintf(
            '<div id="tuningfux-widget"></div>' . "\n" . '<script src="%s"></script>',
            $this->generateUrl('app_website_widget_embed_js', ['embedKey' => $widget->getEmbedKey()], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }

    private function buildIframeSnippet(WebsiteWidgetConfig $widget): string
    {
        return sprintf(
            '<iframe src="%s" width="100%%" height="%d" frameborder="0" style="border:none;"></iframe>',
            $this->buildWidgetUrl($widget),
            $widget->getIframeHeight()
        );
    }

    private function renderPreview(?WebsiteWidgetConfig $widget): string
    {
        if (!$widget instanceof WebsiteWidgetConfig || null === $widget->getEmbedKey()) {
            return '<span class="text-muted">Nach dem Speichern verfuegbar.</span>';
        }

        $url = $this->buildWidgetUrl($widget);
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $height = min(max($widget->getIframeHeight(), 420), 900);

        return sprintf(
            '<div class="d-flex flex-column gap-3"><div><a href="%1$s" class="btn btn-secondary" target="_blank" rel="noopener"><i class="fa fa-eye me-1" aria-hidden="true"></i> Widget-Vorschau oeffnen</a></div><div class="border rounded overflow-hidden bg-body-tertiary"><iframe src="%1$s" width="100%%" height="%2$d" frameborder="0" style="border:0;display:block;width:100%%;min-height:%2$dpx;"></iframe></div></div>',
            $escapedUrl,
            $height
        );
    }

    private function buildWidgetUrl(WebsiteWidgetConfig $widget): string
    {
        return $this->generateUrl('app_website_widget_show', ['embedKey' => $widget->getEmbedKey()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
