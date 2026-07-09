<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class VpicApiService
{
    /**
     * @var array<int, array{title:string,description:string,endpoints:array<int, array<string, mixed>>}>
     */
    private const ENDPOINT_GROUPS = [
        [
            'title' => 'Decode & WMI',
            'description' => 'VIN-Decoding, WMI-Infos und Batch-Decode.',
            'endpoints' => [
                [
                    'id' => 'decode_vin',
                    'title' => 'Decode VIN',
                    'path' => '/vehicles/DecodeVin/{vin}',
                    'description' => 'Key-Value-Ausgabe; unterstützt teilweise VINs und Model Year.',
                    'example' => '/vehicles/DecodeVin/5UXWX7C5*BA?format=xml&modelyear=2011',
                    'pathParams' => [
                        ['name' => 'vin', 'label' => 'VIN', 'placeholder' => '5UXWX7C5*BA', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'modelyear', 'label' => 'Model Year', 'placeholder' => '2011'],
                    ],
                ],
                [
                    'id' => 'decode_vin_flat',
                    'title' => 'Decode VIN (flat format)',
                    'path' => '/vehicles/DecodeVinValues/{vin}',
                    'description' => 'Flaches Format (ein Datensatz) mit Model Year.',
                    'example' => '/vehicles/DecodeVinValues/5UXWX7C5*BA?format=xml&modelyear=2011',
                    'pathParams' => [
                        ['name' => 'vin', 'label' => 'VIN', 'placeholder' => '5UXWX7C5*BA', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'modelyear', 'label' => 'Model Year', 'placeholder' => '2011'],
                    ],
                ],
                [
                    'id' => 'decode_vin_extended',
                    'title' => 'Decode VIN Extended',
                    'path' => '/vehicles/DecodeVinExtended/{vin}',
                    'description' => 'Decode VIN mit erweiterten NHTSA-Variablen.',
                    'example' => '/vehicles/DecodeVinExtended/5UXWX7C5*BA?format=json&modelyear=2011',
                    'pathParams' => [
                        ['name' => 'vin', 'label' => 'VIN', 'placeholder' => '5UXWX7C5*BA', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'modelyear', 'label' => 'Model Year', 'placeholder' => '2011'],
                    ],
                ],
                [
                    'id' => 'decode_vin_extended_flat',
                    'title' => 'Decode VIN Extended (flat format)',
                    'path' => '/vehicles/DecodeVinValuesExtended/{vin}',
                    'description' => 'Flaches Format mit erweiterten NHTSA-Variablen.',
                    'example' => '/vehicles/DecodeVinValuesExtended/5UXWX7C5*BA?format=json&modelyear=2011',
                    'pathParams' => [
                        ['name' => 'vin', 'label' => 'VIN', 'placeholder' => '5UXWX7C5*BA', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'modelyear', 'label' => 'Model Year', 'placeholder' => '2011'],
                    ],
                ],
                [
                    'id' => 'decode_wmi',
                    'title' => 'Decode WMI',
                    'path' => '/vehicles/DecodeWMI/{wmi}',
                    'description' => 'WMI-Informationen (3 oder 6 Zeichen).',
                    'example' => '/vehicles/DecodeWMI/1FD?format=xml',
                    'pathParams' => [
                        ['name' => 'wmi', 'label' => 'WMI', 'placeholder' => '1FD', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'decode_vin_batch',
                    'title' => 'Decode VIN Values Batch',
                    'path' => '/vehicles/DecodeVINValuesBatch/{data}',
                    'description' => 'Batch-Decode im flachen Format (max. 50 VINs).',
                    'example' => '/vehicles/DecodeVINValuesBatch/5UXWX7C5*BA,2011;5YJSA3DS*EF?format=json',
                    'pathParams' => [
                        [
                            'name' => 'data',
                            'label' => 'Batch Data',
                            'placeholder' => 'VIN,Year; VIN,Year; ...',
                            'required' => true,
                            'type' => 'textarea',
                        ],
                    ],
                    'queryParams' => [],
                ],
            ],
        ],
        [
            'title' => 'Hersteller & Makes',
            'description' => 'Hersteller- und Markenabfragen aus der vPIC-Datenbank.',
            'endpoints' => [
                [
                    'id' => 'get_all_makes',
                    'title' => 'Get All Makes',
                    'path' => '/vehicles/GetAllMakes',
                    'description' => 'Liste aller Makes in der vPIC-Datenbank.',
                    'example' => '/vehicles/GetAllMakes?format=csv',
                    'pathParams' => [],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_all_manufacturers',
                    'title' => 'Get All Manufacturers',
                    'path' => '/vehicles/GetAllManufacturers',
                    'description' => 'Liste aller Hersteller, optional nach Typ und Seite.',
                    'example' => '/vehicles/GetAllManufacturers?format=xml&page=2',
                    'pathParams' => [],
                    'queryParams' => [
                        ['name' => 'page', 'label' => 'Page', 'placeholder' => '1'],
                        ['name' => 'ManufacturerType', 'label' => 'Manufacturer Type', 'placeholder' => 'Intermediate'],
                    ],
                ],
                [
                    'id' => 'get_manufacturer_details',
                    'title' => 'Get Manufacturer Details',
                    'path' => '/vehicles/GetManufacturerDetails/{manufacturer}',
                    'description' => 'Details zu Hersteller (Id oder Name).',
                    'example' => '/vehicles/GetManufacturerDetails/honda?format=xml',
                    'pathParams' => [
                        ['name' => 'manufacturer', 'label' => 'Manufacturer', 'placeholder' => 'honda oder 989', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'page', 'label' => 'Page', 'placeholder' => '1'],
                    ],
                ],
                [
                    'id' => 'get_make_for_manufacturer',
                    'title' => 'Get Make For Manufacturer',
                    'path' => '/vehicles/GetMakeForManufacturer/{manufacturer}',
                    'description' => 'Makes zu Hersteller (Id oder Name).',
                    'example' => '/vehicles/GetMakeForManufacturer/honda?format=json',
                    'pathParams' => [
                        ['name' => 'manufacturer', 'label' => 'Manufacturer', 'placeholder' => 'honda oder 988', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_makes_for_manufacturer_year',
                    'title' => 'Get Makes For Manufacturer And Year',
                    'path' => '/vehicles/GetMakesForManufacturerAndYear/{manufacturer}',
                    'description' => 'Makes nach Hersteller und Jahr.',
                    'example' => '/vehicles/GetMakesForManufacturerAndYear/mer?year=2013&format=json',
                    'pathParams' => [
                        ['name' => 'manufacturer', 'label' => 'Manufacturer', 'placeholder' => 'mer oder 988', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'year', 'label' => 'Year', 'placeholder' => '2013', 'required' => true],
                    ],
                ],
                [
                    'id' => 'get_makes_for_vehicle_type',
                    'title' => 'Get Makes For Vehicle Type',
                    'path' => '/vehicles/GetMakesForVehicleType/{vehicleType}',
                    'description' => 'Makes zu Fahrzeugtyp (Name).',
                    'example' => '/vehicles/GetMakesForVehicleType/car?format=json',
                    'pathParams' => [
                        ['name' => 'vehicleType', 'label' => 'Vehicle Type', 'placeholder' => 'car', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_wmi_for_manufacturer',
                    'title' => 'Get WMIs For Manufacturer',
                    'path' => '/vehicles/GetWMIsForManufacturer/{manufacturer}',
                    'description' => 'WMIs nach Hersteller, optional Fahrzeugtyp.',
                    'example' => '/vehicles/GetWMIsForManufacturer/hon?format=xml',
                    'pathParams' => [
                        ['name' => 'manufacturer', 'label' => 'Manufacturer', 'placeholder' => 'hon oder 987', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'vehicleType', 'label' => 'Vehicle Type', 'placeholder' => 'car oder 2'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Models & Vehicle Types',
            'description' => 'Modelle, Fahrzeugtypen und kombinierte Filter.',
            'endpoints' => [
                [
                    'id' => 'get_vehicle_types_for_make',
                    'title' => 'Get Vehicle Types For Make',
                    'path' => '/vehicles/GetVehicleTypesForMake/{make}',
                    'description' => 'Fahrzeugtypen zu Make (Name).',
                    'example' => '/vehicles/GetVehicleTypesForMake/mercedes?format=json',
                    'pathParams' => [
                        ['name' => 'make', 'label' => 'Make', 'placeholder' => 'mercedes', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_vehicle_types_for_make_id',
                    'title' => 'Get Vehicle Types For MakeId',
                    'path' => '/vehicles/GetVehicleTypesForMakeId/{makeId}',
                    'description' => 'Fahrzeugtypen zu MakeId.',
                    'example' => '/vehicles/GetVehicleTypesForMakeId/450?format=json',
                    'pathParams' => [
                        ['name' => 'makeId', 'label' => 'MakeId', 'placeholder' => '450', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_models_for_make',
                    'title' => 'Get Models For Make',
                    'path' => '/vehicles/GetModelsForMake/{make}',
                    'description' => 'Modelle zu Make (Name).',
                    'example' => '/vehicles/GetModelsForMake/honda?format=json',
                    'pathParams' => [
                        ['name' => 'make', 'label' => 'Make', 'placeholder' => 'honda', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_models_for_make_id',
                    'title' => 'Get Models For MakeId',
                    'path' => '/vehicles/GetModelsForMakeId/{makeId}',
                    'description' => 'Modelle zu MakeId.',
                    'example' => '/vehicles/GetModelsForMakeId/440?format=json',
                    'pathParams' => [
                        ['name' => 'makeId', 'label' => 'MakeId', 'placeholder' => '440', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_models_for_make_year',
                    'title' => 'Get Models For Make Year',
                    'path' => '/vehicles/GetModelsForMakeYear/make/{make}/modelyear/{modelyear}',
                    'description' => 'Modelle zu Make + Model Year.',
                    'example' => '/vehicles/GetModelsForMakeYear/make/honda/modelyear/2015?format=csv',
                    'pathParams' => [
                        ['name' => 'make', 'label' => 'Make', 'placeholder' => 'honda', 'required' => true],
                        ['name' => 'modelyear', 'label' => 'Model Year', 'placeholder' => '2015', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'vehicletype', 'label' => 'Vehicle Type (optional)', 'placeholder' => 'truck'],
                    ],
                ],
                [
                    'id' => 'get_models_for_make_vehicle_type',
                    'title' => 'Get Models For Make + Vehicle Type',
                    'path' => '/vehicles/GetModelsForMakeYear/make/{make}/vehicletype/{vehicletype}',
                    'description' => 'Modelle zu Make + Vehicle Type.',
                    'example' => '/vehicles/GetModelsForMakeYear/make/honda/vehicletype/truck?format=csv',
                    'pathParams' => [
                        ['name' => 'make', 'label' => 'Make', 'placeholder' => 'honda', 'required' => true],
                        ['name' => 'vehicletype', 'label' => 'Vehicle Type', 'placeholder' => 'truck', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_models_for_make_year_vehicle_type',
                    'title' => 'Get Models For Make + Year + Vehicle Type',
                    'path' => '/vehicles/GetModelsForMakeYear/make/{make}/modelyear/{modelyear}/vehicletype/{vehicletype}',
                    'description' => 'Modelle zu Make + Year + Vehicle Type.',
                    'example' => '/vehicles/GetModelsForMakeYear/make/honda/modelyear/2015/vehicletype/truck?format=csv',
                    'pathParams' => [
                        ['name' => 'make', 'label' => 'Make', 'placeholder' => 'honda', 'required' => true],
                        ['name' => 'modelyear', 'label' => 'Model Year', 'placeholder' => '2015', 'required' => true],
                        ['name' => 'vehicletype', 'label' => 'Vehicle Type', 'placeholder' => 'truck', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_models_for_make_id_year',
                    'title' => 'Get Models For MakeId Year',
                    'path' => '/vehicles/GetModelsForMakeIdYear/makeId/{makeId}/modelyear/{modelyear}',
                    'description' => 'Modelle zu MakeId + Model Year.',
                    'example' => '/vehicles/GetModelsForMakeIdYear/makeId/474/modelyear/2015?format=csv',
                    'pathParams' => [
                        ['name' => 'makeId', 'label' => 'MakeId', 'placeholder' => '474', 'required' => true],
                        ['name' => 'modelyear', 'label' => 'Model Year', 'placeholder' => '2015', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'vehicletype', 'label' => 'Vehicle Type (optional)', 'placeholder' => 'truck'],
                    ],
                ],
                [
                    'id' => 'get_models_for_make_id_vehicle_type',
                    'title' => 'Get Models For MakeId + Vehicle Type',
                    'path' => '/vehicles/GetModelsForMakeIdYear/makeId/{makeId}/vehicletype/{vehicletype}',
                    'description' => 'Modelle zu MakeId + Vehicle Type.',
                    'example' => '/vehicles/GetModelsForMakeIdYear/makeId/474/vehicletype/truck?format=csv',
                    'pathParams' => [
                        ['name' => 'makeId', 'label' => 'MakeId', 'placeholder' => '474', 'required' => true],
                        ['name' => 'vehicletype', 'label' => 'Vehicle Type', 'placeholder' => 'truck', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_models_for_make_id_year_vehicle_type',
                    'title' => 'Get Models For MakeId + Year + Vehicle Type',
                    'path' => '/vehicles/GetModelsForMakeIdYear/makeId/{makeId}/modelyear/{modelyear}/vehicletype/{vehicletype}',
                    'description' => 'Modelle zu MakeId + Year + Vehicle Type.',
                    'example' => '/vehicles/GetModelsForMakeIdYear/makeId/474/modelyear/2015/vehicletype/truck?format=csv',
                    'pathParams' => [
                        ['name' => 'makeId', 'label' => 'MakeId', 'placeholder' => '474', 'required' => true],
                        ['name' => 'modelyear', 'label' => 'Model Year', 'placeholder' => '2015', 'required' => true],
                        ['name' => 'vehicletype', 'label' => 'Vehicle Type', 'placeholder' => 'truck', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
            ],
        ],
        [
            'title' => 'Variablen & Werte',
            'description' => 'Variablenliste und Werte für Lookups.',
            'endpoints' => [
                [
                    'id' => 'get_vehicle_variable_list',
                    'title' => 'Get Vehicle Variable List',
                    'path' => '/vehicles/GetVehicleVariableList',
                    'description' => 'Liste aller Vehicle-Variablen im vPIC.',
                    'example' => '/vehicles/GetVehicleVariableList?format=xml',
                    'pathParams' => [],
                    'queryParams' => [],
                ],
                [
                    'id' => 'get_vehicle_variable_values_list',
                    'title' => 'Get Vehicle Variable Values List',
                    'path' => '/vehicles/GetVehicleVariableValuesList/{variable}',
                    'description' => 'Werte für eine Variable (Name oder ID).',
                    'example' => '/vehicles/GetVehicleVariableValuesList/battery type',
                    'pathParams' => [
                        ['name' => 'variable', 'label' => 'Variable', 'placeholder' => 'battery type oder 2', 'required' => true],
                    ],
                    'queryParams' => [],
                ],
            ],
        ],
        [
            'title' => 'Parts & Weitere Daten',
            'description' => 'Teile- und Zusatzdaten inklusive Canadian Vehicle Specs.',
            'endpoints' => [
                [
                    'id' => 'get_parts',
                    'title' => 'Get Parts',
                    'path' => '/vehicles/GetParts',
                    'description' => 'ORGs im Datumsbereich nach Typ (565/566).',
                    'example' => '/vehicles/GetParts?type=565&fromDate=1/1/2015&toDate=5/5/2015&format=xml&page=1',
                    'pathParams' => [],
                    'queryParams' => [
                        ['name' => 'type', 'label' => 'Type', 'placeholder' => '565', 'required' => true],
                        ['name' => 'fromDate', 'label' => 'From Date', 'placeholder' => '1/1/2015'],
                        ['name' => 'toDate', 'label' => 'To Date', 'placeholder' => '5/5/2015'],
                        ['name' => 'manufacturer', 'label' => 'Manufacturer', 'placeholder' => 'hon oder 988'],
                        ['name' => 'page', 'label' => 'Page', 'placeholder' => '1'],
                    ],
                ],
                [
                    'id' => 'get_equipment_plant_codes',
                    'title' => 'Get Equipment Plant Codes',
                    'path' => '/vehicles/GetEquipmentPlantCodes/{year}',
                    'description' => 'Equipment Plant Codes nach Jahr, Typ und Report.',
                    'example' => '/vehicles/GetEquipmentPlantCodes/2015?format=json',
                    'pathParams' => [
                        ['name' => 'year', 'label' => 'Year', 'placeholder' => '2016', 'required' => true],
                    ],
                    'queryParams' => [
                        ['name' => 'equipmentType', 'label' => 'Equipment Type', 'placeholder' => '1, 3, 13, 16'],
                        ['name' => 'reportType', 'label' => 'Report Type', 'placeholder' => 'New/Updated/Closed/All'],
                    ],
                ],
                [
                    'id' => 'get_canadian_vehicle_specs',
                    'title' => 'Get Canadian Vehicle Specifications',
                    'path' => '/vehicles/GetCanadianVehicleSpecifications',
                    'description' => 'Kanadische Fahrzeugabmessungen.',
                    'example' => '/vehicles/GetCanadianVehicleSpecifications/?year=2011&make=Acura&format=csv',
                    'pathParams' => [],
                    'queryParams' => [
                        ['name' => 'year', 'label' => 'Year', 'placeholder' => '2011', 'required' => true],
                        ['name' => 'make', 'label' => 'Make', 'placeholder' => 'Acura', 'required' => true],
                        ['name' => 'model', 'label' => 'Model', 'placeholder' => 'Pilot'],
                        ['name' => 'units', 'label' => 'Units', 'placeholder' => 'Metric oder US'],
                    ],
                ],
            ],
        ],
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%vin_decoder.vpic_base%')]
        private readonly string $baseUrl,
        #[Autowire('%vin_decoder.vpic_timeout%')]
        private readonly int $timeout,
    ) {
    }

    /**
     * @return array<int, array{title:string,description:string,endpoints:array<int, array<string, mixed>>}>
     */
    public function getEndpointGroups(): array
    {
        return self::ENDPOINT_GROUPS;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findEndpoint(string $id): ?array
    {
        foreach (self::ENDPOINT_GROUPS as $group) {
            foreach ($group['endpoints'] as $endpoint) {
                if ($endpoint['id'] === $id) {
                    return $endpoint;
                }
            }
        }

        return null;
    }

    /**
     * @return array{endpoint:array<string, mixed>,groupIndex:int,groupTitle:string}|null
     */
    public function findEndpointWithGroup(string $id): ?array
    {
        foreach (self::ENDPOINT_GROUPS as $index => $group) {
            foreach ($group['endpoints'] as $endpoint) {
                if ($endpoint['id'] === $id) {
                    return [
                        'endpoint' => $endpoint,
                        'groupIndex' => (int) $index,
                        'groupTitle' => (string) ($group['title'] ?? ''),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $pathParams
     *
     * @return array{ok:bool,url:string,status:int,contentType:?string,data:mixed,raw:string,error:?string}
     */
    public function request(string $path, array $pathParams, array $queryParams, string $format = 'json'): array
    {
        $base = rtrim(trim($this->baseUrl), '/');
        if ('' === $base) {
            return [
                'ok' => false,
                'url' => '',
                'status' => 0,
                'contentType' => null,
                'data' => null,
                'raw' => '',
                'error' => 'VPIC Base-URL fehlt.',
            ];
        }

        $resolvedPath = $this->replacePathParams($path, $pathParams);
        if ('' === $resolvedPath) {
            return [
                'ok' => false,
                'url' => '',
                'status' => 0,
                'contentType' => null,
                'data' => null,
                'raw' => '',
                'error' => 'Pfadparameter fehlen.',
            ];
        }

        $query = $this->normalizeQueryParams($queryParams, $format);
        $url = $base . '/' . ltrim($resolvedPath, '/');
        if ([] !== $query) {
            $url .= '?' . http_build_query($query);
        }

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => max(1, $this->timeout),
            ]);
            $status = $response->getStatusCode();
            $contentType = $response->getHeaders(false)['content-type'][0] ?? null;
            $raw = $response->getContent(false);
        } catch (ExceptionInterface) {
            return [
                'ok' => false,
                'url' => $url,
                'status' => 0,
                'contentType' => null,
                'data' => null,
                'raw' => '',
                'error' => 'VPIC-Request fehlgeschlagen.',
            ];
        }

        $data = $this->decodePayload($raw, $contentType);

        return [
            'ok' => $status >= 200 && $status < 300,
            'url' => $url,
            'status' => $status,
            'contentType' => $contentType,
            'data' => $data,
            'raw' => $raw,
            'error' => null,
        ];
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function normalizeQueryParams(array $params, string $format): array
    {
        $query = [];
        foreach ($params as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if (null === $value) {
                continue;
            }
            if (is_string($value)) {
                $trimmed = trim($value);
                if ('' === $trimmed) {
                    continue;
                }
                $query[$key] = $trimmed;
                continue;
            }
            $query[$key] = $value;
        }

        if (!isset($query['format'])) {
            $query['format'] = $format;
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $pathParams
     */
    private function replacePathParams(string $path, array $pathParams): string
    {
        $missing = false;
        $resolved = preg_replace_callback('/\\{([a-zA-Z0-9_]+)\\}/', function (array $matches) use ($pathParams, &$missing): string {
            $key = $matches[1] ?? '';
            if ('' === $key || !array_key_exists($key, $pathParams)) {
                $missing = true;
                return '';
            }
            $value = $pathParams[$key];
            if (null === $value) {
                $missing = true;
                return '';
            }
            $stringValue = trim((string) $value);
            if ('' === $stringValue) {
                $missing = true;
                return '';
            }

            return rawurlencode($stringValue);
        }, $path);

        if (true === $missing || null === $resolved) {
            return '';
        }

        return $resolved;
    }

    private function decodePayload(string $raw, ?string $contentType): mixed
    {
        if (null === $contentType) {
            return $raw;
        }

        if (str_contains($contentType, 'json')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === \JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $raw;
    }
}
