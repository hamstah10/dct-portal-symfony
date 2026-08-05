<?php

namespace App\Controller\Admin;

use App\Entity\ChiptuningWidgetConfig;
use App\Service\ChiptuningWidgetConfigManager;
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

#[AdminCrud(routePath: '/chiptuning-widget', routeName: 'admin_chiptuning_widget')]
final class ChiptuningWidgetConfigCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ChiptuningWidgetConfigManager $widgetManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ChiptuningWidgetConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Chiptuning Widget')
            ->setEntityLabelInPlural('Chiptuning Widgets')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $previewAction = Action::new('previewWidget', 'Vorschau')
            ->setIcon('mdi:eye-outline')
            ->linkToUrl(fn (ChiptuningWidgetConfig $widget): string => $this->buildWidgetUrl($widget))
            ->setHtmlAttributes(['target' => '_blank', 'rel' => 'noopener'])
            ->displayIf(static fn (ChiptuningWidgetConfig $widget): bool => null !== $widget->getId());

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
            TextareaField::new('javascriptSnippet', 'JavaScript-Snippet')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?ChiptuningWidgetConfig $widget): string => $this->renderSnippet($widget, 'js')),
            TextareaField::new('iframeSnippet', 'iFrame-Code')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?ChiptuningWidgetConfig $widget): string => $this->renderSnippet($widget, 'iframe')),
            TextField::new('widgetPreview', 'Widget-Vorschau')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?ChiptuningWidgetConfig $widget): string => $this->renderPreview($widget)),

            FormField::addFieldset('Allgemein'),
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            BooleanField::new('active', 'Widget aktiviert'),
            TextField::new('embedKey', 'Einbau-Code')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?ChiptuningWidgetConfig $widget): string => $this->renderIndexSnippet($widget)),
            AssociationField::new('kunde', 'Kunde')
                ->autocomplete()
                ->setRequired(true),
            AssociationField::new('apiToken', 'API-Key')
                ->autocomplete()
                ->setRequired(false)
                ->setHelp('Leer lassen, um beim Speichern automatisch einen API-Key fuer den Kunden zu erzeugen.'),
            AssociationField::new('vehicleDataPool', 'Datenpool')
                ->setRequired(true)
                ->setHelp('Pflichtfeld: dieser Datenbestand wird fuer die Widget-API verwendet.'),
            AssociationField::new('vehicleType', 'Angezeigter Fahrzeugtyp')
                ->autocomplete()
                ->setRequired(false)
                ->setHelp('Optional: begrenzt das Widget z. B. auf PKW.'),
            TextField::new('embedKey', 'Widget-Key')
                ->setRequired(false)
                ->hideOnIndex()
                ->hideOnDetail()
                ->setHelp('Leer lassen, um automatisch einen oeffentlichen Key fuer den Einbettungs-Code zu erzeugen.'),
            TextField::new('title', 'Titel'),
            TextField::new('subtitle', 'Untertitel')->setRequired(false)->hideOnIndex(),
            ChoiceField::new('templateVariant', 'Layout')
                ->setChoices(ChiptuningWidgetConfig::getTemplateChoices())->hideOnIndex(),
            IntegerField::new('iframeHeight', 'iFrame-Hoehe')->hideOnIndex(),

            FormField::addFieldset('Branding & Farben'),
            TextField::new('primaryColor', 'Primaerfarbe')->hideOnIndex(),
            TextField::new('backgroundColor', 'Hintergrundfarbe')->hideOnIndex(),
            TextField::new('textColor', 'Textfarbe')->hideOnIndex(),
            TextField::new('selectBackgroundColor', 'Selectbox Hintergrundfarbe')->hideOnIndex(),
            TextField::new('selectOpenBackgroundColor', 'Selectbox offen/Hover Hintergrundfarbe')->hideOnIndex(),
            TextareaField::new('customCss', 'Custom CSS')
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp('Wird am Ende der Widget-Styles eingefuegt. Selector .vdb-page verwenden.'),

            FormField::addFieldset('Sicherheit & Verhalten'),
            TextareaField::new('allowedDomains', 'Erlaubte Domains')
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp('Eine Domain pro Zeile. Leer lassen = alle Domains erlaubt. Wildcard: *.domain.de'),
            BooleanField::new('sendTrackingEvents', 'Tracking-Events senden')->hideOnIndex(),
            BooleanField::new('showPerformanceChart', 'Diagramm anzeigen')->hideOnIndex(),
            ChoiceField::new('typeIconColor', 'Fahrzeugtyp-Icon Farbe')
                ->setChoices(['Weiss' => 'white', 'Schwarz' => 'black'])
                ->hideOnIndex(),

            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ChiptuningWidgetConfig) {
            $this->widgetManager->prepareForSave($entityInstance, $entityManager, true);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ChiptuningWidgetConfig) {
            $this->widgetManager->prepareForSave($entityInstance, $entityManager, false);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function renderSnippet(?ChiptuningWidgetConfig $widget, string $mode): string
    {
        if (!$widget instanceof ChiptuningWidgetConfig || null === $widget->getEmbedKey()) {
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

    private function renderIndexSnippet(?ChiptuningWidgetConfig $widget): string
    {
        if (!$widget instanceof ChiptuningWidgetConfig || null === $widget->getEmbedKey()) {
            return '<span class="text-muted">Nach dem Speichern verfuegbar.</span>';
        }

        $snippet = $this->buildJavascriptSnippet($widget);
        $escaped = htmlspecialchars($snippet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<div class="d-flex align-items-start gap-2" style="min-width:360px;max-width:520px"><pre class="m-0 p-2 border rounded bg-dark text-light flex-grow-1 small" style="white-space:pre-wrap;line-height:1.35">%s</pre><button type="button" class="btn btn-link p-0 text-primary" title="Code kopieren" aria-label="Code kopieren" data-copy-clipboard="%s"><i class="fa fa-copy fs-5" aria-hidden="true"></i></button></div>',
            $escaped,
            $escaped
        );
    }

    private function buildJavascriptSnippet(ChiptuningWidgetConfig $widget): string
    {
        return sprintf(
            '<div id="chiptuning-widget"></div>' . "\n" . '<script src="%s"></script>',
            $this->generateUrl('app_chiptuning_widget_embed_js', ['embedKey' => $widget->getEmbedKey()], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }

    private function buildIframeSnippet(ChiptuningWidgetConfig $widget): string
    {
        return sprintf(
            '<iframe src="%s" width="100%%" height="%d" frameborder="0" style="border:none;"></iframe>',
            $this->buildWidgetUrl($widget),
            $widget->getIframeHeight()
        );
    }

    private function renderPreview(?ChiptuningWidgetConfig $widget): string
    {
        if (!$widget instanceof ChiptuningWidgetConfig || null === $widget->getEmbedKey()) {
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

    private function buildWidgetUrl(ChiptuningWidgetConfig $widget): string
    {
        return $this->generateUrl('app_chiptuning_widget_show', ['embedKey' => $widget->getEmbedKey()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
