# Memory

## Website Widget

- Datum: 2026-05-13
- Superadmin-Modul: `Website Widget`
- Zweck: kundenspezifische Einbettung der Fahrzeugdatenbank per Snippet, ohne API-Key im Browser.
- Kern-Dateien:
  - `src/Entity/WebsiteWidgetConfig.php`
  - `src/Controller/Admin/WebsiteWidgetConfigCrudController.php`
  - `src/Controller/WebsiteWidgetController.php`
  - `src/Controller/CustomerWebsiteWidgetController.php`
  - `src/Form/WebsiteWidgetConfigFormType.php`
  - `src/Service/WebsiteWidgetConfigManager.php`
  - `src/Security/WebsiteWidgetCustomerUser.php`
  - `src/Security/WebsiteWidgetCustomerUserProvider.php`
  - `src/Security/WebsiteWidgetCustomerAuthenticator.php`
  - `templates/website/vehicle_database.html.twig`
  - `templates/website/widget_inquiry.html.twig`
  - `templates/customer_widget/`
- Routen (oeffentlich/Widget):
  - `/widget/{embedKey}/embed.js`
  - `/widget/{embedKey}`
  - `/widget/{embedKey}/anfrage`
  - `/widget/{embedKey}/api/*`
- Routen (Kunden-Portal):
  - `/kunden-widget/login`
  - `/kunden-widget`
  - `/kunden-widget/logout`
- Kunden-Portal:
  - Kunden koennen ihr Widget selbst anlegen und bearbeiten unter `/kunden-widget`.
  - Eigene Symfony-Firewall `customer_widget`, Zugriffsrolle `ROLE_WEBSITE_WIDGET_CUSTOMER`.
  - Beim ersten Speichern werden `embedKey` und `ApiToken` automatisch erzeugt.
  - Kunden-Anleitung: `docs/website-widget-kunden-anleitung.md`.
- Anfragen:
  - Button in der Fahrzeug-Detailansicht fuehrt auf eigene Anfrage-Seite.
  - Ausgewaehltes Fahrzeug wird auf der Formularseite angezeigt.
  - Name und Nachricht sind Basisfelder.
  - Telefon, E-Mail, FIN/VIN, Kennzeichen und Kilometerstand sind konfigurierbare Zusatzfelder.
  - Anfrage wird per Mail an `Kunde.email` gesendet, nicht als CRM-Lead gespeichert.
- Datenpool:
  - `WebsiteWidgetConfig.vehicleDataPool` erzwingt den Pool im Widget-Proxy.
  - Ohne Widget-Pool greift API-Token-Kontext/Fallback.
- Styling:
  - Primaerfarbe, Hintergrundfarbe, Textfarbe, Selectbox-Hintergrundfarbe, Selectbox-offen-Hintergrundfarbe und Custom CSS.
  - `typeIconColor` (`white` oder `black`) steuert die Farbe der Fahrzeugtyp-Icons.
  - Widget-only Themes: `Premium Dunkel` (`widget_premium_dark`), `Premium Hell` (`widget_premium_light`), `V5 Tabelle` (`layout5`), `V6 Sidebar Hell` (`layout6`).
  - `Premium Dunkel` Defaultfarben: `#07090d`, `#10141b`, `#d53b2a`, `#111722`, `#f4f7fa`, Selectbox-offen: `#182131`.
- Migrationen:
  - `Version20260504120000` – Create website_widget_config
  - `Version20260504123000` – Add vehicle_data_pool
  - `Version20260504124500` – Add select_background_color
  - `Version20260511120000` – Add select_open_background_color
  - `Version20260512100000` – Add type_icon_color

## Vehicle-Datenpool / Portal-Stand

- Datum: 2026-04-05
- Portal: `chiptuningfile`
- Aktueller Fokus: Vehicle-API im Portal mit portalabhängigem `VehicleDataPool`

## Datenpool-Logik

- `Portal` hat jetzt eine Relation `vehicleDataPool`.
- Der Portal-Proxy für die Vehicle-API übergibt das Portal an `VehicleApiProxy`.
- `VehicleApiProxy` hängt bei aktivem Pool automatisch `poolKey` an die API-Requests.
- Token-Auflösung:
  - zuerst kundenbezogener Token für den Pool
  - dann globaler Token für den Pool
  - dann Fallback auf globalen aktiven Token
