# Mobile API fuer die Admin/CRM-App

Stand: 07.07.2026

Diese API ist fuer die interne Mobile-App von Mitarbeitern gedacht (Dashboard, Portale, Auftraege, Tickets, Kunden, Fahrzeugdatenbank). Sie richtet sich an interne `User`-Konten mit der Rolle `ROLE_ADMIN` -- nicht an Kunden (`Kunde`-Entity). Kunden-Logins laufen ausschliesslich ueber die Auth API in [docs/api.md](/var/www/vhosts/dct-files.de/portal.dct-files.de/docs/api.md), Abschnitt 1.

- Base URL: `https://portal.dct-files.de`
- API Root: `https://portal.dct-files.de/api/mobile`
- Content-Type fuer JSON-Requests: `application/json`
- Controller: `src/Controller/Api/MobileDashboardController.php`, `src/Controller/Api/MobileAuthController.php`, `src/Controller/Api/MobileOrderController.php`, `src/Controller/Api/MobileTicketController.php`, `src/Controller/Api/MobileCustomerController.php`, `src/Controller/Api/MobileVehicleController.php`

## Endpunkte

- `POST /api/mobile/auth/login`
- `POST /api/mobile/auth/push-token`
- `POST /api/mobile/push/test`
- `GET /api/mobile/dashboard`
- `GET /api/mobile/portal/details`
- `PATCH /api/mobile/portal/settings`
- `GET /api/mobile/orders`
- `GET /api/mobile/orders/{id}`
- `PATCH /api/mobile/orders/{id}/status`
- `GET /api/mobile/tickets`
- `GET /api/mobile/tickets/{id}`
- `POST /api/mobile/tickets/{id}/reply`
- `PATCH /api/mobile/tickets/{id}/status`
- `GET /api/mobile/customers`
- `GET /api/mobile/customers/{id}`
- `GET /api/mobile/vehicle/types`
- `GET /api/mobile/vehicle/brands/{typeId}`
- `GET /api/mobile/vehicle/series/{brandId}`
- `GET /api/mobile/vehicle/models/{seriesId}`
- `GET /api/mobile/vehicle/engines/{modelId}`
- `GET /api/mobile/vehicle/engine-details/{engineId}`

## Login

`POST /api/mobile/auth/login`

Request:

```json
{
  "email": "mitarbeiter@tuningfux.de",
  "password": "DeinPasswort",
  "portalKey": "cpt",
  "deviceName": "iphone-app"
}
```

Erfolgsantwort:

```json
{
  "token": "...",
  "refreshToken": "...",
  "user": {
    "id": 12,
    "email": "mitarbeiter@tuningfux.de",
    "name": "Max Mustermann"
  },
  "portal": {
    "key": "cpt",
    "name": "Chiptuningfile",
    "firma": "Tuningfux GmbH"
  }
}
```

Fehler:

- `422 Unprocessable Entity` -- `email` oder `password` fehlen.
- `401 Unauthorized` -- falsche Zugangsdaten.
- `403 Forbidden` -- Konto inaktiv oder ohne `ROLE_ADMIN` (`"Kein Zugriff auf die mobile App."`).

Hinweise:

- `portalKey` ist optional und wird nur zur Anreicherung der Antwort verwendet (siehe `portal`-Objekt).
- Es gibt aktuell **keinen** `/api/mobile/auth/refresh`- und keinen `/api/mobile/auth/logout`-Endpunkt. Der Access-Token laeuft nach `API_ACCESS_TOKEN_TTL` (Standard 1 Stunde) einfach ab; ein erneuter Login ist dann noetig.

## Authentifizierte Requests

```text
Authorization: Bearer <ACCESS_TOKEN>
```

Jeder Endpunkt unterhalb von Login prueft den Token serverseitig. Bei ungueltigem oder abgelaufenem Token antworten alle Endpunkte einheitlich:

```json
{
  "message": "Access-Token ist ungueltig oder abgelaufen."
}
```

mit Status `401 Unauthorized`.

## Push-Token registrieren

`POST /api/mobile/auth/push-token`

Request:

```json
{
  "pushToken": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
}
```

Antwort: `204 No Content`. Bei leerem `pushToken`: `422 Unprocessable Entity`.

