# Fahrzeugdatenbank: Datenpools und Datenkontexte

Stand: 05.04.2026

## Zweck

Die Fahrzeugdatenbank arbeitet nicht mehr nur mit einem globalen Datenbestand, sondern mit:

- `VehicleDataPool`: konkreter Datenbestand
- `VehicleDataContext`: fachlicher Zugriffskontext mit erlaubten Pools

Damit koennen z. B. getrennte Bestände fuer `live`, `preview`, `test`, Importe oder Kunden-spezifische Daten gepflegt werden, ohne dass sich Daten gegenseitig ueberschreiben.

## Begriffe

### Datenpool

Ein Datenpool ist ein konkreter Bestand von Fahrzeugdaten.

Typische Eigenschaften:

- `name`: Anzeigename im Admin
- `poolKey`: technischer, eindeutiger Key
- `description`: optionale Beschreibung
- `active`: ob der Pool verwendet werden darf
- `vehicleTypes`: zugeordnete Fahrzeugtypen

Ein Pool haengt direkt an importierten Datensaetzen wie z. B.:

- Marken
- Baureihen
- Modelle
- Motoren
- Stages
- ECUs
- ECU-Optionen
- Getrieben

Code:
[VehicleDataPool.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Entity/VehicleDataPool.php)

### Datenkontext

Ein Datenkontext definiert, in welchem fachlichen Rahmen Pools verwendet werden duerfen.

Typische Eigenschaften:

- `name`: Anzeigename im Admin
- `contextKey`: technischer, eindeutiger Key
- `usageType`: `internal`, `api`, `preview`
- `description`: optionale Beschreibung
- `active`: ob der Kontext verwendet werden darf
- `defaultVehicleDataPool`: Standard-Pool fuer diesen Kontext
- `allowedDataPools`: Liste erlaubter Pools

Ein Kontext begrenzt also, welche Datenbestaende fuer einen Request oder API-Token ueberhaupt sichtbar sind.

Code:
[VehicleDataContext.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Entity/VehicleDataContext.php)

## Aufloesung in Requests

Die Pool-/Kontext-Auswahl wird zentral ueber den Resolver bestimmt.

Code:
[VehicleDataContextResolver.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Service/VehicleDataContextResolver.php)

### Verwendete Header

- `X-Vehicle-Data-Context`
- `X-Vehicle-Data-Pool`

Alternativ sind Query-Parameter moeglich:

- `contextKey`
- `poolKey`

### Reihenfolge der Pool-Aufloesung

1. Falls ein `contextKey` gesendet wird, wird der aktive Kontext geladen.
2. Falls ein API-Token an einen Kontext gebunden ist, darf nur genau dieser Kontext verwendet werden.
3. Falls ein `poolKey` gesendet wird, wird dieser Pool nur akzeptiert, wenn:
   - er aktiv ist
   - und vom Kontext erlaubt ist
4. Falls kein `poolKey` gesendet wird:
   - wird zuerst `defaultVehicleDataPool` des Kontexts verwendet
   - oder bei genau einem erlaubten Pool dieser eine Pool
   - sonst der globale Default-Pool `live`

Wichtige Konstante:

- Default-Pool-Key: `live`

## Verhalten der API

Die Fahrzeugdatenbank-API arbeitet poolbasiert. Responses liefern teilweise den verwendeten Pool im `meta`-Block zurueck.

Relevanter Controller:
[VehicleDatabaseApiController.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Api/VehicleDatabaseApiController.php)

Wichtig:

- Ein Motor muss zum aufgeloesten Pool passen.
- Stages, ECUs, Gearboxes und Optionen werden ebenfalls auf denselben Pool gefiltert.
- Ein ungueltiger oder nicht erlaubter Pool fuehrt dazu, dass nichts oder ein Fehler geliefert wird, je nach Endpoint-Logik.

## Admin-Oberflaeche

### Datenbestaende

Pfad:
`Fahrzeugdatenbank -> Datenbestände`

Controller:
[VehicleDataPoolCrudController.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/VehicleDataPoolCrudController.php)

Dort kann man:

- Namen und `poolKey` pflegen
- Beschreibung setzen
- Fahrzeugtypen zuordnen
- Pool aktiv/inaktiv setzen
- einen Pool komplett loeschen

Hinweis:
Die Aktion `Datenpool löschen` entfernt den Pool mitsamt importierten Fahrzeugdaten ueber den Purger-Service.

### Datenkontexte

Pfad:
`Fahrzeugdatenbank -> Datenkontexte`

Controller:
[VehicleDataContextCrudController.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/VehicleDataContextCrudController.php)

Dort kann man:

- Namen und `contextKey` pflegen
- Nutzungstyp setzen
- Beschreibung setzen
- Standard-Datenbestand festlegen
- erlaubte Datenbestaende verknuepfen
- Kontext aktiv/inaktiv setzen

Regel:
Der Standard-Datenbestand eines Kontexts sollte auch in `allowedDataPools` enthalten sein.

## Wie wird ein Pool eingebunden?

Es gibt aktuell zwei verschiedene Wege. Welcher richtig ist, haengt davon ab, woher die Fahrzeugdaten kommen.

### 1. Portal-intern ueber `Portal.vehicleDataPool`

Dieser Weg gilt fuer die internen Portale hier im System, wenn das Portal die Fahrzeugdaten ueber den lokalen Proxy bezieht.

Pfad:
`Verwaltung -> Portale`

Feld:
`Fahrzeugdaten-Pool`

Verhalten:

- das Portal bekommt damit einen festen Standard-Pool
- der Proxy haengt bei Requests automatisch den `poolKey` an
- wenn es keinen poolspezifischen Token gibt, wird auf einen globalen Token zurueckgefallen

Code:
[PortalCrudController.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/PortalCrudController.php)
[VehicleApiProxy.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Service/VehicleApiProxy.php)

### 2. Extern ueber API-Token, Kontext und optionalen `poolKey`

Dieser Weg gilt fuer externe Verbraucher der Fahrzeugdatenbank-API.

Wichtig:
`chiptuning-software.com` faellt in genau diese Kategorie. Die Seite ist hier nicht an `Portal.vehicleDataPool` gebunden, sondern bekommt ihre Fahrzeugdaten ueber die API.

Das bedeutet:

- der API-Token kann an einen `VehicleDataContext` gebunden sein
- der Kontext definiert, welche Pools erlaubt sind
- der Request kann optional einen `poolKey` mitschicken
- wenn kein `poolKey` mitgeschickt wird, greift der Default des Kontexts

Code:
[ApiToken.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Entity/ApiToken.php)
[VehicleDataContextResolver.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Service/VehicleDataContextResolver.php)

## API-Tokens und Kontextbindung

API-Tokens koennen an einen `VehicleDataContext` gebunden werden.

Relevantes Feld:
[ApiToken.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Entity/ApiToken.php)

Folge:

- Ist ein Token an einen Kontext gebunden, kann der Request keinen anderen Kontext erzwingen.
- Ein gesendeter `X-Vehicle-Data-Context` muss dann zum Token-Kontext passen.

## Anleitung: Pool fuer `chiptuning-software.com` einbinden

Da `chiptuning-software.com` die Daten ueber die API bezieht, ist der richtige Weg nicht das Portal-Feld, sondern die Kombination aus Pool, Kontext und API-Token.

### Zielbild

Am Ende gibt es:

- einen aktiven `VehicleDataPool`
- einen aktiven `VehicleDataContext`
- der Kontext erlaubt genau diesen Pool oder mehrere definierte Pools
- der verwendete API-Token ist an diesen Kontext gebunden oder sendet den Kontext mit
- der API-Request sendet bei Bedarf den passenden `poolKey`

### Schritt 1: Pool anlegen oder pruefen

Pfad:
`Fahrzeugdatenbank -> Datenbestände`

Pruefen oder anlegen:

- `name`
- `poolKey`
- `active = ja`
- passende `vehicleTypes`

Beispiel:

- `name = Chiptuning Software`
- `poolKey = chiptuning-software`

### Schritt 2: Kontext anlegen oder pruefen

Pfad:
`Fahrzeugdatenbank -> Datenkontexte`

Empfohlene Konfiguration:

- `name = Chiptuning Software API`
- `contextKey = chiptuning-software-api`
- `usageType = api`
- `active = ja`
- `defaultVehicleDataPool = <dein Pool>`
- `allowedDataPools` enthaelt mindestens diesen Pool

Wichtig:
Wenn nur genau ein Pool erlaubt ist, kann die API auch ohne `poolKey` sauber auf diesen Pool fallen. Sauberer ist trotzdem, den `poolKey` bewusst mitzusenden.

### Schritt 3: API-Token an den Kontext binden

Pfad:
`API Tokens`

Am verwendeten Token setzen:

- `active = ja`
- `vehicleDataContext = chiptuning-software-api`

Damit ist festgelegt, in welchem fachlichen Rahmen dieser Token ueberhaupt Daten lesen darf.

### Schritt 4: Request aus `chiptuning-software.com` senden

Moeglich sind Header oder Query-Parameter.

Per Header:

- `X-Vehicle-Data-Context: chiptuning-software-api`
- `X-Vehicle-Data-Pool: chiptuning-software`

Oder per Query:

- `?contextKey=chiptuning-software-api&poolKey=chiptuning-software`

Beispiel:

```text
/api/fahrzeugdatenbank/brands?contextKey=chiptuning-software-api&poolKey=chiptuning-software
```

### Schritt 5: Verhalten verstehen

Der Resolver arbeitet so:

1. Kontext aus Header oder `contextKey` lesen
2. falls der Token an einen Kontext gebunden ist, darf kein anderer Kontext verwendet werden
3. `poolKey` nur akzeptieren, wenn der Pool aktiv und im Kontext erlaubt ist
4. wenn kein `poolKey` gesendet wird, den Default-Pool des Kontexts verwenden
5. wenn kein Kontext greift, auf `live` zurueckfallen

### Schritt 6: Verifizieren

Pruefen:

- der API-Token ist aktiv
- der Kontext ist aktiv
- der Pool ist aktiv
- der Pool steht in `allowedDataPools`
- `defaultVehicleDataPool` ist gesetzt, wenn Requests auch ohne `poolKey` funktionieren sollen

Die API liefert an vielen Endpoints den verwendeten Pool im `meta.poolKey` zurueck. Daran sieht man sofort, ob der richtige Pool verwendet wurde.

## Abgrenzung

Wenn ein interner Kunde fragt: "Wie binde ich einen Pool an mein Portal?", dann ist meist `Portal.vehicleDataPool` gemeint.

Wenn ein externer Client wie `chiptuning-software.com` fragt: "Wie bekomme ich einen bestimmten Datenbestand aus der API?", dann ist fast immer `ApiToken + VehicleDataContext + poolKey` gemeint.

## Import von OLSX-Daten in separaten Pool

Fuer OLSX-JSON-Dateien gibt es einen Import-Command, der Pool und Kontext direkt anlegen oder aktualisieren kann.

Command:
[ImportOlsxVehiclePoolCommand.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Command/ImportOlsxVehiclePoolCommand.php)

Name:
`app:vehicle-data:import-olsx-json`

### Wichtige Optionen

- `file`: JSON-Datei
- `--pool-key`
- `--pool-name`
- `--context-key`
- `--context-name`
- `--vehicle-type`
- `--dry-run`

### Verhalten

- legt Pool an oder aktualisiert ihn
- legt Kontext an oder aktualisiert ihn
- setzt den Kontext auf `usageType = preview`
- setzt den Pool als `defaultVehicleDataPool`
- fuegt den Pool zu `allowedDataPools` hinzu
- importiert die Fahrzeugstruktur in diesen Pool

### Beispiel

```bash
php bin/console app:vehicle-data:import-olsx-json \
  scrapy-remote-scraper/olsx_brands_car_20260405_165946.json \
  --pool-key=olsx-car-test \
  --pool-name="OLSX Car Test" \
  --context-key=olsx-car-preview \
  --context-name="OLSX Car Preview" \
  --vehicle-type=pkw
```

## Superadmin: Scraper-Importseiten

Neben dem CLI-Command gibt es im Superadmin inzwischen zwei getrennte Importstrecken fuer OLSX-Daten.

