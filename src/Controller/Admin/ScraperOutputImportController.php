<?php

namespace App\Controller\Admin;

use App\Entity\ScraperImportLog;
use App\Entity\VehicleType;
use App\Repository\ScraperImportLogRepository;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleDataPoolRepository;
use App\Service\OlsxVehicleImporter;
use App\Service\ScraperImportAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ScraperOutputImportController extends AbstractController
{
    private const OUTPUT_DIR = '/scrapy-remote-scraper/data/output';

    public function __construct(
        private readonly ScraperImportAnalyzer $analyzer,
        private readonly VehicleDataPoolRepository $poolRepository,
        private readonly VehicleBrandRepository $brandRepository,
        private readonly ScraperImportLogRepository $logRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly OlsxVehicleImporter $importer,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/scraper/import-output', name: 'scraper_output_import', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $outputDir = $this->projectDir . self::OUTPUT_DIR;
        $jsonFiles = $this->analyzer->listJsonFilesRecursivelyFromDirectory($outputDir);
        $filesBySelectionKey = [];
        foreach ($jsonFiles as $index => $file) {
            $file['selectionKey'] = hash('sha256', (string) $file['path']);
            $file['displayName'] = $this->createDisplayName($file, $index + 1);
            $jsonFiles[$index] = $file;
            $filesBySelectionKey[$file['selectionKey']] = $file;
        }

        $selectedPoolId = $this->normalizePoolId($request);
        $selectedFiles = $this->normalizeSelectedFiles($request, $filesBySelectionKey);
        $selectedJson = $selectedFiles[0]['path'] ?? null;

        $errors = [];
        $preview = null;
        $importResult = null;
        $logs = $this->logRepository->findLatestBySource(ScraperImportLog::SOURCE_OUTPUT, 10);
        $pools = $this->poolRepository->findBy(['active' => true]);
        $groupedJsonFiles = $this->groupFilesByFolder($jsonFiles);
        $importedFiles = $this->indexImportedFiles(
            $this->logRepository->findLatestForPathsBySource(array_column($jsonFiles, 'path'), ScraperImportLog::SOURCE_OUTPUT)
        );

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('admin_scraper_output_import', $csrfToken)) {
                $errors[] = 'Ungültiges Formular-Token.';
            }

            $poolId = $this->normalizePoolId($request);
            $confirm = $request->request->getBoolean('confirm');
            $pool = $poolId ? $this->poolRepository->find($poolId) : null;

            if ([] === $selectedFiles) {
                $errors[] = 'Bitte wählen Sie mindestens eine JSON-Datei aus.';
            }

            if (null === $pool) {
                $errors[] = 'Bitte wählen Sie einen Datenpool aus.';
            }

            $analyses = [];
            if ([] === $errors) {
                foreach ($selectedFiles as $file) {
                    try {
                        $analyses[] = [
                            'file' => $file,
                            'analysis' => $this->analyzer->analyze($file['path']),
                        ];
                    } catch (\JsonException) {
                        $errors[] = sprintf('Die Datei "%s" ist kein gültiges JSON.', $file['name']);
                    }
                }
            }

            if ([] === $errors && [] !== $analyses) {
                $preview = $this->buildPreview($analyses, $pool);
            }

            if ($confirm && null !== $preview && [] === $errors) {
                $stats = [
                    'files' => 0,
                    'brands' => 0,
                    'models' => 0,
                    'engines' => 0,
                    'created' => 0,
                    'updated' => 0,
                    'unchanged' => 0,
                ];

                foreach ($selectedFiles as $file) {
                    try {
                        $payload = json_decode((string) file_get_contents($file['path']), true, 512, \JSON_THROW_ON_ERROR);
                    } catch (\Throwable) {
                        $payload = null;
                    }

                    if (!is_array($payload) || [] === $payload) {
                        $errors[] = sprintf('Die Datei "%s" konnte nicht gelesen werden.', $file['name']);
                        continue;
                    }

                    if ($this->looksLikeSingleBrandPayload($payload)) {
                        $payload = [$payload];
                    }

                    $vehicleType = $this->importer->resolveVehicleTypeFromFile($file['path']);
                    if (!$vehicleType instanceof VehicleType) {
                        $errors[] = sprintf('Fahrzeugtyp konnte für "%s" nicht bestimmt werden.', $file['name']);
                        continue;
                    }

                    $analysis = $this->analyzer->analyze($file['path']);
                    $fileStats = $this->importer->import(
                        $payload,
                        $pool,
                        $vehicleType,
                        $this->importer->detectScraperSourceFromFile($file['path'])
                    );
                    $this->entityManager->flush();

                    try {
                        $summaryJson = json_encode($analysis['brands'], \JSON_THROW_ON_ERROR);
                    } catch (\JsonException) {
                        $summaryJson = null;
                    }

                    $importLog = (new ScraperImportLog())
                        ->setVehicleDataPool($pool)
                        ->setFileName($file['name'])
                        ->setFilePath($file['path'])
                        ->setSource(ScraperImportLog::SOURCE_OUTPUT)
                        ->setType($analysis['type'])
                        ->setBrandCount((int) $analysis['brandCount'])
                        ->setBrandSummary($summaryJson)
                        ->setStatus('imported')
                        ->setMessage(sprintf(
                            'Import protokolliert (%s Models, %s Engines, neu: %s, aktualisiert: %s, unverändert: %s).',
                            $fileStats['Models'] ?? 0,
                            $fileStats['Engines'] ?? 0,
                            $fileStats['Created'] ?? 0,
                            $fileStats['Updated'] ?? 0,
                            $fileStats['Unchanged'] ?? 0
                        ));

                    $this->entityManager->persist($importLog);
                    $this->entityManager->flush();

                    ++$stats['files'];
                    $stats['brands'] += (int) ($fileStats['Brands'] ?? 0);
                    $stats['models'] += (int) ($fileStats['Models'] ?? 0);
                    $stats['engines'] += (int) ($fileStats['Engines'] ?? 0);
                    $stats['created'] += (int) ($fileStats['Created'] ?? 0);
                    $stats['updated'] += (int) ($fileStats['Updated'] ?? 0);
                    $stats['unchanged'] += (int) ($fileStats['Unchanged'] ?? 0);
                }

                if ([] === $errors) {
                    $importResult = $stats;
                    $logs = $this->logRepository->findLatestBySource(ScraperImportLog::SOURCE_OUTPUT, 10);
                    $importedFiles = $this->indexImportedFiles(
                        $this->logRepository->findLatestForPathsBySource(array_column($jsonFiles, 'path'), ScraperImportLog::SOURCE_OUTPUT)
                    );
                    $this->addFlash('success', sprintf('Import komplett ausgeführt. %d Datei(en) importiert.', $stats['files']));
                }
            }
        }

        return $this->render('admin/scraper_import/output.html.twig', [
            'pools' => $pools,
            'logs' => $logs,
            'deleteLogCsrfTokenId' => 'admin_scraper_output_import_delete_log',
            'deleteLogRoute' => 'admin_scraper_output_import_log_delete',
            'errors' => $errors,
            'preview' => $preview,
            'importResult' => $importResult,
            'selectedJson' => $selectedJson,
            'selectedPoolId' => $selectedPoolId,
            'pageTitle' => 'Scraper Import Output',
            'formCsrfTokenId' => 'admin_scraper_output_import',
            'groupedJsonFiles' => $groupedJsonFiles,
            'selectedFileKeys' => array_column($selectedFiles, 'selectionKey'),
            'importedFiles' => $importedFiles,
        ]);
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/scraper/import-output/log/{id}/delete', name: 'scraper_output_import_log_delete', options: ['methods' => ['POST']])]
    public function deleteLog(int $id, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_scraper_output_import_delete_log', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('danger', 'Ungültiges Formular-Token.');

            return $this->redirectToRoute('admin_scraper_output_import');
        }

        $log = $this->logRepository->find($id);
        if (!$log instanceof ScraperImportLog || ScraperImportLog::SOURCE_OUTPUT !== $log->getSource()) {
            $this->addFlash('warning', 'Der Historie-Eintrag wurde nicht gefunden.');

            return $this->redirectToRoute('admin_scraper_output_import');
        }

        $fileName = $log->getFileName();
        $this->entityManager->remove($log);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Historie-Eintrag "%s" wurde gelöscht.', $fileName));

        return $this->redirectToRoute('admin_scraper_output_import');
    }

    /**
     * @param array<string,array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int,selectionKey:string,displayName:string}> $filesBySelectionKey
     * @return list<array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int,selectionKey:string,displayName:string}>
     */
    private function normalizeSelectedFiles(Request $request, array $filesBySelectionKey): array
    {
        $selected = $request->request->all('json_files');
        if (!is_array($selected)) {
            return [];
        }

        $normalized = [];
        foreach ($selected as $selectionKey) {
            if (!is_string($selectionKey) || !isset($filesBySelectionKey[$selectionKey])) {
                continue;
            }

            $normalized[] = $filesBySelectionKey[$selectionKey];
        }

        return $normalized;
    }

    /**
     * @param array<int,array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int,selectionKey:string,displayName:string}> $files
     * @return array<string,list<array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int,selectionKey:string,displayName:string}>>
     */
    private function groupFilesByFolder(array $files): array
    {
        $groups = [];
        foreach ($files as $file) {
            try {
                $type = $this->analyzer->analyze($file['path'])['type'] ?? 'unbekannt';
            } catch (\JsonException) {
                $type = 'unbekannt';
            }
            $groups[sprintf('Typ %s', $type ?: 'unbekannt')][] = $file;
        }

        ksort($groups);

        return $groups;
    }

    /**
     * @param list<array{file:array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int},analysis:array{type:?string,brandCount:int,brands:array<int,array{slug:mixed,name:mixed,model_count:mixed}>}}> $analyses
     * @return array{files:list<array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int}>,analysis:array{type:string,brandCount:int},pool:object|null,brands:list<array{slug:mixed,name:mixed,model_count:mixed,exists:bool,inPool:bool}>}
     */
    private function buildPreview(array $analyses, ?object $pool): array
    {
        $brandEntries = [];
        $types = [];
        $files = [];

        foreach ($analyses as $entry) {
            $files[] = $entry['file'];
            $type = $entry['analysis']['type'] ?? null;
            if (is_string($type) && '' !== $type) {
                $types[$type] = true;
            }

            foreach ($entry['analysis']['brands'] as $brand) {
                $existing = $this->brandRepository->findOneBySlugOrName($brand['slug'] ?? null, $brand['name'] ?? null);
                $inPool = null !== $existing && null !== $pool && $existing->getVehicleDataPool()?->getId() === $pool->getId();
                $brandEntries[] = [
                    'slug' => $brand['slug'],
                    'name' => $brand['name'],
                    'model_count' => $brand['model_count'],
                    'exists' => null !== $existing,
                    'inPool' => $inPool,
                ];
            }
        }

        usort($brandEntries, static function (array $left, array $right): int {
            return strcmp((string) ($left['name'] ?? $left['slug'] ?? ''), (string) ($right['name'] ?? $right['slug'] ?? ''));
        });

        return [
            'files' => $files,
            'analysis' => [
                'type' => implode(', ', array_keys($types)),
                'brandCount' => count($brandEntries),
            ],
            'pool' => $pool,
            'brands' => $brandEntries,
        ];
    }

    /**
     * @param array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int} $file
     */
    private function createDisplayName(array $file, int $index): string
    {
        $name = trim((string) ($file['name'] ?? ''));
        if ('' !== $name && !str_contains(mb_strtolower($name), 'olsx') && !str_contains(mb_strtolower($name), 'truck_chiptuningfiles')) {
            return $name;
        }

        return sprintf('Datensatz %03d.json', $index);
    }

    /**
     * @param array<mixed> $payload
     */
    private function looksLikeSingleBrandPayload(array $payload): bool
    {
        return array_key_exists('brand_slug', $payload)
            || array_key_exists('brand_name', $payload)
            || array_key_exists('models', $payload);
    }

    private function normalizePoolId(Request $request): ?int
    {
        $value = $request->request->get('pool_id');
        if (null === $value || '' === $value) {
            return null;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        if (null === $filtered) {
            return null;
        }

        return (int) $filtered;
    }

    /**
     * @param list<ScraperImportLog> $logs
     * @return array<string,array{createdAt:string,poolName:string|null,status:string}>
     */
    private function indexImportedFiles(array $logs): array
    {
        $indexed = [];
        foreach ($logs as $log) {
            $indexed[$log->getFilePath()] = [
                'createdAt' => $log->getCreatedAt()->format('Y-m-d H:i:s'),
                'poolName' => $log->getVehicleDataPool()?->getName(),
                'status' => $log->getStatus(),
            ];
        }

        return $indexed;
    }
}
