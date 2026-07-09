<?php

namespace App\Controller\Admin;

use App\Entity\VehicleEngine;
use App\Entity\VehicleStage;
use App\Service\Ai\VehicleDatabaseLookup;
use App\Service\Ai\VehicleOcrRunStorage;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

final class AiVehicleController extends AbstractController
{
    private const DEFAULT_MODEL = 'gpt-4o-mini';
    private const UPLOAD_DIR = 'public/uploads/ai/vehicle';
    private const PREVIEW_DIR = 'public/uploads/ai/vehicle/preview';

    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly VehicleDatabaseLookup $lookup,
        private readonly VehicleOcrRunStorage $storage,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[AdminRoute(path: '/ai/vehicle-database', name: 'ai_vehicle', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST') && '1' === (string) $request->request->get('delete_run', '')) {
            return $this->deleteRun($request);
        }

        $error = null;
        $extracted = null;
        $rawText = null;
        $rawResponse = null;
        $results = [];
        $criteriaUsed = [];
        $documentPath = null;
        $previewPath = null;
        $previewPaths = [];

        $manual = [
            'brand' => $this->sanitizeText($request->request->get('brand')),
            'series' => $this->sanitizeText($request->request->get('series')),
            'model' => $this->sanitizeText($request->request->get('model')),
            'engineCode' => $this->sanitizeText($request->request->get('engineCode')),
            'engineName' => $this->sanitizeText($request->request->get('engineName')),
            'fuel' => $this->sanitizeText($request->request->get('fuel')),
            'displacement' => $this->sanitizeText($request->request->get('displacement')),
            'powerHp' => $this->toInt($request->request->get('powerHp')),
            'powerKw' => $this->toInt($request->request->get('powerKw')),
        ];

        $limit = (int) $request->request->get('limit', 15);
        if ($limit <= 0) {
            $limit = 15;
        }
        if ($limit > 50) {
            $limit = 50;
        }

        $model = (string) $request->request->get('modelName', self::DEFAULT_MODEL);

        $runs = $this->storage->listRuns();
        $activeRunId = (int) $request->query->get('run', 0);
        $activeRun = $activeRunId > 0 ? $this->storage->getRun($activeRunId) : null;

        if (!$request->isMethod('POST') && is_array($activeRun)) {
            $extracted = $activeRun['extracted'] ?? null;
            $rawText = $activeRun['rawText'] ?? null;
            $rawResponse = $activeRun['rawResponse'] ?? null;
            $criteriaUsed = is_array($activeRun['criteria'] ?? null) ? $activeRun['criteria'] : [];
            $results = is_array($activeRun['results'] ?? null) ? $activeRun['results'] : [];
            $error = $activeRun['error'] ?? null;
            $model = (string) ($activeRun['model'] ?? $model);
            $documentPath = $activeRun['documentPath'] ?? null;
            $previewPath = $activeRun['previewPath'] ?? null;
            $previewPaths = is_array($activeRun['previewPaths'] ?? null) ? $activeRun['previewPaths'] : [];
            $manual = $this->applyCriteriaToManual($manual, $criteriaUsed);
        }

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('admin_ai_vehicle', $csrfToken)) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_ai_vehicle');
            }

            $file = $request->files->get('document');
            $sourceType = 'manual';
            $mimeType = null;
            $originalName = null;
            $previewAbsolute = null;
            $previewPaths = [];
            $previewAbsoluteList = [];
            $fatalError = null;
            $ocrError = null;
            $hasManual = !$this->isManualEmpty($manual);

            if ($file instanceof UploadedFile && $file->isValid()) {
                $mimeType = (string) ($file->getMimeType() ?? '');
                $sourceType = $this->detectSourceType($file, $mimeType);
                if (null === $sourceType) {
                    $fatalError = 'Bitte ein Bild oder PDF hochladen.';
                } else {
                    try {
                        $upload = $this->persistUpload($file);
                        $documentPath = $upload['relativePath'];
                        $originalName = $upload['originalName'];
                        $mimeType = $upload['mimeType'];
                        if ('pdf' === $sourceType) {
                            [$previewPaths, $previewAbsoluteList] = $this->renderPdfPreviews($upload['absolutePath'], $upload['baseName']);
                            $previewPath = $previewPaths[0] ?? null;
                            $previewAbsolute = $previewAbsoluteList[0] ?? null;
                        } else {
                            $previewPath = $documentPath;
                            $previewAbsolute = $upload['absolutePath'];
                            $previewPaths = $previewPath ? [$previewPath] : [];
                            $previewAbsoluteList = $previewAbsolute ? [$previewAbsolute] : [];
                        }
                    } catch (\Throwable $exception) {
                        $ocrError = $exception->getMessage();
                    }
                }
            } elseif ($file instanceof UploadedFile) {
                $fatalError = 'Upload fehlgeschlagen.';
            }

            if (null === $fatalError && null === $file && !$hasManual) {
                $fatalError = 'Bitte Fahrzeugschein hochladen oder Kriterien angeben.';
            }

            if (null === $fatalError && ($file instanceof UploadedFile || $hasManual)) {
                $runId = $this->storage->createRun(
                    $sourceType,
                    $originalName,
                    $mimeType,
                    $documentPath,
                    $previewPath,
                    $previewPaths,
                    $model
                );

                try {
                    if (null === $ocrError && $previewAbsolute !== null) {
                        [$extracted, $rawText, $rawResponse] = $this->extractFromImagePath($previewAbsolute, $model);
                    }

                    $criteriaUsed = $this->mergeCriteria($manual, $extracted);
                    if ([] === $criteriaUsed) {
                        throw new \RuntimeException($ocrError ?? 'Keine Suchkriterien gefunden.');
                    }

                    $matches = $this->lookup->search($criteriaUsed, $limit);
                    $results = $this->mapMatches($matches);
                } catch (\Throwable $exception) {
                    $fatalError = $exception->getMessage();
                }

                $error = $fatalError ?? $ocrError;
                $this->storage->updateRun($runId, $extracted, $rawText, $rawResponse, $criteriaUsed, $results, $error);

                return $this->redirectToRoute('admin_ai_vehicle', ['run' => $runId]);
            }

            $error = $fatalError ?? $ocrError;
        }

        return $this->render('admin/ai_vehicle/index.html.twig', [
            'error' => $error,
            'modelName' => $model,
            'manual' => $manual,
            'limit' => $limit,
            'extracted' => $extracted,
            'rawText' => $rawText,
            'rawResponse' => $rawResponse,
            'criteriaUsed' => $criteriaUsed,
            'results' => $results,
            'runs' => $runs,
            'activeRunId' => $activeRunId,
            'documentPath' => $documentPath,
            'previewPath' => $previewPath,
            'previewPaths' => $previewPaths,
        ]);
    }

    private function deleteRun(Request $request): Response
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_ai_vehicle', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_ai_vehicle');
        }

        $id = (int) $request->request->get('runId', 0);
        if ($id <= 0) {
            return $this->redirectToRoute('admin_ai_vehicle');
        }

        $run = $this->storage->getRun($id);
        if (!is_array($run)) {
            $this->addFlash('danger', 'Scan nicht gefunden.');
            return $this->redirectToRoute('admin_ai_vehicle');
        }

        $paths = [];
        foreach (['documentPath', 'previewPath'] as $key) {
            $value = $run[$key] ?? null;
            if (is_string($value) && '' !== trim($value)) {
                $paths[] = $value;
            }
        }
        $previewPaths = $run['previewPaths'] ?? null;
        if (is_array($previewPaths)) {
            foreach ($previewPaths as $path) {
                if (is_string($path) && '' !== trim($path)) {
                    $paths[] = $path;
                }
            }
        }

        $paths = array_values(array_unique($paths));
        foreach ($paths as $path) {
            $absolute = $this->projectDir.'/public/'.ltrim($path, '/');
            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }

        $this->storage->deleteRun($id);
        $this->addFlash('success', 'Scan geloescht.');

        return $this->redirectToRoute('admin_ai_vehicle');
    }

    /**
     * @return array{0: array<string, mixed>|null, 1: string|null, 2: string|null}
     */
    private function extractFromImagePath(string $path, string $model): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException('Bilddatei nicht gefunden.');
        }

        $instruction = <<<'PROMPT'
