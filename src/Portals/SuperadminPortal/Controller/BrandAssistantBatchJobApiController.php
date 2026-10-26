<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\BrandAssistantBatchJob;
use App\Entity\SuperadminSetting;
use App\Entity\BrandAssistantBatchJobItem;
use App\Entity\Portal;
use App\Entity\VehicleEngine;
use App\Message\ProcessBrandAssistantBatchJobMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class BrandAssistantBatchJobApiController extends AbstractController
{
    private const MAX_ENGINES_PER_JOB = 100;
    private const MAX_PROMPT_LENGTH = 5000;

    /** Schlüssel der Einstellung, in der die bearbeiteten Vorlagen gespeichert werden. */
    private const TEMPLATE_SETTING_KEY = 'batch_job_templates';

    private const JOB_TEMPLATES = [
        'technical_data' => 'Technische Daten',
        'tuning_potential' => 'Tuning-Potenzial',
        'market_info' => 'Markt- und Verfügbarkeitsinformationen',
        'safety_features' => 'Sicherheitsmerkmale',
        'fuel_economy' => 'Verbrauch und Emissionen',
        'ecu_units' => 'Steuergeräte',
        'tuning_data' => 'Weitere Tuningdaten',
        'hardware' => 'Hardware für Stage 2 und mehr',
        'read_methods' => 'Lesemethoden und Werkzeuge',
        'interventions' => 'Eingriffe und Abgasnachbehandlung',
        'gearbox' => 'Getriebe und Schaltprogramme',
        'legal' => 'Eintragung und Rechtliches',
        'typical_faults' => 'Typische Fehler nach dem Tuning',
        'installation' => 'Einbau und Aufwand',
        'competitors' => 'Tool-Anbieter und Konkurrenz',
    ];

    /** Kategorie je Vorlage. Reihenfolge der Kategorien bestimmt die Anzeige. */
    private const JOB_CATEGORIES = [
        'Technik' => ['technical_data', 'safety_features', 'fuel_economy'],
        'Steuergerät & Software' => ['ecu_units', 'read_methods', 'gearbox', 'typical_faults'],
        'Tuning & Hardware' => ['tuning_potential', 'tuning_data', 'hardware', 'interventions', 'installation'],
        'Markt & Recht' => ['market_info', 'competitors', 'legal'],
    ];

    private const JOB_PROMPTS = [
        'technical_data' => 'Gib alle verfügbaren technischen Spezifikationen an (Hubraum, Verdichtungsverhältnis, Bohrung x Hub, Motorcode, etc.)',
        'tuning_potential' => 'Analysiere das Tuning-Potenzial dieses Motors und nenne typische Leistungssteigerungen',
        'market_info' => 'Recherchiere aktuelle Marktinformationen, Verfügbarkeit und typische Preise für diesen Motor',
        'safety_features' => 'Beschreibe die Sicherheitsfeatures und spezifischen Sicherheitsmerkmale dieses Motors',
        'fuel_economy' => 'Gib den Kraftstoffverbrauch und die Abgasemissionen an',
        'ecu_units' => 'Nenne alle Steuergeräte (ECU) dieses Motors: Hersteller, Typ bzw. Bezeichnung (z. B. Bosch MG1, Simos 19), Einsatz und Quellen. Antworte als Liste.',
        'hardware' => 'Nenne die Hardware-Teile, die für Stufen über Stage 1 typisch sind (z. B. Downpipe, Ladeluftkühler, Auspuffanlage, Ansaugung, Turbolader, Ladeluftschläuche, Kraftstoffsystem, Injektoren). Gib pro Teil an, ob es für diesen Motor passende Varianten gibt, typische Leistungsgewinne, Eintragung (ABE/TÜV) und Quellen. Kennzeichne Unsicheres.',
        'read_methods' => 'Nenne die Lesemethoden für das Steuergerät dieses Motors (OBD, Bench, Boot). Gib pro Methode an, welche Werkzeuge sie unterstützen, ob Ausbau nötig ist, typischer Zeitaufwand und Quellen. Kennzeichne Unsicheres.',
        'interventions' => 'Beschreibe, welche Abgas- und Motorbauteile beim Tuning dieses Motors typischerweise betroffen sind (z. B. DPF, AGR, Lambdasonde, SCR/AdBlue, Drallklappen) und was dabei technisch und rechtlich zu beachten ist. Nenne Quellen.',
        'gearbox' => 'Beschreibe die Getriebeoptionen zu diesem Motor: Getriebetyp, Schaltprogramme, Software-Optionen und Grenzen des Tunings. Nenne Quellen und kennzeichne Unsicheres.',
        'legal' => 'Nenne die Eintragungsfragen für Tuning-Änderungen an diesem Motor: ABE- oder TÜV-Fähigkeit der typischen Änderungen, nötige Unterlagen und typische Stolpersteine. Nenne Quellen und weise darauf hin, dass dies keine Rechtsberatung ist.',
        'typical_faults' => 'Nenne typische Fehler und Probleme, die nach Tuning an diesem Motor auftreten (Fehlercodes, Ursachen, Abhilfe). Nenne Quellen und kennzeichne Unsicheres.',
        'installation' => 'Beschreibe Einbau und Aufwand der typischen Tuning-Hardware für diesen Motor: Ausbauschritte, geschätzte Arbeitszeit, benötigtes Werkstattwissen und Sonderwerkzeug. Nenne Quellen.',
        'competitors' => 'Nenne Tuning-Tools und Anbieter für das Steuergerät dieses Motors. Vergleiche Funktionsumfang, Preis, Lesemethoden und Verfügbarkeit. Nenne Quellen und kennzeichne Unsicheres.',
        'tuning_data' => 'Recherchiere weitere Tuningdaten zu diesem Motor: Lesemethoden und Werkzeuge, Stage-Werte (Leistung, Drehmoment), Eingriffe wie DPF, AGR oder Lambda und typische Hinweise. Nenne jede Angabe mit Quelle und kennzeichne Unsicheres.',
    ];

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        #[Autowire('%kernel.environment%')] private readonly string $environment,
    ) {
    }

    #[Route('/operations-api/batch-jobs', name: 'superadmin_portal_batch_jobs_list', methods: ['GET'])]
    public function listJobs(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $jobs = $this->entityManager->getRepository(BrandAssistantBatchJob::class)
            ->findRecentJobs(limit: 20);

        $data = array_map(function (BrandAssistantBatchJob $job) {
            return [
                'id' => $job->getId(),
                'name' => $job->getName(),
                'template' => $job->getTemplate(),
                'status' => $job->getStatus(),
                'createdAt' => $job->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'startedAt' => $job->getStartedAt()?->format(\DateTimeInterface::ATOM),
                'completedAt' => $job->getCompletedAt()?->format(\DateTimeInterface::ATOM),
                'totalItems' => $job->getItems()->count(),
                'completedItems' => $job->getCompletedCount(),
                'failedItems' => $job->getFailedCount(),
            ];
        }, $jobs);

        return $this->json(['data' => $data]);
    }

    #[Route('/operations-api/batch-jobs', name: 'superadmin_portal_batch_jobs_create', methods: ['POST'])]
    public function createJob(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload');
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $template = trim((string) ($payload['template'] ?? ''));
        $engineIds = (array) ($payload['engineIds'] ?? []);

        if ('' === $name) {
            throw new BadRequestHttpException('Name is required');
        }

        if ('' === $template || !array_key_exists($template, self::JOB_TEMPLATES)) {
            throw new BadRequestHttpException('Invalid or missing template');
        }

        if (empty($engineIds)) {
            throw new BadRequestHttpException('At least one engine ID is required');
        }

        if (count($engineIds) > self::MAX_ENGINES_PER_JOB) {
            throw new BadRequestHttpException(sprintf('Maximum %d engines per job', self::MAX_ENGINES_PER_JOB));
        }

        $job = new BrandAssistantBatchJob();
        $job->setName($name)
            ->setTemplate($template)
            ->setPrompt($this->mergedTemplate($template, self::JOB_TEMPLATES[$template], [], $this->templateOverrides())['prompt']);

        foreach ($engineIds as $engineId) {
            $id = filter_var((int) $engineId, FILTER_VALIDATE_INT);
            if (false === $id || $id <= 0) {
                continue;
            }

            $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($id);
            if (!$engine instanceof VehicleEngine || !$this->isInActiveDataset($engine)) {
                continue;
            }

            $item = new BrandAssistantBatchJobItem();
            $item->setVehicleEngine($engine);
            $job->addItem($item);
        }

        if ($job->getItems()->isEmpty()) {
            throw new BadRequestHttpException('No valid engines found');
        }

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        // Queue the job for processing
        $this->messageBus->dispatch(new ProcessBrandAssistantBatchJobMessage($job->getId()));

        return $this->json(['data' => [
            'id' => $job->getId(),
            'name' => $job->getName(),
            'template' => $job->getTemplate(),
            'status' => $job->getStatus(),
        ]], 201);
    }

    /** Motor, Modell, Serie, Marke und Datenpool müssen alle aktiv sein. */
    private function isInActiveDataset(VehicleEngine $engine): bool
    {
        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();
        $pool = $brand?->getVehicleDataPool();

        return $engine->isActive()
            && $model?->isActive() === true
            && $series?->isActive() === true
            && $brand?->isActive() === true
            && $pool?->isActive() === true;
    }

    #[Route('/operations-api/vehicle-engines', name: 'superadmin_portal_vehicle_engines', methods: ['GET'])]
    public function getVehicleEngines(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        // Nur Motoren aus dem aktiven Datenbestand: die ganze Kette von Marke bis Motor und der Datenpool sind aktiv.
        $query = $this->entityManager->getRepository(VehicleEngine::class)
            ->createQueryBuilder('e')
            ->innerJoin('e.vehicleModel', 'm')
            ->innerJoin('m.vehicleSeries', 's')
            ->innerJoin('s.vehicleBrand', 'b')
            ->innerJoin('b.vehicleDataPool', 'p')
            ->andWhere('e.active = true AND m.active = true AND s.active = true AND b.active = true AND p.active = true')
            ->select('e.id as id', 'e.name', 'm.name as modelName', 's.name as seriesName', 'b.name as brandName')
            ->orderBy('b.name', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->addOrderBy('m.name', 'ASC')
            ->addOrderBy('e.name', 'ASC')
            ->getQuery();

        $engines = $query->getArrayResult();

        return $this->json(['data' => $engines]);
    }

    #[Route('/operations-api/batch-jobs/templates', name: 'superadmin_portal_batch_jobs_templates', methods: ['GET'])]
    public function getTemplates(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $categoryOf = [];
        foreach (self::JOB_CATEGORIES as $category => $keys) {
            foreach ($keys as $key) {
                $categoryOf[$key] = $category;
            }
        }

        $overrides = $this->templateOverrides();
        $templates = [];
        foreach (self::JOB_TEMPLATES as $key => $defaultLabel) {
            $templates[] = $this->mergedTemplate($key, $defaultLabel, $categoryOf, $overrides);
        }

        return $this->json(['data' => $templates]);
    }

    /** Bearbeiten einer Vorlage: Bezeichnung und Prompt. Mit reset=true auf den Standard zurücksetzen. */
    #[Route('/operations-api/batch-jobs/templates/{key}', name: 'superadmin_portal_batch_jobs_template_update', requirements: ['key' => '[a-z_]+'], methods: ['PUT'])]
    public function updateTemplate(string $key, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        if (!array_key_exists($key, self::JOB_TEMPLATES)) {
            throw new NotFoundHttpException('Unbekannte Vorlage.');
        }

        $body = json_decode((string) $request->getContent(), true);
        $body = is_array($body) ? $body : [];
        $overrides = $this->templateOverrides();

        if (($body['reset'] ?? false) === true) {
            unset($overrides[$key]);
        } else {
            $label = trim((string) ($body['label'] ?? ''));
            $prompt = trim((string) ($body['prompt'] ?? ''));
            if ('' === $label || mb_strlen($label) > 120) {
                throw new BadRequestHttpException('Bezeichnung ist Pflicht und darf höchstens 120 Zeichen haben.');
            }
            if ('' === $prompt || mb_strlen($prompt) > self::MAX_PROMPT_LENGTH) {
                throw new BadRequestHttpException(sprintf('Prompt ist Pflicht und darf höchstens %d Zeichen haben.', self::MAX_PROMPT_LENGTH));
            }
            $overrides[$key] = ['label' => $label, 'prompt' => $prompt];
        }

        $setting = $this->entityManager->getRepository(SuperadminSetting::class)->findOneBy(['settingKey' => self::TEMPLATE_SETTING_KEY])
            ?? (new SuperadminSetting())->setSettingKey(self::TEMPLATE_SETTING_KEY);
        $setting->setSettingValue($overrides === [] ? null : $overrides);
        $this->entityManager->persist($setting);
        $this->entityManager->flush();

        return $this->json(['data' => $this->mergedTemplate($key, self::JOB_TEMPLATES[$key], $this->categoryMap(), $overrides)]);
    }

    /** @return array<string, array{label?: string, prompt?: string}> */
    private function templateOverrides(): array
    {
        $setting = $this->entityManager->getRepository(SuperadminSetting::class)->findOneBy(['settingKey' => self::TEMPLATE_SETTING_KEY]);
        $value = $setting?->getSettingValue();

        return is_array($value) ? $value : [];
    }

    /** @return array<string, string> */
    private function categoryMap(): array
    {
        $map = [];
        foreach (self::JOB_CATEGORIES as $category => $keys) {
            foreach ($keys as $key) {
                $map[$key] = $category;
            }
        }

        return $map;
    }

    /**
     * @param array<string, string>                              $categoryOf
     * @param array<string, array{label?: string, prompt?: string}> $overrides
     *
     * @return array{key: string, label: string, prompt: string, category: string, defaultLabel: string, defaultPrompt: string, customized: bool}
     */
    private function mergedTemplate(string $key, string $defaultLabel, array $categoryOf, array $overrides): array
    {
        $defaultPrompt = self::JOB_PROMPTS[$key] ?? '';
        $override = $overrides[$key] ?? [];
        $categories = $categoryOf === [] ? $this->categoryMap() : $categoryOf;

        return [
            'key' => $key,
            'label' => (string) ($override['label'] ?? $defaultLabel),
            'prompt' => (string) ($override['prompt'] ?? $defaultPrompt),
            'category' => $categories[$key] ?? 'Sonstige',
            'defaultLabel' => $defaultLabel,
            'defaultPrompt' => $defaultPrompt,
            'customized' => isset($overrides[$key]),
        ];
    }

    #[Route('/operations-api/batch-jobs/{id}', name: 'superadmin_portal_batch_jobs_show', methods: ['GET'])]
    public function showJob(int $id, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $job = $this->entityManager->getRepository(BrandAssistantBatchJob::class)->find($id);
        if (!$job instanceof BrandAssistantBatchJob) {
            throw new NotFoundHttpException('Job not found');
        }

        $items = array_map(function (BrandAssistantBatchJobItem $item) {
            return [
                'id' => $item->getId(),
                'engineId' => $item->getVehicleEngine()->getId(),
                'engineName' => $item->getVehicleEngine()->getName(),
                'result' => $item->getResult(),
                'error' => $item->getError(),
                'processedAt' => $item->getProcessedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }, $job->getItems()->toArray());

        return $this->json(['data' => [
            'id' => $job->getId(),
            'name' => $job->getName(),
            'template' => $job->getTemplate(),
            'status' => $job->getStatus(),
            'createdAt' => $job->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'startedAt' => $job->getStartedAt()?->format(\DateTimeInterface::ATOM),
            'completedAt' => $job->getCompletedAt()?->format(\DateTimeInterface::ATOM),
            'totalItems' => $job->getItems()->count(),
            'completedItems' => $job->getCompletedCount(),
            'failedItems' => $job->getFailedCount(),
            'items' => $items,
        ]]);
    }

    private function assertAccess(Request $request): void
    {
        $systemType = (string) $request->attributes->get('_brand_system_type', '');
        if (SystemType::SUPERADMIN === $systemType) {
            return;
        }

        $portal = $this->portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Superadmin operations API not available for this portal.');
        }
    }
}
