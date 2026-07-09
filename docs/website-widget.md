# Website Widget

Stand: 13.05.2026

Das Website Widget ist eine Superadmin-Funktion, mit der fuer einen Kunden ein einbettbarer Fahrzeugdatenbank-Konfigurator bereitgestellt wird. Der Kunde erhaelt nur einen HTML-/JavaScript-Code zur Einbindung auf seiner Website. API-Zugangsdaten bleiben ausschliesslich serverseitig im CRM.

## Zweck

Das Widget erlaubt externen Websites, die Fahrzeugdatenbank zu nutzen, ohne dass der Kunde eigene API-Logik oder einen sichtbaren API-Key im Browser betreiben muss.

Der Ablauf ist:

1. Superadmin legt eine Widget-Konfiguration fuer einen Kunden an, oder der Kunde legt das Widget selbst an unter `/kunden-widget`.
2. Superadmin hinterlegt oder erzeugt einen API-Key (automatisch beim ersten Speichern).
3. Superadmin waehlt optional einen Fahrzeugdatenpool.
4. Kunde bekommt nur das Snippet.
5. Widget laedt Fahrzeugdaten ueber serverseitige Proxy-Routen.
6. Anfrageformular sendet eine E-Mail an die beim Kunden hinterlegte E-Mail-Adresse.

## Admin

Menuepunkt:

```text
Website Widget
```

Controller:

```text
src/Controller/Admin/WebsiteWidgetConfigCrudController.php
```

Entity:

```text
src/Entity/WebsiteWidgetConfig.php
```

Repository:

```text
src/Repository/WebsiteWidgetConfigRepository.php
```

## Einbettung

Im Detail des Website Widgets werden zwei Snippets angezeigt.

Empfohlen ist das JavaScript-Snippet:

```html
<div id="tuningfux-widget"></div>
<script src="https://verwaltung.tuningfux.de/widget/<embedKey>/embed.js"></script>
```

Alternativ kann direkt ein iframe genutzt werden:

```html
<iframe src="https://verwaltung.tuningfux.de/widget/<embedKey>" width="100%" height="850" frameborder="0" style="border:none;"></iframe>
```

Das JavaScript-Snippet erzeugt intern ebenfalls ein iframe. Dadurch bleiben CSS und JavaScript der Kundenseite vom Widget isoliert.

## Oeffentliche Routen

```text
GET  /widget/{embedKey}/embed.js
GET  /widget/{embedKey}
GET  /widget/{embedKey}/anfrage
POST /widget/{embedKey}/anfrage
```

Interne Widget-API-Proxy-Routen:

```text
GET  /widget/{embedKey}/api/types
GET  /widget/{embedKey}/api/brands/{typeId}
GET  /widget/{embedKey}/api/series/{brandId}
GET  /widget/{embedKey}/api/models/{seriesId}
GET  /widget/{embedKey}/api/engines/{modelId}
GET  /widget/{embedKey}/api/engine-details/{engineId}
POST /widget/{embedKey}/api/track
POST /widget/{embedKey}/api/inquiry
```

Die direkte Formularroute `/widget/{embedKey}/anfrage` ist der normale Weg fuer Endnutzer. Der JSON-Endpunkt `/widget/{embedKey}/api/inquiry` bleibt fuer programmatische oder spaetere JS-basierte Varianten vorhanden.

## API-Key

Das Widget referenziert einen `ApiToken`.

Wichtig:

- Der API-Key wird nie im Kundensnippet ausgegeben.
- Der Proxy setzt intern `Authorization: Bearer <apiToken>`.
- Der Proxy setzt intern `X-Token-Id: <tokenIdentifier>`.
- Wenn im Widget kein API-Key gewaehlt wird, erzeugt der Admin-CRUD beim Speichern automatisch einen aktiven API-Key fuer den Kunden.

## Datenpool

Das Widget kann optional direkt mit einem `VehicleDataPool` verbunden werden.

Wenn ein Datenpool gesetzt ist:

- alle Widget-API-Requests erzwingen diesen Pool serverseitig
- ein vom Browser gesendeter `poolKey` wird entfernt und durch den Widget-Pool ersetzt
- der Kunde sieht den Pool nicht im Snippet

Wenn kein Datenpool gesetzt ist:

- es greift der API-Token-Kontext
- danach der Standard-/Fallback-Mechanismus der Fahrzeugdatenbank-API

## Konfigurationsfelder

### Allgemein

