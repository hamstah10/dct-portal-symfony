<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Anzeige der Scraper-Ausgaben je Anbieter (olsx, dyno). Die Quelldaten werden einmal je Änderung in
 * eine Übersicht und eine Datei pro Marke aufgeteilt. Anfragen lesen nur die kleinen Dateien.
 */
final class ScraperDataController extends AbstractController
{
    private const TYPES = ['car', 'truck', 'moto', 'atv', 'agricultural', 'boat', 'jetski'];
    private const PROVIDERS = ['olsx', 'dyno', 'truck_chiptuningfiles', 'td'];

    /** Typen, für die die Tuning-Datenbank (TD) Ordner hat (data/output/tuning_datenbank/<typ>). */
    private const TD_TYPES = ['car'];

    /** Typen, für die truck_chiptuningfiles Ordner hat (data/output/truck_chiptuningfiles/<typ>). */
    private const TRUCK_CHIPTUNINGFILES_TYPES = ['car', 'truck', 'agricultural'];

    /** Version der Übersicht. Ändert sich das Format, wird neu aufgeteilt. */
    private const SCHEMA = 2;

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
    ) {
    }

    #[Route('/operations-api/scraper-data', name: 'superadmin_portal_scraper_data_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);
        [$provider, $type] = $this->resolveScope($request);

        $indexPath = $this->ensureViewer($provider, $type);

        return $this->json(['data' => json_decode((string) file_get_contents($indexPath), true, 512, \JSON_THROW_ON_ERROR)]);
    }

    #[Route('/operations-api/scraper-data/brand/{slug}', name: 'superadmin_portal_scraper_data_brand', requirements: ['slug' => '[a-z0-9][a-z0-9_-]*'], methods: ['GET'])]
    public function brand(string $slug, Request $request): JsonResponse
    {
        $this->assertAccess($request);
        [$provider, $type] = $this->resolveScope($request);
        if ('td' === $provider) {
            // Einzelne Marken der Tuning-Datenbank sind sehr groß (über 200 MB). Das Standardlimit reicht dafür nicht.
            ini_set('memory_limit', '3G');
        }

        $this->ensureViewer($provider, $type);
        $path = $this->viewerDir($provider, $type) . '/brands/' . $slug . '.json';
        if (!is_file($path)) {
            throw new NotFoundHttpException('Marke nicht gefunden.');
        }

        return $this->json(['data' => json_decode((string) file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR)]);
    }

    /** Stellt sicher, dass die Übersicht zur aktuellen Quelle passt. Gibt den Pfad der Übersicht zurück. */
    private function ensureViewer(string $provider, string $type): string
    {
        $source = $this->source($provider, $type);
        $sourceMtime = $this->sourceMtime($source);
        $viewerDir = $this->viewerDir($provider, $type);
        $indexPath = $viewerDir . '/index.json';

        if ($this->indexIsCurrent($indexPath, $source, $sourceMtime)) {
            return $indexPath;
        }

        // Sperre: parallele Anfragen warten, damit nicht zwei Aufteilungen gleichzeitig schreiben
        if (!is_dir($viewerDir)) {
            mkdir($viewerDir, 0775, true);
        }
        $lock = fopen($viewerDir . '/.lock', 'c');
        flock($lock, \LOCK_EX);
        try {
            if ($this->indexIsCurrent($indexPath, $source, $sourceMtime)) {
                return $indexPath;
            }

            $scraperDir = $this->getParameter('kernel.project_dir') . '/scrapy-remote-scraper';
            $process = new Process([
                $scraperDir . '/.venv/bin/python',
                $scraperDir . '/scripts/split_scraper_output.py',
                $source,
                $viewerDir,
            ]);
            $process->setTimeout(600);
            $process->mustRun();
        } finally {
            flock($lock, \LOCK_UN);
            fclose($lock);
        }

        return $indexPath;
    }

    private function indexIsCurrent(string $indexPath, string $source, int $sourceMtime): bool
    {
        if (!is_file($indexPath)) {
            return false;
        }
        $index = json_decode((string) file_get_contents($indexPath), true);

        return is_array($index)
            && ($index['schema'] ?? 0) === self::SCHEMA
            && ($index['source'] ?? null) === basename($source)
            && ($index['source_mtime'] ?? null) === $sourceMtime;
    }

    /** Quelle je Anbieter: olsx = neueste Datei je Typ, dyno = Ordner mit einer Datei je Marke. */
    private function source(string $provider, string $type): string
    {
        $base = $this->getParameter('kernel.project_dir') . '/scrapy-remote-scraper/data/output';

        if ('dyno' === $provider) {
            $dir = $base . '/dyno/dyno_chiptuningfiles';
            if (!is_dir($dir)) {
                throw new NotFoundHttpException('Keine Dyno-Daten gefunden.');
            }

            return $dir;
        }

        if ('td' === $provider) {
            $dir = $base . '/tuning_datenbank/' . $type;
            if (!is_dir($dir)) {
                throw new NotFoundHttpException('Keine Tuning-Datenbank-Daten gefunden.');
            }

            return $dir;
        }

        if ('truck_chiptuningfiles' === $provider) {
            $dir = $base . '/truck_chiptuningfiles/' . $type;
            if (!is_dir($dir)) {
                throw new NotFoundHttpException('Keine Truck-Chiptuningfiles-Daten für diesen Typ gefunden.');
            }

            return $dir;
        }

        $dir = $base . '/olsx_brands_spider/' . $type;
        $files = glob($dir . '/olsx_brands_' . $type . '_*.json') ?: [];
        $files = array_values(array_filter($files, static fn (string $f): bool => !str_contains(basename($f), '_partial')));
        if ([] === $files) {
            throw new NotFoundHttpException('Keine Scraper-Ausgabe für diesen Typ gefunden.');
        }
        usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return $files[0];
    }

    /** Änderungszeit der Quelle. Bei einem Ordner die jüngste Datei darin. */
    private function sourceMtime(string $source): int
    {
        if (!is_dir($source)) {
            return (int) filemtime($source);
        }

        $times = array_map(static fn (string $f): int => (int) filemtime($f), glob($source . '/*.json') ?: []);

        return [] === $times ? (int) filemtime($source) : max($times);
    }

    private function viewerDir(string $provider, string $type): string
    {
        $key = match ($provider) {
            'dyno' => 'dyno',
            'truck_chiptuningfiles' => 'truck_chiptuningfiles/' . $type,
            'td' => 'td',
            default => $type,
        };

        return $this->getParameter('kernel.project_dir') . '/scrapy-remote-scraper/data/viewer/' . $key;
    }

    /** @return array{0: string, 1: string} */
    private function resolveScope(Request $request): array
    {
        $provider = strtolower((string) $request->query->get('provider', 'olsx'));
        if (!in_array($provider, self::PROVIDERS, true)) {
            throw new NotFoundHttpException('Unbekannter Anbieter.');
        }

        $type = strtolower((string) $request->query->get('type', 'car'));
        if ('olsx' === $provider && !in_array($type, self::TYPES, true)) {
            throw new NotFoundHttpException('Unbekannter Typ.');
        }
        if ('td' === $provider && !in_array($type, self::TD_TYPES, true)) {
            throw new NotFoundHttpException('Für die Tuning-Datenbank gibt es nur den Typ Pkw.');
        }
        if ('truck_chiptuningfiles' === $provider && !in_array($type, self::TRUCK_CHIPTUNINGFILES_TYPES, true)) {
            throw new NotFoundHttpException('Unbekannter Typ.');
        }

        return [$provider, $type];
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