- Wichtig: Der globale Fallback wurde bewusst beibehalten, damit `/vehicle-api/types` nicht mit `401` scheitert, wenn für den Pool noch kein eigener Token existiert.

## Bekannter VW-Stand

- Für den VW-Datenpool sind aktuell 7 Fahrzeugtypen relevant/verfügbar.
- Die API liefert diese Typen im Portal-Netzwerk korrekt aus.
- Das frühere Problem war nicht das Laden, sondern die Darstellung im React-Frontend.

## Frontend-Status

- Betroffenes Frontend:
  - `templates/portal/theme_react_chiptuningfile/chiptuningfilede/frontend`
- Betroffene Datei:
  - `src/pages/Configurator.jsx`
- Fix:
  - API-Typen werden nicht mehr auf wenige starre Sammeltypen zusammengefaltet.
  - Die Typ-Kacheln rendern jetzt die echten API-Typen.
  - Die Fahrzeugauswahl läuft jetzt direkt für den gewählten API-Typ weiter.
- Ergebnis:
  - Die 7 Typen aus dem VW-Datenpool sollen im Portal sichtbar sein.

## Backend-Dateien mit Bezug

- `src/Entity/Portal.php`
- `src/Service/VehicleApiProxy.php`
- `src/Portals/Chiptuningfile/Controller/VehicleApiProxyController.php`
- `src/Portals/SuperadminPortal/Controller/VehicleApiProxyController.php`

## Letzter Build

- React-Build für `chiptuningfile` wurde am 2026-04-05 erfolgreich ausgeführt.
- Zielpfad:
  - `public/assets/portals/chiptuningfile/react`

## Scraper-Import im Superadmin

- Stand: 2026-04-21
- Im Superadmin gibt es jetzt drei zusammenhängende Scraper-Bereiche:
  - `admin_scraper_control`
  - `admin_scraper_import`
  - `admin_scraper_output_import`

### Klassischer Import

- Route: `/admin/fahrzeugdatenbank/scraper/import`
- Quelle: Root-Dateien wie `scrapy-remote-scraper/olsx_brands_car_20260416_024939.json`
- Verhalten:
  - Vorschau für eine ausgewählte JSON-Datei
  - Pool-Auswahl
  - Importhistorie für klassische Root-Dateien

### Output-Import

- Route: `/admin/fahrzeugdatenbank/scraper/import-output`
- Quelle: `scrapy-remote-scraper/data/output/<type>/*.json`
- Verhalten:
  - rekursives Einlesen der Marken-Einzelfiles
  - Gruppierung nach Typ (`car`, `moto`, `atv`, `agricultural`, usw.)
  - pro Typ eine Tabelle mit:
    - `Alle`-Checkbox
    - Einzel-Checkboxen pro Datei
  - Mehrfachauswahl über mehrere Dateien
  - Sammelvorschau vor dem Import
  - getrennte Historie nur für `data/output`

### Datenmodell / Logik

- Analyzer:
  - kann klassische Root-Dateien und rekursive `data/output`-Dateien lesen
  - erkennt beim Output-Import auch Einzeldateien mit genau einer Marke
- Historie:
  - klassische Importseite blendet `data/output`-Logs aus
  - Output-Import zeigt nur Logs mit Dateipfad unter `scrapy-remote-scraper/data/output`

### Relevante Dateien

- `src/Controller/Admin/ScraperControlController.php`
- `src/Controller/Admin/ScraperImportController.php`
- `src/Controller/Admin/ScraperOutputImportController.php`
- `src/Service/ScraperImportAnalyzer.php`
- `src/Repository/ScraperImportLogRepository.php`
- `templates/admin/scraper_control/index.html.twig`
- `templates/admin/scraper_import/index.html.twig`
- `templates/admin/scraper_import/output.html.twig`

## ECU Intelligence (Tool-News-Scraper + ECU-Erkennung)

- Stand: 2026-07-04
- Erweitert den bestehenden Scraper-Bereich um einen zweiten, unabhaengigen Zweig fuer Tool-Anbieter-News (Autotuner, MagicMotorsport), getrennt vom OLSX-Fahrzeugdaten-Scraper.
- Neue Menue-Sektion im EasyAdmin: `ECU Intelligence` (Tool-Anbieter, News-Quellen, Anbieter-News, Erkannte ECUs, Erkennungsmuster).

