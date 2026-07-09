# API-Integration fuer TFD CRM

Stand: 13.05.2026

Die CRM-API ist direkt in diese Symfony-8-Anwendung eingebettet. Extern ist der Einstieg ueber den Host erreichbar, also ohne `/public`.

- Base URL: `https://portal.dct-files.de`
- API Root: `https://portal.dct-files.de/api`
- Content-Type fuer JSON-Requests: `application/json`

## 1. Auth API fuer Kunden-Login

Diese Endpunkte sind fuer Kunden-Apps oder Kundenportale gedacht. Beim Login werden Access- und Refresh-Tokens pro Kunde und optional pro Device ausgestellt.

### Endpunkte

- `POST /api/auth/login`
- `POST /api/auth/refresh`
- `GET /api/auth/me`
- `POST /api/auth/logout`

### Login

`POST /api/auth/login`

Request:

```json
{
  "email": "kunde@example.com",
  "password": "DeinPasswort",
  "deviceName": "android-app"
}
```

Erfolgsantwort:

```json
{
  "tokenType": "Bearer",
  "accessToken": "...",
  "accessTokenExpiresAt": "2026-03-20T12:00:00+00:00",
  "refreshToken": "...",
  "refreshTokenExpiresAt": "2026-04-19T12:00:00+00:00",
  "customer": {
    "id": 1,
    "companyName": "Muster GmbH",
    "email": "kunde@example.com",
    "username": "kunde@example.com"
  }
}
```

Wichtig:

- `email` kann alternativ auch als `username` gesendet werden.
- `deviceName` kann alternativ auch `device_name` heissen.
- `username` in der Antwort entspricht aktuell immer der Kunden-E-Mail.
- Gesperrte Kunden erhalten `403 Forbidden`.

### Authentifizierte Requests

```text
Authorization: Bearer <ACCESS_TOKEN>
```

### Refresh

`POST /api/auth/refresh`

```json
{
  "refreshToken": "<DEIN_REFRESH_TOKEN>"
}
```

### Logout

`POST /api/auth/logout`

Antwort: `204 No Content`

## 2. Customer API fuer System-Integrationen

Diese API ist fuer Server-zu-Server-Kommunikation gedacht.

### Endpunkte

- `GET /api/customers`
- `GET /api/customers/{id}`
- `POST /api/customers`
- `PUT /api/customers/{id}`
- `PATCH /api/customers/{id}`
- `DELETE /api/customers/{id}`

### Authentifizierung

```text
Authorization: Bearer <TOKEN>
```

Der Bearer-Token kann sein:

- der Umgebungswert `API_TOKEN`
- oder ein aktiver API-Token aus `Verwaltung -> API Tokens`

### Hinweise

- Responses liefern Kundendaten als JSON.
- Beim Anlegen und Aktualisieren werden Validierungen serverseitig erzwungen.
- `plainPassword` kann uebergeben werden. Neue Passwoerter werden automatisch gehasht.
- `DELETE /api/customers/{id}` liefert auch dann `204 No Content`, wenn der Datensatz bereits nicht mehr existiert.

## 3. Order API

Fuer die Uebermittlung von Tuning-Auftraegen.

### Endpunkt

- `POST /api/orders`

### Authentifizierung

```text
Authorization: Bearer <TOKEN>
```

Akzeptiert:

- einen gueltigen Customer-Access-Token aus `/api/auth/login`
- oder einen System-/Admin-API-Token

Wenn ein Customer-Access-Token verwendet wird, muss `customerId` zur angemeldeten Kunden-ID passen.

### Pflichtfelder

- `customerId` (Integer)
- `customerEmail` (String)
- `fileName` (String)
- `fileData` (String, Base64; Data-URI wird ebenfalls akzeptiert)
- `fileSize` (Integer, Byte-Groesse der dekodierten Datei)
- `vehicleType` (String)
- `manufacturer` (String)
- `model` (String)
- `built` (String)
- `engine` (String)
- `vehicleDisplay` (String)
- `tuningTool` (String)
- `method` (String)
- `slaveOrMaster` (String)
- `stage` (String)
- `createdAt` (String, ISO-8601)
- `deviceName` (String)

### Optionale Fahrzeugdaten

Zusaetzliche Fahrzeugdaten koennen auf drei Arten mitgegeben werden:

- als `vehicleData` Objekt
- alternativ als `fahrzeugDaten` Objekt
- alternativ ueber einzelne Top-Level-Felder wie `vehicleBrand`, `vehicleModel`, `vehicleEngine`, `vehicleVin`

### Verhalten

- `customerEmail` muss zur angegebenen `customerId` passen.
- `fileSize` muss exakt zur dekodierten Base64-Datei passen.
- Erfolgreiche Erstellung liefert `201 Created`.
- Bei Erfolg wird zusaetzlich das Webhook-Event `order.created` ausgeloest.

## 4. Fahrzeugdatenbank API

Die externe Fahrzeugdatenbank-API ist separat abgesichert.

Ergaenzende interne Detaildoku zu Datenpools und Datenkontexten:

- [docs/vehicle-data-pools-contexts.md](/home/verwaltung/htdocs/portal.dct-files.de/public/docs/vehicle-data-pools-contexts.md)
- [docs/website-widget.md](/home/verwaltung/htdocs/portal.dct-files.de/public/docs/website-widget.md)

### Endpunkte

- `GET /api/fahrzeugdatenbank`
- `GET /api/fahrzeugdatenbank/types`
- `GET /api/fahrzeugdatenbank/brands/{typeId}`
- `GET /api/fahrzeugdatenbank/series/{brandId}`
- `GET /api/fahrzeugdatenbank/models/{seriesId}`
- `GET /api/fahrzeugdatenbank/engines/{modelId}`
- `GET /api/fahrzeugdatenbank/engine-details/{engineId}`
- `POST /api/fahrzeugdatenbank/track`

### Website Widget

Das Website Widget ist keine direkte externe API-Nutzung durch den Kundenbrowser. Es nutzt serverseitige Proxy-Routen unter `/widget/{embedKey}/api/*` und setzt den hinterlegten `ApiToken` intern.

Oeffentliche Widget-Routen:

- `GET /widget/{embedKey}/embed.js`
- `GET /widget/{embedKey}`
- `GET|POST /widget/{embedKey}/anfrage`

Proxy-Routen:

- `GET /widget/{embedKey}/api/types`
- `GET /widget/{embedKey}/api/brands/{typeId}`
- `GET /widget/{embedKey}/api/series/{brandId}`
- `GET /widget/{embedKey}/api/models/{seriesId}`
- `GET /widget/{embedKey}/api/engines/{modelId}`
- `GET /widget/{embedKey}/api/engine-details/{engineId}`
- `POST /widget/{embedKey}/api/track`
- `POST /widget/{embedKey}/api/inquiry`

Der Kunde bekommt nur ein Snippet. API-Token und Token-ID werden nie im Browser ausgegeben.

### Authentifizierung

Beide Header sind erforderlich:

```text
Authorization: Bearer <API_TOKEN>
X-Token-Id: <TOKEN_ID>
```

Hinweise:

- Tokens werden unter `Verwaltung -> API Tokens` verwaltet.
- Token koennen einem Kunden zugeordnet und mit Ablaufdatum versehen werden.
- Token koennen optional an einen `VehicleDataContext` gebunden sein.
- Die Selektions-Endpunkte liefern nur aktive Datensaetze.
- Die API arbeitet poolbasiert. Ohne explizite Auswahl wird der Token-Kontext, dessen Standard-Pool oder der aktive Pool `live` verwendet.

Optionale Kontext-/Pool-Auswahl:

- Header `X-Vehicle-Data-Context: <contextKey>`
- Header `X-Vehicle-Data-Pool: <poolKey>`
- Query-Alternative `?contextKey=<contextKey>&poolKey=<poolKey>`
- Header haben Vorrang vor Query-Parametern.
- Ein explizit gewaehlter Pool muss im gewaehlten Kontext erlaubt sein.

### Engine-Details

`GET /api/fahrzeugdatenbank/engine-details/{engineId}`

Die Antwort enthaelt u. a.:

- `engine`
- `brand`
- `series`
- `stages`
- `ecus`
- `gearboxes`
- `settings`

Zusatzhinweise:

- `stageId` kann optional als Query-Parameter gesetzt werden.
- `templateVariant` kann optional als Query-Parameter gesetzt werden.
- Preise in Stages und ECU-Optionen haengen von den Frontend-Settings ab.
- Die Antwort enthaelt zusaetzlich `pool` mit `id`, `key` und `name`.
- `gearboxes` enthaelt aktive Getriebe inkl. Hersteller, Typ, Icon und Bildern.

### Tracking

`POST /api/fahrzeugdatenbank/track`

Fuer Client-Tracking koennen z. B. folgende Felder gesendet werden:

- `event`
- `typeId`
- `vehicleTypeId`
- `brandId`
- `seriesId`
- `modelId`
- `engineId`
- `stageId`
- `templateVariant`
- `meta`

### OpenAPI

- OpenAPI gesamt: `docs/openapi.json`
- OpenAPI nur Fahrzeugdatenbank: `docs/openapi-vehicle.json` (aktuelle Fahrzeugdatenbank-Beschreibung)
- Postman Collection Fahrzeugdatenbank: `docs/postman-vehicle.collection.json`
- Kundenbeschreibung fuer gemietete API-Anbindungen: `docs/fahrzeugdatenbank-api-kunden.md`
- Einbindungsanleitung fuer Kundenentwickler: `docs/fahrzeugdatenbank-api-einbindung.md`

## 5. DTC-Katalog API

Controller: `src/Controller/Api/DtcCatalogApiController.php`

Diese API liest den DTC-Code-Katalog aus dem Admin-Bereich `Verwaltung -> Tools -> Fehlercodes`.
Ausgegeben werden nur Datensaetze mit `hidden = false` und `deleted = false`.

### Endpunkte

