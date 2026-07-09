<?php

declare(strict_types=1);

namespace App\Portals\No1tuningfiles\Service;

use App\Entity\Portal;
use App\Entity\VehicleEngine;
use App\Repository\VehicleEngineRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PortalHeaderSearchService
{
    public function __construct(
        private readonly VehicleEngineRepository $vehicleEngineRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param list<string> $enabledModules
     *
     * @return list<array{
     *     text:string,
     *     subtext:?string,
     *     url:string,
     *     group:string,
     *     meta:?string,
     *     keywords:string
     * }>
     */
    public function search(string $query, array $enabledModules = [], int $limit = 8): array
    {
        $query = trim($query);
        if ($this->length($query) < 2) {
            return [];
        }

        $results = [];
        $this->addPageResults($results, $query, $enabledModules);

        if (\count($results) < $limit) {
            $this->addVehicleResults($results, $query, $limit - \count($results));
        }

        return \array_slice($results, 0, $limit);
    }

    /**
     * @param list<string> $enabledModules
     */
    public function getDefaultSearchUrl(array $enabledModules = []): string
    {
        if ($this->isModuleEnabled(Portal::MODULE_VEHICLE_DATABASE, $enabledModules)) {
            return $this->urlGenerator->generate('cpt_portal_vehicles');
        }

        return $this->urlGenerator->generate('cpt_portal_dashboard');
    }

    /**
     * @param list<array{
     *     text:string,
     *     subtext:?string,
     *     url:string,
     *     group:string,
     *     meta:?string,
     *     keywords:string
     * }> $results
     * @param list<string> $enabledModules
     */
    private function addPageResults(array &$results, string $query, array $enabledModules): void
    {
        $pages = [
            [
                'module' => Portal::MODULE_DASHBOARD,
                'text' => 'Dashboard',
                'subtext' => 'Uebersicht und Startseite des Portals',
                'url' => $this->urlGenerator->generate('cpt_portal_dashboard'),
                'group' => 'Bereiche',
                'meta' => 'Seite',
                'keywords' => 'dashboard start home uebersicht portal',
            ],
            [
                'module' => Portal::MODULE_PROFILE,
                'text' => 'Profil',
                'subtext' => 'Persoenliche Daten und Konto bearbeiten',
                'url' => $this->urlGenerator->generate('cpt_portal_profile'),
                'group' => 'Bereiche',
                'meta' => 'Seite',
                'keywords' => 'profil konto benutzer daten email passwort',
            ],
            [
                'module' => Portal::MODULE_UPLOADS,
                'text' => 'Uploads',
                'subtext' => 'Dateien hochladen und Upload-Historie ansehen',
                'url' => $this->urlGenerator->generate('cpt_portal_uploads'),
                'group' => 'Bereiche',
                'meta' => 'Seite',
                'keywords' => 'upload datei dateien hochladen file files',
            ],
            [
                'module' => Portal::MODULE_CREDITS,
                'text' => 'Credits',
                'subtext' => 'Guthaben aufladen und Zahlungen ansehen',
                'url' => $this->urlGenerator->generate('cpt_portal_credits'),
                'group' => 'Bereiche',
                'meta' => 'Seite',
                'keywords' => 'credits guthaben paypal zahlung aufladen',
            ],
            [
                'module' => Portal::MODULE_VEHICLE_DATABASE,
                'text' => 'Fahrzeugdatenbank',
                'subtext' => 'Marke, Modell, Motor und Stages durchsuchen',
                'url' => $this->urlGenerator->generate('cpt_portal_vehicles'),
                'group' => 'Bereiche',
                'meta' => 'Seite',
                'keywords' => 'fahrzeugdatenbank fahrzeuge marke modell motor stage tuning',
            ],
        ];

        foreach ($pages as $page) {
            if (!$this->isModuleEnabled($page['module'], $enabledModules)) {
                continue;
            }

            if (!$this->matches($query, $page['text'], $page['subtext'], $page['keywords'])) {
                continue;
            }

            $results[] = $page;
        }
    }

    /**
     * @param list<array{
     *     text:string,
     *     subtext:?string,
     *     url:string,
     *     group:string,
     *     meta:?string,
     *     keywords:string
     * }> $results
     */
    private function addVehicleResults(array &$results, string $query, int $limit): void
    {
        if ($limit <= 0) {
            return;
        }

        $needle = '%' . $this->normalize($query) . '%';
        $items = $this->vehicleEngineRepository->createQueryBuilder('engine')
            ->select('engine', 'model', 'series', 'brand')
            ->leftJoin('engine.vehicleModel', 'model')->addSelect('model')
            ->leftJoin('model.vehicleSeries', 'series')->addSelect('series')
            ->leftJoin('series.vehicleBrand', 'brand')->addSelect('brand')
            ->andWhere('engine.active = true')
            ->andWhere('
                LOWER(COALESCE(engine.name, \'\')) LIKE :needle
                OR LOWER(COALESCE(engine.engineCode, \'\')) LIKE :needle
                OR LOWER(COALESCE(model.name, \'\')) LIKE :needle
                OR LOWER(COALESCE(series.name, \'\')) LIKE :needle
                OR LOWER(COALESCE(brand.name, \'\')) LIKE :needle
            ')
            ->setParameter('needle', $needle)
            ->orderBy('brand.name', 'ASC')
            ->addOrderBy('series.name', 'ASC')
            ->addOrderBy('model.name', 'ASC')
            ->addOrderBy('engine.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            if (!$item instanceof VehicleEngine) {
                continue;
            }

            $model = $item->getVehicleModel();
            $series = $model?->getVehicleSeries();
            $brand = $series?->getVehicleBrand();

            $text = trim(implode(' ', array_filter([
                $brand?->getName(),
                $series?->getName(),
                $model?->getName(),
            ], static fn (?string $value): bool => null !== $value && '' !== trim($value))));

            if ('' === $text) {
                $text = 'Fahrzeugdatenbank';
            }

            $subtextParts = array_filter([
                trim((string) $item->getName()),
                trim((string) ($item->getEngineCode() ?? '')),
            ], static fn (string $value): bool => '' !== $value);

            $results[] = [
                'text' => $text,
                'subtext' => $subtextParts ? implode(' | ', $subtextParts) : null,
                'url' => $this->urlGenerator->generate('cpt_portal_vehicles', [
                    'q' => $this->buildVehicleQuery($item),
                ]),
                'group' => 'Fahrzeugdatenbank',
                'meta' => 'Suche',
                'keywords' => implode(' ', array_filter([
                    $brand?->getName(),
                    $series?->getName(),
                    $model?->getName(),
                    $item->getName(),
                    $item->getEngineCode(),
                ], static fn (?string $value): bool => null !== $value && '' !== trim($value))),
            ];
        }
    }

    /**
     * @param list<string> $enabledModules
     */
    private function isModuleEnabled(string $module, array $enabledModules): bool
    {
        if ([] === $enabledModules) {
            return true;
        }

        return \in_array($module, $enabledModules, true);
    }

    private function matches(string $query, ?string ...$haystacks): bool
    {
        $normalizedQuery = $this->normalize($query);
        foreach ($haystacks as $haystack) {
            if (null === $haystack || '' === trim($haystack)) {
                continue;
            }

            if (str_contains($this->normalize($haystack), $normalizedQuery)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function length(string $value): int
    {
        return mb_strlen($value);
    }

    private function buildVehicleQuery(VehicleEngine $engine): string
    {
        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();

        $parts = array_filter([
            trim((string) $brand?->getName()),
            trim((string) $model?->getName()),
            trim((string) ($engine->getEngineCode() ?: $engine->getName())),
        ], static fn (string $value): bool => '' !== $value);

        return implode(' ', $parts);
    }
}
