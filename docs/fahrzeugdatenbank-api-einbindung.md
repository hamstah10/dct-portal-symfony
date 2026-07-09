# Fahrzeugdatenbank API - Einbindung fuer Kunden

Diese Anleitung beschreibt, wie Sie die gemietete Fahrzeugdatenbank-API in Ihre eigene Website, Ihr Kundenportal oder Ihre Anwendung einbinden.

## 1. Zugangsdaten

Sie erhalten von DCT:

- `API_TOKEN`
- `TOKEN_ID`
- optional `contextKey`
- optional `poolKey`

Die API ist geschuetzt und erwartet bei jedem Request:

```text
Authorization: Bearer <API_TOKEN>
X-Token-Id: <TOKEN_ID>
```

Wichtig:
Der `API_TOKEN` darf nicht in oeffentlichem JavaScript, HTML oder in einer mobilen App fest eingebaut werden. Legen Sie den Token auf Ihrem Server ab und rufen Sie die DCT-API ueber ein eigenes Backend oder einen Proxy auf.

## 2. Empfohlene Architektur

Empfohlen:

```text
Website/Frontend des Kunden
  -> eigenes Kunden-Backend oder API-Proxy
    -> DCT Fahrzeugdatenbank API
```

Nicht empfohlen:

```text
Website/Frontend des Kunden
  -> DCT Fahrzeugdatenbank API direkt mit sichtbarem API_TOKEN
```

Der Proxy hat drei Aufgaben:

- geheimen API-Token schuetzen
- Requests an DCT weiterleiten
- Antworten an das eigene Frontend zurueckgeben

## 3. Basisdaten

DCT API-Basis:

```text
https://portal.dct-files.de/api/fahrzeugdatenbank
```

Empfohlene eigene Proxy-Basis auf Kundenseite:

```text
/api/vehicle-database
```

Die eigene URL kann frei gewaehlt werden. Wichtig ist nur, dass Ihr Frontend nicht direkt den geheimen DCT-Token kennt.

## 4. Server-Umgebungsvariablen

Legen Sie die Zugangsdaten in Ihrer Serverumgebung ab:

```env
TFD_VEHICLE_API_BASE=https://portal.dct-files.de/api/fahrzeugdatenbank
TFD_VEHICLE_API_TOKEN=Ihr_API_TOKEN
TFD_VEHICLE_TOKEN_ID=Ihre_TOKEN_ID
TFD_VEHICLE_CONTEXT_KEY=
TFD_VEHICLE_POOL_KEY=
```

Wenn Sie keinen `contextKey` oder `poolKey` erhalten haben, lassen Sie diese Werte leer.

## 5. Eigener Proxy: PHP-Beispiel

Minimalbeispiel fuer ein PHP-Backend:

```php
<?php

declare(strict_types=1);

$apiBase = rtrim((string) getenv('TFD_VEHICLE_API_BASE'), '/');
$apiToken = (string) getenv('TFD_VEHICLE_API_TOKEN');
$tokenId = (string) getenv('TFD_VEHICLE_TOKEN_ID');
$contextKey = (string) getenv('TFD_VEHICLE_CONTEXT_KEY');
$poolKey = (string) getenv('TFD_VEHICLE_POOL_KEY');

$path = '/' . trim((string) ($_GET['path'] ?? 'types'), '/');
$allowedPattern = '#^/(types|brands/\d+|series/\d+|models/\d+|engines/\d+|engine-details/\d+|track)$#';

if (!preg_match($allowedPattern, $path)) {
    http_response_code(404);
    echo json_encode(['message' => 'Endpoint nicht erlaubt.']);
    exit;
}

$url = $apiBase . $path;
$query = [];

foreach (['stageId', 'templateVariant'] as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        $query[$key] = (string) $_GET[$key];
    }
}

if ($query !== []) {
    $url .= '?' . http_build_query($query);
}

$headers = [ 
    'Authorization: Bearer ' . $apiToken,
    'X-Token-Id: ' . $tokenId,
    'Accept: application/json',
];

if ($contextKey !== '') {
    $headers[] = 'X-Vehicle-Data-Context: ' . $contextKey;
}

if ($poolKey !== '') {
    $headers[] = 'X-Vehicle-Data-Pool: ' . $poolKey;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => $method === 'POST' ? 'POST' : 'GET',
    CURLOPT_HTTPHEADER => $headers,
]);

if ($method === 'POST') {
    $body = file_get_contents('php://input') ?: '{}';
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: 500;
curl_close($ch);

http_response_code($status);
header('Content-Type: application/json');
echo $response ?: json_encode(['message' => 'API-Request fehlgeschlagen.']);
```