### 1. Klassischer Scraper-Import

Route:
`/admin/fahrzeugdatenbank/scraper/import`

Eigenschaften:

- liest zusammengefasste JSON-Dateien direkt aus `scrapy-remote-scraper/`
- erwartet Dateinamen wie `olsx_brands_car_20260405_165946.json`
- arbeitet dateibasiert: genau eine JSON-Datei pro Importvorgang
- zeigt vor dem Import eine Vorschau mit Marken, Modellanzahl und Pool-Status
- fuehrt eine eigene Importhistorie fuer diese Root-Dateien

### 2. Output-Import fuer Marken-Einzelfiles

Route:
`/admin/fahrzeugdatenbank/scraper/import-output`

Eigenschaften:

- liest rekursiv JSON-Dateien aus `scrapy-remote-scraper/data/output`
- gruppiert die Dateien im UI nach Typ, z. B. `car`, `moto`, `atv`, `agricultural`
- zeigt pro Typ eine eigene Tabelle
- jede Tabelle hat:
  - eine `Alle`-Checkbox fuer den kompletten Typ
  - Einzel-Checkboxen pro Datei
- unterstuetzt Mehrfachauswahl und importiert mehrere Marken-Dateien in einem Durchlauf
- verarbeitet sowohl Einzeldateien wie `data/output/car/audi.json` als auch die klassische Array-Struktur
- fuehrt eine getrennte Importhistorie nur fuer Dateien unter `data/output`

### Wann welche Importseite sinnvoll ist

- klassischer Import:
  - wenn ein kompletter Lauf als eine grosse `olsx_brands_<type>_*.json` vorliegt
- Output-Import:
  - wenn der neue `olsx_brand_files_spider` oder seine Runner pro Marke Einzeldateien erzeugt
  - wenn nur einzelne Marken oder Teilmengen pro Typ in einen Pool uebernommen werden sollen

## Empfohlene Konventionen

### Pool-Keys

Empfohlen:

- `live`
- `preview`
- `olsx-car-test`
- `olsx-truck-test`
- `customer-x-preview`

### Context-Keys

Empfohlen:

- `internal-live`
- `public-preview`
- `olsx-car-preview`
- `partner-a-api`

### Nutzungstypen

- `internal`: interne Backoffice-/Admin-Nutzung
- `api`: externe API-Nutzung
- `preview`: Test-, Vorschau- oder Staging-Daten

## Typische Setups

### Einfaches Setup

- Pool `live`
- Kontext `internal-live`
- `defaultVehicleDataPool = live`
- `allowedDataPools = [live]`

### Preview-Setup

- Pool `live`
- Pool `preview`
- Kontext `public-preview`
- `defaultVehicleDataPool = preview`
- `allowedDataPools = [preview]`

### API mit begrenztem Testbestand

- Pool `olsx-car-test`
- Kontext `partner-a-api`
- Token ist an `partner-a-api` gebunden
- `allowedDataPools = [olsx-car-test]`

## Wichtige Stolperfallen

- `defaultVehicleDataPool` ohne Eintrag in `allowedDataPools` ist fachlich inkonsistent.
- Inaktive Pools oder Kontexte werden vom Resolver nicht verwendet.
- Ein explizit angeforderter Pool wird abgelehnt, wenn er im Kontext nicht erlaubt ist.
- Ohne Kontext kann das System auf `live` zurueckfallen.
- Beim Loeschen eines Datenpools werden importierte Daten dieses Pools entfernt.

## Relevante Dateien

- [VehicleDataPool.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Entity/VehicleDataPool.php)
- [VehicleDataContext.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Entity/VehicleDataContext.php)
- [VehicleDataContextResolver.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Service/VehicleDataContextResolver.php)
- [VehicleDataPoolCrudController.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/VehicleDataPoolCrudController.php)
- [VehicleDataContextCrudController.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/VehicleDataContextCrudController.php)
- [VehicleDatabaseApiController.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Api/VehicleDatabaseApiController.php)
- [ImportOlsxVehiclePoolCommand.php](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Command/ImportOlsxVehiclePoolCommand.php)