- `active`: Widget aktiv/inaktiv
- `kunde`: Empfaenger und fachliche Zuordnung
- `apiToken`: interner API-Key
- `vehicleDataPool`: optional erzwungener Datenpool
- `embedKey`: oeffentlicher Widget-Key
- `title`: Titel im Widget und auf der Anfrage-Seite
- `subtitle`: Untertitel
- `buttonText`: Text des Anfrage-Buttons
- `successTitle`: Titel nach erfolgreicher Anfrage
- `successMessage`: Nachricht nach erfolgreicher Anfrage
- `templateVariant`: Layoutvariante der Fahrzeugdatenbank. Im Widget stehen zusaetzlich zu den normalen Varianten `Dunkel`, `Hell / Minimal`, `Contrast`, `Sidebar` und `Download` auch `Premium Dunkel` (`widget_premium_dark`), `Premium Hell` (`widget_premium_light`), `Widget Katalog Hell` (`widget_catalog_light`), `V5 Tabelle` (`layout5`) und `V6 Sidebar Hell` (`layout6`) zur Auswahl.
- `iframeHeight`: Hoehe fuer das iframe-Snippet

### Branding und Farben

- `primaryColor`: Primaerfarbe fuer Buttons, aktive Tabs und Akzente
- `backgroundColor`: Widget-Hintergrund
- `textColor`: Textfarbe
- `selectBackgroundColor`: Hintergrundfarbe fuer Selectboxen, Select2 und Formularfelder
- `selectOpenBackgroundColor`: Hintergrundfarbe fuer geoeffnete Dropdown-Zeilen und das Suchfeld in Selectboxen (Standard: `#f3f4f6`)
- `typeIconColor`: Farbe der Fahrzeugtyp-Icons, `white` oder `black` (Standard: `white`)
- `customCss`: zusaetzliches CSS, wird am Ende der Widget-Styles eingefuegt

## Widget Theme: Premium Dunkel

`Premium Dunkel` ist eine Widget-only Layoutvariante und technisch als `widget_premium_dark` gespeichert. Das Theme nutzt die Struktur des bisherigen dunklen Performance-Layouts, bekommt aber eine eigene Farbpalette fuer externe Kundenseiten.

Farbbericht:

| Farbe | Wert | Verwendung |
| --- | --- | --- |
| Seitenhintergrund | `#07090d` | Basisflaeche des Widgets |
| Hintergrund-Verlauf dunkel | `#0b1018` | oberer Verlauf und Tiefenwirkung |
| Panel | `#10141b` | Karten, Stages und Anfragebox |
| Panel erhoeht | `#151b24` | hervorgehobene Stage-/Optionsbereiche |
| Panel weich | `#0d1117` | kleine Boxen, Metriken, Tabellenbereiche |
| Rahmen | `rgba(244, 247, 250, 0.12)` | neutrale Karten- und Selectbox-Rahmen |
| Rahmen Akzent | `rgba(213, 59, 42, 0.45)` | aktive oder wichtige Bereiche |
| Text | `#f4f7fa` | Haupttext und Zahlen |
| Text gedaempft | `#a7b0bf` | Untertitel und Hilfstexte |
| Text sekundaer | `#6f7a8a` | Labels, Overlines und Metaangaben |
| Primaer/Akzent | `#d53b2a` | Buttons, aktive Tabs, Akzente, Stage-Werte |
| Akzent-Kontrasttext | `#ffffff` | Text auf Primaerflaechen |
| Selectbox Hintergrund | `#111722` | Selectboxen, Select2 und Formularfelder |
| Selectbox Rahmen | `#2b3544` | Feldrahmen |
| Selectbox Hover | `#182131` | aktive Dropdown-Zeile und Suchfeld |
| Erfolg | `#47c26b` | Leistungs-/Fortschrittsverlauf |

Die Felder `primaryColor`, `backgroundColor`, `textColor` und `selectBackgroundColor` ueberschreiben die Theme-Defaults weiterhin. Wenn beim Wechsel auf `Premium Dunkel` noch die alten Widget-Standardfarben gesetzt sind, werden sie automatisch auf die Premium-Dunkel-Defaults umgestellt.

## Widget Theme: Premium Hell

`Premium Hell` ist eine helle Variante des Premium-Themes und technisch als `widget_premium_light` gespeichert. Es teilt die Struktur von `Premium Dunkel`, verwendet aber eine helle Farbpalette.

Farbbericht:

| Farbe | Wert | Verwendung |
| --- | --- | --- |
| Seitenhintergrund | `#f8f9fc` | Basisflaeche des Widgets |
| Hintergrund-Verlauf | `#eef0f5` | oberer Verlauf |
| Panel | `#ffffff` | Karten, Stages und Anfragebox |
| Panel erhoeht | `#f3f4f8` | hervorgehobene Stage-/Optionsbereiche |
| Panel weich | `#f5f6fa` | kleine Boxen, Metriken, Tabellenbereiche |
| Rahmen | `rgba(15, 20, 40, 0.10)` | neutrale Karten- und Selectbox-Rahmen |
| Rahmen Akzent | `rgba(213, 59, 42, 0.35)` | aktive oder wichtige Bereiche |
| Text | `#111827` | Haupttext und Zahlen |
| Text gedaempft | `#4b5563` | Untertitel und Hilfstexte |
| Text sekundaer | `#6b7280` | Labels, Overlines und Metaangaben |
| Primaer/Akzent | `#d53b2a` | Buttons, aktive Tabs, Akzente, Stage-Werte |
| Akzent-Kontrasttext | `#ffffff` | Text auf Primaerflaechen |
| Selectbox Hintergrund | `#ffffff` | Selectboxen, Select2 und Formularfelder |
| Selectbox Rahmen | `#d1d5db` | Feldrahmen |
| Selectbox Hover | `#f3f4f6` | aktive Dropdown-Zeile und Suchfeld |
| Erfolg | `#47c26b` | Leistungs-/Fortschrittsverlauf |

Beim Wechsel auf `Premium Hell` wird `primaryColor` automatisch auf `#d53b2a` umgestellt, wenn noch der alte Widget-Standard `#5a2116` gesetzt ist. Hintergrund, Textfarbe und Selectbox-Hintergrund bleiben bei den hellen Standardwerten.

## Widget Theme: Widget Katalog Hell

`Widget Katalog Hell` ist eine helle Widget-only Layoutvariante und technisch als `widget_catalog_light` gespeichert. Das Theme bildet die Fahrzeugdetailansicht als Katalogansicht ab: Breadcrumb mit Markenlogo, breite Stage-Karten, drei Prognose-Karten, ein grosser Tuning-Optionen-Block und ein direkt sichtbares Leistungsdiagramm.

Farbbericht:

| Farbe | Wert | Verwendung |
| --- | --- | --- |
| Seitenhintergrund | `#ffffff` | Basisflaeche des Widgets |
| Panel | `#ffffff` | Stage-, Prognose-, Optionen- und Chart-Karten |
| Panel weich | `#f7f8fb` | Selectbox-Hover und dezente Buttonflaechen |
| Rahmen | `#d9dee7` | Karten, Optionen und Chart-Raster |
| Text | `#171d2a` | Haupttext, Leistungswerte, Drehmomentwerte |
| Text gedaempft | `#687184` | Labels, Breadcrumb-Trenner, Chart-Achsen |
| Primaer/Akzent | `#c4933c` | aktive Stage, Delta-Werte, beliebtes Badge |
| Eco | `#28a96b` | Eco-Stage und Eco-Akzente |

Beim Wechsel auf `Widget Katalog Hell` wird `primaryColor` automatisch auf `#c4933c` umgestellt, wenn noch der alte Widget-Standard `#5a2116` gesetzt ist.

### Datenerfassung

Name und Nachricht sind immer Basisfelder auf der Anfrage-Seite.

Die folgenden Felder sind Zusatzfelder und werden nur angezeigt, wenn sie in der Konfiguration aktiv sind:

- Telefon
- E-Mail
- FIN/VIN
- Kennzeichen
- Kilometerstand

Pflichtlogik:

- Telefon Pflicht greift nur, wenn Telefon aktiv ist.
- E-Mail Pflicht greift nur, wenn E-Mail aktiv ist.
- Deaktivierte Zusatzfelder werden nicht validiert und nicht leer in die E-Mail geschrieben.

### Datenschutz

- `privacyRequired`: Datenschutz-Checkbox erforderlich
- `privacyUrl`: Link zur Datenschutzerklaerung
- `privacyTextHtml`: HTML-Text neben der Checkbox
- `redirectAfterSubmit`: Weiterleitung nach erfolgreichem Absenden

### Sicherheit

- `allowedDomains`: erlaubte Domains, eine pro Zeile
- Wildcards sind mit `*.domain.de` moeglich
- Leeres Feld bedeutet: alle Domains erlaubt
- `sendTrackingEvents`: Tracking an die Fahrzeugdatenbank-API senden