## Test-Push senden

`POST /api/mobile/push/test`

Sendet eine Test-Benachrichtigung an alle registrierten Push-Tokens des angemeldeten Users.

Antwort:

```json
{ "sent": true }
```

## Dashboard

`GET /api/mobile/dashboard`

Query-Parameter:

- `portalKey` (optional) -- laedt Name/Firma/Logo des angegebenen Portals in `portal`.

Antwort:

```json
{
  "portal": {
    "name": "Chiptuningfile",
    "firma": "Tuningfux GmbH",
    "kuerzel": "TG",
    "logoUrl": "https://portal.dct-files.de/uploads/portal/logo.png",
    "firmaLogoUrl": "https://portal.dct-files.de/uploads/firmen/tuningfux-gmbh.png"
  },
  "portals": [
    {
      "id": 1,
      "portalKey": "cpt",
      "name": "Chiptuningfile",
      "firma": "Tuningfux GmbH",
      "kuerzel": "TG",
      "logoUrl": "https://portal.dct-files.de/uploads/portal/logo.png",
      "firmaLogoUrl": "https://portal.dct-files.de/uploads/firmen/tuningfux-gmbh.png"
    }
  ],
  "kpis": {
    "totalOrders": 4210,
    "totalCustomers": 1875,
    "todayOrders": 12,
    "weekOrders": 87,
    "open": 34
  },
  "statusBreakdown": {
    "eingegangen": 10,
    "in_bearbeitung": 20,
    "onhold": 4,
    "abgeschlossen": 4150,
    "abgelehnt": 26
  },
  "recentOrders": [
    {
      "id": 4321,
      "status": "in_bearbeitung",
      "customerEmail": "kunde@example.com",
      "fileName": "stage1.bin",
      "vehicleDisplay": "VW Golf 7 2.0 TDI",
      "manufacturer": "VW",
      "model": "Golf 7",
      "engine": "2.0 TDI",
      "stageLabel": "Stage 1",
      "tuningMethod": "OBD",
      "createdAt": "2026-07-06T09:15:00+00:00",
      "customerId": 88,
      "customerName": "Max Mustermann"
    }
  ]
}
```

Hinweise:

- `portals` enthaelt alle aktiven Portale vom Typ Kundenportal (`portalType = customer_portal`), sortiert nach `sortOrder`, dann `id` -- unabhaengig vom gewaehlten `portalKey`. Andere Portal-Typen (Marken-Suite, Marketing-Seite, Superadmin-Portal) werden nicht gelistet.
- `kpis`, `statusBreakdown` und `recentOrders` sind aktuell global ueber alle Auftraege/Kunden berechnet, nicht nach Portal gefiltert.
- `open` zaehlt Auftraege mit Status `eingegangen`, `in_bearbeitung` oder `onhold`.

## Portal-Details

`GET /api/mobile/portal/details`

Liefert die Detailansicht eines einzelnen Portals (z. B. fuer den Klick auf ein Portal aus der `portals`-Liste des Dashboards).

Query-Parameter (genau einer erforderlich):

- `id` (Portal-ID, numerisch)
- `portalKey` (alternativ zur ID)

Antwort (Typ `PortalDetails`):

```json
{
  "id": 1,
  "portalKey": "no1",
  "name": "No1tuningfiles",
  "firma": "Tuningfux GmbH",
  "kuerzel": "TG",
  "logoUrl": "https://portal.dct-files.de/uploads/portal/logo.png",
  "firmaLogoUrl": "https://portal.dct-files.de/uploads/firmen/tuningfux-gmbh.png",
  "url": "https://no1tuningfiles.com",
  "active": true,
  "registrationsOpen": true,
  "maintenanceMode": false,
  "creditPrice": "1.00",
  "openOrders": 3,
  "totalCustomers": 3,
  "totalOrders": 38,
  "creditsInCirculation": 8195,
  "revenue30d": 4280.00
}
```

Feldbedeutung:

- `active` -- entspricht "Portal aktiv" (Login & Uploads erlaubt).
- `registrationsOpen` -- entspricht "Neue Registrierungen".
- `maintenanceMode` -- entspricht "Wartungsmodus" (Portal offline fuer Kunden).
- `creditPrice` -- manuell gepflegter Preis pro Credit in EUR, `null` falls nicht gesetzt.
- `kuerzel` -- Kuerzel der zugeordneten Firma (`Firma::getKuerzel()`), `null` falls dem Portal keine Firma zugeordnet ist oder die Firma kein Kuerzel hat.
- `firmaLogoUrl` -- Logo der zugeordneten Firma (`Firma::getLogo()`, Pfad `/uploads/firmen/<datei>`), unabhaengig vom Portal-Design-`logoUrl` (das ein eigenes Portal-Logo bevorzugt und nur als Fallback das Firmenlogo nutzt). `null` falls keine Firma zugeordnet ist oder die Firma kein Logo hat.
- `openOrders`/`totalCustomers`/`totalOrders`/`creditsInCirculation`/`revenue30d` werden ueber die `Firma` des Portals ermittelt (alle Kunden/Auftraege der zugeordneten Firma). Ist dem Portal keine Firma zugeordnet, sind diese Werte `0`.
- `revenue30d` summiert `priceGross` aus Credit-Kaeufen (`customer_topup`) der letzten 30 Tage.

Fehler: `404 Not Found` (`"Portal nicht gefunden."`), wenn weder `id` noch `portalKey` ein Portal ergeben.

## Portal-Einstellungen aendern

`PATCH /api/mobile/portal/settings`

Gleiche Query-Parameter wie bei `GET /api/mobile/portal/details` (`id` oder `portalKey`) zur Auswahl des Portals.

Request (alle Felder optional, nur uebergebene Felder werden geaendert):

```json
{
  "active": true,
  "registrationsOpen": true,
  "maintenanceMode": false,
  "creditPrice": 1.00
}
```

Antwort:

```json
{
  "message": "Portal aktualisiert.",
  "portal": { "...": "PortalDetails, siehe oben" }
}
```

Fehler: `422 Unprocessable Entity`, wenn ein uebergebenes Feld den falschen Typ hat.

## Auftraege

`GET /api/mobile/orders`

Query-Parameter:

- `status` (optional)
- `search` (optional, durchsucht `customerEmail`, `fileName`, `vehicleDisplay`, `manufacturer`, `model`)
- `limit` (optional, 1-100, Standard 20)
- `offset` (optional, Standard 0)

Antwort:

```json
{
  "orders": [
    { "id": 4321, "status": "in_bearbeitung", "customerEmail": "kunde@example.com", "fileName": "stage1.bin", "vehicleDisplay": "VW Golf 7 2.0 TDI", "manufacturer": "VW", "model": "Golf 7", "engine": "2.0 TDI", "stageLabel": "Stage 1", "tuningMethod": "OBD", "createdAt": "2026-07-06T09:15:00+00:00", "customerId": 88, "customerName": "Max Mustermann" }
  ],
  "total": 4210
}
```

`GET /api/mobile/orders/{id}`

Antwort: ein einzelnes Order-Objekt wie oben, oder `404 Not Found`.

`PATCH /api/mobile/orders/{id}/status`

Request:

```json
{ "status": "abgeschlossen" }
```

Erlaubte Werte: `eingegangen`, `in_bearbeitung`, `onhold`, `parked`, `abgeschlossen`, `abgelehnt`. Ungueltiger Wert: `422 Unprocessable Entity`.

Antwort:

```json
{
  "message": "Status aktualisiert.",
  "order": { "id": 4321, "status": "abgeschlossen", "...": "..." }
}
```

## Tickets

`GET /api/mobile/tickets`

Query-Parameter:

- `status` (optional)
- `search` (optional, durchsucht nur `subject`)
- `limit` (optional, 1-100, Standard 20)
- `offset` (optional, Standard 0)

Antwort:

```json
{
  "tickets": [
    {
      "id": 55,
      "ticketNumber": "T-2026-0055",
      "subject": "Frage zu Stage 2",
      "status": "open",
      "priority": "normal",
      "messageCount": 3,
      "lastMessagePreview": "Vielen Dank fuer die schnelle Antwort...",
      "lastMessageAt": "2026-07-06T10:00:00+00:00",
      "createdAt": "2026-07-01T08:00:00+00:00",
      "updatedAt": "2026-07-06T10:00:00+00:00",
      "customerId": 88
    }
  ],
  "total": 120
}
```