- `GET /api/dtc-codes`
- `GET /api/dtc-codes?q={suchbegriff}`
- `GET /api/dtc-codes?category={uid|buchstabe|name}`
- `GET /api/dtc-codes/{uidOderCode}`
- `GET /api/dtc-codes/categories`
- `GET /api/dtc-codes/categories/{uid}`
- `GET /api/dtc-codes/categories/{uid}/codes`

Alias-Routen fuer Kategorien:

- `GET /api/dtc-categories`
- `GET /api/dtc-categories/{uid}`
- `GET /api/dtc-categories/{uid}/codes`

### Authentifizierung

Aktuell keine separate API-Token-Pruefung. Die Routes sind nur auf dem Superadmin-Host-Kontext registriert.

### Codes listen

`GET /api/dtc-codes`

Query-Parameter:

- `q` sucht in Fehlercode und Beschreibung.
- `category` filtert nach Kategorie-UID, Kategorie-Buchstabe oder exaktem Kategorie-Namen.

Beispielantwort:

```json
{
  "data": [
    {
      "uid": 123,
      "code": "P0100",
      "description": "Mass or Volume Air Flow Circuit",
      "category": {
        "uid": 1,
        "name": "Powertrain",
        "letter": "P",
        "description": "",
        "created_at": "2026-03-20T12:00:00+00:00",
        "updated_at": "2026-03-20T12:00:00+00:00"
      },
      "created_at": "2026-03-20T12:00:00+00:00",
      "updated_at": "2026-03-20T12:00:00+00:00"
    }
  ],
  "total": 1
}
```

### Einzelnen Code laden

`GET /api/dtc-codes/{uidOderCode}`

`uidOderCode` kann entweder die numerische `uid` oder der Fehlercode sein, z. B. `P0100`.

### Kategorien

`GET /api/dtc-codes/categories`

Die Antwort enthaelt pro Kategorie zusaetzlich `code_count`.

`GET /api/dtc-codes/categories/{uid}/codes`

Liefert alle sichtbaren Codes einer sichtbaren Kategorie.

## 6. Dyno- und Telemetrie-API

Diese Endpunkte sind fuer Telemetrie, Fahrten, Sprintzeiten und CRM-Auswertungen vorhanden.

### Oeffentliche Telemetrie-Endpunkte

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

### Geschuetzte CRM-Endpunkte

Diese Endpunkte erwarten einen Bearer-Token. Akzeptiert wird der env-Token oder ein aktiver API-Token aus der Datenbank.

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

## 7. Tuningfux API fuer externe Fahrzeugdaten

Neben der Fahrzeugdatenbank-API gibt es zusaetzlich Proxy-Endpunkte unter `/api/tuningfux`.

### Endpunkte

- `GET /api/tuningfux/types`
- `GET /api/tuningfux/brands/{typeId}`
- `GET /api/tuningfux/series/{brandId}`
- `GET /api/tuningfux/models/{seriesId}`
- `GET /api/tuningfux/engines/{modelId}`
- `GET /api/tuningfux/engine-details/{engineId}`

### Authentifizierung

```text
Authorization: Bearer <TOKEN>
```

Akzeptiert wird der Umgebungswert `API_TOKEN` oder ein aktiver API-Token aus der Datenbank.

## 8. Relevante ENV-Konfiguration

```dotenv
API_ACCESS_TOKEN_TTL=3600
API_REFRESH_TOKEN_TTL=2592000
API_TOKEN=<lange-zufaellige-zeichenkette>
```

Ein Beispiel fuer einen sicheren Token:

```bash
openssl rand -hex 32
```

## 9. Curl-Beispiele

### Login

```bash
curl -X POST "https://portal.dct-files.de/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "kunde@example.com",
    "password": "DeinPasswort",
    "deviceName": "android-app"
  }'
```

### Auth-Status abfragen

```bash
curl "https://portal.dct-files.de/api/auth/me" \
  -H "Authorization: Bearer <ACCESS_TOKEN>"
```

### Kundenliste laden

```bash
curl "https://portal.dct-files.de/api/customers" \
  -H "Authorization: Bearer <API_TOKEN>"
```

### Fahrzeugtypen der Fahrzeugdatenbank laden

```bash
curl "https://portal.dct-files.de/api/fahrzeugdatenbank/types" \
  -H "Authorization: Bearer <API_TOKEN>" \
  -H "X-Token-Id: <TOKEN_ID>"
```

### DTC-Code laden

```bash
curl "https://portal.dct-files.de/api/dtc-codes/P0100"
```

### DTC-Kategorien laden

```bash
curl "https://portal.dct-files.de/api/dtc-codes/categories"
```

## 10. Mobile API fuer die Admin/CRM-App

Ausgelagert in eine eigene Datei: [docs/api-mobile.md](/var/www/vhosts/dct-files.de/portal.dct-files.de/docs/api-mobile.md).

Diese API ist fuer die interne Mobile-App von Mitarbeitern gedacht (Dashboard, Portale, Auftraege, Tickets, Kunden, Fahrzeugdatenbank) und richtet sich an interne `User`-Konten mit der Rolle `ROLE_ADMIN` -- nicht an Kunden (`Kunde`-Entity).
