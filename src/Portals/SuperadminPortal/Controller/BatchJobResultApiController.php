<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Entity\BrandAssistantBatchJobItem;
use App\Entity\VehicleEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class BatchJobResultApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/operations-api/vehicle-engines/{engineId}/save-analysis', name: 'superadmin_portal_save_engine_analysis', methods: ['POST'])]
    public function saveAnalysis(int $engineId, Request $request): JsonResponse
    {
        $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($engineId);
        if (!$engine) {
            throw new NotFoundHttpException('Engine not found');
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload');
        }

        $template = $payload['template'] ?? '';
        $result = $payload['result'] ?? '';
        $jobItemId = $payload['jobItemId'] ?? null;

        if (!$template || !$result) {
            throw new BadRequestHttpException('Template and result are required');
        }

        $analysisData = $engine->getAiAnalysisData() ?? [];

        if (!isset($analysisData['analyses'])) {
            $analysisData['analyses'] = [];
        }

        $analysisData['analyses'][] = [
            'template' => $template,
            'result' => $result,
            'jobItemId' => $jobItemId,
            'savedAt' => (new \DateTimeImmutable())->format('c'),
        ];

        $engine->setAiAnalysisData($analysisData);
        $this->entityManager->flush();

        return $this->json(['success' => true, 'data' => $analysisData]);
    }

    #[Route('/operations-api/vehicle-engines/{engineId}/analysis', name: 'superadmin_portal_get_engine_analysis', methods: ['GET'])]
    public function getAnalysis(int $engineId): JsonResponse
    {
        $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($engineId);
        if (!$engine) {
            throw new NotFoundHttpException('Engine not found');
        }

        $analysisData = $engine->getAiAnalysisData();

        return $this->json([
            'engineId' => $engineId,
            'engineName' => $engine->getName(),
            'analyses' => $analysisData['analyses'] ?? [],
            'updatedAt' => $engine->getAiAnalysisUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/operations-api/vehicle-engines/{engineId}/analysis/{index}', name: 'superadmin_portal_delete_analysis', methods: ['DELETE'])]
    public function deleteAnalysis(int $engineId, int $index): JsonResponse
    {
        $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($engineId);
        if (!$engine) {
            throw new NotFoundHttpException('Engine not found');
        }

        $analysisData = $engine->getAiAnalysisData() ?? [];

        if (!isset($analysisData['analyses'][$index])) {
            throw new NotFoundHttpException('Analysis not found');
        }

        unset($analysisData['analyses'][$index]);
        $analysisData['analyses'] = array_values($analysisData['analyses']);

        $engine->setAiAnalysisData($analysisData);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
