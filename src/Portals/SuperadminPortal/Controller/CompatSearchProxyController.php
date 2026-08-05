<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Same-origin proxy to the shared Meilisearch instance for the
 * `toolscraper_compatibility` index (autotuner.com ECU/vehicle
 * compatibility mirror). The browser can’t call Meilisearch directly
 * from the portal, so the request is routed through Symfony instead.
 *
 * The target Meilisearch URL/key come from the same env vars used by the
 * rest of the app so we always hit the real search backend, not a stale
 * hardcoded host.
 */
final class CompatSearchProxyController extends AbstractController
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    #[Route('/api/compat-search/{path}', name: 'superadmin_portal_compat_search_proxy', requirements: ['path' => '.+'], methods: ['GET', 'POST'])]
    public function proxy(string $path, Request $request): Response
    {
        $meiliHost = rtrim((string) ($_ENV['MEILISEARCH_URL'] ?? ''), '/');
        $meiliKey = trim((string) ($_ENV['MEILISEARCH_KEY'] ?? ''));
        if ('' === $meiliHost || '' === $meiliKey) {
            return new JsonResponse([
                'error' => 'MEILISEARCH_URL oder MEILISEARCH_KEY fehlt.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $method = $request->getMethod();
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $meiliKey,
                'Content-Type' => 'application/json',
            ],
        ];

        if ($method === 'GET') {
            $options['query'] = $request->query->all();
        } else {
            $options['body'] = $request->getContent();
        }

        $response = $this->httpClient->request($method, $meiliHost . '/' . $path, $options);

        $headers = [];
        $responseHeaders = $response->getHeaders(false);
        if (isset($responseHeaders['content-type'][0])) {
            $headers['Content-Type'] = $responseHeaders['content-type'][0];
        }

        return new Response($response->getContent(false), $response->getStatusCode(), $headers);
    }
}
