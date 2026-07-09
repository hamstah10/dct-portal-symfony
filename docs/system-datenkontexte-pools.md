# System-Uebersicht: Datenkontexte und Pools

Stand: 13.05.2026

Diese Datei beschreibt nicht allgemein, was Pools und Kontexte sind, sondern ordnet zu, welches System welchen Mechanismus fuer Fahrzeugdaten nutzt.

Grundsaetzlich gibt es zwei Ebenen:

- `VehicleDataPool`
  - der konkrete Datenbestand
- `VehicleDataContext`
  - der fachliche Zugriffskontext fuer API-Zugriffe mit erlaubten Pools und optionalem Default-Pool

## Schnellmatrix

| System | Nutzt Pool direkt | Nutzt Kontext | Primaere Stelle im Code |
| --- | --- | --- | --- |
| EasyAdmin-Backoffice | ja | indirekt, nur zur Verwaltung | `src/Controller/Admin/*`, `src/Controller/Admin/VehicleDataPoolCrudController.php`, `src/Controller/Admin/VehicleDataContextCrudController.php` |
| Superadmin-Portal | ja, ueber `Portal.vehicleDataPool` | normalerweise nein | `src/Service/VehicleApiProxy.php`, `src/Portals/SuperadminPortal/Controller/VehicleApiProxyController.php` |
| Chiptuningfile-System | ja, ueber `Portal.vehicleDataPool` | normalerweise nein | `src/Service/VehicleApiProxy.php`, `src/Portals/Chiptuningfile/Controller/VehicleApiProxyController.php`, `src/Websites/Chiptuningfile/Controller/VehicleApiProxyController.php` |
| Oeffentliche Fahrzeugdatenbank `/chiptuning` | optional per `poolKey`, sonst Fallback | teilweise ja | `src/Controller/VehicleDatabaseController.php`, `src/Service/VehicleDataContextResolver.php` |
| Externe API-Clients | optional per `poolKey` | ja | `src/Controller/Api/VehicleDatabaseApiController.php`, `src/Service/VehicleDataContextResolver.php` |

## 1. Grundregel

Es gibt im Projekt zwei typische Nutzungsarten:

### A. Direkte System-Zuordnung ueber `Portal.vehicleDataPool`

Das System haengt direkt an einem bestimmten Pool.

Merkmale:

- kein `contextKey` noetig
- `poolKey` wird systemseitig automatisch gesetzt oder direkt verwendet
- typisch fuer Portale und markenspezifische Oberflaechen

### B. API-Zuordnung ueber `VehicleDataContext`

Der Zugriff wird nicht direkt am Portal fest verdrahtet, sondern ueber Kontext + Token + optional `poolKey` aufgeloest.

Merkmale:

- `contextKey` kommt per Header oder Query
- `poolKey` ist optional, muss aber im Kontext erlaubt sein
- typisch fuer externe API-Clients und definierte API-Integrationen

## 2. EasyAdmin-Backoffice

System:

- `/admin`

Pool-Mechanik:

- im Admin wird fachlich direkt mit Entities gearbeitet, die einem `VehicleDataPool` zugeordnet sind
- viele CRUDs zeigen oder bearbeiten den Pool direkt am Datensatz
- Beispiele:
  - `VehicleBrand.vehicleDataPool`
  - `VehicleSeries.vehicleDataPool`
  - `VehicleModel.vehicleDataPool`
  - `VehicleEngine.vehicleDataPool`
  - `Ecu.vehicleDataPool`
  - `EcuOption.vehicleDataPool`

Kontext-Mechanik:

- der Admin selbst ist nicht primaer kontextgesteuert
- `VehicleDataContext` wird im Admin verwaltet, aber typischerweise fuer API-Zugriffe konfiguriert

Wichtige Stellen:

- `src/Controller/Admin/VehicleDataPoolCrudController.php`
- `src/Controller/Admin/VehicleDataContextCrudController.php`
- `src/Controller/Admin/PortalCrudController.php`

## 3. Superadmin-Portal

System:

- Portaltyp `superadmin_portal`

Pool-Mechanik:

- sobald Fahrzeugdaten ueber den Vehicle-API-Proxy gezogen werden, laeuft die Auswahl ueber `Portal.vehicleDataPool`
- `VehicleApiProxy` haengt den `poolKey` des aktiven Portal-Pools automatisch an den Request

Kontext-Mechanik:

- kein eigener `VehicleDataContext` im normalen Portal-Flow sichtbar
- die API-Token-Auswahl laeuft poolbasiert

Wichtige Stellen:

- `src/Entity/Portal.php`
- `src/Service/VehicleApiProxy.php`
- `src/Portals/SuperadminPortal/Controller/VehicleApiProxyController.php`

Praktisch:

- wenn das Superadmin-Portal einen anderen Fahrzeugdatenbestand sehen soll, wird typischerweise der `vehicleDataPool` am `Portal` geaendert