Beispielaufruf an den eigenen Proxy:

```text
/api/vehicle-database.php?path=types
/api/vehicle-database.php?path=brands/1
/api/vehicle-database.php?path=engine-details/123&templateVariant=performance
```

## 6. Eigener Proxy: Node.js/Express-Beispiel

```js
import express from 'express';

const app = express();
app.use(express.json());

const apiBase = process.env.TFD_VEHICLE_API_BASE;
const apiToken = process.env.TFD_VEHICLE_API_TOKEN;
const tokenId = process.env.TFD_VEHICLE_TOKEN_ID;
const contextKey = process.env.TFD_VEHICLE_CONTEXT_KEY || '';
const poolKey = process.env.TFD_VEHICLE_POOL_KEY || '';

const allowedPath = /^(types|brands\/\d+|series\/\d+|models\/\d+|engines\/\d+|engine-details\/\d+|track)$/;

app.all('/api/vehicle-database/:path(*)', async (req, res) => {
  const path = req.params.path || 'types';

  if (!allowedPath.test(path)) {
    return res.status(404).json({ message: 'Endpoint nicht erlaubt.' });
  }

  const url = new URL(`${apiBase}/${path}`);

  for (const key of ['stageId', 'templateVariant']) {
    if (req.query[key]) {
      url.searchParams.set(key, String(req.query[key]));
    }
  }

  const headers = {
    Authorization: `Bearer ${apiToken}`,
    'X-Token-Id': tokenId,
    Accept: 'application/json',
  };

  if (contextKey) {
    headers['X-Vehicle-Data-Context'] = contextKey;
  }

  if (poolKey) {
    headers['X-Vehicle-Data-Pool'] = poolKey;
  }

  if (req.method === 'POST') {
    headers['Content-Type'] = 'application/json';
  }

  const response = await fetch(url, {
    method: req.method === 'POST' ? 'POST' : 'GET',
    headers,
    body: req.method === 'POST' ? JSON.stringify(req.body || {}) : undefined,
  });

  const data = await response.json().catch(() => ({ message: 'Ungueltige API-Antwort.' }));

  return res.status(response.status).json(data);
});

app.listen(3000);
```

## 7. Frontend-Einbindung

Im Frontend sprechen Sie nur Ihren eigenen Proxy an:

```js
const apiBase = '/api/vehicle-database';

async function request(path) {
  const response = await fetch(`${apiBase}/${path}`, {
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error(`Vehicle API Fehler: ${response.status}`);
  }

  return response.json();
}

export async function loadTypes() {
  return request('types');
}

export async function loadBrands(typeId) {
  return request(`brands/${typeId}`);
}

export async function loadSeries(brandId) {
  return request(`series/${brandId}`);
}

export async function loadModels(seriesId) {
  return request(`models/${seriesId}`);
}

export async function loadEngines(modelId) {
  return request(`engines/${modelId}`);
}

export async function loadEngineDetails(engineId, options = {}) {
  const params = new URLSearchParams();

  if (options.stageId) {
    params.set('stageId', String(options.stageId));
  }

  if (options.templateVariant) {
    params.set('templateVariant', options.templateVariant);
  }

  const suffix = params.toString() ? `?${params.toString()}` : '';

  return request(`engine-details/${engineId}${suffix}`);
}
```

## 8. Kaskadierende Auswahl im Frontend

Der Ablauf:

1. Beim Laden der Seite `loadTypes()` ausfuehren.
2. Wenn der Nutzer einen Fahrzeugtyp waehlt, `loadBrands(typeId)` ausfuehren.
3. Bei Markenwechsel Baureihen, Modelle, Motoren und Details zuruecksetzen.
4. Wenn der Nutzer eine Marke waehlt, `loadSeries(brandId)` ausfuehren.
5. Wenn der Nutzer eine Baureihe waehlt, `loadModels(seriesId)` ausfuehren.
6. Wenn der Nutzer ein Modell waehlt, `loadEngines(modelId)` ausfuehren.
7. Wenn der Nutzer einen Motor waehlt, `loadEngineDetails(engineId)` ausfuehren.

Wichtig:
Setzen Sie abhaengige Auswahlfelder immer zurueck, wenn sich ein vorheriges Feld aendert.

Beispiel:

```js
async function onTypeChange(typeId) {
  selectedBrand = null;
  selectedSeries = null;
  selectedModel = null;
  selectedEngine = null;
  engineDetails = null;

  brands = await loadBrands(typeId);
  series = [];
  models = [];
  engines = [];
}
```

## 9. Erwartete Antwortformate

