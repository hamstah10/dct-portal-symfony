<?php

namespace App\Controller\Api;

use App\Api\ApiValidationException;
use App\Entity\Lead;
use App\Repository\ApiTokenRepository;
use App\Service\Api\LeadApiPayloadService;
use App\Service\Webhook\WebhookDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/leads')]
final class LeadController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LeadApiPayloadService $payloadService,
        private readonly WebhookDispatcher $webhookDispatcher,
        private readonly string $apiToken,
    ) {
    }

    #[Route('', name: 'api_leads_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeSystemApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        try {
            $payload = $this->readJsonPayload($request);

            $lead = new Lead();
            $this->payloadService->applyPayload($lead, $payload);

            $this->entityManager->persist($lead);
            $this->entityManager->flush();

            $this->webhookDispatcher->dispatch('lead.created', $this->payloadService->normalizeLead($lead));
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (ApiValidationException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($this->payloadService->normalizeLead($lead), Response::HTTP_CREATED);
    }

    private function authorizeSystemApi(Request $request): ?JsonResponse
    {
        $provided = $this->getBearerToken($request);
        if (null === $provided) {
            return $this->errorResponse('API-Token fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        $envTokenMatches = '' !== trim($this->apiToken) && hash_equals($this->apiToken, $provided);
        $dbTokenMatches = null !== $this->apiTokenRepository->findOneBy(['apiToken' => $provided]);

        if (!$envTokenMatches && !$dbTokenMatches) {
            return $this->errorResponse('API-Token ist ungueltig.', Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }
}
