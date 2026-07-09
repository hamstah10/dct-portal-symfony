<?php

namespace App\Service;

use App\Entity\VinDecoderCache;
use App\Repository\VinDecoderCacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class VinDecoderService
{
    /**
     * Transliteration table for VIN check digit calculation.
     *
     * @var array<string, int>
     */
    private const TRANSLITERATION = [
        'A' => 1,
        'B' => 2,
        'C' => 3,
        'D' => 4,
        'E' => 5,
        'F' => 6,
        'G' => 7,
        'H' => 8,
        'J' => 1,
        'K' => 2,
        'L' => 3,
        'M' => 4,
        'N' => 5,
        'P' => 7,
        'R' => 9,
        'S' => 2,
        'T' => 3,
        'U' => 4,
        'V' => 5,
        'W' => 6,
        'X' => 7,
        'Y' => 8,
        'Z' => 9,
        '0' => 0,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
    ];

    /**
     * VIN position weights for check digit calculation.
     *
     * @var array<int, int>
     */
    private const WEIGHTS = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2];

    /**
     * Base model year decoding (1980-2009). Add 30-year cycles as needed.
     *
     * @var array<string, int>
     */
    private const MODEL_YEAR_CODES = [
        'A' => 1980,
        'B' => 1981,
        'C' => 1982,
        'D' => 1983,
        'E' => 1984,
        'F' => 1985,
        'G' => 1986,
        'H' => 1987,
        'J' => 1988,
        'K' => 1989,
        'L' => 1990,
        'M' => 1991,
        'N' => 1992,
        'P' => 1993,
        'R' => 1994,
        'S' => 1995,
        'T' => 1996,
        'V' => 1997,
        'W' => 1998,
        'X' => 1999,
        'Y' => 2000,
        '1' => 2001,
        '2' => 2002,
        '3' => 2003,
        '4' => 2004,
        '5' => 2005,
        '6' => 2006,
        '7' => 2007,
        '8' => 2008,
        '9' => 2009,
    ];

    /**
     * Partial WMI manufacturer list (offline, extendable).
     *
     * @var array<string, string>
     */
    private const DEFAULT_WMI_MANUFACTURERS = [
        'WAU' => 'Audi',
        'WUA' => 'Audi Sport',
        'WBA' => 'BMW',
        'WBS' => 'BMW M',
        'WBY' => 'BMW i',
        'WDB' => 'Mercedes-Benz',
        'WDC' => 'Mercedes-Benz',
        'WDD' => 'Mercedes-Benz',
        'WDF' => 'Mercedes-Benz',
        'W1K' => 'Mercedes-Benz',
        'W1N' => 'Mercedes-Benz',
        'WP0' => 'Porsche',
        'WVW' => 'Volkswagen',
        'WVG' => 'Volkswagen',
        'WV1' => 'Volkswagen Nutzfahrzeuge',
        'WV2' => 'Volkswagen Nutzfahrzeuge',
        'W0L' => 'Opel/Vauxhall',
        'W0V' => 'Opel/Vauxhall',
        'WME' => 'smart',
        'WMW' => 'MINI',
        'SAJ' => 'Jaguar',
        'SAL' => 'Land Rover',
        'SCB' => 'Bentley',
        'SCC' => 'Lotus',
        'VF1' => 'Renault',
        'VF3' => 'Peugeot',
        'VF7' => 'Citroën',
        'VSS' => 'SEAT',
        'ZFA' => 'Fiat',
        'ZFF' => 'Ferrari',
        'ZAM' => 'Maserati',
        'ZHW' => 'Lamborghini',
        'YV1' => 'Volvo',
        'YV4' => 'Volvo',
        'YS3' => 'Saab',
        'JHM' => 'Honda',
        'JH4' => 'Acura',
        'JTD' => 'Toyota',
        'JTE' => 'Toyota',
        'JT2' => 'Toyota',
        'JMB' => 'Mitsubishi',
        'JN1' => 'Nissan',
        'JN8' => 'Nissan',
        'JF1' => 'Subaru',
        'JF2' => 'Subaru',
        'JM1' => 'Mazda',
        'JM3' => 'Mazda',
        'JS2' => 'Suzuki',
        'JS3' => 'Suzuki',
        'JSA' => 'Suzuki',
        'KMH' => 'Hyundai',
        'KNA' => 'Kia',
        'KNM' => 'Renault Samsung',
        '1FA' => 'Ford',
        '1FB' => 'Ford',
        '1FC' => 'Ford',
        '1FD' => 'Ford',
        '1FM' => 'Ford',
        '1FT' => 'Ford',
        '1G1' => 'Chevrolet',
        '1G2' => 'Pontiac',
        '1G3' => 'Oldsmobile',
        '1G4' => 'Buick',
        '1G6' => 'Cadillac',
        '1GC' => 'Chevrolet Truck',
        '1GT' => 'GMC Truck',
        '1HG' => 'Honda',
        '1HJ' => 'Honda',
        '1J4' => 'Jeep',
        '1J8' => 'Jeep',
        '1LN' => 'Lincoln',
        '1N4' => 'Nissan',
        '1N6' => 'Nissan',
        '2HG' => 'Honda (Canada)',
        '2T1' => 'Toyota (Canada)',
        '3FA' => 'Ford (Mexico)',
        '3HG' => 'Honda (Mexico)',
        '3N1' => 'Nissan (Mexico)',
        '3VW' => 'Volkswagen (Mexico)',
        '5YJ' => 'Tesla',
    ];

    private const VPIC_CACHE_KEY = '__vpic';

    /**
     * @var array<string, string>
     */
    private const VPIC_FIELD_MAP = [
        'Make' => 'make',
        'Model' => 'model',
        'ModelYear' => 'modelYear',
        'VehicleType' => 'vehicleType',
        'BodyClass' => 'bodyClass',
        'Series' => 'series',
        'DriveType' => 'driveType',
        'Doors' => 'doors',
        'SeatRows' => 'seatRows',
        'FuelTypePrimary' => 'fuelTypePrimary',
        'EngineCylinders' => 'engineCylinders',
        'EngineModel' => 'engineModel',
        'DisplacementCC' => 'displacementCc',
        'DisplacementL' => 'displacementL',
        'TransmissionStyle' => 'transmissionStyle',
        'TransmissionSpeeds' => 'transmissionSpeeds',
        'PlantCountry' => 'plantCountry',
        'PlantState' => 'plantState',
        'PlantCity' => 'plantCity',
        'Manufacturer' => 'manufacturer',
        'GVWR' => 'gvwr',
        'Turbo' => 'turbo',
        'ElectrificationLevel' => 'electrificationLevel',
        'BatteryType' => 'batteryType',
        'OtherEngineInfo' => 'otherEngineInfo',
    ];

    /**
     * @var array<string, string>
     */
    private array $manufacturers;

    /**
     * @var array<string, array<string, array<string, string>>>
     */
    private array $plantCodes;

    /**
     * @var array<string, array<int, array{pattern:string,label:string,description:?string,type:string,data:?array}>>
     */
    private array $vdsRules;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $vinOverrides;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly VinDecoderCacheRepository $vinDecoderCacheRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%vin_decoder.api_base%')]
        private readonly string $apiBase,
        #[Autowire('%vin_decoder.api_key%')]
        private readonly string $apiKey,
        #[Autowire('%vin_decoder.api_secret%')]
        private readonly string $apiSecret,
        #[Autowire('%vin_decoder.api_timeout%')]
        private readonly int $apiTimeout,
        #[Autowire('%vin_decoder.vpic_base%')]
        private readonly string $vpicBase,
        #[Autowire('%vin_decoder.vpic_timeout%')]
        private readonly int $vpicTimeout,
        #[Autowire('%vin_decoder.manufacturers%')]
        private readonly array $manufacturerOverrides = [],
        #[Autowire('%vin_decoder.plant_codes%')]
        private readonly array $plantCodesInput = [],
        #[Autowire('%vin_decoder.vds_rules%')]
        private readonly array $vdsRulesInput = [],
        #[Autowire('%vin_decoder.vin_overrides%')]
        private readonly array $vinOverridesInput = [],
    ) {
        $this->manufacturers = $this->normalizeManufacturerMap(self::DEFAULT_WMI_MANUFACTURERS);
        if ([] !== $this->manufacturerOverrides) {
            $this->manufacturers = array_replace(
                $this->manufacturers,
                $this->normalizeManufacturerMap($this->manufacturerOverrides)
            );
        }

        $this->plantCodes = $this->normalizePlantCodes($this->plantCodesInput);
        $this->vdsRules = $this->normalizeVdsRules($this->vdsRulesInput);
        $this->vinOverrides = $this->normalizeVinOverrides($this->vinOverridesInput);
    }

    /**
     * @return array{
     *     raw:string,
     *     normalized:string,
     *     length:int,
     *     isLengthValid:bool,
     *     invalidPositions:array<int, array{position:int, char:string}>,
     *     region:?string,
     *     country:?string,
     *     manufacturer:?string,
     *     wmi:?string,
     *     vds:?string,
     *     vis:?string,
     *     checkDigit:?string,
     *     checkDigitComputed:?string,
     *     checkDigitValid:?bool,
     *     checkDigitRequired:?bool,
     *     checkDigitSum:?int,
     *     checkDigitRemainder:?int,
     *     modelYearCode:?string,
     *     modelYear:?int,
     *     modelYearCandidates:array<int, int>,
     *     plantCode:?string,
     *     plantName:?string,
     *     plantInfo:?array<string, string>,
     *     plantSource:?string,
     *     serialNumber:?string,
     *     vdsMatches:array<int, array{label:string,description:?string,pattern:string,type:string,data:?array}>,
     *     vdsRulesAvailable:int,
     *     vinOverride:?array<string, mixed>,
     *     vpic:?array<string, mixed>,
     *     errors:array<int, string>,
     *     warnings:array<int, string>
     * }
     */
    public function decode(string $input): array
    {
        $raw = trim($input);
        $normalized = strtoupper((string) preg_replace('/[\\s-]+/', '', $raw));
        $length = strlen($normalized);

        $invalidPositions = [];
        for ($i = 0; $i < $length; ++$i) {
            $char = $normalized[$i];
            if (!$this->isValidVinChar($char)) {
                $invalidPositions[] = ['position' => $i + 1, 'char' => $char];
            }
        }

        $errors = [];
        $warnings = [];
        if ('' === $normalized) {
            $errors[] = 'VIN fehlt.';
        }
        if (0 !== $length && 17 !== $length) {
            $errors[] = sprintf('VIN muss 17 Zeichen haben (aktuell %d).', $length);
        }
        if ([] !== $invalidPositions) {
            $errors[] = 'VIN enthält ungültige Zeichen (I, O, Q oder Sonderzeichen).';
        }

        $wmi = $length >= 1 ? substr($normalized, 0, min(3, $length)) : null;
        $vds = $length > 3 ? substr($normalized, 3, min(5, $length - 3)) : null;
        $checkDigit = $length >= 9 ? $normalized[8] : null;
        $modelYearCode = $length >= 10 ? $normalized[9] : null;
        $plantCode = $length >= 11 ? $normalized[10] : null;
        $serialNumber = $length >= 12 ? substr($normalized, 11, min(6, $length - 11)) : null;
        $vis = $length > 9 ? substr($normalized, 9, min(8, $length - 9)) : null;
        $vinOverride = $this->vinOverrides[$normalized] ?? null;
        $vinOverride = is_array($vinOverride) ? $vinOverride : [];
        $apiPayload = null;
        if (17 === $length && [] === $invalidPositions) {
            $apiPayload = $this->loadCachedVinData($normalized);
            if (!$this->hasVincarioPayload($apiPayload)) {
                $apiData = $this->fetchVinApiData($normalized);
                if (is_array($apiData)) {
                    $this->storeVinCache($normalized, $apiData);
                    $apiPayload = $this->mergePayload($apiPayload, $apiData);
                }
            }
            $apiOverride = $this->mapVinApiData($apiPayload);
            if ([] !== $apiOverride) {
                $vinOverride = array_replace($vinOverride, $apiOverride);
            }
        }
        if ([] === $vinOverride) {
            $vinOverride = null;
        }

        $region = null;
        $country = null;
        $checkDigitRequired = null;
        if ($length >= 1) {
            [$region, $country] = $this->resolveRegionCountry($normalized[0]);
            $checkDigitRequired = $this->isCheckDigitRequired($normalized[0]);
        }

        $manufacturer = null;
        if ($length >= 3 && null !== $wmi && 3 === strlen($wmi)) {
            $manufacturer = $this->manufacturers[$wmi] ?? null;
        }
        if (is_array($vinOverride) && isset($vinOverride['manufacturer'])) {
            $overrideManufacturer = trim((string) $vinOverride['manufacturer']);
            if ('' !== $overrideManufacturer) {
                $manufacturer = $overrideManufacturer;
            }
        }

        $plantName = null;
        $plantInfo = null;
        $plantSource = null;
        if (null !== $plantCode && null !== $wmi) {
            $plantLookup = $this->lookupByWmiPrefix($this->plantCodes, $wmi);
            if (null !== $plantLookup) {
                $plantSource = $plantLookup['key'];
                $plants = $plantLookup['value'];
                $plantEntry = $plants[$plantCode] ?? null;
                if (\is_array($plantEntry)) {
                    $plantName = (string) ($plantEntry['name'] ?? '');
                    if ('' === $plantName && isset($plantEntry['location'])) {
                        $plantName = (string) $plantEntry['location'];
                    }
                    $plantInfo = $plantEntry;
                } elseif (is_string($plantEntry)) {
                    $plantName = $plantEntry;
                }
            }
        }
        if ('' === $plantName) {
            $plantName = null;
        }

        $vdsMatches = [];
        $vdsRulesAvailable = 0;
        if (null !== $vds && null !== $wmi) {
            $rulesLookup = $this->lookupByWmiPrefix($this->vdsRules, $wmi);
            if (null !== $rulesLookup) {
                $rules = $rulesLookup['value'];
                $vdsRulesAvailable = \count($rules);
                foreach ($rules as $rule) {
                    $pattern = $rule['pattern'];
                    $type = $rule['type'];
                    $matched = false;

                    if ('prefix' === $type) {
                        $matched = str_starts_with($vds, $pattern);
                    } else {
                        $regex = $this->buildRegex($pattern);
                        if ('' !== $regex) {
                            $matchResult = @preg_match($regex, $vds);
                            if (false === $matchResult) {
                                $warnings[] = sprintf('Ungültiges VDS-RegEx: %s', $pattern);
                                continue;
                            }
                            $matched = 1 === $matchResult;
                        }
                    }

                    if ($matched) {
                        $vdsMatches[] = [
                            'label' => $rule['label'],
                            'description' => $rule['description'],
                            'pattern' => $pattern,
                            'type' => $type,
                            'data' => $rule['data'],
                        ];
                    }
                }
            }
        }

        $checkDigitComputed = null;
        $checkDigitValid = null;
        $checkDigitSum = null;
        $checkDigitRemainder = null;
        if (17 === $length && [] === $invalidPositions) {
            $check = $this->computeCheckDigit($normalized);
            if (null !== $check) {
                $checkDigitComputed = $check['computed'];
                $checkDigitSum = $check['sum'];
                $checkDigitRemainder = $check['remainder'];
                $checkDigitValid = null !== $checkDigit ? $checkDigit === $checkDigitComputed : null;
            }
        } elseif (17 === $length && [] !== $invalidPositions) {
            $warnings[] = 'Prüfziffer konnte wegen ungültiger Zeichen nicht berechnet werden.';
        }

        if (is_array($vinOverride)) {
            $overrideValidity = strtolower(trim((string) ($vinOverride['checkDigitValidity'] ?? '')));
            if ('' !== $overrideValidity) {
                if (in_array($overrideValidity, ['valid', 'ok', 'true', 'yes'], true)) {
                    $checkDigitValid = true;
                } elseif (in_array($overrideValidity, ['invalid', 'false', 'no'], true)) {
                    $checkDigitValid = false;
                }
            }
            if (isset($vinOverride['checkDigitComputed']) && null !== $vinOverride['checkDigitComputed']) {
                $checkDigitComputed = (string) $vinOverride['checkDigitComputed'];
            }
        }

        if (false === $checkDigitValid && false === $checkDigitRequired) {
            $checkDigitValid = null;
        }

        if (null === $checkDigitValid && 17 === $length && [] === $invalidPositions && true === $checkDigitRequired) {
            $warnings[] = 'Prüfziffer konnte nicht geprüft werden.';
        } elseif (false === $checkDigitValid && true === $checkDigitRequired) {
            $warnings[] = 'Prüfziffer ungültig (in Nordamerika vorgeschrieben).';
        }

        $modelYear = null;
        $modelYearCandidates = [];
        if (null !== $modelYearCode) {
            $yearInfo = $this->decodeModelYear($modelYearCode);
            $modelYear = $yearInfo['best'];
            $modelYearCandidates = $yearInfo['candidates'];
            if ([] === $modelYearCandidates) {
                $warnings[] = 'Modelljahr konnte nicht bestimmt werden (Code unbekannt).';
            }
        }
        if (is_array($vinOverride) && isset($vinOverride['modelYear'])) {
            $overrideYear = filter_var($vinOverride['modelYear'], FILTER_VALIDATE_INT);
            if (false !== $overrideYear) {
                $modelYear = (int) $overrideYear;
                $modelYearCandidates = [(int) $overrideYear];
            }
        }

        if (null === $manufacturer && null !== $wmi && 3 === strlen($wmi)) {
            $warnings[] = 'Hersteller anhand des WMI nicht in der Offline-Liste gefunden.';
        }

        return [
            'raw' => $raw,
            'normalized' => $normalized,
            'length' => $length,
            'isLengthValid' => 17 === $length,
            'invalidPositions' => $invalidPositions,
            'region' => $region,
            'country' => $country,
            'manufacturer' => $manufacturer,
            'wmi' => $wmi,
            'vds' => $vds,
            'vis' => $vis,
            'checkDigit' => $checkDigit,
            'checkDigitComputed' => $checkDigitComputed,
            'checkDigitValid' => $checkDigitValid,
            'checkDigitRequired' => $checkDigitRequired,
            'checkDigitSum' => $checkDigitSum,
            'checkDigitRemainder' => $checkDigitRemainder,
            'modelYearCode' => $modelYearCode,
            'modelYear' => $modelYear,
            'modelYearCandidates' => $modelYearCandidates,
            'plantCode' => $plantCode,
            'plantName' => $plantName,
            'plantInfo' => $plantInfo,
            'plantSource' => $plantSource,
            'serialNumber' => $serialNumber,
            'vdsMatches' => $vdsMatches,
            'vdsRulesAvailable' => $vdsRulesAvailable,
            'vinOverride' => $vinOverride,
            'vpic' => null,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function isValidVinChar(string $char): bool
    {
        return 1 === preg_match('/^[A-HJ-NPR-Z0-9]$/', $char);
    }

    private function isValidVinForApi(string $vin): bool
    {
        if (17 !== strlen($vin)) {
            return false;
        }

        for ($i = 0; $i < 17; ++$i) {
            if (!$this->isValidVinChar($vin[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function resolveRegionCountry(string $firstChar): array
    {
        $region = null;
        $country = null;

        if (preg_match('/^[1-5]$/', $firstChar)) {
            $region = 'Nordamerika';
            $country = match ($firstChar) {
                '1', '4', '5' => 'USA',
                '2' => 'Kanada',
                '3' => 'Mexiko',
                default => null,
            };
        } elseif (preg_match('/^[6-7]$/', $firstChar)) {
            $region = 'Ozeanien';
            $country = $firstChar === '6' ? 'Australien' : 'Neuseeland';
        } elseif (preg_match('/^[8-9]$/', $firstChar)) {
            $region = 'Südamerika';
        } elseif (preg_match('/^[A-H]$/', $firstChar)) {
            $region = 'Afrika';
        } elseif (preg_match('/^[J-R]$/', $firstChar)) {
            $region = 'Asien';
            $country = match ($firstChar) {
                'J' => 'Japan',
                'K' => 'Südkorea',
                'L' => 'China',
                'M' => 'Indien',
                default => null,
            };
        } elseif (preg_match('/^[S-Z]$/', $firstChar)) {
            $region = 'Europa';
            $country = match ($firstChar) {
                'S' => 'Vereinigtes Königreich',
                'T' => 'Schweiz',
                'V' => 'Frankreich/Spanien',
                'W' => 'Deutschland',
                'X' => 'Russland',
                'Y' => 'Schweden/Finnland',
                'Z' => 'Italien',
                default => null,
            };
        }

        return [$region, $country];
    }

    private function isCheckDigitRequired(string $firstChar): bool
    {
        return 1 === preg_match('/^[1-5]$/', $firstChar);
    }

    /**
     * @param array<string, array<mixed>> $map
     *
     * @return array{key:string,value:array<mixed>}|null
     */
    private function lookupByWmiPrefix(array $map, string $wmi): ?array
    {
        $wmi = strtoupper($wmi);
        $keys = array_keys($map);
        usort($keys, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($keys as $key) {
            if ('' === $key) {
                continue;
            }
            if (str_starts_with($wmi, $key)) {
                return [
                    'key' => $key,
                    'value' => $map[$key],
                ];
            }
        }

        return null;
    }

    /**
     * @return array{computed:string,sum:int,remainder:int}|null
     */
    private function computeCheckDigit(string $vin): ?array
    {
        if (17 !== strlen($vin)) {
            return null;
        }

        $sum = 0;
        for ($i = 0; $i < 17; ++$i) {
            $char = $vin[$i];
            $value = self::TRANSLITERATION[$char] ?? null;
            if (null === $value) {
                return null;
            }

            $sum += $value * self::WEIGHTS[$i];
        }

        $remainder = $sum % 11;
        $computed = 10 === $remainder ? 'X' : (string) $remainder;

        return [
            'computed' => $computed,
            'sum' => $sum,
            'remainder' => $remainder,
        ];
    }

    /**
     * @return array{best:?int,candidates:array<int, int>}
     */
    private function decodeModelYear(string $code): array
    {
        $base = self::MODEL_YEAR_CODES[$code] ?? null;
        if (null === $base) {
            return [
                'best' => null,
                'candidates' => [],
            ];
        }

        $maxYear = (int) (new \DateTimeImmutable('now'))->format('Y') + 1;
        $candidates = [];

        $year = $base;
        while ($year <= $maxYear) {
            $candidates[] = $year;
            $year += 30;
        }

        if ([] === $candidates) {
            $candidates[] = $base;
        }

        return [
            'best' => max($candidates),
            'candidates' => $candidates,
        ];
    }

    /**
     * @param array<string, string> $map
     *
     * @return array<string, string>
     */
    private function normalizeManufacturerMap(array $map): array
    {
        $out = [];
        foreach ($map as $key => $value) {
            if (!is_string($key) || '' === trim($key)) {
                continue;
            }
            $name = trim((string) $value);
            if ('' === $name) {
                continue;
            }

            $out[strtoupper(trim($key))] = $name;
        }

        return $out;
    }

    /**
     * @param array<mixed> $input
     *
     * @return array<string, array<string, array<string, string>>>
     */
    private function normalizePlantCodes(array $input): array
    {
        $out = [];
        foreach ($input as $wmi => $plants) {
            if (!is_array($plants)) {
                continue;
            }
            $wmiKey = strtoupper(trim((string) $wmi));
            if ('' === $wmiKey) {
                continue;
            }

            $out[$wmiKey] = [];
            foreach ($plants as $code => $info) {
                $codeKey = strtoupper(trim((string) $code));
                if ('' === $codeKey) {
                    continue;
                }

                if (\is_array($info)) {
                    $normalized = [];
                    foreach ($info as $infoKey => $infoValue) {
                        if (!is_string($infoKey)) {
                            continue;
                        }
                        $value = trim((string) $infoValue);
                        if ('' !== $value) {
                            $normalized[$infoKey] = $value;
                        }
                    }
                    if ([] !== $normalized) {
                        if (!isset($normalized['name']) && isset($normalized['location'])) {
                            $normalized['name'] = $normalized['location'];
                        }
                        $out[$wmiKey][$codeKey] = $normalized;
                    }
                } else {
                    $name = trim((string) $info);
                    if ('' !== $name) {
                        $out[$wmiKey][$codeKey] = ['name' => $name];
                    }
                }
            }

            if ([] === $out[$wmiKey]) {
                unset($out[$wmiKey]);
            }
        }

        return $out;
    }

    /**
     * @param array<mixed> $input
     *
     * @return array<string, array<int, array{pattern:string,label:string,description:?string,type:string,data:?array}>>
     */
    private function normalizeVdsRules(array $input): array
    {
        $out = [];
        foreach ($input as $wmi => $rules) {
            if (!is_array($rules)) {
                continue;
            }
            $wmiKey = strtoupper(trim((string) $wmi));
            if ('' === $wmiKey) {
                continue;
            }

            foreach ($rules as $rule) {
                if (!is_array($rule)) {
                    continue;
                }

                $pattern = trim((string) ($rule['pattern'] ?? ''));
                $label = trim((string) ($rule['label'] ?? ''));
                if ('' === $pattern || '' === $label) {
                    continue;
                }

                $type = strtolower(trim((string) ($rule['type'] ?? 'regex')));
                if (!\in_array($type, ['regex', 'prefix'], true)) {
                    $type = 'regex';
                }

                $description = trim((string) ($rule['description'] ?? ''));
                $data = $rule['data'] ?? null;
                if (!\is_array($data)) {
                    $data = null;
                }

                $out[$wmiKey][] = [
                    'pattern' => $pattern,
                    'label' => $label,
                    'description' => '' !== $description ? $description : null,
                    'type' => $type,
                    'data' => $data,
                ];
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadCachedVinData(string $vin): ?array
    {
        $cached = $this->vinDecoderCacheRepository->findOneBy(['vin' => $vin]);
        if (!$cached instanceof VinDecoderCache) {
            return null;
        }

        $payload = $cached->getPayload();

        return [] !== $payload ? $payload : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function storeVinCache(string $vin, array $payload): void
    {
        if ('' === trim($vin) || [] === $payload) {
            return;
        }

        $existing = $this->vinDecoderCacheRepository->findOneBy(['vin' => $vin]);
        if ($existing instanceof VinDecoderCache) {
            $merged = $this->mergePayload($existing->getPayload(), $payload);
            $existing->setPayload($merged);
            if ('' === trim($existing->getProvider())) {
                $existing->setProvider('vincario');
            }
            $this->entityManager->flush();

            return;
        }

        $entry = (new VinDecoderCache())
            ->setVin($vin)
            ->setPayload($payload)
            ->setProvider('vincario');

        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function hasVincarioPayload(?array $payload): bool
    {
        if (!is_array($payload) || [] === $payload) {
            return false;
        }

        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if (!str_starts_with($key, '__')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function extractVpicFromCache(?array $payload): ?array
    {
        if (!is_array($payload)) {
            return null;
        }

        $vpic = $payload[self::VPIC_CACHE_KEY] ?? null;

        return is_array($vpic) ? $vpic : null;
    }

    /**
     * @param array<string, mixed> $vpic
     */
    private function storeVpicCache(string $vin, array $vpic): void
    {
        if ('' === trim($vin) || [] === $vpic) {
            return;
        }

        $existing = $this->vinDecoderCacheRepository->findOneBy(['vin' => $vin]);
        if ($existing instanceof VinDecoderCache) {
            $payload = $existing->getPayload();
            $payload[self::VPIC_CACHE_KEY] = $vpic;
            $existing->setPayload($payload);
            if ('' === trim($existing->getProvider())) {
                $existing->setProvider('vpic');
            }
            $this->entityManager->flush();

            return;
        }

        $entry = (new VinDecoderCache())
            ->setVin($vin)
            ->setPayload([self::VPIC_CACHE_KEY => $vpic])
            ->setProvider('vpic');

        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed>|null $payload
     * @param array<string, mixed> $incoming
     *
     * @return array<string, mixed>
     */
    private function mergePayload(?array $payload, array $incoming): array
    {
        return array_replace($payload ?? [], $incoming);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchVpic(string $input, ?int $modelYear = null): ?array
    {
        $normalized = strtoupper((string) preg_replace('/[\\s-]+/', '', trim($input)));
        if ('' === $normalized || !$this->isValidVinForApi($normalized)) {
            return null;
        }

        $cached = $this->extractVpicFromCache($this->loadCachedVinData($normalized));
        if (null !== $cached) {
            return $cached;
        }

        $vpic = $this->fetchVpicData($normalized, $modelYear);
        if (is_array($vpic)) {
            $this->storeVpicCache($normalized, $vpic);
        }

        return $vpic;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchVinApiData(string $vin): ?array
    {
        $apiKey = trim($this->apiKey);
        $apiSecret = trim($this->apiSecret);
        $apiBase = trim($this->apiBase);
        if ('' === $apiKey || '' === $apiSecret || '' === $apiBase) {
            return null;
        }

        $id = 'decode';
        $controlSum = substr(sha1(sprintf('%s|%s|%s|%s', $vin, $id, $apiKey, $apiSecret)), 0, 10);
        $base = rtrim($apiBase, '/');
        $url = sprintf(
            '%s/%s/%s/%s/%s.json',
            $base,
            rawurlencode($apiKey),
            $controlSum,
            $id,
            rawurlencode($vin)
        );

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => max(1, $this->apiTimeout),
            ]);
            $payload = $response->toArray(false);
        } catch (ExceptionInterface) {
            return null;
        }

        if (!is_array($payload)) {
            return null;
        }

        $decoded = $payload['decode'] ?? null;
        if (!is_array($decoded)) {
            return null;
        }

        $out = [];
        foreach ($decoded as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $label = trim((string) ($entry['label'] ?? ''));
            if ('' === $label) {
                continue;
            }
            $value = $entry['value'] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            if (null === $value || '' === $value) {
                continue;
            }
            $out[$label] = $value;
        }

        return [] !== $out ? $out : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchVpicData(string $vin, ?int $modelYear = null): ?array
    {
        $base = trim($this->vpicBase);
        if ('' === $base) {
            return null;
        }

        $query = ['format' => 'json'];
        if (null !== $modelYear) {
            $query['modelyear'] = $modelYear;
        }

        $url = rtrim($base, '/') . '/vehicles/DecodeVinValuesExtended/' . rawurlencode($vin);
        $url .= '?' . http_build_query($query);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => max(1, $this->vpicTimeout),
            ]);
            $payload = $response->toArray(false);
        } catch (ExceptionInterface) {
            return null;
        }

        if (!is_array($payload)) {
            return null;
        }

        $results = $payload['Results'] ?? null;
        if (!is_array($results) || [] === $results) {
            return null;
        }

        $row = $results[0] ?? null;
        if (!is_array($row)) {
            return null;
        }

        $errorCode = $this->normalizeVpicStatus($row['ErrorCode'] ?? null);
        $errorText = $this->normalizeVpicStatus($row['ErrorText'] ?? null);

        $values = [];
        foreach (self::VPIC_FIELD_MAP as $field => $key) {
            if (!array_key_exists($field, $row)) {
                continue;
            }
            $value = $this->normalizeVpicValue($row[$field]);
            if (null !== $value) {
                $values[$key] = $value;
            }
        }

        if ([] === $values && null === $errorCode && null === $errorText) {
            return null;
        }

        return [
            'source' => 'vpic',
            'vin' => $vin,
            'modelYear' => $modelYear,
            'values' => $values,
            'errorCode' => $errorCode,
            'errorText' => $errorText,
        ];
    }

    /**
     * @param array<string, mixed>|null $apiData
     *
     * @return array<string, mixed>
     */
    private function mapVinApiData(?array $apiData): array
    {
        if (!is_array($apiData) || [] === $apiData) {
            return [];
        }

        $mapping = [
            'Make' => 'make',
            'Model' => 'model',
            'Series' => 'series',
            'Model Year' => 'modelYear',
            'Product Type' => 'productType',
            'Body' => 'body',
            'Drive' => 'drive',
            'Engine' => 'engine',
            'Engine (full)' => 'engineFull',
            'Engine Model' => 'engineModel',
            'Engine Cylinders' => 'engineCylinders',
            'Engine Cylinders Position' => 'engineCylindersPosition',
            'Engine Displacement (ccm)' => 'engineDisplacementCcm',
            'Engine Power (kW)' => 'enginePowerKw',
            'Engine Torque (RPM)' => 'engineTorque',
            'Engine Position' => 'enginePosition',
            'Engine Compression Ratio' => 'engineCompressionRatio',
            'Engine Stroke (mm)' => 'engineStrokeMm',
            'Engine Cylinder Bore (mm)' => 'engineCylinderBoreMm',
            'Engine Turbine' => 'engineTurbine',
            'Engine Oil Capacity (l)' => 'engineOilCapacityL',
            'Engine Coolant (l)' => 'engineCoolantL',
            'Engine Max Speed (rpm)' => 'engineMaxSpeedRpm',
            'Valves per Cylinder' => 'valvesPerCylinder',
            'Valve Train' => 'valveTrain',
            'Transmission' => 'transmission',
            'Number of Gears' => 'numberOfGears',
            'Manufacturer' => 'manufacturer',
            'Manufacturer Address' => 'manufacturerAddress',
            'Plant City' => 'plantCity',
            'Plant Country' => 'plantCountry',
            'Engine Manufacturer' => 'engineManufacturer',
            'Average CO2 Emission (g/km)' => 'averageCo2',
            'CO2 Emission (g/km)' => 'co2Emission',
            'Number Wheels' => 'numberOfWheels',
            'Number of Axles' => 'numberOfAxles',
            'Number of Doors' => 'numberOfDoors',
            'Number of Seats' => 'numberOfSeats',
            'Power Steering' => 'powerSteering',
            'Steering Type' => 'steeringType',
            'Rear Brakes' => 'rearBrakes',
            'Front Breaks' => 'frontBrakes',
            'Rear Breaks' => 'rearBrakes',
            'Brake System' => 'brakeSystem',
            'Fuel Type - Primary' => 'fuelType',
            'Fuel System' => 'fuelSystem',
            'Fuel Capacity (l)' => 'fuelCapacityL',
            'Fuel Consumption Urban (l/100km)' => 'fuelConsumptionUrban',
            'Fuel Consumption Extra Urban (l/100km)' => 'fuelConsumptionExtraUrban',
            'Fuel Consumption Combined (l/100km)' => 'fuelConsumptionCombined',
            'Wheel Size Array' => 'wheelSize',
            'Wheelbase Array (mm)' => 'wheelbaseMm',
            'Wheel Size' => 'wheelSize',
            'Wheel Rims Size' => 'wheelRimsSize',
            'Wheel Rims Size Array' => 'wheelRimsSize',
            'Wheelbase (mm)' => 'wheelbaseMm',
            'Height (mm)' => 'heightMm',
            'Length (mm)' => 'lengthMm',
            'Width (mm)' => 'widthMm',
            'Width including mirrors (mm)' => 'widthIncludingMirrorsMm',
            'Track Front (mm)' => 'trackFrontMm',
            'Track Rear (mm)' => 'trackRearMm',
            'Max Speed (km/h)' => 'maxSpeedKmh',
            'Weight Empty (kg)' => 'weightEmptyKg',
            'Max Weight (kg)' => 'maxWeightKg',
            'Max roof load (kg)' => 'maxRoofLoadKg',
            'Permitted trailer load without brakes (kg)' => 'trailerNoBrakesKg',
            'Permitted trailer load with brakes 8% (kg)' => 'trailerWithBrakesKg',
            'Permitted towbar download (kg)' => 'towbarDownloadKg',
            'Minimum Trunk Capacity (l)' => 'trunkMinL',
            'Maximum Trunk Capacity (l)' => 'trunkMaxL',
            'Front Overhang (mm)' => 'frontOverhangMm',
            'Rear Overhang (mm)' => 'rearOverhangMm',
            'Minimum Turning Circle (m)' => 'turningCircle',
            'Drag Coefficient' => 'dragCoefficient',
            'Emission Standard' => 'emissionStandard',
            'Production Started' => 'productionStarted',
            'Production Stopped' => 'productionStopped',
            'Front Suspension' => 'frontSuspension',
            'Rear Suspension' => 'rearSuspension',
            'ABS' => 'abs',
            'Check Digit' => 'checkDigit',
            'Valid Check Digit' => 'checkDigitComputed',
            'Check Digit Validity' => 'checkDigitValidity',
            'Sequential Number' => 'sequentialNumber',
            'Vehicle ID' => 'vehicleId',
            'Acceleration 0-100 km/h (s)' => 'acceleration0To100',
            'Make Logo' => 'makeLogo',
            'Price' => 'priceHistory',
            'Odometer (km)' => 'odometerHistory',
        ];

        $normalizedMap = [];
        foreach ($mapping as $label => $key) {
            $normalizedMap[$this->normalizeApiLabel($label)] = $key;
        }

        $out = [];
        foreach ($apiData as $label => $value) {
            if (!is_string($label)) {
                continue;
            }

            $normalizedLabel = $this->normalizeApiLabel($label);
            if (!array_key_exists($normalizedLabel, $normalizedMap)) {
                continue;
            }

            $key = $normalizedMap[$normalizedLabel];
            if (array_key_exists($key, $out)) {
                continue;
            }

            if ('priceHistory' === $key) {
                $history = $this->normalizeApiHistory($value, ['date', 'price', 'currency']);
                if ([] !== $history) {
                    $out[$key] = $history;
                }
                continue;
            }
            if ('odometerHistory' === $key) {
                $history = $this->normalizeApiHistory($value, ['date', 'odometer']);
                if ([] !== $history) {
                    $out[$key] = $history;
                }
                continue;
            }
            if ('abs' === $key) {
                $parsed = $this->normalizeApiBool($value);
                if (null !== $parsed) {
                    $out[$key] = $parsed;
                }
                continue;
            }
            $normalized = $this->normalizeApiValue($value);
            if (null !== $normalized) {
                $out[$key] = $normalized;
                if ('manufacturer' === $key && !array_key_exists('manufacturerFull', $out)) {
                    $out['manufacturerFull'] = $normalized;
                }
            }
        }

        return $out;
    }

    private function normalizeApiBool(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value > 0;
        }
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if ('' === $normalized) {
                return null;
            }
            if (in_array($normalized, ['1', 'yes', 'true', 'ja', 'y'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'no', 'false', 'nein', 'n'], true)) {
                return false;
            }
        }

        return null;
    }

    private function normalizeApiValue(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }
        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            return '' === $trimmed ? null : $trimmed;
        }
        if (is_array($value)) {
            $items = [];
            foreach ($value as $item) {
                if (is_string($item)) {
                    $item = trim($item);
                    if ('' !== $item) {
                        $items[] = $item;
                    }
                    continue;
                }
                if (is_bool($item)) {
                    $items[] = $item ? 'true' : 'false';
                    continue;
                }
                if (is_int($item) || is_float($item)) {
                    $items[] = (string) $item;
                    continue;
                }
            }

            return [] === $items ? null : implode(', ', $items);
        }

        return null;
    }

    private function normalizeVpicStatus(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            return '' === $trimmed ? null : $trimmed;
        }

        return null;
    }

    private function normalizeVpicValue(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            $stringValue = (string) $value;
            if (in_array($stringValue, ['0', '0.0', '0.00'], true)) {
                return null;
            }

            return $stringValue;
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            if ('' === $trimmed) {
                return null;
            }
            $normalized = strtolower($trimmed);
            if (in_array($normalized, ['0', '0.0', '0.00', 'n/a', 'na', 'not applicable', 'not available', 'none', 'null'], true)) {
                return null;
            }

            return $trimmed;
        }

        return null;
    }

    private function normalizeApiLabel(string $label): string
    {
        $normalized = strtolower(trim($label));
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    /**
     * @param array<int, string> $fields
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeApiHistory(mixed $value, array $fields): array
    {
        if (!is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $row = [];
            foreach ($fields as $field) {
                if (!array_key_exists($field, $entry)) {
                    continue;
                }
                $cell = $entry[$field];
                if (is_string($cell)) {
                    $cell = trim($cell);
                    if ('' === $cell) {
                        continue;
                    }
                }
                if (null !== $cell) {
                    $row[$field] = $cell;
                }
            }
            if ([] !== $row) {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @param array<mixed> $input
     *
     * @return array<string, array<string, mixed>>
     */
    private function normalizeVinOverrides(array $input): array
    {
        $out = [];
        foreach ($input as $vin => $data) {
            if (!is_string($vin)) {
                continue;
            }
            $vinKey = strtoupper(trim($vin));
            if ('' === $vinKey || !is_array($data)) {
                continue;
            }

            $normalized = [];
            foreach ($data as $field => $value) {
                if (!is_string($field) || '' === trim($field)) {
                    continue;
                }

                if (is_string($value)) {
                    $trimmed = trim($value);
                    if ('' === $trimmed) {
                        continue;
                    }
                    $normalized[$field] = $trimmed;
                    continue;
                }

                if (null === $value) {
                    continue;
                }

                $normalized[$field] = $value;
            }

            if ([] !== $normalized) {
                $out[$vinKey] = $normalized;
            }
        }

        return $out;
    }

    private function buildRegex(string $pattern): string
    {
        $pattern = trim($pattern);
        if ('' === $pattern) {
            return '';
        }

        $first = $pattern[0];
        $last = $pattern[strlen($pattern) - 1];
        if (!ctype_alnum($first) && $first === $last && strlen($pattern) > 1) {
            return $pattern;
        }

        return '#' . $pattern . '#';
    }
}
