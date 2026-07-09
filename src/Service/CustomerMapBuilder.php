<?php

namespace App\Service;

use App\Entity\Kunde;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\Icon\Icon;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

final class CustomerMapBuilder
{
    private const CACHE_FILE = '/var/cache/customer_geocoding.json';
    private const FIRMA_MARKER_COLORS = [
        'TFD' => '#bd1e21',
        'CPT' => '#121212',
        'TF' => '#111931',
    ];
    private const DEFAULT_MARKER_COLOR = '#334155';
    private const DEFAULT_STADIA_STYLE = 'alidade_smooth';
    private const DEFAULT_STADIA_HOST = 'tiles.stadiamaps.com';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @param array<int, Kunde> $customers
     *
     * @return array{map: Map, markerCount: int, unresolvedCount: int}
     */
    public function build(array $customers): array
    {
        $cache = $this->loadCache();
        $cacheChanged = false;
        $markers = [];
        $unresolvedCount = 0;
        $markerIcon = Icon::ux('uil:map-pin')->width(32)->height(32);

        foreach ($customers as $customer) {
            $address = $this->buildAddress($customer);
            if (null === $address) {
                continue;
            }

            $cacheKey = sha1(mb_strtolower($address));
            $coordinates = $cache[$cacheKey] ?? null;

            if (!\is_array($coordinates) || !isset($coordinates['lat'], $coordinates['lng'])) {
                $coordinates = $this->geocode($address);
                if (null !== $coordinates) {
                    $cache[$cacheKey] = $coordinates;
                    $cacheChanged = true;
                }
            }

            if (null === $coordinates) {
                ++$unresolvedCount;
                continue;
            }

            $title = $customer->getFirmenname() ?: trim((string) ($customer->getVorname() . ' ' . $customer->getNachname()));
            if ('' === $title) {
                $title = 'Kunde #' . $customer->getId();
            }

            $info = new InfoWindow(
                headerContent: htmlspecialchars($title, \ENT_QUOTES, 'UTF-8'),
                content: htmlspecialchars($address, \ENT_QUOTES, 'UTF-8')
            );

            $markers[] = new Marker(
                position: new Point((float) $coordinates['lat'], (float) $coordinates['lng']),
                title: $title,
                infoWindow: $info,
                extra: [
                    'customerId' => (int) $customer->getId(),
                    'markerColor' => $this->resolveMarkerColor($customer),
                ],
                id: (string) $customer->getId(),
                icon: $markerIcon
            );
        }

        if ($cacheChanged) {
            $this->saveCache($cache);
        }

        $map = (new Map())
            ->center(new Point(51.1657, 10.4515))
            ->zoom(6);

        $leafletOptions = (new LeafletOptions())
            ->tileLayer(new TileLayer(
                url: $this->buildStadiaTileUrl(),
                attribution: $this->buildStadiaAttribution(),
                options: [
                    'maxZoom' => 20,
                ]
            ));
        $map->options($leafletOptions);

        foreach ($markers as $marker) {
            $map->addMarker($marker);
        }

        if ([] !== $markers) {
            $map->fitBoundsToMarkers(true);
        }

        return [
            'map' => $map,
            'markerCount' => \count($markers),
            'unresolvedCount' => $unresolvedCount,
        ];
    }

    private function resolveMarkerColor(Kunde $customer): string
    {
        $kuerzel = '';
        $firma = $customer->getFirma();
        if ($firma) {
            $kuerzel = strtoupper(trim((string) ($firma->getKuerzel() ?? '')));
        }

        if ('' === $kuerzel) {
            $kuerzel = strtoupper(trim((string) ($customer->getFirmenname() ?? '')));
        }

        return self::FIRMA_MARKER_COLORS[$kuerzel] ?? self::DEFAULT_MARKER_COLOR;
    }

    private function buildStadiaTileUrl(): string
    {
        $style = $this->getEnvValue('STADIA_MAPS_STYLE') ?: self::DEFAULT_STADIA_STYLE;
        $host = $this->getEnvValue('STADIA_MAPS_HOST') ?: self::DEFAULT_STADIA_HOST;
        $apiKey = $this->getEnvValue('STADIA_MAPS_API_KEY');

        $url = sprintf('https://%s/tiles/%s/{z}/{x}/{y}{r}.png', $host, $style);
        if ('' !== $apiKey) {
            $url .= '?api_key=' . rawurlencode($apiKey);
        }

        return $url;
    }

    private function buildStadiaAttribution(): string
    {
        $style = strtolower($this->getEnvValue('STADIA_MAPS_STYLE') ?: self::DEFAULT_STADIA_STYLE);

        $parts = [
            '&copy; <a href="https://stadiamaps.com/" target="_blank">Stadia Maps</a>',
        ];

        if (str_starts_with($style, 'stamen_')) {
            $parts[] = '&copy; <a href="https://stamen.com/" target="_blank">Stamen Design</a>';
        }

        $parts[] = '&copy; <a href="https://openmaptiles.org/" target="_blank">OpenMapTiles</a>';
        $parts[] = '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>';

        return implode(' ', $parts);
    }

    private function getEnvValue(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: '';

        return trim((string) $value);
    }

    private function buildAddress(Kunde $customer): ?string
    {
        $street = trim(sprintf('%s %s', (string) $customer->getStrasse(), (string) $customer->getHausnummer()));
        $cityLine = trim(sprintf('%s %s', (string) $customer->getPlz(), (string) $customer->getStadt()));
        $country = trim((string) ($customer->getLand() ?: 'Deutschland'));

        $parts = array_values(array_filter([$street, $cityLine, $country], static fn (string $value): bool => '' !== $value));
        if ([] === $parts) {
            return null;
        }

        return implode(', ', $parts);
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function geocode(string $address): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . rawurlencode($address);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: TFD-CRM/1.0 (admin map)\r\nAccept: application/json\r\n",
                'timeout' => 5,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if (false === $response || '' === $response) {
            return null;
        }

        try {
            $json = json_decode($response, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!\is_array($json) || !isset($json[0]['lat'], $json[0]['lon'])) {
            return null;
        }

        return [
            'lat' => (float) $json[0]['lat'],
            'lng' => (float) $json[0]['lon'],
        ];
    }

    /**
     * @return array<string, array{lat: float, lng: float}>
     */
    private function loadCache(): array
    {
        $path = $this->projectDir . self::CACHE_FILE;
        if (!is_file($path)) {
            return [];
        }

        $content = @file_get_contents($path);
        if (false === $content || '' === $content) {
            return [];
        }

        try {
            $data = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return \is_array($data) ? $data : [];
    }

    /**
     * @param array<string, array{lat: float, lng: float}> $cache
     */
    private function saveCache(array $cache): void
    {
        $path = $this->projectDir . self::CACHE_FILE;
        $dir = \dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        @file_put_contents($path, json_encode($cache, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE));
    }
}
