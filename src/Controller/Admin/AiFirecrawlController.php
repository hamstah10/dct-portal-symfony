<?php

namespace App\Controller\Admin;

use App\Service\Ai\FirecrawlRunStorage;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

final class AiFirecrawlController extends AbstractController
{
    private const DEFAULT_MODEL = 'gpt-5-mini';

    /** Chat-Modelle der OpenAI-Plattform (Firecrawl-Läufe nutzen PlatformInterface = ai.platform.openai). */
    private const MODELS = [
        'gpt-5.2', 'gpt-5', 'gpt-5-chat-latest', 'gpt-5-mini', 'gpt-5-nano',
        'gpt-4.5-preview', 'gpt-4.1', 'gpt-4.1-mini', 'gpt-4.1-nano',
        'gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo',
        'o3', 'o3-mini', 'o3-mini-high',
    ];

    public function __construct(
        private readonly FirecrawlRunStorage $storage,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[AdminRoute(path: '/ai/firecrawl', name: 'ai_firecrawl', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST') && '1' === (string) $request->request->get('delete_run', '')) {
            return $this->deleteRun($request);
        }

        $action = strtolower(trim((string) $request->request->get('action', $request->query->get('action', 'scrape'))));
        if (!\in_array($action, ['scrape', 'crawl', 'map'], true)) {
            $action = 'scrape';
        }

        $url = trim((string) $request->request->get('url', ''));
        $instruction = trim((string) $request->request->get('instruction', ''));
        $model = trim((string) $request->request->get('model', self::DEFAULT_MODEL));
        $crawlDepth = (int) $request->request->get('crawlDepth', 0);
        $crawlLimit = (int) $request->request->get('crawlLimit', 0);
        if ('' === $instruction) {
            $instruction = $this->defaultInstruction($action);
        }
        $instruction = $this->applyCrawlOptions($instruction, $action, $crawlDepth, $crawlLimit);

        $response = null;
        $metadata = null;
        $error = null;
        $mapLinks = [];
        $activeRunStatus = null;
        $activeRunProgressLabel = null;
        $activeRunProgressCurrent = 0;
        $activeRunProgressMax = FirecrawlRunStorage::DEFAULT_PROGRESS_MAX;
        $activeRunProgressPercent = 0;
        $runs = $this->storage->listRuns();
        $activeRunId = (int) $request->query->get('run', 0);
        $activeRun = $activeRunId > 0 ? $this->storage->getRun($activeRunId) : null;
        if (!is_array($activeRun)) {
            $activeRunId = 0;
        }

        if (!$request->isMethod('POST') && is_array($activeRun)) {
            $action = (string) ($activeRun['action'] ?? $action);
            $url = (string) ($activeRun['url'] ?? $url);
            $instruction = (string) ($activeRun['instruction'] ?? $instruction);
            $model = (string) ($activeRun['model'] ?? $model);
            $crawlDepth = (int) ($activeRun['crawlDepth'] ?? $crawlDepth);
            $crawlLimit = (int) ($activeRun['crawlLimit'] ?? $crawlLimit);
            $response = $activeRun['response'] ?? null;
            $metadata = $activeRun['metadata'] ?? null;
            $error = $activeRun['error'] ?? null;
            $mapLinks = $activeRun['mapLinks'] ?? [];
            $activeRunStatus = (string) ($activeRun['status'] ?? FirecrawlRunStorage::STATUS_SUCCESS);
            $activeRunProgressLabel = $activeRun['progressLabel'] ?? null;
            $activeRunProgressCurrent = (int) ($activeRun['progressCurrent'] ?? 0);
            $activeRunProgressMax = (int) ($activeRun['progressMax'] ?? FirecrawlRunStorage::DEFAULT_PROGRESS_MAX);
            $activeRunProgressPercent = (int) ($activeRun['progressPercent'] ?? 0);
        }

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('admin_ai_firecrawl', $csrfToken)) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_ai_firecrawl');
            }