Listen-Endpunkte liefern immer ein `data` Array:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Audi"
    }
  ]
}
```

Motor-Details liefern ein Objekt unter `data`.

Vollstaendiges Beispiel:

```json
{
  "data": {
    "engine": {
      "id": 123,
      "name": "2.0 TFSI",
      "horsepower": 245,
      "torque": 370,
      "fuel": "Benzin",
      "displacement": "1984 ccm",
      "image": "/uploads/vehicle-engine/audi-20-tfsi.png",
      "glbModel": "/uploads/vehicle-engine-glb/audi-20-tfsi.glb",
      "mediaDisplayMode": "image"
    },
    "brand": {
      "id": 10,
      "name": "Audi",
      "image": "/uploads/brand-master/audi.svg"
    },
    "series": {
      "id": 55,
      "name": "A3"
    },
    "model": {
      "id": 210,
      "name": "A3 Sportback (8Y)"
    },
    "stages": [
      {
        "id": 1,
        "name": "Stage 1",
        "description": "Optimierung der Serien-Software ohne Hardwareumbau.",
        "price": 399,
        "hp": 300,
        "nm": 450,
        "ecu": "Bosch MED17.5",
        "icon": "/uploads/vehicle-stage/stage-1.svg",
        "images": [
          "/uploads/vehicle-stage/stage-1-1.jpg",
          "/uploads/vehicle-stage/stage-1-2.jpg"
        ],
        "videos": "https://example.com/video-stage-1"
      },
      {
        "id": 2,
        "name": "Stage 2",
        "description": "Leistungssteigerung fuer Fahrzeuge mit angepasster Hardware.",
        "price": 599,
        "hp": 330,
        "nm": 490,
        "ecu": "Bosch MED17.5",
        "icon": "/uploads/vehicle-stage/stage-2.svg",
        "images": [],
        "videos": null
      }
    ],
    "ecus": [
      {
        "id": 77,
        "name": "MED17.5",
        "type": "ECU",
        "controlUnitKind": "MCU",
        "manufacturer": "Bosch",
        "manufacturerLogo": "/uploads/ecu-manufacturer/bosch.svg",
        "toolEntries": [
          {
            "manufacturer": "Alientech",
            "manufacturerLogo": "/uploads/tool-manufacturer/alientech.svg",
            "methods": ["OBD", "Bench"]
          },
          {
            "manufacturer": "Flashtec",
            "manufacturerLogo": "/uploads/tool-manufacturer/flashtec.svg",
            "methods": ["OBD"]
          }
        ],
        "toolManufacturer": "Alientech",
        "toolManufacturerLogo": "/uploads/tool-manufacturer/alientech.svg",
        "toolMethods": ["OBD", "Bench"],
        "options": [
          {
            "id": 501,
            "name": "OBD",
            "masterOptionName": "OBD-Programmierung",
            "description": "Lesen und Schreiben ueber OBD-Schnittstelle.",
            "icon": "/uploads/tool-methode/obd.svg",
            "price": 0,
            "isMainOption": true
          },
          {
            "id": 502,
            "name": "Bench",
            "masterOptionName": "Bench-Programmierung",
            "description": "Lesen und Schreiben direkt am Steuergeraet.",
            "icon": "/uploads/tool-methode/bench.svg",
            "price": 99,
            "isMainOption": false
          }
        ],
        "optionHints": [
          {
            "masterOption": "OBD-Programmierung",
            "hints": [
              {
                "brandName": "Audi",
                "manufacturer": "Bosch",
                "model": "MED17.5",
                "notes": "OBD-Stecker befindet sich unter dem Lenkrad links."
              }
            ]
          }
        ]
      }
    ],
    "gearboxes": [
      {
        "id": 31,
        "title": "DQ381",
        "description": "7-Gang DSG Getriebe.",
        "icon": "/uploads/gearbox/dq381.svg",
        "images": [
          "/uploads/gearbox/dq381-1.webp"
        ],
        "type": "DSG",
        "manufacturer": "Volkswagen AG",
        "manufacturerLogo": "/uploads/gearbox-manufacturer/vw.svg"
      }
    ],
    "einbauHinweise": [
      {
        "id": 12,
        "title": "Steuergeraet ausbauen",
        "description": "Batterie abklemmen, dann das Steuergeraet im Motorraum links vorne loesen.",
        "ecuOptionMaster": "Bench-Programmierung",
        "activeEcuId": 77,
        "activeEcuName": "MED17.5",
        "images": [
          {
            "src": "/uploads/einbau-hinweis/med175-ausbau-1.jpg",
            "annotations": [
              { "x": 0.42, "y": 0.31, "label": "Stecker loesen" }
            ]
          },
          {
            "src": "/uploads/einbau-hinweis/med175-ausbau-2.jpg",
            "annotations": []
          }
        ]
      }
    ],
    "settings": {
      "layoutSwitcherEnabled": true,
      "templateVariant": "performance",
      "layoutSettings": {
        "performance": {
          "glbAutorotate": true,
          "showPrices": true,
          "showLogo": true,
          "showOnlyStage1": false
        },
        "minimal": {
          "glbAutorotate": false,
          "showPrices": false,
          "showLogo": false,
          "showOnlyStage1": true
        }
      }
    }
  }
}
```

Hinweise zu den ECU-Feldern:

- `toolEntries` — Liste aller unterstuetzten Tool-Hersteller fuer diese ECU, je mit eigenen Methoden. Dieses Feld ersetzt die Legacy-Felder.
- `toolManufacturer` / `toolManufacturerLogo` / `toolMethods` — Legacy-Felder, enthalten nur den ersten Eintrag aus `toolEntries`. Werden fuer Abwaertskompatibilitaet weiterhin geliefert.
- `masterOptionName` auf einer ECU-Option — Name der uebergeordneten Master-Option (z. B. "OBD-Programmierung"), die mehrere Unter-Optionen zusammenfasst.
- `optionHints` — Fahrzeug- und herstellerspezifische Hinweise zur jeweiligen Option (z. B. wo der OBD-Stecker sitzt), gruppiert nach Master-Option.
- `einbauHinweise` — Motorspezifische Einbauhinweise (z. B. Steuergeraet ausbauen), unabhaengig von den ECU-Optionen. Enthalten Bilder mit optionalen Annotationen.

## 10. Tracking einbinden

Tracking ist optional. Nutzen Sie es, wenn Auswahlvorgaenge oder Konfigurator-Nutzung ausgewertet werden sollen.

Frontend-Beispiel ueber den eigenen Proxy:

```js
async function trackVehicleEvent(payload) {
  await fetch('/api/vehicle-database/track', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify(payload),
  });
}

