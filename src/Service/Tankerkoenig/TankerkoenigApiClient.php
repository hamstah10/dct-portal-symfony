<?php

namespace App\Service\Tankerkoenig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TankerkoenigApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(string:TANKERKOENIG_API_BASE)%')]
        private readonly string $apiBase,
        #[Autowire('%env(string:TANKERKOENIG_API_KEY)%')]
        private readonly string $apiKey,
        #[Autowire('%env(int:TANKERKOENIG_API_TIMEOUT)%')]
        private readonly int $timeout,
        #[Autowire('%env(string:TANKERKOENIG_API_USER_AGENT)%')]
        private readonly string $userAgent,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function findCheapestFuelPrice(float $latitude, float $longitude, string $fuelType, float $radius = 5.0): array
    {
        $type = $this->normalizeFuelType($fuelType);
        $url = rtrim($this->apiBase, '/') . '/list.php';

        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'lat' => $latitude,
                'lng' => $longitude,
                'rad' => min(25, max(1, $radius)),
                'sort' => 'price',
                'type' => $type,
                'apikey' => trim($this->apiKey),
            ],
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => $this->userAgent,
            ],
            'timeout' => max(1, $this->timeout),
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode >= 400) {
            throw new TankerkoenigApiException(sprintf('Tankerkoenig API error (%d).', $statusCode));
        }

        try {
            $payload = $response->toArray(false);
        } catch (ExceptionInterface) {
            throw new TankerkoenigApiException('Tankerkoenig API returned invalid JSON.');
        }

        if (!is_array($payload) || ($payload['ok'] ?? false) !== true) {
            $message = is_string($payload['message'] ?? null) ? $payload['message'] : 'Tankerkoenig API request failed.';
            throw new TankerkoenigApiException($message);
        }

        $stations = $payload['stations'] ?? [];
        if (!is_array($stations)) {
            $stations = [];
        }

        $openStations = array_values(array_filter($stations, static function (mixed $station): bool {
            return is_array($station)
                && ($station['isOpen'] ?? false) === true
                && is_numeric($station['price'] ?? null);
        }));

        if ([] === $openStations) {
            throw new TankerkoenigApiException('Keine offenen Tankstellen mit Preisen gefunden.');
        }

        /** @var array<string, mixed> $station */
        $station = $openStations[0];

        return [
            'fuelType' => $type,
            'price' => (float) $station['price'],
            'station' => [
                'id' => (string) ($station['id'] ?? ''),
                'name' => (string) ($station['name'] ?? ''),
                'brand' => (string) ($station['brand'] ?? ''),
                'street' => trim(sprintf(
                    '%s %s',
                    (string) ($station['street'] ?? ''),
                    (string) ($station['houseNumber'] ?? '')
                )),
                'place' => (string) ($station['place'] ?? ''),
                'postCode' => (string) ($station['postCode'] ?? ''),
                'distance' => (float) ($station['dist'] ?? 0),
                'isOpen' => true,
            ],
            'license' => (string) ($payload['license'] ?? 'CC BY 4.0 - https://creativecommons.tankerkoenig.de'),
            'source' => (string) ($payload['data'] ?? 'MTS-K'),
        ];
    }

    private function normalizeFuelType(string $fuelType): string
    {
        $normalized = strtolower(trim($fuelType));

        return match ($normalized) {
            'diesel' => 'diesel',
            'benzin', 'e5', 'super' => 'e5',
            default => throw new TankerkoenigApiException('Ungueltiger Kraftstofftyp.'),
        };
    }
}
