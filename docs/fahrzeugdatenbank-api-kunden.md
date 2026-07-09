# Fahrzeugdatenbank API - Kundenbeschreibung

Diese Beschreibung ist fuer Kunden gedacht, die die Fahrzeugdatenbank-API als externe Anbindung mieten und in ihre eigene Website, ihr Portal oder eine interne Anwendung integrieren moechten.

## Kurzbeschreibung

Mit der Fahrzeugdatenbank-API erhalten Sie Zugriff auf strukturierte Fahrzeugdaten von DCT. Die API stellt eine kaskadierende Fahrzeugsuche bereit:

- Fahrzeugtyp
- Marke
- Baureihe
- Modell
- Motor
- Motor-Details mit Leistung, Drehmoment, Stages, Steuergeraeten, Optionen und Getriebedaten

Die Daten koennen in eigene Frontends eingebunden werden, zum Beispiel fuer eine Fahrzeugsuche, einen Chiptuning-Konfigurator, eine Angebotsseite oder einen Bestellprozess.

## Was ist in der API-Miete enthalten?

Die API-Miete umfasst den technischen Zugriff auf die Fahrzeugdatenbank ueber einen geschuetzten API-Key.

Enthalten sind:

- Zugriff auf die freigegebenen Fahrzeugdatenbestaende
- geschuetzter API-Zugang mit Bearer Token und Token-ID
- Endpunkte fuer die kaskadierende Fahrzeugauswahl
- Detaildaten zu Motoren, Stages, ECUs, ECU-Optionen und Getrieben
- optionale Trennung nach Datenbestand, falls fuer den Kunden ein eigener Datenpool eingerichtet wurde
- Postman Collection und OpenAPI-Beschreibung fuer Entwickler

Nicht enthalten sind automatisch:

- fertige Frontend-Integration auf Kundenseite
- Hosting der Kundenseite
- individuelle Datenimporte oder Sonderdaten, sofern nicht separat vereinbart
- unbegrenzte Weitergabe des API-Zugangs an Dritte

## Zugangsdaten

Fuer die Integration werden zwei Zugangsdaten benoetigt:

```text
Authorization: Bearer <API_TOKEN>
X-Token-Id: <TOKEN_ID>
```

Der `API_TOKEN` ist der eigentliche geheime Zugangsschluessel. Die `TOKEN_ID` identifiziert den Token im System. Beide Werte werden pro Kunde bereitgestellt und duerfen nicht oeffentlich im Browser-Code sichtbar sein, wenn die Integration sicher betrieben werden soll.

Empfehlung:
Der API-Aufruf sollte ueber ein eigenes Backend oder einen Proxy des Kunden laufen. So bleibt der geheime Token auf dem Server und wird nicht an Website-Besucher ausgeliefert.

## Basis-URL

```text
https://portal.dct-files.de
```

Alle Fahrzeugdatenbank-Endpunkte liegen unter:

```text
/api/fahrzeugdatenbank
```

## Typischer Ablauf der Integration

Die API wird schrittweise abgefragt. Der Benutzer waehlt zuerst den Fahrzeugtyp, danach Marke, Baureihe, Modell und Motor.

1. Fahrzeugtypen laden
2. Marken fuer den gewaehlten Fahrzeugtyp laden
3. Baureihen fuer die gewaehlte Marke laden
4. Modelle fuer die gewaehlte Baureihe laden
5. Motoren fuer das gewaehlte Modell laden
6. Motor-Details fuer den gewaehlten Motor laden

## Endpunkte

### API-Uebersicht

```text
GET /api/fahrzeugdatenbank
```

Liefert eine kurze Uebersicht der verfuegbaren Endpunkte und den verwendeten Datenpool.

### Fahrzeugtypen

```text
GET /api/fahrzeugdatenbank/types
```

Liefert aktive Fahrzeugtypen, zum Beispiel PKW, Motorrad oder Transporter.

### Marken

```text
GET /api/fahrzeugdatenbank/brands/{typeId}
```

Liefert aktive Marken fuer einen Fahrzeugtyp.

### Baureihen

```text
GET /api/fahrzeugdatenbank/series/{brandId}
```

Liefert aktive Baureihen fuer eine Marke.

### Modelle

```text
GET /api/fahrzeugdatenbank/models/{seriesId}
```

Liefert aktive Modelle fuer eine Baureihe.

### Motoren