### Schritt 1: Scraping + Import

- Entities: `ToolProvider`, `ToolNewsSource`, `ToolNewsItem`.
- Scrapy-Spider unter `scrapy-remote-scraper/scraper_project/spiders/`:
  - `autotuner_news_spider.py` (echte Seite: `autotuner.com/de/blogs/neuigkeiten/tagged/tool`, extrahiert zusaetzlich die "Hauptmerkmale"-Box als `key_features`-Dict via JSON-LD-unabhaengigem XPath).
  - `magicmotorsport_news_spider.py` (echte Seite: `magicmotorsport.com/en/news-updates/?e-filter-33ffcdb-category=flex-update`, nutzt Yoast JSON-LD (`script.yoast-schema-graph`) fuer Titel/Datum statt HTML-Parsing).
  - `alientech_news_spider.py` (Stand 2026-07-06, echte Seite: `alientech-tools.com/de/kess3-nachrichten/`). Anderes Muster als die ersten beiden: Listing-Seite wird nur genutzt, um die Post-ID aus der CSS-Klasse `post-<id>` auf `article.elementor-post` zu lesen und die Pagination zu verfolgen (`a.page-numbers.next` bzw. Fallback `.e-load-more-anchor[data-next-page]`); Artikel-Details kommen **nicht** per HTML-Scraping, sondern ueber die WordPress-REST-API des Custom-Post-Types `news`: `https://www.alientech-tools.com/de/wp-json/wp/v2/news/{id}?_embed=1`. Liefert sauberes JSON (`title.rendered`, `content.rendered` als Elementor-HTML das per Regex von Tags befreit wird, `date_gmt`, `_embedded['wp:featuredmedia'][0].source_url` fuers Bild). `key_features` wird aus der "Technischer Fokus"-`<ul>` im Content geparst (ECU, Verbindung, Produktionsjahre, Brands).
  - Alle drei unterstuetzen `-a limit=N` und `-a year=YYYY` (Jahresfilter, bricht Pagination ab sobald ein aelterer Artikel auftaucht — Achtung: MagicMotorsport-Listing ist nicht 100% chronologisch sortiert, der erste Eintrag kann "gepinnt" sein).
  - `ValidationPipeline` in `scraper_project/pipelines.py` hat alle drei Spider-Namen in `skip_validation_spiders` whitelisted (neben dem bereits vorhandenen `olsx_brands_spider`), da das Item-Schema abweicht. **Bestehende OLSX-Logik nicht veraendert.**
  - Neuer Anbieter braucht immer zwei Schritte: Spider-Datei hier anlegen **und** `SPIDER_MAP` in `src/Command/ToolNewsRunCommand.php` um `'<provider-slug>' => '<spider_name>'` ergaenzen — ein `ToolNewsSource`/`ToolProvider`-Datensatz im EasyAdmin allein (auch mit gesetztem `spiderName`-Feld) macht einen Anbieter **nicht** scrapefaehig, das Feld ist nur eine freie Text-Referenz ohne Verbindung zum tatsaechlichen Code.
- Commands: `app:tool-news:run {provider} [--year=]`, `app:tool-news:import {file}` (Dedup ueber `url` und `contentHash`).
- Wichtig: die lokale `.venv` unter `scrapy-remote-scraper/` war zwischenzeitlich kaputt (Shebang zeigte auf einen alten Server-Pfad, Python-Binary inkompatibel zur System-glibc). Wurde neu aufgebaut mit `virtualenv` + `pip install -r requirements.txt`; `twisted` musste explizit auf `<24` gepinnt werden (neuere Version bricht mit Scrapy 2.11.2).

### Schritt 2: ECU-Erkennung

