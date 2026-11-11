<?php

namespace Tfd\PartnerWizardBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Service\PartnerWizardDomainGuard;
use Tfd\PartnerWizardBundle\Service\PartnerWizardStructureService;
use Tfd\PartnerWizardBundle\Service\PartnerWizardTextService;

/**
 * Headless-API zum Pflegen der Wizard-Inhalte durch den Partner:
 * Texte (/texts) sowie Fragen und Antwortoptionen anlegen, ändern, löschen (/questions, /options).
 *
 * Nur Server-Zugriff: gültiger API-Token des Clients UND Schalter „Inhalte per API bearbeiten".
 * „Erlaubte Domains" zählen hier bewusst nicht, und es gibt kein CORS.
 */
final class PartnerWizardContentApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PartnerWizardTextService $textService,
        private readonly PartnerWizardStructureService $structureService,
        private readonly PartnerWizardDomainGuard $domainGuard,
    ) {
    }

    // ------------------------------------------------------------------ Texte

    #[Route('/api/tfd-partner-wizard/texts/{clientId}', name: 'tfd_partner_wizard_texts', methods: ['GET'])]
    public function listTexts(string $clientId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }

        return $this->noStore($this->json($this->textService->listTexts($client)));
    }

    #[Route('/api/tfd-partner-wizard/texts/{clientId}', name: 'tfd_partner_wizard_texts_update', methods: ['PATCH'])]
    public function updateTexts(string $clientId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }
        $payload = $this->decode($request);
        if ($payload === null) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $result = $this->textService->applyChanges($client, $payload['changes'] ?? null);
        if ($result['errors'] !== []) {
            return $this->json(['error' => 'Validation failed', 'errors' => $result['errors']], 422);
        }

        return $this->json(['ok' => true, 'applied' => $result['applied']]);
    }

    // ------------------------------------------------------------------ Fragen

    #[Route('/api/tfd-partner-wizard/questions/{clientId}', name: 'tfd_partner_wizard_questions', methods: ['GET'])]
    public function listQuestions(string $clientId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }

        return $this->noStore($this->json($this->structureService->listStructure($client)));
    }

    #[Route('/api/tfd-partner-wizard/questions/{clientId}', name: 'tfd_partner_wizard_question_create', methods: ['POST'])]
    public function createQuestion(string $clientId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }
        $payload = $this->decode($request);
        if ($payload === null) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $result = $this->structureService->createQuestion($client, $payload);
        if ($result['errors'] !== []) {
            return $this->validationError($result['errors']);
        }

        return $this->json(['ok' => true, 'question' => $this->structureService->serializeQuestion($result['question'])], 201);
    }

    #[Route('/api/tfd-partner-wizard/questions/{clientId}/{questionId}', name: 'tfd_partner_wizard_question_update', requirements: ['questionId' => '\d+'], methods: ['PATCH'])]
    public function updateQuestion(string $clientId, int $questionId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }
        $question = $this->structureService->findQuestion($client, $questionId);
        if (!$question) {
            return $this->json(['error' => 'Question not found'], 404);
        }
        $payload = $this->decode($request);
        if ($payload === null) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $result = $this->structureService->updateQuestion($question, $payload);
        if ($result['errors'] !== []) {
            return $this->validationError($result['errors']);
        }

        return $this->json(['ok' => true, 'question' => $this->structureService->serializeQuestion($result['question'])]);
    }

    #[Route('/api/tfd-partner-wizard/questions/{clientId}/{questionId}', name: 'tfd_partner_wizard_question_delete', requirements: ['questionId' => '\d+'], methods: ['DELETE'])]
    public function deleteQuestion(string $clientId, int $questionId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }
        $question = $this->structureService->findQuestion($client, $questionId);
        if (!$question) {
            return $this->json(['error' => 'Question not found'], 404);
        }

        $errors = $this->structureService->deleteQuestion($question);
        if ($errors !== []) {
            return $this->json(['error' => 'Conflict', 'errors' => $errors], 409);
        }

        return $this->json(['ok' => true, 'deleted' => $questionId]);
    }

    // ------------------------------------------------------------------ Optionen

    #[Route('/api/tfd-partner-wizard/questions/{clientId}/{questionId}/options', name: 'tfd_partner_wizard_option_create', requirements: ['questionId' => '\d+'], methods: ['POST'])]
    public function createOption(string $clientId, int $questionId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }
        $question = $this->structureService->findQuestion($client, $questionId);
        if (!$question) {
            return $this->json(['error' => 'Question not found'], 404);
        }
        $payload = $this->decode($request);
        if ($payload === null) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $result = $this->structureService->createOption($question, $payload);
        if ($result['errors'] !== []) {
            return $this->validationError($result['errors']);
        }

        return $this->json(['ok' => true, 'option' => $this->structureService->serializeOption($result['option'])], 201);
    }

    #[Route('/api/tfd-partner-wizard/options/{clientId}/{optionId}', name: 'tfd_partner_wizard_option_update', requirements: ['optionId' => '\d+'], methods: ['PATCH'])]
    public function updateOption(string $clientId, int $optionId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }
        $option = $this->structureService->findOption($client, $optionId);
        if (!$option) {
            return $this->json(['error' => 'Option not found'], 404);
        }
        $payload = $this->decode($request);
        if ($payload === null) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $result = $this->structureService->updateOption($option, $payload);
        if ($result['errors'] !== []) {
            return $this->validationError($result['errors']);
        }

        return $this->json(['ok' => true, 'option' => $this->structureService->serializeOption($result['option'])]);
    }

    #[Route('/api/tfd-partner-wizard/options/{clientId}/{optionId}', name: 'tfd_partner_wizard_option_delete', requirements: ['optionId' => '\d+'], methods: ['DELETE'])]
    public function deleteOption(string $clientId, int $optionId, Request $request): JsonResponse
    {
        $client = $this->findClient($clientId);
        if ($denied = $this->authorize($client, $request)) {
            return $denied;
        }
        $option = $this->structureService->findOption($client, $optionId);
        if (!$option) {
            return $this->json(['error' => 'Option not found'], 404);
        }

        $errors = $this->structureService->deleteOption($option);
        if ($errors !== []) {
            return $this->json(['error' => 'Conflict', 'errors' => $errors], 409);
        }

        return $this->json(['ok' => true, 'deleted' => $optionId]);
    }

    // ------------------------------------------------------------------ intern

    private function findClient(string $clientId): ?PartnerWizardClient
    {
        return $this->em->getRepository(PartnerWizardClient::class)->findOneBy(['clientId' => $clientId, 'active' => true]);
    }

    private function authorize(?PartnerWizardClient $client, Request $request): ?JsonResponse
    {
        if (!$client) {
            return $this->json(['error' => 'Client not found'], 404);
        }

        $token = $this->domainGuard->extractToken($request);
        if ($token === null) {
            return $this->json(['error' => 'API token required'], 401);
        }
        if (!$client->isApiTokenValid($token)) {
            return $this->json(['error' => 'Invalid API token'], 401);
        }
        if (!$client->isTextApiEnabled()) {
            return $this->json(['error' => 'Content editing via API is disabled for this client'], 403);
        }

        return null;
    }

    private function decode(Request $request): ?array
    {
        $payload = json_decode($request->getContent(), true);

        return is_array($payload) && !array_is_list($payload) ? $payload : null;
    }

    /** @param list<string> $errors */
    private function validationError(array $errors): JsonResponse
    {
        return $this->json(['error' => 'Validation failed', 'errors' => $errors], 422);
    }

    private function noStore(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