```text
GET /api/fahrzeugdatenbank/engines/{modelId}
```

Liefert aktive Motoren fuer ein Modell.

### Motor-Details

```text
GET /api/fahrzeugdatenbank/engine-details/{engineId}
```

Liefert die Detaildaten fuer einen Motor.

Die Antwort enthaelt unter anderem:

- Motorname
- Serienleistung
- Drehmoment
- Kraftstoff
- Hubraum
- Bild oder 3D-Modell, falls vorhanden
- Marke und Baureihe
- Stages
- ECUs
- ECU-Optionen
- Getriebe
- Frontend-Einstellungen
- verwendeter Datenpool

Optionale Query-Parameter:

```text
stageId=<ID>
templateVariant=<VARIANTE>
```

### Tracking

```text
POST /api/fahrzeugdatenbank/track
```

Mit diesem Endpunkt koennen einfache Client-Events protokolliert werden, zum Beispiel Auswahl eines Motors oder einer Stage.

Beispiel-Body:

```json
{
  "event": "engine_selected",
  "engineId": 123,
  "stageId": 1,
  "templateVariant": "performance",
  "meta": {
    "source": "customer-website"
  }
}
```

## Beispielrequest

```bash
curl "https://portal.dct-files.de/api/fahrzeugdatenbank/types" \
  -H "Authorization: Bearer <API_TOKEN>" \
  -H "X-Token-Id: <TOKEN_ID>"
```

Beispielantwort:

```json
{
  "data": [
    {
      "id": 1,
      "name": "PKW",
      "slug": "pkw",
      "image": "/uploads/vehicle-type/pkw.svg",
      "icon": "/uploads/vehicle-type/pkw.svg"
    }
  ]
}
```

## Datenbestand und Freischaltung

Jeder Kunde kann auf einen bestimmten Datenbestand freigeschaltet werden. Je nach Vereinbarung kann das sein:

- der allgemeine Live-Datenbestand
- ein kundenspezifischer Datenbestand
- ein Test- oder Preview-Datenbestand
- ein Datenbestand fuer eine bestimmte Website oder Marke

Technisch wird das ueber Datenkontexte und Datenpools gesteuert. In der Regel muss der Kunde diese Details nicht selbst verwalten. Falls mehrere Datenbestaende freigegeben sind, kann der gewuenschte Pool optional mitgesendet werden:

```text
X-Vehicle-Data-Context: <contextKey>
X-Vehicle-Data-Pool: <poolKey>
```

Alternativ sind Query-Parameter moeglich:

```text
?contextKey=<contextKey>&poolKey=<poolKey>
```

Wenn nur ein Datenbestand fuer den Kunden freigegeben ist, reicht normalerweise der API-Token ohne zusaetzliche Pool-Auswahl.

## Sicherheit

Bitte beachten:

- Der API-Token ist geheim und sollte nicht in oeffentlichem JavaScript eingebettet werden.
- API-Zugangsdaten duerfen nicht an Dritte weitergegeben werden.
- Bei Verdacht auf Missbrauch sollte der Token sofort erneuert werden.
- Der Zugriff kann zeitlich begrenzt oder deaktiviert werden.
- Die API darf nur fuer die vereinbarte Website, Anwendung oder Kundennutzung eingesetzt werden.

## Fehlerantworten

Typische Fehler:

```json
{
  "message": "Bearer-Token fehlt."
}
```

```json
{
  "message": "X-Token-Id fehlt."
}
```

```json
{
  "message": "API-Token ist ungueltig oder abgelaufen."
}
```

```json
{
  "message": "Kein gueltiger Datenbestand gefunden."
}
```

```json
{
  "message": "Motor nicht gefunden."
}
```

## Entwicklerunterlagen

Fuer die technische Integration stehen folgende Dateien bereit:

- OpenAPI: `docs/openapi-vehicle.json`
- Postman Collection: `docs/postman-vehicle.collection.json`
- Technische API-Doku: `docs/api.md`

## Uebergabe an den Kunden

Bei Aktivierung der API-Anbindung sollte der Kunde erhalten:

- API-Basis-URL
- API-Token
- Token-ID
- optionaler Context-Key
- optionaler Pool-Key
- Link oder Datei zur OpenAPI-Beschreibung
- Link oder Datei zur Postman Collection
- Hinweis, ob der Token zeitlich begrenzt ist
- Ansprechpartner fuer technische Rueckfragen
