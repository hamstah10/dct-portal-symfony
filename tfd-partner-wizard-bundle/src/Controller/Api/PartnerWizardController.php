<?php

namespace Tfd\PartnerWizardBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tfd\PartnerWizardBundle\Service\PartnerWizardAppointmentService;
use Tfd\PartnerWizardBundle\Service\PartnerWizardConfigService;
use Tfd\PartnerWizardBundle\Service\PartnerWizardDomainGuard;
use Tfd\PartnerWizardBundle\Service\PartnerWizardLeadService;
use Tfd\PartnerWizardBundle\Service\PartnerWizardRecommendationService;

final class PartnerWizardController extends AbstractController
{
    public function __construct(
        private readonly PartnerWizardConfigService $configService,
        private readonly PartnerWizardRecommendationService $recommendationService,
        private readonly PartnerWizardLeadService $leadService,
        private readonly PartnerWizardDomainGuard $domainGuard,
        private readonly PartnerWizardAppointmentService $appointmentService,
    ) {
    }

    #[Route('/api/tfd-partner-wizard/config/{clientId}', name: 'tfd_partner_wizard_config', methods: ['GET'])]
    public function config(string $clientId, Request $request): JsonResponse
    {
        $client = $this->configService->getClient($clientId);
        if (!$client) {
            return $this->withCors($this->json(['error' => 'Client not found'], 404), $request);
        }

        if ($denied = $this->domainGuard->authorize($client, $request)) {
            return $this->withCors($this->json(['error' => $denied['error']], $denied['status']), $request);
        }

        return $this->withCors($this->json($this->configService->toConfigArray($client)), $request);
    }

    #[Route('/api/tfd-partner-wizard/config/{clientId}', name: 'tfd_partner_wizard_config_options', methods: ['OPTIONS'])]
    public function configOptions(Request $request): Response
    {
        return $this->withCors(new Response('', Response::HTTP_NO_CONTENT), $request);
    }

    #[Route('/api/tfd-partner-wizard/recommend', name: 'tfd_partner_wizard_recommend', methods: ['POST'])]
    public function recommend(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload === null) {
            return $this->withCors($this->json(['error' => 'Invalid JSON'], 400), $request);
        }

        $client = $this->configService->getClient($payload['clientId'] ?? $request->headers->get('X-TFD-Client') ?? 'default');
        if (!$client) {
            return $this->withCors($this->json(['error' => 'Client not found'], 404), $request);
        }

        if ($denied = $this->domainGuard->authorize($client, $request)) {
            return $this->withCors($this->json(['error' => $denied['error']], $denied['status']), $request);
        }

