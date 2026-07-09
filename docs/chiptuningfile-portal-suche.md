# Portal `chiptuningfile`: Suche

## Uebersicht

Im Portal `chiptuningfile` existieren derzeit zwei getrennte Suchsysteme:

1. Eine klassische Header-Autocomplete-Suche im Twig-Layout.
2. Eine React-Global-Suche als Command-Palette im neuen React-Header.

Beide Suchen greifen nicht auf dieselbe Datenquelle zu und beeinflussen sich nicht gegenseitig.

## 1. Header-Autocomplete im klassischen Portal-Header

### Einbindung

- Twig-Einbindung im Portal-Layout:
  - `templates/portal/chiptuningfile/base.html.twig`
- Verwendete Live-Component:
  - `src/Twig/Components/PortalHeaderSearch.php`
- Template der Suchbox:
  - `templates/components/portal_header_search.html.twig`

Die Suchbox sitzt im klassischen Header des Twig-Portals und nutzt `symfony/ux-autocomplete`.

### Request-Fluss

1. Der Benutzer tippt in das Suchfeld.
2. Ab 2 Zeichen wird per AJAX geladen:
   - Route: `cpt_portal_header_search_autocomplete`
   - Pfad: `/header-search/autocomplete`
3. Der Controller ruft den Portalkontext auf und laedt Treffer ueber den Suchservice.

### Backend-Dateien

- Controller:
  - `src/Portals/Chiptuningfile/Controller/HeaderSearchController.php`
- Suchservice:
  - `src/Portals/Chiptuningfile/Service/PortalHeaderSearchService.php`

### Rueckgabeformat

Der Endpoint liefert JSON in dieser Form:

```json
{
  "results": [
    {
      "text": "Dashboard",
      "subtext": "Uebersicht und Startseite des Portals",
      "url": "/...",
      "group": "Bereiche",
      "meta": "Seite",
      "keywords": "dashboard start home ..."
    }
  ],
  "next_page": null
}
```

### Suchlogik

Die eigentliche Logik sitzt in `PortalHeaderSearchService::search()`.

Regeln:

- Mindestlaenge: `2` Zeichen
- Standardlimit: `8` Treffer
- Reihenfolge:
  1. Seiten-/Modultreffer
  2. Fahrzeugtreffer, nur wenn noch Platz im Limit ist

### Welche Seiten durchsucht werden

Es gibt fest definierte Seitentreffer fuer aktivierte Module:

- `Dashboard`
- `Profil`
- `Uploads`
- `Credits`
- `Fahrzeugdatenbank`

Die Seite wird nur beruecksichtigt, wenn das jeweilige Modul im Portal aktiviert ist.

Matching erfolgt ueber `matches()` als einfache normalisierte Teilzeichenkette auf:

- `text`
- `subtext`
- `keywords`

Normalisierung aktuell:

- `trim()`
- `mb_strtolower()`

Es gibt hier kein Fuzzy-Matching und keine Gewichtung.

### Welche Fahrzeugdaten durchsucht werden

Fahrzeugtreffer kommen aus `VehicleEngineRepository` ueber einen QueryBuilder auf `VehicleEngine`.

Es wird gesucht in:

- `engine.name`
- `engine.engineCode`
- `model.name`
- `series.name`
- `brand.name`

Weitere Regeln:

- nur `engine.active = true`
- Suche per SQL-`LIKE '%query%'`
- Sortierung:
  - `brand.name`
  - `series.name`
  - `model.name`
  - `engine.name`

Die Treffergruppe heisst immer `Fahrzeugdatenbank`.

### Ziel-URL eines Fahrzeugtreffers

Ein Fahrzeugtreffer oeffnet nicht direkt einen einzelnen Datensatz, sondern leitet auf die Fahrzeugdatenbank weiter:

- Route: `cpt_portal_vehicles`
- Query-Parameter: `q`

Der `q`-Wert wird aus diesen Teilen zusammengesetzt:

- Brand-Name
- Modell-Name
- `engineCode`, sonst `engine.name`

Damit ist die Header-Suche technisch eher ein Einstieg in die Fahrzeugdatenbank-Suche als eine vollstaendige Fahrzeugsuche.

### Fallback-Ziel beim Formular-Submit

