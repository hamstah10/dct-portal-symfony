# Fahrzeugdatenbank Gesamt-Dokumentation (inkl. Stammdaten)

Stand: 22.05.2026

Diese Datei ist die zentrale Uebersicht fuer den gesamten Bereich Fahrzeugdatenbank:

- Stammdaten und fachliche Struktur
- Datenpools und Datenkontexte
- API-Integration (intern und extern)
- Admin-Pflege und Betriebsregeln

## 1. Zweck und Geltungsbereich

Die Fahrzeugdatenbank liefert strukturierte Fahrzeug- und Tuningdaten fuer:

- EasyAdmin-Backoffice
- interne Portale (z. B. Superadmin, Chiptuningfile)
- oeffentliche Seiten und Widgets
- externe API-Kunden

Sie unterstuetzt sowohl einen globalen Datenbestand als auch mehrere getrennte Datenbestaende (Pools), die ueber Kontexte gesteuert werden koennen.

## 2. Stammdatenmodell (fachlich)

Kaskadierende Struktur der Fahrzeugauswahl:

1. Fahrzeugtyp
2. Marke
3. Baureihe
4. Modell
5. Motor
6. Motor-Details

Zu den Motor-Details gehoeren je nach Datenlage:

- Serienleistung
- Drehmoment
- Kraftstoff, Hubraum
- Stage-Daten
- Steuergeraete (ECUs)
- ECU-Optionen
- Getriebedaten
- Frontend-Einstellungen
- Medien (Bild/3D), falls vorhanden

### 2.1 Typische Stammdaten-Entities

- `VehicleType`
- `VehicleBrand`
- `VehicleSeries`
- `VehicleModel`
- `VehicleEngine`
- `Stage`
- `Ecu`
- `EcuOption`
- `Gearbox`

Wichtig:
Die meisten fachlichen Datensaetze sind poolgebunden und damit einem `VehicleDataPool` zugeordnet.

### 2.2 Stammdaten-Logik (fachliche Regeln)

Die Stammdaten werden strikt kaskadierend geladen. Jede Ebene ist vom gewaehlten Datensatz der vorherigen Ebene abhaengig.

Grundlogik:

1. `types` liefert aktive Fahrzeugtypen
2. `brands/{typeId}` liefert nur Marken dieses Typs
3. `series/{brandId}` liefert nur Baureihen dieser Marke
4. `models/{seriesId}` liefert nur Modelle dieser Baureihe
5. `engines/{modelId}` liefert nur Motoren dieses Modells
6. `engine-details/{engineId}` liefert genau die Detaildaten dieses Motors

### 2.3 Filter- und Sichtbarkeitsregeln

Bei jeder Stufe gelten dieselben Kernregeln:

- nur aktive Datensaetze werden ausgeliefert
- Datensaetze muessen zum aufgeloesten Datenpool passen
- IDs aus fremden Pools liefern keine Treffer bzw. keinen Zugriff auf Details

Dadurch ist sichergestellt, dass innerhalb eines Requests immer ein konsistenter Datenbestand genutzt wird.

### 2.4 Detail-Logik fuer Motoren

Die Detailansicht eines Motors ist der zentrale Knoten fuer Produkt- und Angebotslogik.

Typisches Verhalten:

- Basis-Motordaten kommen direkt vom Motor-Datensatz
- Stages werden nur fuer denselben Pool geladen
- ECUs und ECU-Optionen werden poolkonsistent gefiltert
- Getriebedaten werden nur passend zum Motor/Pool-Kontext geliefert
- `stageId` kann die Antwort auf eine konkrete Stage fokussieren
- `templateVariant` kann frontendseitige Variantendarstellung beeinflussen

Wichtig:
Wenn ein `engineId` technisch existiert, aber nicht im aktuell aufgeloesten Pool liegt, darf keine fachlich vollstaendige Detailantwort erzeugt werden.

## 3. Datenbestaende: Pools und Kontexte

## 3.1 VehicleDataPool

Ein `VehicleDataPool` ist ein konkreter Fahrzeugdatenbestand.

Typische Felder:

- `name`
- `poolKey` (eindeutig)
- `description`
- `active`
- zugeordnete Fahrzeugtypen

Typischer Einsatz:

- `live`
- `preview`
- `test`
- kunden- oder markenspezifische Bestaende

## 3.2 VehicleDataContext

Ein `VehicleDataContext` steuert, welche Pools in einem API-Kontext sichtbar sind.

Typische Felder:

- `name`
- `contextKey` (eindeutig)
- `usageType` (`internal`, `api`, `preview`)
- `active`
- `defaultVehicleDataPool`
- `allowedDataPools`

Zweck:
Ein Kontext begrenzt den Zugriff fuer API-Requests auf definierte Pools.

## 3.3 Resolver-Logik (vereinfacht)

Aufloesung ueber Header oder Query:

- Header: `X-Vehicle-Data-Context`, `X-Vehicle-Data-Pool`
- Query: `contextKey`, `poolKey`

Reihenfolge:

1. Kontext bestimmen (`contextKey` oder tokengebunden)
2. Optionalen `poolKey` nur akzeptieren, wenn aktiv und erlaubt
3. Ohne `poolKey`: Kontext-Default nutzen
4. Sonst Fallback auf `live`

## 4. Zugriffsmuster je System

## 4.1 EasyAdmin-Backoffice

- arbeitet direkt mit poolgebundenen Entities
- verwaltet Pools und Kontexte
- primaer keine direkte Kontext-Logik pro Admin-Request

## 4.2 Interne Portale

- typischerweise poolbasiert ueber `Portal.vehicleDataPool`
- `VehicleApiProxy` setzt den `poolKey` automatisch
- geeignet fuer feste systemseitige Datenzuordnung

## 4.3 Externe API-Clients

- nutzen API-Token + optional Kontext + optional `poolKey`
- Kontexte und erlaubte Pools begrenzen den Zugriff
- ohne `poolKey` greift Kontext-Default bzw. Fallback

## 4.4 Website-Widget

- nutzt serverseitig hinterlegten API-Token
- kann explizit auf `WebsiteWidgetConfig.vehicleDataPool` gebunden werden
- falls nicht gesetzt, greift Token-/Kontext-Logik

## 5. API Uebersicht (Fahrzeugdatenbank)

Basis:

```text
https://verwaltung.tuningfux.de/api/fahrzeugdatenbank
```

Authentifizierung:

```text
Authorization: Bearer <API_TOKEN>
X-Token-Id: <TOKEN_ID>
```

Wichtige Endpunkte:

- `GET /api/fahrzeugdatenbank`
- `GET /api/fahrzeugdatenbank/types`
- `GET /api/fahrzeugdatenbank/brands/{typeId}`
- `GET /api/fahrzeugdatenbank/series/{brandId}`
- `GET /api/fahrzeugdatenbank/models/{seriesId}`
- `GET /api/fahrzeugdatenbank/engines/{modelId}`
- `GET /api/fahrzeugdatenbank/engine-details/{engineId}`
- `POST /api/fahrzeugdatenbank/track`

Optionale Parameter bei Detailabfragen:

- `stageId`
- `templateVariant`

## 6. Sicherheits- und Integrationsregeln

- API-Token niemals im oeffentlichen Frontend ausliefern
- API immer ueber eigenes Backend/Proxy anbinden
- im Proxy nur erlaubte Endpunkte durchreichen
- Header und Query validieren
- auf sinnvolle Rate-Limits und Logging achten

Empfohlene Architektur:

```text
Frontend -> Kunden-Backend/Proxy -> Tuningfux API
```

## 7. Admin-Pflege und Betrieb

Pflegeorte:

- `Fahrzeugdatenbank -> Datenbestaende` (Pools)
- `Fahrzeugdatenbank -> Datenkontexte` (Kontexte)

Betriebsregeln:

- Standard-Pool eines Kontexts sollte in `allowedDataPools` enthalten sein
- nur aktive Pools/Kontexte fuer produktive Nutzung freigeben
- Loeschaktionen auf Pools mit Vorsicht (enthaelt ggf. abh. Fahrzeugdaten)
- bei Datenabweichungen immer Pool/Kontext/Token-Zuordnung zuerst pruefen

## 8. Fehleranalyse (Kurz-Checkliste)

Wenn Daten fehlen oder unerwartet sind:

1. Ist der richtige Pool aktiv?
2. Ist der Kontext aktiv und korrekt am Token gebunden?
3. Ist der `poolKey` im Kontext erlaubt?
4. Greift ein unerwarteter Fallback auf `live`?
5. Nutzt das System Portal-Pool oder API-Kontext-Logik?

## 9. Relevante Code-Stellen

- `src/Entity/VehicleDataPool.php`
- `src/Entity/VehicleDataContext.php`
- `src/Entity/ApiToken.php`
- `src/Service/VehicleDataContextResolver.php`
- `src/Service/VehicleApiProxy.php`
- `src/Controller/Api/VehicleDatabaseApiController.php`
- `src/Controller/VehicleDatabaseController.php`
- `src/Controller/Admin/VehicleDataPoolCrudController.php`
- `src/Controller/Admin/VehicleDataContextCrudController.php`
- `src/Controller/Admin/PortalCrudController.php`

## 10. Verwandte Dokus

- `docs/fahrzeugdatenbank-api-kunden.md`
- `docs/fahrzeugdatenbank-api-einbindung.md`
- `docs/vehicle-data-pools-contexts.md`
- `docs/system-datenkontexte-pools.md`

Diese Datei dient als zentrale Master-Doku fuer den Gesamtbereich Fahrzeugdatenbank und Stammdaten.
