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

/**
 * Dyno-Daten je Fahrzeugtyp. Die Marken-Zuordnung kommt aus data/dyno_type_map.json. Marken mit Typ null
 * (offen) oder excluded werden nicht gezählt. Die Dyno-Marken-Dateien werden einzeln gelesen.
 */
final class ScraperDynoByTypeController extends AbstractController
{
    private const TYPES = ['car', 'truck', 'moto', 'agricultural', 'atv', 'boat', 'jetski'];

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[Route('/operations-api/dyno-by-type', name: 'superadmin_portal_dyno_by_type', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $root = $this->projectDir . '/scrapy-remote-scraper';
        $mapPath = $root . '/data/dyno_type_map.json';
        if (!is_file($mapPath)) {
            throw new NotFoundHttpException('Zuordnungsdatei fehlt.');
        }

        $map = json_decode((string) file_get_contents($mapPath), true, 512, \JSON_THROW_ON_ERROR);
        $perType = array_fill_keys(self::TYPES, null);
        foreach ($map['brands'] ?? [] as $entry) {
            $type = $entry['type'] ?? null;
            if (!is_string($type) || !array_key_exists($type, $perType)) {
                continue;
            }

            $brandFile = $root . '/data/output/dyno/dyno_chiptuningfiles/' . $entry['brand_slug'] . '.json';
            if (!is_file($brandFile)) {
                continue;
            }

            $perType[$type] ??= $this->emptyTotals();
            $this->addBrand($perType[$type], $entry, $brandFile);
        }

        $types = [];
        foreach ($perType as $type => $totals) {
            if (null === $totals) {
                continue;
            }
            $types[$type] = $this->finish($totals);
        }

        return $this->json(['data' => ['types' => $types]]);
    }

    /** @return array<string, mixed> */
    private function emptyTotals(): array
    {
        return [
            'brands' => [],
            'models' => 0,
            'versions' => 0,
            'engines' => 0,
            'controllers' => 0,
            'options' => 0,
            'stages' => 0,
            'engines_with_stages' => 0,
            'engines_with_options' => 0,
            'engines_with_controllers' => 0,
            'engines_with_tools' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $totals
     * @param array<string, mixed> $entry
     */
    private function addBrand(array &$totals, array $entry, string $brandFile): void
    {
        $brand = json_decode((string) file_get_contents($brandFile), true, 512, \JSON_THROW_ON_ERROR);
        $brandRow = [
            'slug' => (string) $entry['brand_slug'],
            'name' => (string) ($entry['brand_name'] ?? $brand['brand_name'] ?? $entry['brand_slug']),
            'logo' => isset($brand['brand_logo']) && '' !== $brand['brand_logo'] ? (string) $brand['brand_logo'] : null,
            'models' => 0,
            'versions' => 0,
            'engines' => 0,
            'controllers' => 0,
            'options' => 0,
            'stages' => 0,
        ];

        foreach ($brand['models'] ?? [] as $model) {
            ++$brandRow['models'];
            ++$totals['models'];
            foreach ($model['versions'] ?? [] as $version) {
                ++$brandRow['versions'];
                ++$totals['versions'];
                foreach ($version['engines'] ?? [] as $engine) {
                    ++$brandRow['engines'];
                    ++$totals['engines'];

                    $stages = count($engine['stages'] ?? []);
                    $brandRow['stages'] += $stages;
                    $totals['stages'] += $stages;

                    $controllers = count($engine['controllers'] ?? []);
                    $brandRow['controllers'] += $controllers;
                    $totals['controllers'] += $controllers;
                    $options = count($engine['options'] ?? []);
                    $brandRow['options'] += $options;
                    $totals['options'] += $options;

                    if ($stages > 0) {
                        ++$totals['engines_with_stages'];
                    }
                    if ($options > 0) {
                        ++$totals['engines_with_options'];
                    }
                    if ($controllers > 0) {
                        ++$totals['engines_with_controllers'];
                    }
                    if (count($engine['read_methods'] ?? []) > 0) {
                        ++$totals['engines_with_tools'];
                    }
                }
            }
        }

        $totals['brands'][] = $brandRow;
    }

    /**
     * @param array<string, mixed> $totals
     *
     * @return array<string, mixed>
     */
    private function finish(array $totals): array
    {
        $engines = max(1, $totals['engines']);
        $pct = static fn (int $value): float => round($value * 100 / $engines, 1);

        usort($totals['brands'], static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

        return [
            'brand_count' => count($totals['brands']),
            'totals' => [
                'model_count' => $totals['models'],
                'version_count' => $totals['versions'],
                'engine_count' => $totals['engines'],
                'controller_count' => $totals['controllers'],
                'option_count' => $totals['options'],
                'stage_count' => $totals['stages'],
            ],
            'stats' => [
                'coverage' => [
                    'engines' => $totals['engines'],
                    'with_stages_pct' => $pct($totals['engines_with_stages']),
                    'with_options_pct' => $pct($totals['engines_with_options']),
                    'with_controllers_pct' => $pct($totals['engines_with_controllers']),
                    'with_tools_pct' => $pct($totals['engines_with_tools']),
                ],
            ],
            'brands' => $totals['brands'],
            'date_from' => null,
            'date_to' => null,
        ];
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
