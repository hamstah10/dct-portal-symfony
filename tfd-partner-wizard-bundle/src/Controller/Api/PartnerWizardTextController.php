<?php

namespace Tfd\PartnerWizardBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Service\PartnerWizardStructureService;
use Tfd\PartnerWizardBundle\Service\PartnerWizardTextService;

/**
 * Inline-Editor des Partner-Wizards (Texte, Fragen, Antwortoptionen). Nur für eingeloggte Admins (Session + CSRF),
 * bewusst ohne CORS – wird ausschließlich aus der Vorschau auf derselben Domain aufgerufen.
 */
#[IsGranted('ROLE_ADMIN')]
final class PartnerWizardTextController extends AbstractController
{
    public const CSRF_TOKEN_ID = 'tfd_partner_wizard_texts';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PartnerWizardTextService $textService,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly PartnerWizardStructureService $structureService,
    ) {
    }

    #[Route('/api/tfd-partner-wizard/admin/texts/{clientId}', name: 'tfd_partner_wizard_admin_texts', methods: ['GET'])]
    public function list(string $clientId): JsonResponse
    {
        $client = $this->findClient($clientId);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], 404);
        }

        $response = $this->json($this->textService->listTexts($client));
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    #[Route('/api/tfd-partner-wizard/admin/texts/{clientId}', name: 'tfd_partner_wizard_admin_texts_update', methods: ['PATCH'])]
    public function update(string $clientId, Request $request): JsonResponse
    {
        $token = (string) $request->headers->get('X-CSRF-Token', '');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, $token))) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $client = $this->findClient($clientId);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], 404);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $result = $this->textService->applyChanges($client, $payload['changes'] ?? null);
        if ($result['errors'] !== []) {
            return $this->json(['error' => 'Validation failed', 'errors' => $result['errors']], 422);
        }

        return $this->json(['ok' => true, 'applied' => $result['applied']]);
    }

    // ------------------------------------------------------------------ Fragen & Optionen

    #[Route('/api/tfd-partner-wizard/admin/questions/{clientId}', name: 'tfd_partner_wizard_admin_question_create', methods: ['POST'])]
    public function createQuestion(string $clientId, Request $request): JsonResponse
    {
        [$client, $payload, $denied] = $this->prepare($clientId, $request, true);
        if ($denied) {
            return $denied;
        }

        $result = $this->structureService->createQuestion($client, $payload);
        if ($result['errors'] !== []) {
            return $this->json(['error' => 'Validation failed', 'errors' => $result['errors']], 422);
        }

        return $this->json(['ok' => true, 'question' => $this->structureService->serializeQuestion($result['question'])], 201);
    }

    #[Route('/api/tfd-partner-wizard/admin/questions/{clientId}/{questionId}', name: 'tfd_partner_wizard_admin_question_update', requirements: ['questionId' => '\d+'], methods: ['PATCH'])]
    public function updateQuestion(string $clientId, int $questionId, Request $request): JsonResponse
    {
        [$client, $payload, $denied] = $this->prepare($clientId, $request, true);
        if ($denied) {
            return $denied;
        }
        $question = $this->structureService->findQuestion($client, $questionId);
        if (!$question) {
            return $this->json(['error' => 'Question not found'], 404);
        }

        $result = $this->structureService->updateQuestion($question, $payload);
        if ($result['errors'] !== []) {
            return $this->json(['error' => 'Validation failed', 'errors' => $result['errors']], 422);
        }

        return $this->json(['ok' => true, 'question' => $this->structureService->serializeQuestion($result['question'])]);
    }

    #[Route('/api/tfd-partner-wizard/admin/questions/{clientId}/{questionId}', name: 'tfd_partner_wizard_admin_question_delete', requirements: ['questionId' => '\d+'], methods: ['DELETE'])]
    public function deleteQuestion(string $clientId, int $questionId, Request $request): JsonResponse
    {
        [$client, , $denied] = $this->prepare($clientId, $request, false);
        if ($denied) {
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

    #[Route('/api/tfd-partner-wizard/admin/questions/{clientId}/{questionId}/options', name: 'tfd_partner_wizard_admin_option_create', requirements: ['questionId' => '\d+'], methods: ['POST'])]
    public function createOption(string $clientId, int $questionId, Request $request): JsonResponse
    {
        [$client, $payload, $denied] = $this->prepare($clientId, $request, true);
        if ($denied) {
            return $denied;
        }
        $question = $this->structureService->findQuestion($client, $questionId);
        if (!$question) {
            return $this->json(['error' => 'Question not found'], 404);
        }

        $result = $this->structureService->createOption($question, $payload);
        if ($result['errors'] !== []) {
            return $this->json(['error' => 'Validation failed', 'errors' => $result['errors']], 422);
        }

        return $this->json(['ok' => true, 'option' => $this->structureService->serializeOption($result['option'])], 201);
    }

    #[Route('/api/tfd-partner-wizard/admin/options/{clientId}/{optionId}', name: 'tfd_partner_wizard_admin_option_delete', requirements: ['optionId' => '\d+'], methods: ['DELETE'])]
    public function deleteOption(string $clientId, int $optionId, Request $request): JsonResponse
    {
        [$client, , $denied] = $this->prepare($clientId, $request, false);
        if ($denied) {
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

    /**
     * CSRF prüfen, Client laden, JSON lesen.
     *
     * @return array{0: ?PartnerWizardClient, 1: array, 2: ?JsonResponse}
     */
    private function prepare(string $clientId, Request $request, bool $withBody): array
    {
        $token = (string) $request->headers->get('X-CSRF-Token', '');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, $token))) {
            return [null, [], $this->json(['error' => 'Invalid CSRF token'], 403)];
        }

        $client = $this->findClient($clientId);
        if (!$client) {
            return [null, [], $this->json(['error' => 'Client not found'], 404)];
        }

        if (!$withBody) {
            return [$client, [], null];
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload) || array_is_list($payload)) {
            return [$client, [], $this->json(['error' => 'Invalid JSON'], 400)];
        }

        return [$client, $payload, null];
    }

    private function findClient(string $clientId): ?PartnerWizardClient
    {
        return $this->em->getRepository(PartnerWizardClient::class)->findOneBy(['clientId' => $clientId]);
    }
}
