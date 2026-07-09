<?php

namespace App\Controller\Admin;

use App\Entity\WebsiteSniffer;
use App\Entity\WebsiteSnifferRun;
use App\Repository\WebsiteSnifferRunRepository;
use App\Service\WebsiteSnifferService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

#[AdminCrud(routePath: '/websites/sniffer', routeName: 'admin_websites_sniffer')]
final class WebsiteSnifferCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly WebsiteSnifferService $websiteSnifferService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly WebsiteSnifferRunRepository $websiteSnifferRunRepository,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return WebsiteSniffer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Sniffer')
            ->setEntityLabelInPlural('Sniffer')
            ->setDefaultSort(['id' => 'DESC'])
            ->overrideTemplate('crud/index', 'admin/website_sniffer/index.html.twig')
            ->showEntityActionsInlined();
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_INDEX !== $pageName) {
            return $responseParameters;
        }

        $preferredRunId = 0;
        $request = $this->getContext()?->getRequest();
        if (null !== $request) {
            $preferredRunIdParam = $request->query->get('progressRunId');
            if (is_scalar($preferredRunIdParam) && ctype_digit((string) $preferredRunIdParam)) {
                $preferredRunId = (int) $preferredRunIdParam;
            }
        }

        $run = $this->resolveProgressRun($preferredRunId);
        if (!$run instanceof WebsiteSnifferRun || null === $run->getId()) {
            return $responseParameters;
        }

        $runId = (int) $run->getId();
        $responseParameters->set('snifferProgressEnabled', true);
        $responseParameters->set('snifferProgressRunId', $runId);
        $responseParameters->set('snifferProgressRunName', $run->getSniffer()?->getName() ?? 'Sniffer');
        $responseParameters->set('snifferProgressStatus', $run->getStatus());
        $responseParameters->set('snifferProgressPercent', $run->getProgressPercent());
        $responseParameters->set('snifferProgressProcessedPages', $run->getProcessedPages());
        $responseParameters->set('snifferProgressTotalPages', $run->getTotalPages());
        $responseParameters->set('snifferProgressPagesCount', $run->getPagesCount());
        $responseParameters->set('snifferProgressError', $run->getErrorMessage());
        $responseParameters->set('snifferProgressStatusUrl', $this->generateUrl('admin_website_sniffer_run_status', ['id' => $runId]));
        $responseParameters->set('snifferProgressRunDetailUrl', $this->buildRunDetailUrl($runId));
        $responseParameters->set('snifferProgressPagesUrl', $run->getPagesCount() > 0 ? $this->buildRunPagesUrl($runId) : null);

        return $responseParameters;
    }

    public function configureActions(Actions $actions): Actions
    {
        $runAction = Action::new('runSniffer', '')
            ->setIcon('line-md:play-filled')
            ->linkToRoute('admin_website_sniffer_run_execute', static function (WebsiteSniffer $sniffer): array {
                return ['id' => $sniffer->getId()];
            })
            ->displayIf(static fn (WebsiteSniffer $sniffer): bool => null !== $sniffer->getId());
        $parsedPagesAction = Action::new('parsedPages', '')
            ->setIcon('ion:images')
            ->linkToRoute('admin_website_sniffer_pages', static function (WebsiteSniffer $sniffer): array {
                return ['id' => $sniffer->getId()];
            });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $parsedPagesAction)
            ->add(Crud::PAGE_DETAIL, $parsedPagesAction)
            ->add(Crud::PAGE_INDEX, $runAction)
            ->add(Crud::PAGE_DETAIL, $runAction);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()
            ->hideOnIndex(),
            TextField::new('name', 'Name'),
            TextField::new('directoryPath', 'Verzeichnis / URL')
                ->setHelp('Absoluter Pfad, relativ zum Projektverzeichnis oder http(s)-URL (z. B. https://example.com/landing/).'),
            BooleanField::new('includeSubdirectories', 'Unterordner scannen')->hideOnIndex(),
            BooleanField::new('active', 'Aktiv'),
            TextareaField::new('description', 'Beschreibung')
                ->setRequired(false)
                ->hideOnIndex(),
            IntegerField::new('runCount', 'Durchlaeufe')
                ->hideOnForm(),
            ChoiceField::new('lastRunStatus', 'Letzter Status')
                ->setChoices(WebsiteSnifferRun::getStatusChoices())
                ->renderAsBadges([
                    WebsiteSnifferRun::STATUS_RUNNING => 'warning',
                    WebsiteSnifferRun::STATUS_SUCCESS => 'success',
                    WebsiteSnifferRun::STATUS_FAILED => 'danger',
                ])
                ->hideOnForm(),
            DateTimeField::new('lastRunAt', 'Letzter Lauf')
                ->hideOnForm(),
            IntegerField::new('lastRunPagesCount', 'Seiten im letzten Lauf')
                ->hideOnForm(),
            TextField::new('name', 'Geparste Seiten')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?WebsiteSniffer $sniffer): string => $this->renderParsedPagesButton($sniffer)),
            AssociationField::new('runs', 'Durchlaeufe')
                ->onlyOnDetail(),
            DateTimeField::new('createdAt', 'Erstellt am')
                ->hideOnForm(),
        ];
    }

    #[Route('/admin/website-sniffer/{id}/run', name: 'admin_website_sniffer_run_execute', methods: ['GET'])]
    public function runSniffer(WebsiteSniffer $sniffer): RedirectResponse
    {
        $run = $this->websiteSnifferService->startRun($sniffer);
        $runId = $run->getId();
        if (null === $runId) {
            $this->addFlash('danger', sprintf('Sniffer "%s" konnte nicht gestartet werden.', $sniffer->getName()));

            return $this->redirect($this->buildSnifferIndexUrl());
        }

        try {
            $this->launchBackgroundRun((int) $runId);
            $this->addFlash('info', sprintf('Sniffer "%s" wurde gestartet.', $sniffer->getName()));
        } catch (\Throwable $throwable) {
            $finishedAt = new \DateTimeImmutable();
            $run
                ->setStatus(WebsiteSnifferRun::STATUS_FAILED)
                ->setFinishedAt($finishedAt)
                ->setErrorMessage('Hintergrundprozess konnte nicht gestartet werden: ' . trim($throwable->getMessage()));

            $sniffer
                ->setLastRunAt($finishedAt)
                ->setLastRunStatus(WebsiteSnifferRun::STATUS_FAILED)
                ->setLastRunPagesCount(0);

            $this->entityManager->persist($run);
            $this->entityManager->persist($sniffer);
            $this->entityManager->flush();

            $this->addFlash('danger', sprintf('Sniffer "%s" fehlgeschlagen: %s', $sniffer->getName(), $run->getErrorMessage()));
        }

        return $this->redirect($this->buildSnifferIndexUrl((int) $runId));
    }

    #[Route('/admin/website-sniffer/runs/{id}/progress', name: 'admin_website_sniffer_run_progress', methods: ['GET'])]
    public function runProgress(WebsiteSnifferRun $run): RedirectResponse
    {
        $runId = $run->getId();
        if (null === $runId) {
            return $this->redirect($this->buildSnifferIndexUrl());
        }

        return $this->redirect($this->buildSnifferIndexUrl((int) $runId));
    }

    #[Route('/admin/website-sniffer/runs/{id}/status', name: 'admin_website_sniffer_run_status', methods: ['GET'])]
    public function runStatus(WebsiteSnifferRun $run): JsonResponse
    {
        $runId = $run->getId();
        $runDetailUrl = null;
        $pagesUrl = null;

        if (null !== $runId) {
            $runDetailUrl = $this->buildRunDetailUrl($runId);
            if ($run->getPagesCount() > 0) {
                $pagesUrl = $this->buildRunPagesUrl($runId);
            }
        }

        return $this->json([
            'id' => $runId,
            'status' => $run->getStatus(),
            'processedPages' => $run->getProcessedPages(),
            'totalPages' => $run->getTotalPages(),
            'pagesCount' => $run->getPagesCount(),
            'progressPercent' => $run->getProgressPercent(),
            'errorMessage' => $run->getErrorMessage(),
            'startedAt' => $run->getStartedAt()?->format(\DateTimeInterface::ATOM),
            'finishedAt' => $run->getFinishedAt()?->format(\DateTimeInterface::ATOM),
            'runDetailUrl' => $runDetailUrl,
            'pagesUrl' => $pagesUrl,
        ]);
    }

    #[Route('/admin/website-sniffer/{id}/pages', name: 'admin_website_sniffer_pages', methods: ['GET'])]
    public function parsedPages(WebsiteSniffer $sniffer): RedirectResponse
    {
        $targetRun = null;
        foreach ($sniffer->getRuns() as $run) {
            if ($run->getPages()->count() > 0) {
                $targetRun = $run;
                break;
            }
        }

        if (!$targetRun instanceof WebsiteSnifferRun || null === $targetRun->getId()) {
            $this->addFlash('warning', sprintf('Keine geparsten Seiten fuer "%s" vorhanden.', $sniffer->getName()));

            return $this->redirect($this->adminUrlGenerator
                ->unsetAll()
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl());
        }

        $pagesUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(WebsiteSnifferPageCrudController::class)
            ->setAction(Action::INDEX)
            ->set('runId', (string) $targetRun->getId())
            ->generateUrl();

        return $this->redirect($pagesUrl);
    }

    private function renderParsedPagesButton(?WebsiteSniffer $sniffer): string
    {
        if (!$sniffer instanceof WebsiteSniffer || null === $sniffer->getId()) {
            return '<span class="text-muted">-</span>';
        }

        $hasParsedPages = false;
        foreach ($sniffer->getRuns() as $run) {
            if ($run->getPages()->count() > 0) {
                $hasParsedPages = true;
                break;
            }
        }

        if (!$hasParsedPages) {
            return '<span class="text-muted">Keine Seiten</span>';
        }

        $url = $this->generateUrl('admin_website_sniffer_pages', ['id' => $sniffer->getId()]);
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<a href="%s" class="btn btn-primary btn-sm rounded-0">Geparste Seiten</a>',
            $escapedUrl
        );
    }

    private function launchBackgroundRun(int $runId): void
    {
        $consolePath = $this->projectDir . '/bin/console';
        $phpBinary = $this->resolvePhpBinary();
        $appEnv = (string) ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev');

        $commandLine = sprintf(
            'cd %s && nohup %s %s app:website-sniffer:run --run-id=%d --env=%s --no-interaction >/dev/null 2>&1 &',
            escapeshellarg($this->projectDir),
            escapeshellarg($phpBinary),
            escapeshellarg($consolePath),
            $runId,
            escapeshellarg($appEnv)
        );

        $process = Process::fromShellCommandline($commandLine);
        $process->setTimeout(10);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Unbekannter Startfehler.');
        }
    }

    private function resolvePhpBinary(): string
    {
        $candidates = [
            PHP_BINARY,
            PHP_BINDIR . '/php',
            '/usr/bin/php',
            '/usr/local/bin/php',
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ('' === $candidate) {
                continue;
            }

            if (str_contains(strtolower(basename($candidate)), 'php-fpm')) {
                continue;
            }

            if (@is_file($candidate) && @is_executable($candidate)) {
                return $candidate;
            }
        }

        return 'php';
    }

    private function buildRunDetailUrl(int $runId): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(WebsiteSnifferRunCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId((string) $runId)
            ->generateUrl();
    }

    private function buildRunPagesUrl(int $runId): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(WebsiteSnifferPageCrudController::class)
            ->setAction(Action::INDEX)
            ->set('runId', (string) $runId)
            ->generateUrl();
    }

    private function buildSnifferIndexUrl(?int $progressRunId = null): string
    {
        $urlGenerator = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX);

        if (null !== $progressRunId && $progressRunId > 0) {
            $urlGenerator->set('progressRunId', (string) $progressRunId);
        }

        return $urlGenerator->generateUrl();
    }

    private function resolveProgressRun(int $preferredRunId): ?WebsiteSnifferRun
    {
        if ($preferredRunId > 0) {
            $run = $this->websiteSnifferRunRepository->find($preferredRunId);
            if ($run instanceof WebsiteSnifferRun) {
                return $run;
            }
        }

        return $this->websiteSnifferRunRepository->createQueryBuilder('run')
            ->where('run.status = :status')
            ->setParameter('status', WebsiteSnifferRun::STATUS_RUNNING)
            ->orderBy('run.startedAt', 'DESC')
            ->addOrderBy('run.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
