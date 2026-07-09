# OpenClaw API

Stand: 20.03.2026

Die OpenClaw-API ist eine eigene CRUD-API unter `/api/openclaw`. Sie ist ausschliesslich fuer Tokens gedacht, deren `applicationName` mit `OpenClaw` beginnt.

## Authentifizierung

Header:

```text
Authorization: Bearer <OPENCLAW_API_TOKEN>
```

Wenn du lokal mit Shell-Umgebungsvariablen arbeitest:

```bash
export OPENCLAW_API_TOKEN=...
export OPENCLAW_API_BASE=https://verwaltung.tuningfux.de/api/openclaw
```

Wichtig:

- Der Token muss in `Verwaltung -> API Tokens` aktiv sein.
- `applicationName` muss mit `OpenClaw` beginnen, zum Beispiel `OpenClaw`, `OpenClaw Prod` oder `OpenClaw Sync`.

## Symfony Integration

Verfuegbare Env-Variablen:

- `OPENCLAW_API_BASE`
- `OPENCLAW_API_TOKEN`
- `OPENCLAW_API_TIMEOUT`
- `OPENCLAW_API_USER_AGENT`

Im Projekt gibt es dafuer bereits diese Services:

- `App\Service\Api\OpenClawApiCredentials`
- `App\Service\Api\OpenClawApiClient`

Beispiel fuer die Verwendung in einem eigenen Service:

```php
<?php

namespace App\Service;

use App\Service\Api\OpenClawApiClient;

final class ExampleService
{
    public function __construct(
        private readonly OpenClawApiClient $openClawApiClient,
    ) {
    }

    public function fetchCustomers(): array
    {
        return $this->openClawApiClient->list('kunde', [
            'page' => 1,
            'limit' => 25,
            'orderBy' => 'id',
            'orderDir' => 'desc',
        ]);
    }
}
```

Verfuegbare Komfortmethoden auf `OpenClawApiClient`:

- `index()`
- `list(string $resource, array $query = [])`
- `schema(string $resource)`
- `detail(string $resource, string|int $id)`
- `create(string $resource, array $payload)`
- `update(string $resource, string|int $id, array $payload, bool $partial = true)`
- `delete(string $resource, string|int $id)`

## Endpunkte

- `GET /api/openclaw`
- `GET /api/openclaw/{resource}`
- `GET /api/openclaw/{resource}/schema`
- `GET /api/openclaw/{resource}/{id}`
- `POST /api/openclaw/{resource}`
- `PUT /api/openclaw/{resource}/{id}`
- `PATCH /api/openclaw/{resource}/{id}`
- `DELETE /api/openclaw/{resource}/{id}`

## Verhalten

### Ressourcenliste

`GET /api/openclaw`

Liefert alle freigegebenen Ressourcen inklusive Klassenname und Schema-Route.

### Listenansicht

`GET /api/openclaw/{resource}`

Unterstuetzt Query-Parameter:

- `page`
- `limit`
- `orderBy`
- `orderDir`
- `ids`
- `view=compact|full`
- `filter[field]=wert`

Beispiel:

```bash
curl "https://verwaltung.tuningfux.de/api/openclaw/kunde?page=1&limit=25&orderBy=id&orderDir=desc" \
  -H "Authorization: Bearer <OPENCLAW_API_TOKEN>"
```

### Schema

`GET /api/openclaw/{resource}/schema`

Liefert:

- Identifier-Felder
- skalare Felder
- Assoziationen
- Hinweis, ob Felder als `heavy` oder `writable` markiert sind

### Detailansicht

`GET /api/openclaw/{resource}/{id}`

Liefert einen einzelnen Datensatz inklusive To-One-Referenzen und Collection-Zaehlungen.

### Erstellen

`POST /api/openclaw/{resource}`

Beispiel:

```bash
curl -X POST "https://verwaltung.tuningfux.de/api/openclaw/firma" \
  -H "Authorization: Bearer <OPENCLAW_API_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "OpenClaw Test GmbH",
    "aktiv": true
  }'
```

### Aendern

`PUT /api/openclaw/{resource}/{id}` oder `PATCH /api/openclaw/{resource}/{id}`

Beispiel:

```bash
curl -X PATCH "https://verwaltung.tuningfux.de/api/openclaw/kunde/1" \
  -H "Authorization: Bearer <OPENCLAW_API_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "telefon": "+49 123 456789",
    "newsletter": true
  }'
```

### Loeschen

`DELETE /api/openclaw/{resource}/{id}`

Antwort bei Erfolg: `204 No Content`

## Praxisbeispiele

### Ressourcen zuerst entdecken

```bash
curl "https://verwaltung.tuningfux.de/api/openclaw" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN"
```

Typischer Nutzen:

- verfuegbare Resource-Namen nachschauen
- Klassenname und Schema-Endpunkt sehen

### Kunden kompakt laden

```bash
curl "https://verwaltung.tuningfux.de/api/openclaw/kunde?page=1&limit=10&view=compact&orderBy=id&orderDir=desc" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN"
```

