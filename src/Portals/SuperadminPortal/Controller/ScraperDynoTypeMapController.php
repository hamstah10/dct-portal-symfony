<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use App\Service\Scraper\BrandContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Zuordnung der Dyno-Marken zu Fahrzeugtypen. Die Datei data/dyno_type_map.json wird beim Speichern
 * vollständig ersetzt, die Herkunft einer geänderten Marke wird auf "manual" gesetzt.
 */
final class ScraperDynoTypeMapController extends AbstractController
{
    /** Erlaubte Typen. null = offen, excluded = nicht vergleichen (z. B. Baumaschinen). */
    private const ALLOWED_TYPES = ['car', 'truck', 'moto', 'agricultural', 'atv', 'boat', 'jetski', 'excluded'];

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly BrandContentService $brandContent,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[Route('/operations-api/dyno-type-map', name: 'superadmin_portal_dyno_type_map_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        return $this->json(['data' => ['brands' => $this->read()['brands']]]);
    }

    /** Modelle und Motoren einer Dyno-Marke, damit die Zuordnung anhand der Inhalte entschieden werden kann. */
    #[Route('/operations-api/dyno-type-map/{slug}/content', name: 'superadmin_portal_dyno_type_map_content', requirements: ['slug' => '[a-z0-9][a-z0-9_-]*'], methods: ['GET'])]
    public function content(string $slug, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        return $this->json(['data' => $this->brandContent->content($slug)]);
    }

    #[Route('/operations-api/dyno-type-map/{slug}', name: 'superadmin_portal_dyno_type_map_update', requirements: ['slug' => '[a-z0-9][a-z0-9_-]*'], methods: ['PUT'])]
    public function update(string $slug, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $payload = json_decode((string) $request->getContent(), true);
        $type = is_array($payload) ? ($payload['type'] ?? null) : null;
        if (null !== $type && !in_array($type, self::ALLOWED_TYPES, true)) {
            throw new BadRequestHttpException('Unbekannter Typ.');
        }

        $map = $this->read();
        $found = false;
        foreach ($map['brands'] as $index => $brand) {
            if (($brand['brand_slug'] ?? null) !== $slug) {
                continue;
            }
            $map['brands'][$index]['type'] = $type;
            $map['brands'][$index]['source'] = 'manual';
            $found = true;
            break;
        }
        if (!$found) {
            throw new NotFoundHttpException('Marke nicht gefunden.');
        }

        $this->write($map);

        return $this->json(['data' => $map['brands'][$index]]);
    }

    /** @return array{brands: list<array<string, mixed>>} */
    private function read(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            throw new NotFoundHttpException('Zuordnungsdatei fehlt.');
        }

        return json_decode((string) file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR);
    }

    /** @param array{brands: list<array<string, mixed>>} $map */
    private function write(array $map): void
    {
        $path = $this->path();
        $tmp = $path . '.tmp';
        file_put_contents($tmp, json_encode($map, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE), \LOCK_EX);
        rename($tmp, $path);
    }

    private function path(): string
    {
        return $this->projectDir . '/scrapy-remote-scraper/data/dyno_type_map.json';
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