Wenn das Suchformular direkt abgesendet wird, kommt die Ziel-URL aus:

- `PortalHeaderSearchService::getDefaultSearchUrl()`

Regel:

- wenn Modul `vehicle_database` aktiv ist: Ziel ist `cpt_portal_vehicles`
- sonst: Ziel ist `cpt_portal_dashboard`

## 2. React-Global-Suche im neuen React-Header

### Einbindung

- React-Header:
  - `templates/portal/theme_react_chiptuningfile/chiptuningfilede/frontend/src/components/layout/Header.jsx`
- Suchdialog:
  - `templates/portal/theme_react_chiptuningfile/chiptuningfilede/frontend/src/components/search/GlobalSearch.jsx`
- Datenaufbau:
  - `templates/portal/theme_react_chiptuningfile/chiptuningfilede/frontend/src/data/portalSearchData.js`

Die Suche oeffnet als Command-Palette ueber `Ctrl+K` bzw. `Cmd+K`.

### Datenquelle

Diese Suche geht aktuell nicht live gegen das Backend.

Der Suchindex wird lokal im Browser aufgebaut aus:

- `window.portalReactBootstrap`
- `getOrdersData()`
- `getInvoicesData()`
- geparkten Auftraegen aus Bootstrap-Daten
- statischen Navigationseintraegen
- statischen Schnellaktionen

### Ergebnisarten

Der Index kennt derzeit diese Typen:

- `action`
- `navigation`
- `order`
- `parked_order`
- `customer`
- `vehicle`
- `invoice`
- `credit_transaction`

### Suchlogik

Die Filterung passiert komplett in `GlobalSearch.jsx` in `filterResults()`.

Regeln:

- Query wird normalisiert mit `trim().toLowerCase()`
- Aufteilung in Tokens per Whitespace
- durchsucht werden:
  - `title`
  - `subtitle`
  - `badge`
  - `keywords`

Scoring pro Token:

- `+5`, wenn Token im `title` vorkommt
- `+3`, wenn Token im `subtitle` vorkommt
- `+2`, wenn Token in `keywords` vorkommt
- `+1`, wenn Token nur irgendwo im Gesamthaystack vorkommt

Ein Treffer bleibt nur erhalten, wenn `score >= Anzahl Tokens`.

Danach Sortierung:

1. hoeherer Score zuerst
2. bei Gleichstand alphabetisch nach `title`

### Charakter der React-Suche

Diese Suche ist eine Portal-Navigation und Datensatz-Schnellsuche fuer bereits im Frontend bekannte Daten.

Sie durchsucht insbesondere:

- Navigation
- Schnellaktionen
- Portalauftraege
- geparkte Auftraege
- Rechnungen
- Credits/Zahlungen
- Kundenprofil-Daten
- Fahrzeuge aus Auftraegen

Sie ist keine Live-Suche gegen die Fahrzeugdatenbank und ersetzt die Header-Autocomplete nicht.

## 3. Abgrenzung beider Suchen

### Header-Autocomplete

- lebt im Twig-Portal
- geht gegen Symfony-Backend
- kennt Portalmodule
- kennt Fahrzeugdatenbank ueber `VehicleEngine`
- hat hartes Limit `8`

### React-Global-Suche

- lebt im React-Header
- sucht lokal im Browser
- basiert auf Bootstrap-/Session-Daten
- eignet sich fuer Navigation und vorhandene Portalobjekte
- hat kein Backend-Request pro Suchvorgang

## 4. Wichtige technische Konsequenz

Wenn man die Suche im Portal aendern will, muss zuerst klar sein, welche Suche gemeint ist:

- Treffer im klassischen Header-Dropdown:
  - `PortalHeaderSearchService`
- Treffer in der React-Command-Palette:
  - `GlobalSearch.jsx`
  - `portalSearchData.js`
- Treffer in der Fahrzeugdatenbank selbst:
  - separate Suche auf der Fahrzeugdatenbank-Seite
  - nicht Teil der hier beschriebenen Header-/Global-Suche

## 5. Aktueller Stand in einem Satz

`chiptuningfile` hat derzeit keine einheitliche zentrale Suche, sondern eine backendgestuetzte Header-Autocomplete und daneben eine getrennte frontendseitige React-Global-Suche.
