<?php

namespace App\Controller\Admin;

use App\Entity\WebsiteSnifferPage;
use App\Entity\WebsiteSnifferRun;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[AdminCrud(routePath: '/websites/sniffer/runs', routeName: 'admin_websites_sniffer_run')]
final class WebsiteSnifferRunCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return WebsiteSnifferRun::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Sniffer-Lauf')
            ->setEntityLabelInPlural('Sniffer-Laeufe')
            ->setDefaultSort(['runAt' => 'DESC', 'id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $progressAction = Action::new('runProgress', 'Fortschritt')
            ->setIcon('mynaui:spinner-one')
            ->linkToRoute('admin_website_sniffer_run_progress', static function (WebsiteSnifferRun $run): array {
                return ['id' => $run->getId()];
            })
            ->displayIf(static fn (WebsiteSnifferRun $run): bool => WebsiteSnifferRun::STATUS_RUNNING === $run->getStatus());

        $parsedPagesAction = Action::new('parsedPages', 'Geparste Seiten')
            ->setIcon('ion:images')
            ->linkToRoute('admin_website_sniffer_run_pages', static function (WebsiteSnifferRun $run): array {
                return ['id' => $run->getId()];
            })
            ->displayIf(static fn (WebsiteSnifferRun $run): bool => $run->getPagesCount() > 0);

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $progressAction)
            ->add(Crud::PAGE_INDEX, $parsedPagesAction)
            ->add(Crud::PAGE_DETAIL, $progressAction)
            ->add(Crud::PAGE_DETAIL, $parsedPagesAction);
    }

    public function configureFields(string $pageName): iterable
    {
        $pagesGallery = TextField::new('sourceDirectory', 'Seiten Galerie')
            ->onlyOnDetail()
            ->renderAsHtml()
            ->formatValue(fn (mixed $value, ?WebsiteSnifferRun $run): string => $this->renderPagesGallery($run));

        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('sniffer', 'Sniffer'),
            DateTimeField::new('runAt', 'Lauf gestartet'),
            DateTimeField::new('startedAt', 'Startzeit'),
            DateTimeField::new('finishedAt', 'Endzeit')->setRequired(false),
            ChoiceField::new('status', 'Status')
                ->setChoices(WebsiteSnifferRun::getStatusChoices())
                ->renderAsBadges([
                    WebsiteSnifferRun::STATUS_RUNNING => 'warning',
                    WebsiteSnifferRun::STATUS_SUCCESS => 'success',
                    WebsiteSnifferRun::STATUS_FAILED => 'danger',
                ]),
            IntegerField::new('processedPages', 'Bearbeitet')
                ->hideOnForm(),
            IntegerField::new('totalPages', 'Gesamt')
                ->hideOnForm(),
            IntegerField::new('pagesCount', 'Gefundene Seiten'),
            IntegerField::new('durationInSeconds', 'Dauer (Sek.)')
                ->setRequired(false)
                ->hideOnForm(),
            TextField::new('sourceDirectory', 'Verzeichnis')
                ->setHelp('Aufgeloester Pfad fuer diesen Lauf.')
                ->hideOnIndex(),
            TextareaField::new('errorMessage', 'Fehler')
                ->setRequired(false)
                ->hideOnIndex(),
            $pagesGallery,
        ];
    }

    private function renderPagesGallery(?WebsiteSnifferRun $run): string
    {
        if (!$run instanceof WebsiteSnifferRun) {
            return '<span class="text-muted">Keine Seiten</span>';
        }

        $pages = $run->getPages()->toArray();
        if ([] === $pages) {
            return '<span class="text-muted">Keine Seiten in diesem Lauf</span>';
        }

        $cards = [];
        foreach ($pages as $page) {
            if (!$page instanceof WebsiteSnifferPage) {
                continue;
            }

            $pageId = $page->getId();
            if (null === $pageId) {
                continue;
            }

            $pageDetailUrl = $this->adminUrlGenerator
                ->unsetAll()
                ->setController(WebsiteSnifferPageCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId((string) $pageId)
                ->generateUrl();
            $escapedDetailUrl = htmlspecialchars($pageDetailUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $relativePath = trim((string) $page->getRelativePath());
            $escapedRelativePath = htmlspecialchars('' !== $relativePath ? $relativePath : '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $filePath = trim((string) $page->getFilePath());
            $escapedFilePath = htmlspecialchars('' !== $filePath ? $filePath : '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $title = trim((string) ($page->getTitle() ?? ''));
            $escapedTitle = htmlspecialchars('' !== $title ? $title : 'Ohne Titel', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $screenshotPath = trim((string) ($page->getScreenshotPath() ?? ''));
            $thumbHtml = '<div style="display:flex;align-items:center;justify-content:center;height:128px;border-radius:0;background:#f3f5f8;border:1px solid #dbe2ea;color:#7a8698;font-size:12px;">Kein Screenshot</div>';
            if ('' !== $screenshotPath) {
                $screenshotUrl = '/' . ltrim($screenshotPath, '/');
                $escapedScreenshotUrl = htmlspecialchars($screenshotUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $thumbHtml = sprintf(
                    '<button type="button" data-lightbox-url="%1$s" data-lightbox-caption="%2$s" style="display:block;width:100%%;padding:0;border:0;background:transparent;cursor:zoom-in;border-radius:0;"><img src="%1$s" alt="" style="display:block;width:100%%;height:128px;object-fit:cover;border-radius:0;border:1px solid #dbe2ea;"></button>',
                    $escapedScreenshotUrl,
                    $escapedRelativePath
                );
            }

            $openParsedPageLink = '';
            $parsedPageUrl = $this->buildParsedPageUrl($page);
            if (null !== $parsedPageUrl) {
                $openParsedPageLink = sprintf(
                    ' <a href="%s" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm rounded-0" style="padding:5px 8px;">Seite</a>',
                    htmlspecialchars($parsedPageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                );
            }

            $cards[] = sprintf(
                '<article style="display:flex;flex-direction:column;gap:8px;padding:10px;background:#fff;border:1px solid #d9e1ea;border-radius:0;box-shadow:0 1px 2px rgba(15,23,42,.04);min-width:0;">%s<div style="font-size:12px;font-weight:600;color:#182335;line-height:1.35;word-break:break-word;">%s</div><div style="font-size:11px;color:#4f5f75;line-height:1.35;word-break:break-word;" title="%s">%s</div><div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;"><a href="%s" class="btn btn-primary btn-sm rounded-0" style="padding:5px 8px;">Admin</a>%s</div></article>',
                $thumbHtml,
                $escapedRelativePath,
                $escapedFilePath,
                $escapedTitle,
                $escapedDetailUrl,
                $openParsedPageLink
            );
        }

        if ([] === $cards) {
            return '<span class="text-muted">Keine Seiten in diesem Lauf</span>';
        }

        return sprintf(
            '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">%s</div>',
            implode('', $cards)
        );
    }

    private function buildParsedPageUrl(WebsiteSnifferPage $page): ?string
    {
        $filePath = trim((string) $page->getFilePath());
        if ('' === $filePath) {
            return null;
        }

        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://') || str_starts_with($filePath, 'file://')) {
            return $filePath;
        }

        $normalizedFilePath = str_replace('\\', '/', $filePath);
        $publicDir = rtrim(str_replace('\\', '/', $this->projectDir), '/') . '/public/';

        if (!str_starts_with($normalizedFilePath, $publicDir)) {
            return null;
        }

        $relative = ltrim(substr($normalizedFilePath, strlen($publicDir)), '/');
        if ('' === $relative) {
            return null;
        }

        $segments = array_map('rawurlencode', explode('/', $relative));

        return '/' . implode('/', $segments);
    }

    #[Route('/admin/website-sniffer/runs/{id}/pages', name: 'admin_website_sniffer_run_pages', methods: ['GET'])]
    public function parsedPages(WebsiteSnifferRun $run): RedirectResponse
    {
        $runId = $run->getId();
        if (null === $runId) {
            $runsIndexUrl = $this->adminUrlGenerator
                ->unsetAll()
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($runsIndexUrl);
        }

        $pagesUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(WebsiteSnifferPageCrudController::class)
            ->setAction(Action::INDEX)
            ->set('runId', (string) $runId)
            ->generateUrl();

        return $this->redirect($pagesUrl);
    }
}