## 4. Chiptuningfile-System

System:

- Kundenportal `chiptuningfile`
- oeffentliche Website `https://cpt.tuningfux.de/`

Pool-Mechanik:

- beide Oberflaechen gehoeren zum selben Portalsystem und nutzen im Fahrzeugdatenkontext den Pool aus `Portal.vehicleDataPool`
- sowohl Portal als auch Website verwenden den `VehicleApiProxy`
- der Proxy setzt automatisch den `poolKey`, wenn am `Portal` ein aktiver Pool hinterlegt ist

Kontext-Mechanik:

- kein separater `VehicleDataContext` im normalen Website-/Portal-Ablauf notwendig
- die fachliche Auswahl kommt direkt ueber den am `Portal` konfigurierten Pool

Wichtige Stellen:

- `src/Entity/Portal.php`
- `src/Service/VehicleApiProxy.php`
- `src/Portals/Chiptuningfile/Controller/VehicleApiProxyController.php`
- `src/Websites/Chiptuningfile/Controller/VehicleApiProxyController.php`

Praktisch:

- wenn Website und Portal von `chiptuningfile` denselben Datenbestand nutzen sollen, wird der Pool einmal zentral am Portal-Datensatz gesetzt
- dadurch ziehen Website und Portal konsistent denselben Fahrzeugbestand

## 5. Oeffentliche Fahrzeugdatenbank ausserhalb des Portals

System:

- klassische Fahrzeugdatenbank-Controller unter `/chiptuning` und `/api/fahrzeugdatenbank/*`

Pool-Mechanik:

- ohne speziellen Kontext faellt die Aufloesung auf den aktiven Pool `live` zurueck
- bei direkter Web-Nutzung kann optional `poolKey` uebergeben werden

Kontext-Mechanik:

- fuer API-Endpunkte wird bei Bedarf zusaetzlich `VehicleDataContextResolver` verwendet
- dadurch koennen Kontext und Pool kontrolliert pro Request aufgeloest werden

Wichtige Stellen:

- `src/Controller/VehicleDatabaseController.php`
- `src/Controller/Api/VehicleDatabaseApiController.php`
- `src/Service/VehicleDataContextResolver.php`

## 6. Externe API-Clients

Typische Beispiele:

- externe Websites
- Partneranbindungen
- systemfremde Frontends

Pool-Mechanik:

- `poolKey` kann explizit mitgegeben werden
- der Pool muss aktiv sein und im Kontext erlaubt sein

Kontext-Mechanik:

- `contextKey` wird per Header oder Query gesendet
- falls ein API-Token an einen Kontext gebunden ist, darf kein anderer Kontext verwendet werden
- wenn kein `poolKey` gesendet wird, greift:
  - `defaultVehicleDataPool` des Kontexts
  - oder der einzige erlaubte Pool
  - oder als Fallback `live`

Wichtige Stellen:

- `src/Service/VehicleDataContextResolver.php`
- `src/Entity/VehicleDataContext.php`
- `src/Entity\ApiToken.php`
- `docs/vehicle-data-pools-contexts.md`

## 7. Praktische Zuordnung nach System

- EasyAdmin-Backoffice:
  - arbeitet direkt mit poolgebundenen Datensaetzen
- Superadmin-Portal:
  - nutzt im Fahrzeugkontext `Portal.vehicleDataPool`
- Chiptuningfile-System:
  - nutzt im Fahrzeugkontext `Portal.vehicleDataPool`
- Externe API-Clients:
  - nutzen `VehicleDataContext` plus optional `poolKey`
- Website Widget:
  - nutzt einen serverseitig hinterlegten `ApiToken`
  - kann direkt an `WebsiteWidgetConfig.vehicleDataPool` gebunden sein
  - erzwingt diesen Pool im Widget-Proxy, wenn gesetzt
  - faellt sonst auf API-Token-Kontext und Standard-Fallback zurueck

## 8. Entscheidungshilfe

Wenn die Frage lautet:

- "Welchen Fahrzeugdatenbestand sieht dieses Portal oder diese Website?"
  - dann zuerst `Portal.vehicleDataPool` pruefen

- "Welchen Fahrzeugdatenbestand sieht dieser externe API-Client?"
  - dann `VehicleDataContext`, `allowedDataPools`, `defaultVehicleDataPool`, API-Token und `poolKey` pruefen

- "Welchen Fahrzeugdatenbestand sieht ein Website Widget?"
  - dann zuerst `WebsiteWidgetConfig.vehicleDataPool` pruefen
  - danach den hinterlegten `ApiToken` und dessen `VehicleDataContext`

- "Warum liefert `/api/fahrzeugdatenbank/*` andere Daten als ein Portal?"
  - dann pruefen, ob der API-Request kontextgesteuert ist oder auf `live` faellt, waehrend das Portal ueber `Portal.vehicleDataPool` arbeitet
