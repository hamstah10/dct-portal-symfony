<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Suche im Meilisearch-Index scraper_engines. Der Schlüssel bleibt auf dem Server, der Browser spricht nur mit dieser Route.
 */
final class ScraperSearchController extends AbstractController
{
    private const INDEX = 'scraper_engines';
    private const PROVIDERS = ['olsx', 'dyno', 'truck_chiptuningfiles'];
    private const LIMIT = 50;
    private const MIN_QUERY_LENGTH = 2;

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%kernel.environment%')] private readonly string $environment,
        #[Autowire(env: 'MEILISEARCH_URL')] private readonly string $meiliUrl,
        #[Autowire(env: 'MEILISEARCH_KEY')] private readonly string $meiliKey,
    ) {
    }

    #[Route('/operations-api/scraper-search', name: 'superadmin_portal_scraper_search', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $query = trim((string) $request->query->get('q', ''));
        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return $this->json(['data' => ['hits' => [], 'total' => 0]]);
        }

        $body = [
            'q' => $query,
            // Alle eingegebenen Wörter müssen vorkommen. Sonst lässt Meilisearch Wörter weg (z. B. „50d“) und zeigt nur noch die Marke.
            'matchingStrategy' => 'all',
            'limit' => self::LIMIT,
            'attributesToRetrieve' => ['id', 'provider', 'type', 'brand', 'model', 'version', 'engine', 'fuel_type', 'stage_count', 'url'],
        ];

        $filters = [];
        $provider = (string) $request->query->get('provider', '');
        if (in_array($provider, self::PROVIDERS, true)) {
            $filters[] = 'provider = "' . $provider . '"';
        }
        $type = (string) $request->query->get('type', '');
        if (preg_match('/^[a-z_]{2,30}$/', $type)) {
            $filters[] = 'type = "' . $type . '"';
        }
        if ([] !== $filters) {
            $body['filter'] = implode(' AND ', $filters);
        }

        $response = $this->httpClient->request('POST', rtrim($this->meiliUrl, '/') . '/indexes/' . $this->indexName() . '/search', [
            'headers' => ['Authorization' => 'Bearer ' . $this->meiliKey],
            'json' => $body,
            'timeout' => 10,
        ]);

        if (200 !== $response->getStatusCode()) {
            return $this->json(['error' => 'Suche nicht verfügbar.'], 502);
        }

        $data = $response->toArray();

        return $this->json(['data' => [
            'hits' => $data['hits'] ?? [],
            'total' => $data['estimatedTotalHits'] ?? count($data['hits'] ?? []),
        ]]);
    }

    private function indexName(): string
    {
        return ('prod' === $this->environment ? 'prod_' : 'dev_') . self::INDEX;
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
