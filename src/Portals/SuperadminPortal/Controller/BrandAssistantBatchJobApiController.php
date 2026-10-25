<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\BrandAssistantBatchJob;
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

    private const JOB_TEMPLATES = [
        'technical_data' => 'Technische Daten',
        'tuning_potential' => 'Tuning-Potenzial',
        'market_info' => 'Markt- und Verfügbarkeitsinformationen',
        'safety_features' => 'Sicherheitsmerkmale',
        'fuel_economy' => 'Verbrauch und Emissionen',
    ];

    private const JOB_PROMPTS = [
        'technical_data' => 'Gib alle verfügbaren technischen Spezifikationen an (Hubraum, Verdichtungsverhältnis, Bohrung x Hub, Motorcode, etc.)',
        'tuning_potential' => 'Analysiere das Tuning-Potenzial dieses Motors und nenne typische Leistungssteigerungen',
        'market_info' => 'Recherchiere aktuelle Marktinformationen, Verfügbarkeit und typische Preise für diesen Motor',
        'safety_features' => 'Beschreibe die Sicherheitsfeatures und spezifischen Sicherheitsmerkmale dieses Motors',
        'fuel_economy' => 'Gib den Kraftstoffverbrauch und die Abgasemissionen an',
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
            ->setPrompt(self::JOB_PROMPTS[$template] ?? '');

        foreach ($engineIds as $engineId) {
            $id = filter_var((int) $engineId, FILTER_VALIDATE_INT);
            if (false === $id || $id <= 0) {
                continue;
            }

            $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($id);
            if (!$engine instanceof VehicleEngine) {
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

    #[Route('/operations-api/batch-jobs/templates', name: 'superadmin_portal_batch_jobs_templates', methods: ['GET'])]
    public function getTemplates(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $templates = array_map(function (string $key, string $label) {
            return [
                'key' => $key,
                'label' => $label,
                'prompt' => self::JOB_PROMPTS[$key] ?? '',
            ];
        }, array_keys(self::JOB_TEMPLATES), array_values(self::JOB_TEMPLATES));

        return $this->json(['data' => $templates]);
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