- Entities: `DetectedEcu` (ecuName, ecuFamily, manufacturer, mcu, readMethod, protocol, vehicleHint, confidence, status), `EcuDetectionPattern` (optionale, in EasyAdmin pflegbare Zusatzmuster).
- Service `src/Service/ToolNews/EcuDetector.php`: rein Regex-basiert, funktioniert auch ganz ohne DB-Patterns. Confidence-Score additiv (0.25 Hersteller / 0.25 ECU-Name / 0.15 Familie / 0.15 MCU / 0.10 Methode / 0.05 Protokoll / 0.05 Fahrzeug-Hinweis, gedeckelt bei 1.0).
- Command `app:tool-news:analyze [--news-id=] [--provider=] [--only-new] [--force]` setzt `ToolNewsItem.status` auf `analyzed`/`no_ecu_found`.
- Command `app:ecu-patterns:seed` legt 21 Standardmuster an (idempotent).
- Dedup pro News: `ecuName+mcu+readMethod+protocol`, Fallback ohne ecuName: `ecuFamily+manufacturer+mcu`.
- Live getestet: 79 importierte News (2026, beide Provider) -> 106 erkannte ECUs, nur 5 ohne Treffer.
- EasyAdmin-Fallstricke, die dabei auftraten (fuer kuenftige CRUDs mit JSON/Bild-Feldern relevant):
  - `TextField`/`TextareaField` werfen `RuntimeException`, wenn der zugrunde liegende Feldwert ein PHP-Array ist (z.B. `rawData`/`matchData`), **auch mit** `onlyOnDetail()` und eigenem `formatValue()`. Fix: generisches `Field::new(...)->formatValue(fn ($v) => new \Twig\Markup(...))` statt `TextField`.
  - `ImageField` verlangt zwingend `setUploadDir()`, sobald es auf einer Formularseite (Edit/New) aktiv ist — fuer reine externe Bild-URLs (kein Upload) stattdessen `UrlField` nutzen.
  - `TextField::setMaxLength()` akzeptiert keine negativen Werte (kein "unendlich"-Wert) — bei `onlyOnDetail()` einfach weglassen, EasyAdmin zeigt auf der Detailseite ohnehin die volle Laenge.

### Schritt 3: Fahrzeugdatenbank-Matching (Stand 2026-07-06, umgesetzt)

- Entities: `VehicleCoverageMatch` (Vorschlag: DetectedEcu -> konkreter VehicleEngine-Treffer, mit Score/Level/Reason/Status, Status-Werte `new/review/confirmed/rejected/ignored/published`), `VehiclePlatformAlias` (Marken-Plattform-Verwandtschaften wie Fiat Ducato <-> Peugeot Boxer, mit `scoreWeight` als Abschlag).
- Migration `migrations/Version20260706220000.php` legt `vehicle_coverage_match` und `vehicle_platform_alias` an. Bestehende Fahrzeugdatenbank-Tabellen bleiben unangetastet (nur lesender Zugriff).
- Service `src/Service/ToolNews/VehicleMatcher.php`: additive Score-Logik analog `EcuDetector` (ECU-Name 0.35, ECU-Familie 0.20, MCU 0.15, Fahrzeug-Hinweis 0.15, Hersteller 0.10, Protokoll/Methode 0.05 als Verstaerker eines bereits vorhandenen ECU-Signals). Level-Schwellen: `exact >= 0.85`, `strong >= 0.70`, `likely >= 0.50`, sonst `possible`. Matches unter 0.35 werden nicht gespeichert. Dedup ueber `detectedEcu + vehicleEngineId`.
  - Kandidatensuche zweistufig, um die 26k+ `VehicleEngine`-Zeilen nicht komplett laden zu muessen: (1) `EcuRepository::findCandidatesByTerms()` sucht per `LIKE` in `Ecu.name` nach ECU-Name/-Familie und navigiert dann ueber `Ecu.getVehicleEngines()` (ManyToMany) sowie die direkte, parallele `Ecu.getVehicleEngine()`-Beziehung (beide Relationen existieren nebeneinander, siehe `Ecu.php`); (2) `VehicleEngineRepository::findActiveCandidatesByBrandTerms()` sucht per Marken-Namen aus einem simplen "erstes Wort"-Parsing von `DetectedEcu.vehicleHint`, plus alle aktiven `VehiclePlatformAlias`-Eintraege fuer diese Marke.
  - `VehicleEngine` hat kein eigenes ECU-Namensfeld und keine MCU/Baujahr-Felder — MCU-Matching laeuft ersatzweise gegen `Ecu.type` (Freitext wie "EDC17C46 TCU"), Baujahr (`yearFrom`/`yearTo` in `VehicleCoverageMatch`) bleibt in dieser Datenbank grundsaetzlich `null`, da es keine Entsprechung in `VehicleEngine`/`VehicleModel` gibt.
