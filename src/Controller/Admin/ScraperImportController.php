<?php

namespace App\Controller\Admin;

use App\Entity\ScraperImportLog;
use App\Repository\ScraperImportLogRepository;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleDataPoolRepository;
use App\Service\OlsxVehicleImporter;
use App\Service\ScraperImportAnalyzer;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JsonException;

final class ScraperImportController extends AbstractController
{
    public function __construct(
        private readonly ScraperImportAnalyzer $analyzer,
        private readonly VehicleDataPoolRepository $poolRepository,
        private readonly VehicleBrandRepository $brandRepository,
        private readonly ScraperImportLogRepository $logRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly OlsxVehicleImporter $importer,
    ) {
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/scraper/import', name: 'scraper_import', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $jsonFiles = $this->analyzer->listJsonFiles();
        $logs = $this->logRepository->findLatestBySource(ScraperImportLog::SOURCE_ROOT, 10);
        $pools = $this->poolRepository->findBy(['active' => true]);

        $selectedJson = $request->request->get('json_file', $jsonFiles[0]['path'] ?? null);
        $selectedPoolId = $this->normalizePoolId($request);

        $errors = [];
        $preview = null;
        $importResult = null;

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('admin_scraper_import', $csrfToken)) {
                $errors[] = 'Ungültiges Formular-Token.';
            }

            $fileKey = $request->request->get('json_file');
            $poolId = $this->normalizePoolId($request);
            $confirm = $request->request->getBoolean('confirm');
            $pool = $poolId ? $this->poolRepository->find($poolId) : null;

            $metadata = null;
            foreach ($jsonFiles as $file) {
                if ($file['path'] === $fileKey || $file['name'] === $fileKey || $file['publicPath'] === $fileKey) {
                    $metadata = $file;
                    break;
                }
            }

            if (null === $metadata) {
                $errors[] = 'Bitte eine gültige JSON-Datei auswählen.';
            }

            if (null === $pool) {
                $errors[] = 'Bitte wählen Sie einen Datenpool aus.';
            }

            $analysis = null;
            if ([] === $errors && $metadata) {
                try {
                    $analysis = $this->analyzer->analyze($metadata['path']);
                } catch (JsonException $exception) {
                    $errors[] = 'Die ausgewählte Datei ist kein gültiges JSON.';
                }
            }

            if (null !== $analysis) {
                $brandEntries = [];
                $brandEntities = [];
                foreach ($analysis['brands'] as $brand) {
                    $existing = $this->brandRepository->findOneBySlugOrName($brand['slug'] ?? null, $brand['name'] ?? null);
                    $inPool = null !== $existing && null !== $pool && $existing->getVehicleDataPool()?->getId() === $pool->getId();
                    $brandEntries[] = [
                        'slug' => $brand['slug'],
                        'name' => $brand['name'],
                        'model_count' => $brand['model_count'],
                        'exists' => null !== $existing,
                        'inPool' => $inPool,
                    ];
                    if (null !== $existing) {
                        $brandEntities[$brand['slug'] ?? $brand['name']] = $existing;
                    }
                }

                $preview = [
                    'file' => $metadata,
                    'analysis' => $analysis,
                    'pool' => $pool,
                    'brands' => $brandEntries,
                ];
            }

            if ($confirm && null !== $preview && [] === $errors) {
                if (null === $analysis || null === $metadata) {
                    $errors[] = 'Vorschau konnte nicht geladen werden.';
                } else {
                    try {
                        $payload = json_decode(file_get_contents($metadata['path']), true);
                    } catch (\Throwable) {
                        $payload = null;
                    }

                    if (!is_array($payload) || [] === $payload) {
                        $errors[] = 'Die JSON-Datei konnte nicht gelesen werden.';
                    } else {
                        $vehicleType = $this->importer->resolveVehicleTypeFromFile($metadata['path']);
                        if (!$vehicleType instanceof VehicleType) {
                            $errors[] = 'Fahrzeugtyp konnte nicht bestimmt werden.';
                        } else {
                            $stats = $this->importer->import($payload, $pool, $vehicleType);
                            $this->entityManager->flush();

                            try {
                                $summaryJson = json_encode($preview['brands'], JSON_THROW_ON_ERROR);
                            } catch (JsonException) {
                                $summaryJson = null;
                            }

                            $importLog = (new ScraperImportLog())
                                ->setVehicleDataPool($pool)
                                ->setFileName($metadata['name'])
                                ->setFilePath($metadata['path'])
                                ->setSource(ScraperImportLog::SOURCE_ROOT)
                                ->setType($analysis['type'])
                                ->setBrandCount((int) $analysis['brandCount'])
                                ->setBrandSummary($summaryJson)
                                ->setStatus('imported')
                                ->setMessage(sprintf(
                                    'Import protokolliert (%s Models, %s Engines, neu: %s, aktualisiert: %s, unverändert: %s).',
                                    $stats['Models'] ?? 0,
                                    $stats['Engines'] ?? 0,
                                    $stats['Created'] ?? 0,
                                    $stats['Updated'] ?? 0,
                                    $stats['Unchanged'] ?? 0
                                ));

                            $this->entityManager->persist($importLog);
                            $this->entityManager->flush();

                            $importResult = [
                                'log' => $importLog,
                                'stats' => $stats,
                            ];
                            $this->addFlash('success', 'Import komplett ausgeführt.');
                        }
                    }
                }
            }
        }

        return $this->render('admin/scraper_import/index.html.twig', [
            'pools' => $pools,
            'jsonFiles' => $jsonFiles,
            'logs' => $logs,
            'deleteLogCsrfTokenId' => 'admin_scraper_import_delete_log',
            'deleteLogRoute' => 'admin_scraper_import_log_delete',
            'errors' => $errors,
            'preview' => $preview,
            'importResult' => $importResult,
            'selectedJson' => $selectedJson,
            'selectedPoolId' => $selectedPoolId,
            'pageTitle' => 'Scraper Import',
            'formCsrfTokenId' => 'admin_scraper_import',
        ]);
    }

    #[AdminRoute(path: '/fahrzeugdatenbank/scraper/import/log/{id}/delete', name: 'scraper_import_log_delete', options: ['methods' => ['POST']])]
    public function deleteLog(int $id, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_scraper_import_delete_log', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('danger', 'Ungültiges Formular-Token.');

            return $this->redirectToRoute('admin_scraper_import');
        }

        $log = $this->logRepository->find($id);
        if (!$log instanceof ScraperImportLog || ScraperImportLog::SOURCE_ROOT !== $log->getSource()) {
            $this->addFlash('warning', 'Der Historie-Eintrag wurde nicht gefunden.');

            return $this->redirectToRoute('admin_scraper_import');
        }

        $fileName = $log->getFileName();
        $this->entityManager->remove($log);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Historie-Eintrag "%s" wurde gelöscht.', $fileName));

        return $this->redirectToRoute('admin_scraper_import');
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
}