trackVehicleEvent({
  event: 'engine_selected',
  engineId: 123,
  templateVariant: 'performance',
  meta: {
    source: 'customer-website',
  },
});
```

## 11. Fehlerbehandlung

Behandeln Sie mindestens diese Faelle:

- `401`: Token fehlt, Token-ID fehlt oder Zugangsdaten ungueltig
- `404`: Datenpool oder Motor nicht gefunden
- leere `data` Arrays: Es gibt fuer diese Auswahl keine aktiven Folgedaten
- Netzwerkfehler oder Timeout

Empfehlung fuer die UI:

- Bei leeren Listen das naechste Auswahlfeld deaktivieren.
- Bei `401` eine technische Fehlermeldung anzeigen und den Betreiber informieren.
- Bei `404` Auswahl zuruecksetzen oder eine neutrale Meldung anzeigen.
- Requests abbrechen oder ignorieren, wenn der Nutzer schnell mehrere Auswahlfelder hintereinander aendert.

## 12. Caching

Viele Listen aendern sich nicht bei jedem Seitenaufruf. Sie koennen serverseitig oder im Frontend kurz gecacht werden.

Empfehlung:

- `types`: 1 bis 24 Stunden
- `brands`, `series`, `models`: 10 Minuten bis 24 Stunden
- `engines`: 10 Minuten bis 24 Stunden
- `engine-details`: eher kurz cachen, wenn Preise oder Einstellungen haeufig wechseln

Wenn DCT Daten aktualisiert und Sie sofort neue Daten sehen muessen, leeren Sie Ihren Cache.

## 13. Test der Einbindung

Pruefen Sie in dieser Reihenfolge:

1. Optional: Gibt der Basis-Endpunkt `/api/fahrzeugdatenbank` eine gueltige Antwort zurueck?
2. Gibt `/api/vehicle-database/types` Daten zurueck?
3. Funktionieren die IDs aus `types` bei `brands/{typeId}`?
4. Funktioniert die komplette Kette bis `engine-details/{engineId}`?
5. Sind Bilder, Icons und optionale Medienpfade im Frontend korrekt aufloesbar?
6. Bleibt der API-Token im Browser unsichtbar?
7. Werden Fehler sauber abgefangen?

## 14. Hilfreiche Dateien

- Kundenbeschreibung: `docs/fahrzeugdatenbank-api-kunden.md`
- OpenAPI: `docs/openapi-vehicle.json`
- Postman Collection: `docs/postman-vehicle.collection.json`
- Technische API-Doku: `docs/api.md`
