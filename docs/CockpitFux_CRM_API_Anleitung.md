# CockpitFux CRM API - Anleitung

Stand: 20.03.2026

Diese Datei beschreibt die aktuell verfuegbaren externen API-Endpunkte der TFD-CRM-Anwendung. Die bisherige Version mit Preview-Domain und MongoDB-Beispielen war nicht mehr Teil dieses Projekts und wurde auf den realen Stand der Symfony-Anwendung aktualisiert.

## Uebersicht

- Base URL: `https://verwaltung.tuningfux.de`
- API Root: `https://verwaltung.tuningfux.de/api`
- Framework: Symfony 8
- Antwortformat: JSON

## 1. Authentifizierung

Im Projekt gibt es drei relevante Auth-Varianten:

### 1.1 Customer Access Token

Fuer Kunden-Apps und Portale:

- `POST /api/auth/login`
- `POST /api/auth/refresh`
- `GET /api/auth/me`
- `POST /api/auth/logout`

Header fuer Folge-Requests:

```text
Authorization: Bearer <ACCESS_TOKEN>
```

### 1.2 System- und Admin-Token

Fuer Server-zu-Server-Aufrufe:

```text
Authorization: Bearer <API_TOKEN>
```

Akzeptiert werden:

- `API_TOKEN` aus der Umgebung
- oder ein aktiver API-Token aus `Verwaltung -> API Tokens`

### 1.3 Fahrzeugdatenbank-Token mit Token-ID

Nur fuer `/api/fahrzeugdatenbank/*`:

```text
Authorization: Bearer <API_TOKEN>
X-Token-Id: <TOKEN_ID>
```

## 2. Verfuegbare API-Bereiche

### 2.1 Kunden-Login

- `POST /api/auth/login`
- `POST /api/auth/refresh`
- `GET /api/auth/me`
- `POST /api/auth/logout`

Beispiel:

```json
{
  "email": "kunde@example.com",
  "password": "DeinPasswort",
  "deviceName": "ios-app"
}
```

### 2.2 Kundenverwaltung fuer Integrationen

- `GET /api/customers`
- `GET /api/customers/{id}`
- `POST /api/customers`
- `PUT /api/customers/{id}`
- `PATCH /api/customers/{id}`
- `DELETE /api/customers/{id}`

Hinweise:

- Passwortaenderungen koennen ueber `plainPassword` erfolgen.
- Antworten und Fehler kommen als JSON.

### 2.3 Auftraege

- `POST /api/orders`

Der Endpunkt akzeptiert:

- Customer-Access-Token
- oder System-/Admin-API-Token

Pflichtfelder:

- `customerId`
- `customerEmail`
- `fileName`
- `fileData`
- `fileSize`
- `vehicleType`
- `manufacturer`
- `model`
- `built`
- `engine`
- `vehicleDisplay`
- `tuningTool`
- `method`
- `slaveOrMaster`
- `stage`
- `createdAt`
- `deviceName`

Bei erfolgreicher Erstellung wird `order.created` als Webhook-Event ausgeliefert.

### 2.4 Fahrzeugdatenbank

- `GET /api/fahrzeugdatenbank`
- `GET /api/fahrzeugdatenbank/types`
- `GET /api/fahrzeugdatenbank/brands/{typeId}`
- `GET /api/fahrzeugdatenbank/series/{brandId}`
- `GET /api/fahrzeugdatenbank/models/{seriesId}`
- `GET /api/fahrzeugdatenbank/engines/{modelId}`
- `GET /api/fahrzeugdatenbank/engine-details/{engineId}`
- `POST /api/fahrzeugdatenbank/track`

`engine-details` liefert unter anderem:

- `engine`
- `brand`
- `series`
- `stages`
- `ecus`
- `gearboxes`
- `settings`

### 2.5 Dyno / Telemetrie

Oeffentlich:

- `GET /api/vehicles`
- `POST /api/vehicles`
- `PUT /api/vehicles/{id}/activate`
- `DELETE /api/vehicles/{id}`
- `POST /api/vehicles/import-tuningfux`
- `GET /api/trips`
- `POST /api/trips`
- `DELETE /api/trips/{id}`
- `GET /api/trips/export/csv`
- `GET /api/dtcs`
- `POST /api/dtcs/batch`
- `DELETE /api/dtcs`

Geschuetzt:

- `GET /api/crm/customers`
- `GET /api/crm/customers/{id}`
- `POST /api/crm/customers`
- `PUT /api/crm/customers/{id}`
- `DELETE /api/crm/customers/{id}`
- `GET /api/crm/trips`
- `DELETE /api/crm/trips/{id}`
- `GET /api/crm/trips/{id}/datapoints`
- `GET /api/crm/sprints`
- `POST /api/crm/sprints`
- `GET /api/crm/sprints/bestlist`
- `GET /api/crm/analytics/overview`
- `GET /api/crm/analytics/per-vehicle`
- `GET /api/crm/analytics/per-mode`

### 2.6 Tuningfux Proxy API

- `GET /api/tuningfux/types`
- `GET /api/tuningfux/brands/{typeId}`
- `GET /api/tuningfux/series/{brandId}`
- `GET /api/tuningfux/models/{seriesId}`
- `GET /api/tuningfux/engines/{modelId}`
- `GET /api/tuningfux/engine-details/{engineId}`

## 3. Typische Curl-Beispiele

### Login

```bash
curl -X POST "https://verwaltung.tuningfux.de/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "kunde@example.com",
    "password": "DeinPasswort",
    "deviceName": "android-app"
  }'
```

### Auftragsuebermittlung

```bash
curl -X POST "https://verwaltung.tuningfux.de/api/orders" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "customerId": 1,
    "customerEmail": "kunde@example.com",
    "fileName": "ori.bin",
    "fileData": "QkFTRTY0...",
    "fileSize": 1024,
    "vehicleType": "PKW",
    "manufacturer": "Audi",
    "model": "A6 C7",
    "built": "2011",
    "engine": "3.0 TDI EU6",
    "vehicleDisplay": "Audi A6 C7 3.0 TDI EU6",
    "tuningTool": "Autotuner",
    "method": "OBD",
    "slaveOrMaster": "Master",
    "stage": "Stage 1",
    "createdAt": "2026-03-20T10:00:00+00:00",
    "deviceName": "web"
  }'
```

### Fahrzeugdatenbank API

```bash
curl "https://verwaltung.tuningfux.de/api/fahrzeugdatenbank/types" \
  -H "Authorization: Bearer <API_TOKEN>" \
  -H "X-Token-Id: <TOKEN_ID>"
```

## 4. Technische Hinweise

- Die Anwendung nutzt Doctrine ORM und relationale Datenbanken, nicht MongoDB.
- Die alte Preview-Domain `cockpit-coding.preview.emergentagent.com` ist fuer dieses Projekt nicht gueltig.
- Die aktuelle Detaildoku liegt zusaetzlich in `docs/api.md`.
- OpenAPI-Dateien liegen unter `docs/openapi.json` und `docs/openapi-vehicle.json`.