`GET /api/mobile/tickets/{id}`

Antwort: wie oben, zusaetzlich:

```json
{
  "customerName": "Max Mustermann",
  "customerEmail": "kunde@example.com",
  "messages": [
    {
      "id": 210,
      "sender": "customer",
      "senderName": "Max Mustermann",
      "message": "Wann ist mein Auftrag fertig?",
      "createdAt": "2026-07-05T12:00:00+00:00"
    }
  ]
}
```

`POST /api/mobile/tickets/{id}/reply`

Request:

```json
{ "message": "Ihr Auftrag ist in Bearbeitung und morgen fertig." }
```

Leere `message`: `422 Unprocessable Entity`. Antwort ist das neu erstellte Message-Objekt (Absender = angemeldeter Mitarbeiter).

`PATCH /api/mobile/tickets/{id}/status`

Request:

```json
{ "status": "closed" }
```

Erlaubte Werte: `open`, `in_progress`, `closed`. Beim Wechsel auf `closed` wird zusaetzlich `closedAt` gesetzt.

Antwort:

```json
{
  "message": "Status aktualisiert.",
  "ticket": { "id": 55, "status": "closed", "...": "..." }
}
```

## Kunden

`GET /api/mobile/customers`

Query-Parameter:

- `search` (optional, durchsucht `vorname`, `nachname`, `email`, `firmenname`, `kundennummer`)
- `limit` (optional, 1-100, Standard 20)
- `offset` (optional, Standard 0)

Antwort:

```json
{
  "customers": [
    {
      "id": 88,
      "kundennummer": "K-00088",
      "name": "Max Mustermann",
      "email": "kunde@example.com",
      "firma": "Muster GmbH",
      "credits": 250,
      "gesperrt": false,
      "createdAt": "2025-11-02T09:00:00+00:00",
      "vorname": "Max",
      "nachname": "Mustermann",
      "firmenname": "Muster GmbH",
      "telefon": "+49 123 456789",
      "strasse": "Musterstrasse 1",
      "plz": "12345",
      "stadt": "Musterstadt",
      "land": "DE",
      "website": "https://muster-gmbh.de",
      "orderCount": 14
    }
  ],
  "total": 1875
}
```

`GET /api/mobile/customers/{id}`

Antwort: ein einzelnes Kunden-Objekt wie oben, oder `404 Not Found` (`"Kunde nicht gefunden."`).

## Fahrzeugdatenbank (kaskadierend)

Read-only Endpunkte fuer Fahrzeugtyp -> Marke -> Baureihe -> Modell -> Motor, poolbasiert wie die oeffentliche Fahrzeugdatenbank-API.

Pool-Auswahl (optional, sonst wird der Pool `complete` verwendet):

```text
X-Vehicle-Data-Pool: <poolKey>
X-Vehicle-Data-Context: <contextKey>
```

Alternativ als Query-Parameter `poolKey` / `contextKey`.

`GET /api/mobile/vehicle/types`

```json
[
  { "id": 1, "name": "PKW", "icon": "car.png", "image": null }
]
```

`GET /api/mobile/vehicle/brands/{typeId}`

```json
[
  { "id": 10, "name": "VW", "logo": "vw.png" }
]
```

`GET /api/mobile/vehicle/series/{brandId}`

```json
[
  { "id": 100, "name": "Golf 7" }
]
```

`GET /api/mobile/vehicle/models/{seriesId}`

```json
[
  { "id": 1000, "name": "Golf 7 Facelift" }
]
```

`GET /api/mobile/vehicle/engines/{modelId}`

```json
[
  { "id": 10000, "name": "2.0 TDI", "horsepower": 150, "fuel": "Diesel" }
]
```

`GET /api/mobile/vehicle/engine-details/{engineId}`

Query-Parameter `templateVariant` (optional).

```json
{
  "data": {
    "engine": { "...": "..." },
    "brand": { "...": "..." },
    "series": { "...": "..." },
    "stages": [],
    "ecus": [],
    "gearboxes": [],
    "settings": { "...": "..." }
  }
}
```

Ohne aufloesbaren Pool: `404 Not Found` (`"Kein gueltiger Datenbestand gefunden."`).