- Commands: `app:vehicle-platform-aliases:seed` (16 Standard-Aliase: Sevel-Trio, Renault/Opel/Nissan, Ford/VW, VW/MAN, Mercedes/VW — idempotent), `app:tool-news:match-vehicles [--detected-ecu-id=] [--provider=] [--only-new] [--force]` (analog `ToolNewsAnalyzeCommand`-Stil). Nach Matching: `DetectedEcu.status` wird auf `review` gesetzt bei >=1 Treffer, sonst `new`.
- EasyAdmin: `VehicleCoverageMatchCrudController` (Status-Actions confirm/reject/ignore/publish, Links zu News/DetectedEcu — `publish` schreibt bewusst NICHTS an Konfigurator/API, nur Status-Flag), `VehiclePlatformAliasCrudController`. Menüpunkte "Fahrzeug-Matches"/"Plattform-Aliase" unter ECU Intelligence ergaenzt (`DashboardController` + `AdminPageRegistry`).
- `DetectedEcuCrudController`: neuer Button "Fahrzeuge matchen" (`matchDetectedEcu($ecu, true)`, force=true), Detail-Feld zeigt bestehende Matches. `ToolNewsItemCrudController`: neuer Button "Analyse + Matching starten" (kombiniert EcuDetector + VehicleMatcher in einem Klick), Detail-Feld zeigt Fahrzeug-Matches; die bestehende Analyze-Logik wurde dafuer in eine private `runEcuAnalysis()`-Methode extrahiert (vorher inline in der Route-Methode), beide Actions nutzen sie jetzt.
- Getestet mit echten DetectedEcu-Datensaetzen aus der DB (129 Stueck): `--only-new` erzeugte ~9985 Matches (Level ueberwiegend `possible`, einige `likely`), keine Duplikate, kein Score unter 0.35 — Testdaten bewusst in der DB belassen (siehe Akzeptanzkriterien-Verifikation im Code-Verlauf).
- **Bekannte Einschraenkung der lokalen Test-Datenbank** (nicht code-bedingt): Fiat/Peugeot/Citroen haben in dieser DB-Instanz keine verknuepften Series/Models/Engines — das urspruengliche Ducato/Boxer/Jumper-Beispiel aus der Spec liess sich deshalb nicht end-to-end mit echten Daten zeigen, wurde stattdessen mit BMW (1775 Engines vorhanden) verifiziert.
- Naechster Schritt waere Schritt 4 (Review-/Publishing-Schicht: `confirmed`-Matches als interne Coverage speichern, optional an Konfigurator/API uebergeben, Blog-/Social-Vorschlag) — bewusst noch nicht umgesetzt.

### Light-Theme-Versuch (verworfen)

- Es existiert eine fertige `assets/styles/admin-light-theme.css` im Projekt, war aber nie aktiv eingebunden.
- Versuch, sie anstelle von/zusaetzlich zu `assets/styles/admin-theme.css` zu laden, fuehrte zu einem kaputten Mischzustand (Sidebar verschwindet, Tabellen bleiben dunkel, Titel unlesbar) — die beiden Dateien wurden offenbar nie zusammen getestet und ueberschneiden sich unvorhersehbar.
- Aktuell wieder zurueckgesetzt: `assets/admin.js` importiert nur `admin-theme.css` (dunkles Theme, Ausgangszustand). Ein echtes Light-Theme muesste sauber Seite fuer Seite neu aufgebaut/getestet werden, kein einfacher Datei-Swap.

## Nächste sinnvolle Prüfungen

- Im Portal hart neu laden und prüfen, ob alle 7 VW-Typen sichtbar sind.
- Falls wieder Abweichungen auftreten:
  - Netzwerkantwort von `/vehicle-api/types` prüfen
  - React-Bundle/Cache prüfen
  - kontrollieren, welcher `vehicleDataPool` am Portal hinterlegt ist
- Im Superadmin prüfen, ob der Output-Import bei großen Typgruppen performant genug bleibt.
- Bei Bedarf ergänzen:
  - globale `Alle Typen auswählen`-Aktion
  - Filter/Suche innerhalb der Output-Tabellen
  - dedizierte Kennzeichnung bereits importierter Marken-Dateien