Praktisch fuer:

- Listenansichten
- Sync-Jobs
- erste Schnellpruefung einer Resource

### Nach IDs filtern

```bash
curl "https://verwaltung.tuningfux.de/api/openclaw/kunde?ids=1,2,5" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN"
```

### Exakt nach Feld filtern

```bash
curl "https://verwaltung.tuningfux.de/api/openclaw/kunde?filter[email]=kunde@example.com" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN"
```

Hinweis:

- `filter[field]=wert` ist ein exakter Vergleich
- fuer die erlaubten Feldnamen ist `GET /api/openclaw/{resource}/schema` am hilfreichsten

### Detailansicht mit Beziehungen

```bash
curl "https://verwaltung.tuningfux.de/api/openclaw/kunde/1" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN"
```

Die Antwort enthaelt ueblicherweise:

- `_meta` mit Klasse, Resource und ID
- skalare Felder
- To-One-Assoziationen als Objekt mit `resource`, `class`, `id`, `label`
- Collections als Objekt mit `count` und optionalen `items`

### Schema vor einem Write pruefen

```bash
curl "https://verwaltung.tuningfux.de/api/openclaw/firma/schema" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN"
```

Das ist besonders sinnvoll, wenn du:

- Feldtypen vorab validieren willst
- sehen willst, ob ein Feld `writable` ist
- Assoziationen korrekt setzen willst

### Firma anlegen

```bash
curl -X POST "https://verwaltung.tuningfux.de/api/openclaw/firma" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "OpenClaw Demo GmbH",
    "aktiv": true
  }'
```

### Kunde mit To-One-Assoziation anlegen

```bash
curl -X POST "https://verwaltung.tuningfux.de/api/openclaw/kunde" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "vorname": "Max",
    "nachname": "Mustermann",
    "email": "max.mustermann@example.com",
    "firma": { "id": 1 }
  }'
```

Die To-One-Verknuepfung kann je nach Entity meist so gesetzt werden:

- direkt per ID
- oder per Objekt mit `id`

### Datensatz teilweise aendern

```bash
curl -X PATCH "https://verwaltung.tuningfux.de/api/openclaw/kunde/1" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "telefon": "+49 123 456789",
    "newsletter": true
  }'
```

### Collection-Assoziation per ID-Liste synchronisieren

```bash
curl -X PATCH "https://verwaltung.tuningfux.de/api/openclaw/user/1" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "rollen": [1, 2, 3]
  }'
```

Wichtig:

- Collection-Updates laufen `best effort`
- die Ziel-IDs muessen bereits existieren

### Passwortfelder setzen

Bei Spezialfaellen behandelt die API bestimmte Felder mit vorhandener Business-Logik:

- bei `Kunde` funktionieren `plainPasswort` und `plainPassword`
- bei `User` funktioniert `plainPassword`

Beispiel:

```bash
curl -X PATCH "https://verwaltung.tuningfux.de/api/openclaw/user/1" \
  -H "Authorization: Bearer $OPENCLAW_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "plainPassword": "NeuesSicheresPasswort123"
  }'
```

### Symfony Client Beispiel: Schema laden

```php
<?php

namespace App\Service;

use App\Service\Api\OpenClawApiClient;

final class OpenClawSchemaExampleService
{
    public function __construct(
        private readonly OpenClawApiClient $openClawApiClient,
    ) {
    }

    public function getCustomerSchema(): array
    {
        return $this->openClawApiClient->schema('kunde');
    }
}
```

### Symfony Client Beispiel: Kunde anlegen

```php
<?php

namespace App\Service;

use App\Service\Api\OpenClawApiClient;

final class OpenClawCustomerExampleService
{
    public function __construct(
        private readonly OpenClawApiClient $openClawApiClient,
    ) {
    }

    public function createCustomer(): array
    {
        return $this->openClawApiClient->create('kunde', [
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'email' => 'max.mustermann@example.com',
        ]);
    }
}
```

## Schreiblogik

Die API ist generisch aufgebaut:

- skalare Doctrine-Felder koennen direkt gesetzt werden
- To-One-Assoziationen koennen per ID oder Objekt mit `id` gesetzt werden
- Collection-Assoziationen werden best effort ueber ID-Listen synchronisiert
- Setter werden bevorzugt benutzt, damit bestehende Entity-Logik erhalten bleibt
- bei `Kunde` wird `plainPasswort` oder `plainPassword` korrekt gehasht
- bei `User` wird `plainPassword` korrekt gehasht

## Umfang

OpenClaw kann jetzt grundsaetzlich alle Doctrine-Entities unter `App\Entity` lesen und schreiben, also auch:

- Benutzer
- Benutzergruppen 
- API-Tokens
- Webhook-Endpunkte
- Zugangsdaten
- E-Mail-Konten
- Seitenrechte
- Kunden, Auftraege, Support, Shop, Fahrzeugdatenbank, AI, Projektboards

Wenn du die Struktur einer Resource zuerst anschauen willst, nutze:

```bash
GET /api/openclaw/{resource}/schema
```