        return $this->withCors($this->json($this->recommendationService->recommend($client, $payload)), $request);
    }

    #[Route('/api/tfd-partner-wizard/recommend', name: 'tfd_partner_wizard_recommend_options', methods: ['OPTIONS'])]
    public function recommendOptions(Request $request): Response
    {
        return $this->withCors(new Response('', Response::HTTP_NO_CONTENT), $request);
    }

    #[Route('/api/tfd-partner-wizard/leads', name: 'tfd_partner_wizard_lead_create', methods: ['POST'])]
    public function createLead(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload === null) {
            return $this->withCors($this->json(['error' => 'Invalid JSON'], 400), $request);
        }

        $client = $this->configService->getClient($payload['clientId'] ?? $request->headers->get('X-TFD-Client') ?? 'default');
        if (!$client) {
            return $this->withCors($this->json(['error' => 'Client not found'], 404), $request);
        }

        if ($denied = $this->domainGuard->authorize($client, $request)) {
            return $this->withCors($this->json(['error' => $denied['error']], $denied['status']), $request);
        }

        $contact = $payload['contact'] ?? [];
        if (empty($contact['name']) || empty($contact['email'])) {
            return $this->withCors($this->json(['error' => 'Name and email are required'], 422), $request);
        }
        if (($payload['privacyAccepted'] ?? $payload['payload']['privacyAccepted'] ?? false) !== true) {
            return $this->withCors($this->json(['error' => 'Privacy consent is required'], 422), $request);
        }

        // Honeypot against simple spam bots.
        if (!empty($payload['website_confirm'])) {
            return $this->withCors($this->json(['ok' => true, 'message' => 'Anfrage wurde gespeichert.'], 201), $request);
        }

        // Termin: nur wenn im Client aktiviert. Slot wird direkt vor dem Speichern nochmal geprüft.
        $appointment = null;
        $appointmentStart = trim((string) ($payload['appointment']['start'] ?? $payload['appointmentStart'] ?? ''));
        if ($client->isAppointmentEnabled()) {
            if ($appointmentStart === '' && $client->isAppointmentRequired()) {
                return $this->withCors($this->json(['error' => 'Bitte wähle einen Termin.', 'code' => 'appointment_required'], 422), $request);
            }
            if ($appointmentStart !== '') {
                $appointment = $this->appointmentService->resolveFreeSlot($client, $appointmentStart);
                if ($appointment === null) {
                    return $this->withCors($this->json(['error' => 'Der gewählte Termin ist leider nicht mehr frei. Bitte wähle einen anderen.', 'code' => 'appointment_unavailable'], 409), $request);
                }
            }
        }

        $lead = $this->leadService->createLead($client, $payload, $request, $appointment);

        return $this->withCors($this->json([
            'ok' => true,
            'id' => $lead->getId(),
            'recommendation' => [
                'status' => $lead->getRecommendationStatus(),
                'packageKey' => $lead->getRecommendedPackageKey(),
                'packageTitle' => $lead->getRecommendedPackageTitle(),
                'packageText' => $lead->getRecommendedPackageText(),
                'recommendedTools' => $lead->getRecommendedTools(),
                'toolDirection' => $lead->getToolDirection(),
                'nextStep' => $lead->getNextStep(),
                'estimatedMonthlyRevenue' => $lead->getEstimatedMonthlyRevenue(),
            ],
            'appointment' => $lead->getAppointmentStartsAt() ? [
                'start' => $lead->getAppointmentStartsAt()->setTimezone(new \DateTimeZone($client->getAppointmentTimezone()))->format(\DATE_ATOM),
                'end' => $lead->getAppointmentEndsAt()?->setTimezone(new \DateTimeZone($client->getAppointmentTimezone()))->format(\DATE_ATOM),
                'title' => $client->getAppointmentTitle(),
                'location' => $client->getAppointmentLocation(),
            ] : null,
            'message' => 'Partneranfrage wurde gespeichert.',
        ], 201), $request);
    }

    #[Route('/api/tfd-partner-wizard/leads', name: 'tfd_partner_wizard_lead_create_options', methods: ['OPTIONS'])]
    public function createLeadOptions(Request $request): Response
    {
        return $this->withCors(new Response('', Response::HTTP_NO_CONTENT), $request);
    }

    #[Route('/api/tfd-partner-wizard/appointments/{clientId}', name: 'tfd_partner_wizard_appointments', methods: ['GET'])]
    public function appointments(string $clientId, Request $request): JsonResponse
    {
        $client = $this->configService->getClient($clientId);
        if (!$client) {
            return $this->withCors($this->json(['error' => 'Client not found'], 404), $request);
        }

        if ($denied = $this->domainGuard->authorize($client, $request)) {
            return $this->withCors($this->json(['error' => $denied['error']], $denied['status']), $request);
        }

        $settings = $this->appointmentService->getSettings($client);
        if (!$client->isAppointmentEnabled()) {
            return $this->withCors($this->json(['settings' => $settings, 'days' => []]), $request);
        }

        $from = null;
        $fromParam = (string) $request->query->get('from', '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromParam)) {
            $from = \DateTimeImmutable::createFromFormat('!Y-m-d', $fromParam, new \DateTimeZone($client->getAppointmentTimezone())) ?: null;
        }
        $days = $request->query->has('days') ? (int) $request->query->get('days') : null;

        $response = $this->json([
            'settings' => $settings,
            'days' => $this->appointmentService->getFreeSlots($client, $from, $days),
        ]);
        $response->headers->set('Cache-Control', 'no-store');

        return $this->withCors($response, $request);
    }

    #[Route('/api/tfd-partner-wizard/appointments/{clientId}', name: 'tfd_partner_wizard_appointments_options', methods: ['OPTIONS'])]
    public function appointmentsOptions(Request $request): Response
    {
        return $this->withCors(new Response('', Response::HTTP_NO_CONTENT), $request);
    }

    private function decodePayload(Request $request): ?array
    {
        $payload = json_decode($request->getContent(), true);
        return is_array($payload) ? $payload : null;
    }

    private function withCors(Response $response, Request $request): Response
    {
        $origin = $request->headers->get('Origin');

        $response->headers->set('Access-Control-Allow-Origin', $origin ?: '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, X-TFD-Client, Authorization, X-TFD-Api-Key');
        $response->headers->set('Access-Control-Max-Age', '86400');
        $response->headers->set('Vary', 'Origin', false);

        return $response;
    }
}