            if ('' === $url) {
                $error = 'Bitte eine URL angeben.';
            } else {
                try {
                    $runId = $this->storage->createRun($action, $url, $instruction, $model, $crawlDepth, $crawlLimit);
                    $this->launchBackgroundRun($runId);
                    $this->addFlash('info', 'Firecrawl-Run wurde im Hintergrund gestartet.');

                    return $this->redirectToRoute('admin_ai_firecrawl', ['run' => $runId]);
                } catch (\Throwable $exception) {
                    $error = $exception->getMessage();
                    if (isset($runId) && $runId > 0) {
                        $this->storage->failRun($runId, 'Hintergrundprozess konnte nicht gestartet werden: '.trim($error));

                        return $this->redirectToRoute('admin_ai_firecrawl', ['run' => $runId]);
                    }
                }
            }
        }

        return $this->render('admin/ai_firecrawl/index.html.twig', [
            'url' => $url,
            'instruction' => $instruction,
            'model' => '' !== $model ? $model : self::DEFAULT_MODEL,
            'models' => self::MODELS,
            'action' => $action,
            'crawlDepth' => $crawlDepth,
            'crawlLimit' => $crawlLimit,
            'mapLinks' => $mapLinks,
            'runs' => $runs,
            'activeRunId' => $activeRunId,
            'activeRunStatus' => $activeRunStatus,
            'activeRunProgressLabel' => $activeRunProgressLabel,
            'activeRunProgressCurrent' => $activeRunProgressCurrent,
            'activeRunProgressMax' => $activeRunProgressMax,
            'activeRunProgressPercent' => $activeRunProgressPercent,
            'activeRunStatusUrl' => $activeRunId > 0 ? $this->generateUrl('admin_ai_firecrawl_status', ['id' => $activeRunId]) : null,
            'response' => $response,
            'metadata' => $metadata,
            'error' => $error,
        ]);
    }

    #[AdminRoute(path: '/ai/firecrawl/runs/{id}/status', name: 'ai_firecrawl_status', options: ['methods' => ['GET']])]
    public function runStatus(int $id): JsonResponse
    {
        $run = $this->storage->getRun($id);
        if (!is_array($run)) {
            return $this->json([
                'message' => 'Run nicht gefunden.',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => (int) ($run['id'] ?? $id),
            'status' => (string) ($run['status'] ?? FirecrawlRunStorage::STATUS_SUCCESS),
            'progressLabel' => $run['progressLabel'] ?? null,
            'progressCurrent' => (int) ($run['progressCurrent'] ?? 0),
            'progressMax' => (int) ($run['progressMax'] ?? FirecrawlRunStorage::DEFAULT_PROGRESS_MAX),
            'progressPercent' => (int) ($run['progressPercent'] ?? 0),
            'error' => $run['error'] ?? null,
            'updatedAt' => (int) ($run['updatedAt'] ?? time()),
            'finishedAt' => $run['finishedAt'] ?? null,
        ]);
    }

    private function deleteRun(Request $request): Response
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_ai_firecrawl', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_ai_firecrawl');
        }

        $id = (int) $request->request->get('runId', 0);
        if ($id <= 0) {
            return $this->redirectToRoute('admin_ai_firecrawl');
        }

        $this->storage->deleteRun($id);
        $this->addFlash('success', 'Run geloescht.');

        return $this->redirectToRoute('admin_ai_firecrawl');
    }

    private function launchBackgroundRun(int $runId): void
    {
        $consolePath = $this->projectDir.'/bin/console';
        $phpBinary = $this->resolvePhpBinary();
        $appEnv = (string) ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev');

        $commandLine = sprintf(
            'cd %s && nohup %s %s app:ai-firecrawl:run --run-id=%d --env=%s --no-interaction >/dev/null 2>&1 &',
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

    private function defaultInstruction(string $action): string
    {
        return match ($action) {
            'crawl' => 'Summarize the most important information in less than 200 words.',
            'map' => 'Return the discovered URLs as a bullet list.',
            default => 'Summarize the content in less than 200 words.',
        };
    }

    private function applyCrawlOptions(string $instruction, string $action, int $depth, int $limit): string
    {
        if ('crawl' !== $action) {
            return $instruction;
        }

        $extras = [];
        if ($depth > 0) {
            $extras[] = sprintf('Crawl depth: %d', $depth);
        }
        if ($limit > 0) {
            $extras[] = sprintf('Max pages: %d', $limit);
        }

        if ([] === $extras) {
            return $instruction;
        }

        return rtrim($instruction)."\n\n".implode(', ', $extras).'.';
    }

    private function resolvePhpBinary(): string
    {
        $candidates = [
            PHP_BINARY,
            PHP_BINDIR.'/php',
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
}
