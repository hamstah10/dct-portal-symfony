<?php

namespace App\Controller\Admin;

use App\Entity\ScraperRun;
use App\Message\ScraperRunMessage;
use App\Repository\ScraperRunRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Stamp\TransportNameStamp;
use JsonException;

final class ScraperControlController extends AbstractController
{
    private const VEHICLE_TYPES = ['car', 'truck', 'moto', 'atv', 'agricultural', 'boat', 'jetski'];
    private const TRUCK_ALL_TYPES_MODE = 'brands:scrape-all-types';

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        private readonly MessageBusInterface $messageBus,
        private readonly ScraperRunRepository $scraperRunRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/scraper', name: 'scraper_control', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $scraperDir = $this->projectDir . '/scrapy-remote-scraper';
        $errors = [];
        $result = null;

        $formData = [
            'spider' => trim((string) $request->request->get('spider', 'olsx_brands_spider')),
            'type' => trim((string) $request->request->get('type', 'car')),
            'brands_only' => $request->request->getBoolean('brands_only'),
            'limit_brands' => trim((string) $request->request->get('limit_brands', '')),
            'brand_slug' => trim((string) $request->request->get('brand_slug', '')),
            'model_prefixes' => trim((string) $request->request->get('model_prefixes', '')),
            'model_suffixes' => trim((string) $request->request->get('model_suffixes', '')),
            'mode' => trim((string) $request->request->get('mode', 'brands:fetch')),
            'brand' => trim((string) $request->request->get('brand', '')),
        ];

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('admin_scraper_control', $csrfToken)) {
                $errors[] = 'Ungueltiges Formular-Token.';
            }

            if (!is_dir($scraperDir)) {
                $errors[] = 'Scraper-Verzeichnis nicht gefunden.';
            }

            $allowedSpiders = ['olsx_brands_spider', 'truck_chiptuningfiles'];
            if (!in_array($formData['spider'], $allowedSpiders, true)) {
                $errors[] = 'Unbekannter Spider.';
            }

            if (!in_array($formData['type'], self::VEHICLE_TYPES, true)) {
                $errors[] = 'Ungueltiger Typ.';
            }

            if ('olsx_brands_spider' === $formData['spider'] && '' !== $formData['limit_brands'] && (!ctype_digit($formData['limit_brands']) || (int) $formData['limit_brands'] < 1)) {
                $errors[] = 'limit_brands muss eine positive Zahl sein.';
            }

            if ('truck_chiptuningfiles' === $formData['spider'] && !in_array($formData['mode'], ['brands:fetch', 'brands:scrape-one', self::TRUCK_ALL_TYPES_MODE], true)) {
                $errors[] = 'Ungueltiger Modus fuer truck_chiptuningfiles.';
            }

            if ('truck_chiptuningfiles' === $formData['spider'] && 'brands:scrape-one' === $formData['mode'] && '' === $formData['brand']) {
                $errors[] = 'Bitte eine Marke angeben (brand).';
            }

            $batchTruckRuns = [];
            if ([] === $errors && 'truck_chiptuningfiles' === $formData['spider'] && self::TRUCK_ALL_TYPES_MODE === $formData['mode']) {
                $batchTruckRuns = $this->buildTruckAllTypeRuns($scraperDir);
                if ([] === $batchTruckRuns) {
                    $errors[] = 'Keine Markenlisten fuer den Alle-Types-Lauf gefunden. Bitte je Typ zuerst die Markenliste holen.';
                }
            }

            if ([] === $errors) {
                $command = ['scrapy', 'crawl', $formData['spider']];

                if ('truck_chiptuningfiles' === $formData['spider']) {
                    $arguments = [
                        'type' => $formData['type'],
                        'mode' => $formData['mode'],
                        'brand' => '' !== $formData['brand'] ? $formData['brand'] : null,
                    ];
                } else {
                    $arguments = [
                        'type' => $formData['type'],
                        'brands_only' => $formData['brands_only'] ? 'true' : null,
                        'limit_brands' => '' !== $formData['limit_brands'] ? $formData['limit_brands'] : null,
                        'brand_slug' => '' !== $formData['brand_slug'] ? $formData['brand_slug'] : null,
                        'model_prefixes' => '' !== $formData['model_prefixes'] ? $formData['model_prefixes'] : null,
                        'model_suffixes' => '' !== $formData['model_suffixes'] ? $formData['model_suffixes'] : null,
                    ];
                }

                $filteredArguments = array_filter(
                    $arguments,
                    static fn (?string $value): bool => null !== $value && '' !== trim($value)
                );

                foreach ($filteredArguments as $key => $value) {
                    $command[] = '-a';
                    $command[] = sprintf('%s=%s', $key, $value);
                }

                $commandString = implode(' ', array_map(
                    static fn (string $part): string => preg_match('/\s/', $part) ? '"' . $part . '"' : $part,
                    $command
                ));

                try {
                    $parametersJson = json_encode($filteredArguments, \JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    $parametersJson = '[]';
                }

                if ('truck_chiptuningfiles' === $formData['spider'] && self::TRUCK_ALL_TYPES_MODE === $formData['mode']) {
                    $queuedRuns = 0;
                    foreach ($batchTruckRuns as $batchRun) {
                        try {
                            $batchParametersJson = json_encode($batchRun['arguments'], \JSON_THROW_ON_ERROR);
                        } catch (JsonException) {
                            $batchParametersJson = '[]';
                        }

                        $run = (new ScraperRun())
                            ->setSpider($formData['spider'])
                            ->setType($batchRun['type'])
                            ->setParameters($batchParametersJson)
                            ->setSuccess(null)
                            ->setExitCode(null)
                            ->setStdout(null)
                            ->setStderr(null)
                            ->setJsonPath(null);

                        $this->entityManager->persist($run);
                        $this->entityManager->flush();

                        $this->messageBus->dispatch(new ScraperRunMessage(
                            $formData['spider'],
                            $batchRun['type'],
                            $batchRun['arguments'],
                            $run->getId()
                        ));
                        ++$queuedRuns;
                    }

                    $result = [
                        'status' => 'queued_batch',
                        'command' => 'truck_chiptuningfiles: alle Types',
                        'transport' => 'async',
                        'messageId' => null,
                        'runId' => null,
                        'arguments' => [
                            'mode' => self::TRUCK_ALL_TYPES_MODE,
                            'runs' => (string) $queuedRuns,
                        ],
                    ];

                    $this->addFlash('success', sprintf('%d Scraperläufe wurden fuer alle Types gestartet.', $queuedRuns));

                    $jsonFiles = $this->findOutputFiles($scraperDir);

                    return $this->render('admin/scraper_control/index.html.twig', [
                        'formData' => $formData,
                        'errors' => $errors,
                        'result' => $result,
                        'scraperDir' => $scraperDir,
                        'latestJson' => $jsonFiles[0] ?? null,
                        'jsonFiles' => $jsonFiles,
                        'scraperRuns' => $this->scraperRunRepository->findLatest(15),
                        'deleteRunCsrfTokenId' => 'admin_scraper_control_delete_run',
                    ]);
                }

                $run = (new ScraperRun())
                    ->setSpider($formData['spider'])
                    ->setType($formData['type'])
                    ->setParameters($parametersJson)
                    ->setSuccess(null)
                    ->setExitCode(null)
                    ->setStdout(null)
                    ->setStderr(null)
                    ->setJsonPath(null);

                $this->entityManager->persist($run);
                $this->entityManager->flush();

                $message = new ScraperRunMessage($formData['spider'], $formData['type'], $filteredArguments, $run->getId());
                $envelope = $this->messageBus->dispatch($message);

                /** @var TransportMessageIdStamp|null $messageIdStamp */
                $messageIdStamp = $envelope->last(TransportMessageIdStamp::class);
                /** @var TransportNameStamp|null $transportStamp */
                $transportStamp = $envelope->last(TransportNameStamp::class);

                $result = [
                    'status' => 'queued',
                    'command' => $commandString,
                    'transport' => $transportStamp?->getTransportName() ?? 'async',
                    'messageId' => $messageIdStamp?->getId(),
                    'runId' => $run->getId(),
                    'arguments' => $filteredArguments,
                ];

                $this->addFlash('success', 'Scraperlauf wurde gestartet und wird im Hintergrund ausgeführt.');
            }
        }

        $jsonFiles = $this->findOutputFiles($scraperDir);

        return $this->render('admin/scraper_control/index.html.twig', [
            'formData' => $formData,
            'errors' => $errors,
            'result' => $result,
            'scraperDir' => $scraperDir,
            'latestJson' => $jsonFiles[0] ?? null,
            'jsonFiles' => $jsonFiles,
            'scraperRuns' => $this->scraperRunRepository->findLatest(15),
            'deleteRunCsrfTokenId' => 'admin_scraper_control_delete_run',
        ]);
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/scraper/run/{id}/delete', name: 'scraper_control_run_delete', options: ['methods' => ['POST']])]
    public function deleteRun(int $id, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_scraper_control_delete_run', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('danger', 'Ungueltiges Formular-Token.');

            return $this->redirectToRoute('admin_scraper_control');
        }

        $run = $this->scraperRunRepository->find($id);
        if (!$run instanceof ScraperRun) {
            $this->addFlash('warning', 'Der Scraperlauf wurde nicht gefunden.');

            return $this->redirectToRoute('admin_scraper_control');
        }

        $label = sprintf('%s / %s', $run->getSpider(), $run->getType());
        $jsonPath = $run->getJsonPath();
        $jsonDeleted = false;

        if (is_string($jsonPath) && '' !== trim($jsonPath)) {
            $realJsonPath = realpath($jsonPath);
            $baseDir = realpath($this->projectDir . '/scrapy-remote-scraper');

            if (false !== $realJsonPath && false !== $baseDir && str_starts_with($realJsonPath, $baseDir) && is_file($realJsonPath)) {
                $jsonDeleted = @unlink($realJsonPath);
            }
        }

        $this->entityManager->remove($run);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf(
            'Scraperlauf "%s" wurde aus der Historie gelöscht%s.',
            $label,
            $jsonDeleted ? ' und die JSON-Datei entfernt' : ''
        ));

        return $this->redirectToRoute('admin_scraper_control');
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/scraper/json/{id}', name: 'scraper_control_json', options: ['methods' => ['GET']])]
    public function jsonPreview(ScraperRun $run): Response
    {
        $path = $run->getJsonPath();
        if (null === $path || !is_file($path)) {
            return new Response('Nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $baseDir = realpath($this->projectDir . '/scrapy-remote-scraper');
        $realPath = realpath($path);
        if (false === $realPath || false === $baseDir || str_starts_with($realPath, $baseDir) === false) {
            return new Response('Pfad nicht erlaubt.', Response::HTTP_FORBIDDEN);
        }

        $content = file_get_contents($path);
        return new Response($content ?: '', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @return array<int,array{path:string,name:string,mtime:int,size:int,publicPath:string}>
     */
    private function findOutputFiles(string $scraperDir, int $limit = 20): array
    {
        if (!is_dir($scraperDir)) {
            return [];
        }

        $files = glob($scraperDir . '/olsx_brands*.json') ?: [];
        foreach (glob($scraperDir . '/data/output/*/*/*.json') ?: [] as $outputFile) {
            $files[] = $outputFile;
        }
        foreach (glob($scraperDir . '/data/output/*/*.json') ?: [] as $outputFile) {
            $files[] = $outputFile;
        }
        if ([] === $files) {
            return [];
        }

        usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
        if ($limit > 0) {
            $files = array_slice($files, 0, $limit);
        }

        return array_map([$this, 'createOutputFileMetadata'], $files);
    }

    private function findLatestOutputFile(string $scraperDir): ?array
    {
        $files = $this->findOutputFiles($scraperDir, 1);
        return $files[0] ?? null;
    }

    private function createOutputFileMetadata(string $path): array
    {
        $relative = $this->createRelativePublicPath($path);

        return [
            'path' => $path,
            'name' => basename($path),
            'mtime' => (int) filemtime($path),
            'size' => is_file($path) ? (int) filesize($path) : 0,
            'publicPath' => $relative,
        ];
    }

    private function createRelativePublicPath(string $path): string
    {
        $projectDir = realpath($this->projectDir) ?: $this->projectDir;

        if (str_starts_with($path, $projectDir)) {
            $relative = substr($path, strlen($projectDir));
        } else {
            $relative = $path;
        }

        return '/' . ltrim($relative, '/');
    }

    /**
     * @return list<array{type:string,arguments:array<string,string>}>
     */
    private function buildTruckAllTypeRuns(string $scraperDir): array
    {
        $runs = [];
        foreach (self::VEHICLE_TYPES as $type) {
            foreach ($this->loadTruckBrandsForType($scraperDir, $type) as $brand) {
                $runs[] = [
                    'type' => $type,
                    'arguments' => [
                        'type' => $type,
                        'mode' => 'brands:scrape-one',
                        'brand' => $brand,
                    ],
                ];
            }
        }

        return $runs;
    }

    /**
     * @return list<string>
     */
    private function loadTruckBrandsForType(string $scraperDir, string $type): array
    {
        $paths = [
            sprintf('%s/data/brands/truck_chiptuningfiles/%s_brands.json', $scraperDir, $type),
            sprintf('%s/data/brands/%s_brands.json', $scraperDir, $type),
        ];

        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }

            try {
                $payload = json_decode((string) file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                continue;
            }

            if (!is_array($payload)) {
                continue;
            }

            $brands = [];
            foreach ($payload as $entry) {
                $brand = null;
                if (is_string($entry)) {
                    $brand = trim($entry);
                } elseif (is_array($entry)) {
                    $brand = trim((string) ($entry['name'] ?? $entry['slug'] ?? ''));
                }

                if (null !== $brand && '' !== $brand) {
                    $brands[] = $brand;
                }
            }

            $brands = array_values(array_unique($brands));
            sort($brands);

            return $brands;
        }

        return [];
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/scraper/status', name: 'scraper_control_status', options: ['methods' => ['GET']])]
    public function status(): JsonResponse
    {
        $connection = $this->entityManager->getConnection();
        $queueCount = 0;
        try {
            $queueCount = (int) $connection->fetchOne('SELECT COUNT(*) FROM messenger_messages WHERE queue_name = ?', ['async']);
        } catch (\Throwable) {
        }

        $latestRuns = $this->scraperRunRepository->findLatest(1);
        $latestRun = $latestRuns[0] ?? null;
        $runData = null;

        if ($latestRun instanceof ScraperRun) {
            $runData = [
                'id' => $latestRun->getId(),
                'spider' => $latestRun->getSpider(),
                'type' => $latestRun->getType(),
                'success' => $latestRun->isSuccess(),
                'exitCode' => $latestRun->getExitCode(),
                'createdAt' => $latestRun->getCreatedAt()->format('c'),
            ];
        }

        return new JsonResponse([
            'queueCount' => $queueCount,
            'latestRun' => $runData,
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ]);
    }

}