## Anfrageformular

Der Button in der Fahrzeug-Detailansicht fuehrt auf eine eigene Seite:

```text
/widget/{embedKey}/anfrage
```

Die ausgewaehlte Fahrzeugkombination wird als Query-Parameter uebergeben und auf der Formularseite sichtbar angezeigt:

- Fahrzeugtyp
- Marke
- Baureihe
- Modell
- Motor
- Stage

Beim Absenden wird eine E-Mail an `Kunde.email` gesendet.

Die E-Mail enthaelt:

- Widget
- Kunde
- ausgewaehltes Fahrzeug
- Name
- Nachricht
- aktive Zusatzfelder aus der Widget-Konfiguration

Es wird kein CRM-Lead angelegt.

## Logos und Assets

Brand-Logos und andere Asset-Pfade werden im Widget-Proxy fuer JSON-Antworten absolutiert.

Betroffene Felder:

- `image`
- `icon`
- `logo`
- `manufacturerLogo`
- `glbModel`
- `images`

Dadurch funktionieren relative Upload-Pfade wie `/uploads/...` auch im Widget-/iframe-Kontext.

## Kunden-Portal

Kunden koennen ihr Widget selbst konfigurieren ueber ein eigenes Portal mit separatem Login.

### Routen

```text
GET|POST /kunden-widget/login
GET|POST /kunden-widget
GET      /kunden-widget/logout
```

### Zugang

- Login unter `/kunden-widget/login` mit Kunden-E-Mail und Passwort (aus `Kunde.password`).
- Jeder Kunde sieht und bearbeitet nur sein eigenes Widget.
- Die Seite wird durch die Symfony-Firewall `customer_widget` abgesichert.
- Zugriffsrolle: `ROLE_WEBSITE_WIDGET_CUSTOMER`.
- Ist noch kein Widget vorhanden, wird bei erstmaligem Speichern automatisch eines angelegt (inkl. `embedKey` und `ApiToken`).

### Technische Bestandteile

- `src/Controller/CustomerWebsiteWidgetController.php`: Routen, Login, Bearbeitung, Logout
- `src/Form/WebsiteWidgetConfigFormType.php`: Formular fuer Kunden
- `src/Service/WebsiteWidgetConfigManager.php`: Vorbereitung und Erzeugung neuer Widgets
- `src/Security/WebsiteWidgetCustomerUser.php`: UserInterface fuer Kunden-Login
- `src/Security/WebsiteWidgetCustomerUserProvider.php`: laedt den Kunden aus der Datenbank
- `src/Security/WebsiteWidgetCustomerAuthenticator.php`: Login-Formular-Authenticator
- `templates/customer_widget/`: Templates fuer Login- und Bearbeitungsseite

### Firewall

In `config/packages/security.yaml` ist der Firewall-Block `customer_widget` konfiguriert:

```yaml
customer_widget:
    pattern: ^/kunden-widget
    provider: website_widget_customer_provider
    custom_authenticator: App\Security\WebsiteWidgetCustomerAuthenticator
    remember_me:
        lifetime: 604800
        path: /kunden-widget
    logout:
        path: customer_widget_logout
        target: customer_widget_login
```

## Datenbank / Migrationen

Relevante Migrationen:

```text
Version20260504120000  Create website_widget_config
Version20260504123000  Add vehicle_data_pool
Version20260504124500  Add select_background_color
Version20260511120000  Add select_open_background_color
Version20260512100000  Add type_icon_color
```

Nach Code-Aenderungen an der Entity muss die DB mit Doctrine-Migrationen aktualisiert werden:

```bash
php bin/console doctrine:migrations:migrate
```

## Wichtige Dateien

```text
src/Entity/WebsiteWidgetConfig.php
src/Repository/WebsiteWidgetConfigRepository.php
src/Controller/Admin/WebsiteWidgetConfigCrudController.php
src/Controller/WebsiteWidgetController.php
src/Controller/CustomerWebsiteWidgetController.php
src/Form/WebsiteWidgetConfigFormType.php
src/Service/WebsiteWidgetConfigManager.php
src/Security/WebsiteWidgetCustomerUser.php
src/Security/WebsiteWidgetCustomerUserProvider.php
src/Security/WebsiteWidgetCustomerAuthenticator.php
templates/website/vehicle_database.html.twig
templates/website/widget_inquiry.html.twig
templates/customer_widget/
```