Du bist ein Assistent, der Daten aus einem deutschen Fahrzeugschein (Zulassungsbescheinigung Teil I) extrahiert.
Lies das Dokument und gib ausschliesslich JSON zurueck (kein Markdown).

Erwartetes JSON-Schema:
{
  "brand": string|null,
  "series": string|null,
  "model": string|null,
  "engineCode": string|null,
  "engineName": string|null,
  "powerKw": number|null,
  "powerHp": number|null,
  "fuel": string|null,
  "displacement": string|null,
  "vin": string|null,
  "firstRegistration": string|null,
  "rawText": string|null,
  "notes": string|null
}

Hinweise:
- powerKw entspricht Feld P.2 (kW), displacement Feld P.1 (cm3), fuel Feld P.3.
- engineCode kann aus D.2 oder P.5 stammen, falls vorhanden.
- rawText soll eine OCR-aehnliche Volltranskription sein (so gut wie moeglich).
- Unbekanntes Feld: null.
PROMPT;

        $messages = new MessageBag(
            Message::forSystem($instruction),
            Message::ofUser('Extrahiere die Daten aus diesem Fahrzeugschein.', Image::fromFile($path))
        );

        $result = $this->platform->invoke($model, $messages)->getResult();
        if (!$result instanceof TextResult) {
            throw new \RuntimeException('Unerwartetes Ergebnis vom AI Modell.');
        }

        $rawResponse = trim($result->getContent());
        $decoded = $this->decodeJsonPayload($rawResponse);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Antwort konnte nicht als JSON gelesen werden.');
        }

        $rawText = null;
        if (isset($decoded['rawText']) && is_string($decoded['rawText'])) {
            $rawText = trim($decoded['rawText']);
        }

        $decoded = $this->applyExtractionHeuristics($decoded, $rawText);

        return [$decoded, $rawText, $rawResponse];
    }

    /**
     * @param array<string, mixed> $decoded
     *
     * @return array<string, mixed>
     */
    private function applyExtractionHeuristics(array $decoded, ?string $rawText): array
    {
        if (null === $rawText || '' === trim($rawText)) {
            return $decoded;
        }

        $kw = $this->extractPowerKwFromText($rawText);
        if (null !== $kw) {
            $currentKw = $this->toInt($decoded['powerKw'] ?? null);
            if (null === $currentKw || abs($currentKw - $kw) >= 5) {
                $decoded['powerKw'] = $kw;
                $decoded['powerHp'] = (int) round($kw * 1.35962);
            }
        }

        $vin = $this->extractVinFromText($rawText);
        if (null !== $vin) {
            $decoded['vin'] = $vin;
        }

        return $decoded;
    }

    private function extractPowerKwFromText(string $text): ?int
    {
        $normalized = strtoupper($text);
        $normalized = preg_replace('/[ \\t]+/', ' ', $normalized) ?? $normalized;

        $lines = preg_split('/\\R+/', $normalized) ?: [];
        foreach ($lines as $line) {
            if (!preg_match('/\\bP\\.?\\s*2\\b/', $line)) {
                continue;
            }

            if (preg_match('/\\bP\\.?\\s*2\\b[^0-9]{0,12}(\\d{2,3})\\s*[\\/-]\\s*(\\d{2,3})/', $line, $matches)) {
                return (int) $matches[1];
            }

            if (preg_match('/\\bP\\.?\\s*2\\b[^0-9]{0,12}(\\d{2,3})/', $line, $matches)) {
                return (int) $matches[1];
            }

            if (preg_match_all('/\\b(\\d{2,3})\\s*[\\/-]\\s*(\\d{2,3})\\b/', $line, $pairs, \PREG_SET_ORDER)) {
                $best = $this->selectKwFromPairs($pairs);
                if (null !== $best) {
                    return $best;
                }
            }
        }

        if (preg_match('/P\\.?\\s*2[^0-9]{0,12}(\\d{2,3})\\s*[\\/-]\\s*(\\d{2,3})/s', $normalized, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/P\\.?\\s*2[^0-9]{0,12}(\\d{2,3})/s', $normalized, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/\\b(\\d{2,3})\\s*KW\\b/', $normalized, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match_all('/\\b(\\d{2,3})\\s*[\\/-]\\s*(\\d{2,3})\\b/', $normalized, $pairs, \PREG_SET_ORDER)) {
            return $this->selectKwFromPairs($pairs);
        }

        return null;
    }

    /**
     * @param list<array<int, string>> $pairs
     */
    private function selectKwFromPairs(array $pairs): ?int
    {
        $best = null;
        foreach ($pairs as $pair) {
            $kw = (int) ($pair[1] ?? 0);
            $ps = (int) ($pair[2] ?? 0);
            if ($kw < 60 || $ps < 80) {
                continue;
            }

            $ratio = $ps / max(1, $kw);
            if ($ratio < 1.2 || $ratio > 1.6) {
                continue;
            }

            if (null === $best || $kw > $best) {
                $best = $kw;
            }
        }

        return $best;
    }

    private function extractVinFromText(string $text): ?string
    {
        $upper = strtoupper($text);
        $upper = str_replace(['O', 'I', 'Q'], ['0', '1', '0'], $upper);
        $upper = preg_replace('/[^A-Z0-9]/', ' ', $upper) ?? $upper;
        $upper = preg_replace('/\\s+/', ' ', $upper) ?? $upper;
        $upper = trim($upper);

        $tokens = $upper === '' ? [] : preg_split('/\\s+/', $upper);
        if (!is_array($tokens)) {
            $tokens = [];
        }

        foreach ($tokens as $token) {
            $length = strlen($token);
            if ($length < 17) {
                continue;
            }

            for ($i = 0; $i <= $length - 17; $i++) {
                $candidate = substr($token, $i, 17);
                if (preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed>|null $extracted
     *
     * @return array<string, mixed>
     */
    private function mergeCriteria(array $manual, ?array $extracted): array
    {
        $criteria = [];
        foreach ($manual as $key => $value) {
            if ($value !== null && '' !== $value) {
                $criteria[$key] = $value;
            }
        }

        if (null === $extracted) {
            return $criteria;
        }

        $map = [
            'brand' => 'brand',
            'series' => 'series',
            'model' => 'model',
            'engineCode' => 'engineCode',
            'engineName' => 'engineName',
            'fuel' => 'fuel',
            'displacement' => 'displacement',
            'powerHp' => 'powerHp',
            'powerKw' => 'powerKw',
        ];

        foreach ($map as $target => $source) {
            if (isset($criteria[$target]) && null !== $criteria[$target] && '' !== $criteria[$target]) {
                continue;
            }

            $value = $extracted[$source] ?? null;
            if (null === $value || '' === $value) {
                continue;
            }

            if (in_array($target, ['powerHp', 'powerKw'], true)) {
                $numeric = $this->toInt($value);
                if (null !== $numeric) {
                    $criteria[$target] = $numeric;
                }
                continue;
            }

            if (is_string($value)) {
                $criteria[$target] = trim($value);
            }
        }

        return $criteria;
    }

    /**
     * @param array<int, array{engine: VehicleEngine, score: float}> $matches
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapMatches(array $matches): array
    {
        $results = [];
        foreach ($matches as $match) {
            $engine = $match['engine'];
            $stages = array_values(array_filter(
                $engine->getStages()->toArray(),
                static fn (mixed $stage): bool => $stage instanceof VehicleStage
            ));
            usort($stages, static fn (VehicleStage $a, VehicleStage $b): int => strnatcasecmp($a->getName(), $b->getName()));

            $results[] = [
                'score' => $match['score'],
                'id' => $engine->getId(),
                'name' => $engine->getName(),
                'engineCode' => $engine->getEngineCode(),
                'fuel' => $engine->getFuel(),
                'displacement' => $engine->getDisplacement(),
                'horsepower' => $engine->getHorsepower(),
                'torque' => $engine->getTorque(),
                'brand' => $engine->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand()?->getName(),
                'series' => $engine->getVehicleModel()?->getVehicleSeries()?->getName(),
                'model' => $engine->getVehicleModel()?->getName(),
                'detailUrl' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(VehicleEngineCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($engine->getId())
                    ->generateUrl(),
                'stages' => array_map(function (VehicleStage $stage): array {
                    return [
                        'id' => $stage->getId(),
                        'name' => $stage->getName(),
                        'hp' => $stage->getHp(),
                        'nm' => $stage->getNm(),
                        'price' => $stage->getPrice(),
                        'icon' => $stage->getResolvedIconPath(),
                        'active' => $stage->isActive(),
                        'detailUrl' => $this->adminUrlGenerator
                            ->unsetAll()
                            ->setController(VehicleStageCrudController::class)
                            ->setAction(Action::DETAIL)
                            ->setEntityId($stage->getId())
                            ->generateUrl(),
                    ];
                }, $stages),
            ];
        }

        return $results;
    }

    /**
     * @return array{relativePath: string, absolutePath: string, originalName: ?string, mimeType: ?string, baseName: string}
     */
    private function persistUpload(UploadedFile $file): array
    {
        $uploadDir = $this->projectDir.'/'.self::UPLOAD_DIR;
        if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('Upload-Verzeichnis konnte nicht erstellt werden.');
        }

        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $extension = strtolower((string) ($file->guessExtension() ?? $file->getClientOriginalExtension() ?? ''));
        if ('' === $extension) {
            $extension = 'bin';
        }

        $baseName = date('YmdHis').'-'.bin2hex(random_bytes(4));
        $filename = $baseName.'.'.$extension;
        $moved = $file->move($uploadDir, $filename);

        return [
            'relativePath' => 'uploads/ai/vehicle/'.$filename,
            'absolutePath' => $moved->getPathname(),
            'originalName' => $originalName,
            'mimeType' => $mimeType,
            'baseName' => $baseName,
        ];
    }

    /**
     * @return array{0: list<string>, 1: list<string>}
     */
    private function renderPdfPreviews(string $pdfPath, string $baseName): array
    {
        $binary = $this->resolveConvertBinary();
        if (null === $binary) {
            throw new \RuntimeException('PDF Preview nicht moeglich: ImageMagick (convert) fehlt.');
        }

        $previewDir = $this->projectDir.'/'.self::PREVIEW_DIR;
        if (!is_dir($previewDir) && !@mkdir($previewDir, 0775, true) && !is_dir($previewDir)) {
            throw new \RuntimeException('Preview-Verzeichnis konnte nicht erstellt werden.');
        }

        $previewPattern = $baseName.'-page-%02d.png';
        $previewAbsolutePattern = $previewDir.'/'.$previewPattern;

        $process = new Process([
            $binary,
            '-density', '200',
            $pdfPath,
            '-quality', '90',
            '-background', 'white',
            '-alpha', 'remove',
            '-flatten',
            $previewAbsolutePattern,
        ]);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            $message = trim($process->getErrorOutput());
            throw new \RuntimeException($message !== '' ? $message : 'PDF konnte nicht verarbeitet werden.');
        }

        $files = glob($previewDir.'/'.$baseName.'-page-*.png');
        if (!is_array($files) || [] === $files) {
            throw new \RuntimeException('Keine PDF-Seiten gefunden.');
        }

        sort($files, \SORT_NATURAL);
        $relative = [];
        foreach ($files as $filePath) {
            $relative[] = 'uploads/ai/vehicle/preview/'.basename($filePath);
        }

        return [$relative, $files];
    }

    private function resolveConvertBinary(): ?string
    {
        $candidates = [
            '/usr/bin/convert',
            '/usr/local/bin/convert',
            'convert',
        ];

        foreach ($candidates as $candidate) {
            if ('convert' === $candidate) {
                return $candidate;
            }

            if (@is_file($candidate) && @is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function detectSourceType(UploadedFile $file, string $mimeType): ?string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ('pdf' === $extension || str_contains($mimeType, 'pdf')) {
            return 'pdf';
        }

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        return null;
    }

    private function applyCriteriaToManual(array $manual, array $criteria): array
    {
        foreach ($manual as $key => $value) {
            if (!array_key_exists($key, $criteria)) {
                continue;
            }

            $incoming = $criteria[$key];
            if (in_array($key, ['powerHp', 'powerKw'], true)) {
                $manual[$key] = $this->toInt($incoming);
                continue;
            }

            if (is_string($incoming)) {
                $manual[$key] = trim($incoming);
            }
        }

        return $manual;
    }

    private function isManualEmpty(array $manual): bool
    {
        foreach ($manual as $value) {
            if ($value !== null && '' !== $value) {
                return false;
            }
        }

        return true;
    }

    private function sanitizeText(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }

    private function toInt(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value)) {
            if (preg_match('/\\d+/', $value, $matches)) {
                return (int) $matches[0];
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonPayload(string $payload): ?array
    {
        $payload = trim($payload);
        if ('' === $payload) {
            return null;
        }

        if (str_starts_with($payload, '```')) {
            $payload = preg_replace('/^```[a-zA-Z]*\\s*/', '', $payload) ?? $payload;
            $payload = preg_replace('/\\s*```$/', '', $payload) ?? $payload;
            $payload = trim($payload);
        }

        $decoded = json_decode($payload, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($payload, '{');
        $end = strrpos($payload, '}');
        if (false === $start || false === $end || $end <= $start) {
            return null;
        }

        $candidate = substr($payload, $start, $end - $start + 1);
        $decoded = json_decode($candidate, true);

        return is_array($decoded) ? $decoded : null;
    }
}
