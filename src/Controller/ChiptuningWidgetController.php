<?php

namespace App\Controller;

use App\Entity\ChiptuningWidgetConfig;
use App\Repository\ChiptuningWidgetConfigRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ChiptuningWidgetController extends AbstractController
{
    public function __construct(
        private readonly ChiptuningWidgetConfigRepository $widgetRepository,
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    #[Route('/chiptuning-widget/{embedKey}/embed', name: 'app_chiptuning_widget_embed_js', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['GET'])]
    public function embedScript(string $embedKey, Request $request): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof ChiptuningWidgetConfig) {
            return new Response('console.error("Chiptuning Widget nicht gefunden.");', Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/javascript; charset=UTF-8']);
        }

        if (!$this->isDomainAllowed($widget, $request)) {
            return new Response('console.error("Chiptuning Widget fuer diese Domain nicht freigegeben.");', Response::HTTP_FORBIDDEN, ['Content-Type' => 'application/javascript; charset=UTF-8']);
        }

        $widgetUrl = $this->generateUrl('app_chiptuning_widget_show', ['embedKey' => $widget->getEmbedKey()], UrlGeneratorInterface::ABSOLUTE_URL);
        $initialHeight = 500;
        $script = <<<JS
(function () {
    var currentScript = document.currentScript;
    var target = document.getElementById('chiptuning-widget');
    if (!target && currentScript) {
        target = document.createElement('div');
        target.id = 'chiptuning-widget';
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

    #[Route('/chiptuning-widget/{embedKey}', name: 'app_chiptuning_widget_show', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['GET'])]
    public function show(string $embedKey, Request $request): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof ChiptuningWidgetConfig) {
            throw $this->createNotFoundException('Chiptuning Widget nicht gefunden.');
        }

        if (!$this->isDomainAllowed($widget, $request)) {
            return new Response('Chiptuning Widget fuer diese Domain nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        $apiBasePath = preg_replace(
            '#/types$#',
            '',
            $this->generateUrl('app_chiptuning_widget_api_types', ['embedKey' => $widget->getEmbedKey()])
        ) ?? '';

        $response = $this->render('portal/superadmin_portal/_react_host.html.twig', [
            'title' => $widget->getTitle() ?? 'Chiptuning Widget',
            'reactBootstrap' => [
                'portal' => [
                    'key' => 'chiptuning_widget',
                    'name' => $widget->getTitle() ?? 'Chiptuning Widget',
                    'type' => 'widget',
                    'enabledModules' => [],
                    'logoPath' => null,
                ],
                'portalDesign' => [
                    'themePreset' => 'automotive',
                    'primaryColor' => '#bd1e23',
                    'secondaryColor' => '#111111',
                    'menuBackgroundColor' => '#111111',
                    'contentBackgroundColor' => '#161616',
                ],
                'navigation' => [],
                'operationsNavigation' => [],
                'dashboard' => [
                    'title' => $widget->getTitle() ?? 'Chiptuning Widget',
                    'description' => $widget->getSubtitle(),
                ],
            ],
            'chiptuningWidgetBootstrap' => [
                'embedKey' => $widget->getEmbedKey(),
                'title' => $widget->getTitle(),
                'subtitle' => $widget->getSubtitle(),
                'apiBasePath' => $apiBasePath,
                'contextKey' => $widget->getApiToken()?->getVehicleDataContext()?->getContextKey(),
                'skin' => 'tfd-download',
            ],
        ]);
        $response->headers->set('X-Frame-Options', 'ALLOWALL');
        $response->headers->set('Content-Security-Policy', 'frame-ancestors *');

        return $response;
    }

    #[Route('/chiptuning-widget/{embedKey}/api/types', name: 'app_chiptuning_widget_api_types', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['GET'])]
    public function types(string $embedKey, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_types');
    }

    #[Route('/chiptuning-widget/{embedKey}/api/brands/{typeId}', name: 'app_chiptuning_widget_api_brands', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'typeId' => '\d+'], methods: ['GET'])]
    public function brands(string $embedKey, int $typeId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_brands', ['typeId' => $typeId]);
    }

    #[Route('/chiptuning-widget/{embedKey}/api/series/{brandId}', name: 'app_chiptuning_widget_api_series', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'brandId' => '\d+'], methods: ['GET'])]
    public function series(string $embedKey, int $brandId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_series', ['brandId' => $brandId]);
    }

    #[Route('/chiptuning-widget/{embedKey}/api/models/{seriesId}', name: 'app_chiptuning_widget_api_models', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'seriesId' => '\d+'], methods: ['GET'])]
    public function models(string $embedKey, int $seriesId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_models', ['seriesId' => $seriesId]);
    }

    #[Route('/chiptuning-widget/{embedKey}/api/engines/{modelId}', name: 'app_chiptuning_widget_api_engines', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'modelId' => '\d+'], methods: ['GET'])]
    public function engines(string $embedKey, int $modelId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_engines', ['modelId' => $modelId]);
    }

    #[Route('/chiptuning-widget/{embedKey}/api/engine-details/{engineId}', name: 'app_chiptuning_widget_api_engine_details', requirements: ['embedKey' => '[A-Za-z0-9_-]+', 'engineId' => '\d+'], methods: ['GET'])]
    public function engineDetails(string $embedKey, int $engineId, Request $request): Response
    {
        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_engine_details', ['engineId' => $engineId]);
    }

    #[Route('/chiptuning-widget/{embedKey}/api/track', name: 'app_chiptuning_widget_api_track', requirements: ['embedKey' => '[A-Za-z0-9_-]+'], methods: ['POST'])]
    public function track(string $embedKey, Request $request): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof ChiptuningWidgetConfig) {
            return $this->json(['message' => 'Chiptuning Widget nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        if (!$widget->isSendTrackingEvents()) {
            return $this->json(['ok' => true]);
        }

        return $this->proxyApiRequest($embedKey, $request, 'api_vehicle_database_track', [], 'POST');
    }

    private function proxyApiRequest(string $embedKey, Request $request, string $routeName, array $routeParameters = [], ?string $method = null): Response
    {
        $widget = $this->resolveWidget($embedKey);
        if (!$widget instanceof ChiptuningWidgetConfig) {
            return $this->json(['message' => 'Chiptuning Widget nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->isDomainAllowed($widget, $request)) {
            return $this->json(['message' => 'Chiptuning Widget fuer diese Domain nicht freigegeben.'], Response::HTTP_FORBIDDEN);
        }

        $apiToken = $widget->getApiToken();
        if (null === $apiToken || !$apiToken->isActive() || !$apiToken->isGueltig()) {
            return $this->json(['message' => 'API-Key ist nicht aktiv oder abgelaufen.'], Response::HTTP_UNAUTHORIZED);
        }

        if ('api_vehicle_database_brands' === $routeName && !$this->isRequestedVehicleTypeAllowed($widget, $routeParameters['typeId'] ?? null)) {
            return $this->json(['data' => []]);
        }

        $query = $request->query->all();
        unset($query['poolKey'], $query['contextKey']);
        $contextKey = $apiToken->getVehicleDataContext()?->getContextKey();
        if (null !== $contextKey && '' !== trim($contextKey)) {
            $query['contextKey'] = $contextKey;
        }

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

    private function resolveWidget(string $embedKey): ?ChiptuningWidgetConfig
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

    private function isRequestedVehicleTypeAllowed(ChiptuningWidgetConfig $widget, mixed $typeId): bool
    {
        $vehicleType = $widget->getVehicleType();
        if (null === $vehicleType || null === $vehicleType->getId()) {
            return true;
        }

        return (int) $typeId === $vehicleType->getId();
    }

    private function filterTypesResponseForWidget(string $content, ChiptuningWidgetConfig $widget): string
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

    private function isDomainAllowed(ChiptuningWidgetConfig $widget, Request $request): bool
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
}
