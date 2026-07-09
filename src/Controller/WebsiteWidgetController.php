<?php

namespace App\Controller;

use App\Entity\WebsiteWidgetConfig;
use App\Repository\WebsiteWidgetConfigRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WebsiteWidgetController extends AbstractController
{
    public function __construct(
        private readonly WebsiteWidgetConfigRepository $widgetRepository,
        private readonly HttpClientInterface $httpClient,
        private readonly MailerInterface $mailer,
        #[Autowire('%portal.mailer_from%')]
        private readonly string $mailerFrom,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    #[Route('/widget/{embedKey}/embed', name: 'app_website_widget_embed_js', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['GET'])]
    public function embedScript(string $embedKey, Request $request): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof WebsiteWidgetConfig) {
            return new Response('console.error("Website Widget nicht gefunden.");', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/javascript; charset=UTF-8']);
        }

        if (!$this->isDomainAllowed($widget, $request)) {
            return new Response('console.error("Website Widget fuer diese Domain nicht freigegeben.");', Response::HTTP_FORBIDDEN, ['Content-Type' => 'application/javascript; charset=UTF-8']);
        }

        $widgetUrl = $this->generateUrl('app_website_widget_show', ['embedKey' => $widget->getEmbedKey()], UrlGeneratorInterface::ABSOLUTE_URL);
        $initialHeight = 500;
        $script = <<<JS
(function () {
    var currentScript = document.currentScript;
    var target = document.getElementById('tuningfux-widget');
    if (!target && currentScript) {
        target = document.createElement('div');
        target.id = 'tuningfux-widget';
        currentScript.parentNode.insertBefore(target, currentScript);
    }
    if (!target) {
        return;
    }
    var iframe = document.createElement('iframe');
    iframe.src = '{$this->escapeJs($widgetUrl)}';
    iframe.width = '100%';
    iframe.height = '{$initialHeight}';
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('loading', 'lazy');
    iframe.style.border = '0';
    iframe.style.width = '100%';
    iframe.style.height = '{$initialHeight}px';
    iframe.style.minHeight = '{$initialHeight}px';
    iframe.style.display = 'block';
    iframe.style.background = 'transparent';
    var resizeIframe = function (height) {
        var nextHeight = Math.max(320, Math.ceil(Number(height) || 0));
        if (!nextHeight) {
            return;
        }

        iframe.height = String(nextHeight);
        iframe.style.height = nextHeight + 'px';
        iframe.style.minHeight = nextHeight + 'px';
    };
    window.addEventListener('message', function (event) {
        if (event.source !== iframe.contentWindow) {
            return;
        }

        var data = event.data || {};
        if (!data || data.type !== 'tuningfux-widget-height') {
            return;
        }

        resizeIframe(data.height);
    });
    iframe.addEventListener('load', function () {
        try {
            iframe.contentWindow.postMessage({ type: 'tuningfux-widget-height-request' }, '*');
        } catch (error) {
            // Cross-origin Zugriff ist hier erwartet; das Widget meldet sich selbst per postMessage.
        }
    });
    target.innerHTML = '';
    target.appendChild(iframe);
})();
JS;

        return new Response($script, Response::HTTP_OK, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    #[Route('/widget/{embedKey}', name: 'app_website_widget_show', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['GET'])]
    public function show(string $embedKey, Request $request): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof WebsiteWidgetConfig) {
            throw $this->createNotFoundException('Website Widget nicht gefunden.');
        }

        if (!$this->isDomainAllowed($widget, $request)) {
            return new Response('Website Widget fuer diese Domain nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        $apiBasePath = preg_replace(
            '#/types$#',
            '',
            $this->generateUrl('app_website_widget_api_types', ['embedKey' => $widget->getEmbedKey()])
        ) ?? '';

        $response = $this->render('website/vehicle_database.html.twig', [
            'widgetMode' => true,
            'websiteWidget' => $widget->getWidgetPayload(),
            'frontendSettings' => $widget->getFrontendSettingsPayload(),
            'vehicleDatabaseCssVersion' => $this->getVehicleDatabaseCssVersion(),
            'vehicleDatabaseApiBasePath' => $apiBasePath,
            'vehicleWidgetInquiryPath' => $this->generateUrl('app_website_widget_inquiry', ['embedKey' => $widget->getEmbedKey()]),
            'availableDataPools' => [],
            'currentDataPoolKey' => '',
        ]);
        $response->headers->set('X-Frame-Options', 'ALLOWALL');
        $response->headers->set('Content-Security-Policy', "frame-ancestors *");

        return $response;
    }

    #[Route('/widget/{embedKey}/anfrage', name: 'app_website_widget_inquiry', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['GET', 'POST'])]
    public function inquiryPage(string $embedKey, Request $request): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof WebsiteWidgetConfig) {
            throw $this->createNotFoundException('Website Widget nicht gefunden.');
        }

        if (!$this->isDomainAllowed($widget, $request)) {
            return new Response('Website Widget fuer diese Domain nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        $vehicle = $this->readVehicleFromRequest($request);
        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            $payload = $request->request->all();
            $payload['privacyAccepted'] = $request->request->getBoolean('privacyAccepted');
            $payload['vehicle'] = $vehicle;

            $validationError = $this->validateInquiryPayload($widget, $payload);
            $result = null === $validationError
                ? $this->sendInquiryMail($widget, $payload, $vehicle)
                : ['ok' => false, 'message' => $validationError];
            if (true === $result['ok']) {
                $redirect = $widget->getRedirectAfterSubmit();
                if (null !== $redirect) {
                    return $this->redirect($redirect);
                }
                $success = true;
            } else {
                $error = $result['message'];
            }
        }

        $response = $this->render('website/widget_inquiry.html.twig', [
            'widget' => $widget,
            'websiteWidget' => $widget->getWidgetPayload(),
            'vehicle' => $vehicle,
            'error' => $error,
            'success' => $success,
        ]);
        $response->headers->set('X-Frame-Options', 'ALLOWALL');
        $response->headers->set('Content-Security-Policy', "frame-ancestors *");

        return $response;
    }

    #[Route('/widget/{embedKey}/api/types', name: 'app_website_widget_api_types', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['GET'])]
    public function types(string $embedKey, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_types');
    }

    #[Route('/widget/{embedKey}/api/brands/{typeId}', name: 'app_website_widget_api_brands', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'typeId' => '\d+'], methods: ['GET'])]
    public function brands(string $embedKey, int $typeId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_brands', ['typeId' => $typeId]);
    }

    #[Route('/widget/{embedKey}/api/series/{brandId}', name: 'app_website_widget_api_series', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'brandId' => '\d+'], methods: ['GET'])]
    public function series(string $embedKey, int $brandId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_series', ['brandId' => $brandId]);
    }

    #[Route('/widget/{embedKey}/api/models/{seriesId}', name: 'app_website_widget_api_models', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'seriesId' => '\d+'], methods: ['GET'])]
    public function models(string $embedKey, int $seriesId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_models', ['seriesId' => $seriesId]);
    }

    #[Route('/widget/{embedKey}/api/engines/{modelId}', name: 'app_website_widget_api_engines', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'modelId' => '\d+'], methods: ['GET'])]
    public function engines(string $embedKey, int $modelId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_engines', ['modelId' => $modelId]);
    }

    #[Route('/widget/{embedKey}/api/engine-details/{engineId}', name: 'app_website_widget_api_engine_details', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'engineId' => '\d+'], methods: ['GET'])]
    public function engineDetails(string $embedKey, int $engineId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_engine_details', ['engineId' => $engineId]);
    }

    #[Route('/widget/{embedKey}/api/track', name: 'app_website_widget_api_track', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['POST'])]
    public function track(string $embedKey, Request $request): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof WebsiteWidgetConfig) {
            return $this->json(['message' => 'Website Widget nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        if (!$widget->isSendTrackingEvents()) {
            return $this->json(['ok' => true]);
        }

        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_track', [], 'POST');
    }

    #[Route('/widget/{embedKey}/api/inquiry', name: 'app_website_widget_api_inquiry', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['POST'])]
    public function inquiry(string $embedKey, Request $request): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof WebsiteWidgetConfig) {
            return $this->json(['message' => 'Website Widget nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->isDomainAllowed($widget, $request)) {
            return $this->json(['message' => 'Website Widget fuer diese Domain nicht freigegeben.'], Response::HTTP_FORBIDDEN);
        }

        $payload = $this->readInquiryPayload($request);
        if (null === $payload) {
            return $this->json(['message' => 'Ungueltige Anfrage.'], Response::HTTP_BAD_REQUEST);
        }

        $validationError = $this->validateInquiryPayload($widget, $payload);
        if (null !== $validationError) {
            return $this->json(['message' => $validationError], Response::HTTP_BAD_REQUEST);
        }

        $vehicle = \is_array($payload['vehicle'] ?? null) ? $payload['vehicle'] : [];
        $result = $this->sendInquiryMail($widget, $payload, $vehicle);
        if (true !== $result['ok']) {
            return $this->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'ok' => true,
            'successTitle' => $widget->getSuccessTitle(),
            'message' => $widget->getSuccessMessage() ?: $widget->getSuccessTitle(),
            'redirect' => $widget->getRedirectAfterSubmit(),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $vehicle
     * @return array{ok:bool,message:string}
     */
    private function sendInquiryMail(WebsiteWidgetConfig $widget, array $payload, array $vehicle): array
    {
        $recipient = trim((string) $widget->getResolvedRecipientEmail());
        if ('' === $recipient) {
            return ['ok' => false, 'message' => 'Es ist keine Empfaenger-E-Mail fuer das Widget hinterlegt.'];
        }

        $email = trim((string) ($payload['email'] ?? ''));
        $message = (new Email())
            ->from(new Address($this->mailerFrom, 'Tuningfux Website Widget'))
            ->to($recipient)
            ->subject('Neue Anfrage ueber Website Widget')
            ->text($this->buildInquiryTextBody($widget, $payload, $vehicle))
            ->html($this->buildInquiryHtmlBody($widget, $payload, $vehicle));

        if ('' !== $email) {
            $message->replyTo($email);
        }

        $this->mailer->send($message);

        return ['ok' => true, 'message' => ''];
    }

    private function proxyApiRequest(string $embedKey, Request $request, string $routeName, array $routeParameters = [], ?string $method = null): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof WebsiteWidgetConfig) {
            return $this->json(['message' => 'Website Widget nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->isDomainAllowed($widget, $request)) {
            return $this->json(['message' => 'Website Widget fuer diese Domain nicht freigegeben.'], Response::HTTP_FORBIDDEN);
        }

        $apiToken = $widget->getApiToken();
        if (null === $apiToken || !$apiToken->isActive() || !$apiToken->isGueltig()) {
            return $this->json(['message' => 'API-Key ist nicht aktiv oder abgelaufen.'], Response::HTTP_UNAUTHORIZED);
        }

        if ('api_vehicle_database_brands' === $routeName && !$this->isRequestedVehicleTypeAllowed($widget, $routeParameters['typeId'] ?? null)) {
            return $this->json(['data' => []]);
        }

        $query = $request->query->all();
        unset($query['poolKey']);
        $poolKey = $widget->getVehicleDataPool()?->getPoolKey();
        if (null !== $poolKey && '' !== trim($poolKey)) {
            $query['poolKey'] = $poolKey;
        }
        $url = $this->generateUrl($routeName, array_merge($routeParameters, $query), UrlGeneratorInterface::ABSOLUTE_URL);

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiToken->getApiToken(),
            'X-Token-Id' => (string) $apiToken->getTokenIdentifier(),
        ];
        if ('POST' === ($method ?? $request->getMethod())) {
            $headers['Content-Type'] = 'application/json';
        }

        try {
            $response = $this->httpClient->request($method ?? $request->getMethod(), $url, [
                'headers' => $headers,
                'body' => 'POST' === ($method ?? $request->getMethod()) ? $request->getContent() : null,
                'timeout' => 20,
                'max_duration' => 25,
            ]);
        } catch (TransportExceptionInterface $exception) {
            return $this->json(['message' => 'Widget-API konnte nicht erreicht werden: ' . $exception->getMessage()], Response::HTTP_BAD_GATEWAY);
        }

        $content = $response->getContent(false);
        $contentType = $response->getHeaders(false)['content-type'][0] ?? 'application/json';
        if (str_contains(strtolower($contentType), 'application/json')) {
            if ('api_vehicle_database_types' === $routeName) {
                $content = $this->filterTypesResponseForWidget($content, $widget);
            }
            $content = $this->absolutizeAssetUrlsInJson($content, $request);
        }

        return new Response($content, $response->getStatusCode(), [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-store',
        ]);
    }

    private function resolveWidget(string $embedKey): ?WebsiteWidgetConfig
    {
        return $this->widgetRepository->findActiveByEmbedKey($embedKey);
    }

    private function getVehicleDatabaseCssVersion(): string
    {
        $path = rtrim($this->projectDir, '/') . '/public/styles/vehicle_database.css';
        if (!is_file($path)) {
            return (string) time();
        }

        $hash = hash_file('sha1', $path);
        if (false === $hash) {
            $mtime = @filemtime($path);

            return false === $mtime ? (string) time() : (string) $mtime;
        }

        return substr($hash, 0, 12);
    }

    private function isRequestedVehicleTypeAllowed(WebsiteWidgetConfig $widget, mixed $typeId): bool
    {
        $vehicleType = $widget->getVehicleType();
        if (null === $vehicleType || null === $vehicleType->getId()) {
            return true;
        }

        return (int) $typeId === $vehicleType->getId();
    }

    private function filterTypesResponseForWidget(string $content, WebsiteWidgetConfig $widget): string
    {
        $vehicleType = $widget->getVehicleType();
        if (null === $vehicleType || null === $vehicleType->getId()) {
            return $content;
        }

        try {
            $payload = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $content;
        }

        if (!\is_array($payload) || !\is_array($payload['data'] ?? null)) {
            return $content;
        }

        $typeId = $vehicleType->getId();
        $payload['data'] = array_values(array_filter(
            $payload['data'],
            static fn (mixed $type): bool => \is_array($type) && (int) ($type['id'] ?? 0) === $typeId
        ));

        try {
            return json_encode($payload, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
        } catch (\JsonException) {
            return $content;
        }
    }

    private function isDomainAllowed(WebsiteWidgetConfig $widget, Request $request): bool
    {
        $patterns = $widget->getAllowedDomainPatterns();
        if ([] === $patterns) {
            return true;
        }

        $host = $this->extractRequestOriginHost($request);
        if (null === $host) {
            return true;
        }

        foreach ($patterns as $pattern) {
            if ($this->hostMatchesPattern($host, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function extractRequestOriginHost(Request $request): ?string
    {
        foreach (['Origin', 'Referer'] as $headerName) {
            $value = trim((string) $request->headers->get($headerName, ''));
            if ('' === $value) {
                continue;
            }

            $host = parse_url($value, \PHP_URL_HOST);
            if (is_string($host) && '' !== trim($host)) {
                return strtolower(trim($host));
            }
        }

        return null;
    }

    private function hostMatchesPattern(string $host, string $pattern): bool
    {
        $pattern = preg_replace('#^https?://#', '', strtolower(trim($pattern))) ?? '';
        $pattern = trim($pattern, " \t\n\r\0\x0B/");
        if ('' === $pattern) {
            return false;
        }

        if (str_starts_with($pattern, '*.')) {
            $suffix = substr($pattern, 2);

            return $host !== $suffix && str_ends_with($host, '.' . $suffix);
        }

        return $host === $pattern;
    }

    private function escapeJs(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateInquiryPayload(WebsiteWidgetConfig $widget, array $payload): ?string
    {
        $email = trim((string) ($payload['email'] ?? ''));
        $phone = trim((string) ($payload['phone'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));

        if ('' === $name) {
            return 'Bitte Namen angeben.';
        }

        if ($widget->isShowEmail() && $widget->isEmailRequired() && '' === $email) {
            return 'Bitte E-Mail angeben.';
        }

        if ($widget->isShowPhone() && $widget->isPhoneRequired() && '' === $phone) {
            return 'Bitte Telefon angeben.';
        }

        if ($widget->isPrivacyRequired() && true !== ($payload['privacyAccepted'] ?? false)) {
            return 'Bitte Datenschutz bestaetigen.';
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readInquiryPayload(Request $request): ?array
    {
        if (str_contains((string) $request->headers->get('Content-Type'), 'application/json')) {
            try {
                $payload = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return null;
            }

            return \is_array($payload) ? $payload : null;
        }

        $payload = $request->request->all();
        $payload['privacyAccepted'] = $request->request->getBoolean('privacyAccepted');
        $payload['vehicle'] = $this->readVehicleFromRequest($request);

        return $payload;
    }

    /**
     * @return array<string, string>
     */
    private function readVehicleFromRequest(Request $request): array
    {
        $source = $request->isMethod('POST') ? $request->request : $request->query;

        return [
            'type' => trim((string) $source->get('type', '')),
            'brand' => trim((string) $source->get('brand', '')),
            'series' => trim((string) $source->get('series', '')),
            'model' => trim((string) $source->get('model', '')),
            'engine' => trim((string) $source->get('engine', '')),
            'stage' => trim((string) $source->get('stage', '')),
            'options' => trim((string) $source->get('options', '')),
            'optionIds' => trim((string) $source->get('optionIds', '')),
            'engineId' => trim((string) $source->get('engineId', '')),
            'stageId' => trim((string) $source->get('stageId', '')),
        ];
    }

    private function absolutizeAssetUrlsInJson(string $content, Request $request): string
    {
        try {
            $payload = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $content;
        }

        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $normalized = $this->absolutizeAssetUrls($payload, $baseUrl);

        try {
            return json_encode($normalized, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
        } catch (\JsonException) {
            return $content;
        }
    }

    private function absolutizeAssetUrls(mixed $value, string $baseUrl): mixed
    {
        if (!\is_array($value)) {
            return $value;
        }

        $assetKeys = ['image', 'icon', 'logo', 'manufacturerLogo', 'glbModel'];
        foreach ($value as $key => $item) {
            if ('images' === $key && \is_array($item)) {
                $value[$key] = array_map(
                    fn (mixed $image): mixed => \is_string($image) ? $this->absolutizeAssetUrl($image, $baseUrl) : $image,
                    $item
                );
                continue;
            }

            if (\is_array($item)) {
                $value[$key] = $this->absolutizeAssetUrls($item, $baseUrl);
                continue;
            }

            if (!\is_string($key) || !\is_string($item) || '' === trim($item)) {
                continue;
            }

            if (\in_array($key, $assetKeys, true)) {
                $value[$key] = $this->absolutizeAssetUrl($item, $baseUrl);
            }
        }

        return $value;
    }

    private function absolutizeAssetUrl(string $url, string $baseUrl): string
    {
        $trimmed = trim($url);
        if ('' === $trimmed || preg_match('#^(?:https?:)?//#i', $trimmed) === 1 || str_contains($trimmed, '://')) {
            return $trimmed;
        }

        return $baseUrl . '/' . ltrim($trimmed, '/');
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $vehicle
     */
    private function buildInquiryTextBody(WebsiteWidgetConfig $widget, array $payload, array $vehicle): string
    {
        $lines = [
            'Website Widget Anfrage',
            'Widget: ' . ($widget->getTitle() ?? $widget->getEmbedKey()),
            'Kunde: ' . (string) $widget->getKunde(),
            '',
            'Fahrzeug:',
            'Typ: ' . (string) ($vehicle['type'] ?? '-'),
            'Marke: ' . (string) ($vehicle['brand'] ?? '-'),
            'Baureihe: ' . (string) ($vehicle['series'] ?? '-'),
            'Modell: ' . (string) ($vehicle['model'] ?? '-'),
            'Motor: ' . (string) ($vehicle['engine'] ?? '-'),
            'Stage: ' . (string) ($vehicle['stage'] ?? '-'),
            'Tuning-Optionen: ' . (string) ($vehicle['options'] ?: '-'),
            '',
            'Kontaktdaten:',
            'Name: ' . (string) ($payload['name'] ?? '-'),
        ];

        foreach ($this->buildConfiguredInquiryRows($widget, $payload) as $label => $value) {
            $lines[] = $label . ': ' . $value;
        }

        $message = trim((string) ($payload['message'] ?? ''));
        if ('' !== $message) {
            $lines[] = '';
            $lines[] = 'Nachricht:';
            $lines[] = $message;
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $vehicle
     */
    private function buildInquiryHtmlBody(WebsiteWidgetConfig $widget, array $payload, array $vehicle): string
    {
        $rows = [
            'Widget' => $widget->getTitle() ?? $widget->getEmbedKey(),
            'Kunde' => (string) $widget->getKunde(),
            'Fahrzeugtyp' => $vehicle['type'] ?? '-',
            'Marke' => $vehicle['brand'] ?? '-',
            'Baureihe' => $vehicle['series'] ?? '-',
            'Modell' => $vehicle['model'] ?? '-',
            'Motor' => $vehicle['engine'] ?? '-',
            'Stage' => $vehicle['stage'] ?? '-',
            'Tuning-Optionen' => $vehicle['options'] ?: '-',
            'Name' => $payload['name'] ?? '-',
        ] + $this->buildConfiguredInquiryRows($widget, $payload);

        $htmlRows = '';
        foreach ($rows as $label => $value) {
            $htmlRows .= sprintf(
                '<tr><th align="left" style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">%s</th><td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">%s</td></tr>',
                htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                nl2br(htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))
            );
        }

        $message = trim((string) ($payload['message'] ?? ''));
        if ('' !== $message) {
            $htmlRows .= sprintf(
                '<tr><th align="left" style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">Nachricht</th><td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">%s</td></tr>',
                nl2br(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))
            );
        }

        return sprintf(
            '<h2>Neue Website Widget Anfrage</h2><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;">%s</table>',
            $htmlRows
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string>
     */
    private function buildConfiguredInquiryRows(WebsiteWidgetConfig $widget, array $payload): array
    {
        $rows = [];
        if ($widget->isShowPhone()) {
            $rows['Telefon'] = (string) ($payload['phone'] ?? '-');
        }
        if ($widget->isShowEmail()) {
            $rows['E-Mail'] = (string) ($payload['email'] ?? '-');
        }
        if ($widget->isShowFinVin()) {
            $rows['FIN/VIN'] = (string) ($payload['finVin'] ?? '-');
        }
        if ($widget->isShowLicensePlate()) {
            $rows['Kennzeichen'] = (string) ($payload['licensePlate'] ?? '-');
        }
        if ($widget->isShowMileage()) {
            $rows['Kilometerstand'] = (string) ($payload['mileage'] ?? '-');
        }

        return $rows;
    }
}
