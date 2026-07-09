# TFD CRM

Stand: 04.07.2026

TFD CRM ist eine Symfony-8-Anwendung fuer das interne Verwaltungs-, Support-, Portal- und Vertriebsumfeld von Tuningfux. Das Projekt vereint Admin-Backoffice, Kunden- und Auftragsverwaltung, Fahrzeugdatenbank, Support-Kommunikation, Credits, Shop-, API-, AI- und Portal-Funktionen in einer gemeinsamen Codebasis.

## Kurzueberblick

- Framework: Symfony 8.0
- Sprache/Runtime: PHP 8.4+
- Persistence: Doctrine ORM + Doctrine Migrations
- Admin-UI: EasyAdmin
- Frontend: Twig + Symfony UX + AssetMapper/Importmap
- Realtime: Mercure/SSE
- API: Attribut-basierte Symfony-Controller unter `/api`
- Lokales Paket: `tuningfux/instagram-bundle` als Path-Repository

## Einstiegsrouten

- `/login`: Login fuer das Backoffice
- `/admin`: EasyAdmin-Backend fuer Verwaltung und operative Workflows
- `/chiptuning`: Fahrzeugdatenbank / Frontend
- `/widget/{embedKey}`: einbettbares Website Widget fuer Kunden-Websites
- `/kunden-widget/login`: Login fuer Kunden zur Widget-Verwaltung
- `/kunden-widget`: Kunden-Portal fuer die Verwaltung des eigenen Website Widgets
- `/chiptuning/profitcalculator`: Profit Calculator
- `/chiptuning/eco-tuning-calculator`: Eco-Tuning-Rechner
- `/api/...`: externe und interne API-Endpunkte

## Funktionsbereiche

### Backoffice und Verwaltung

- Dashboard mit Kennzahlen, Charts und operativen Uebersichten
- Benutzer, Benutzergruppen und seitenbasierte Admin-Berechtigungen
- Kunden, Firmen, Software, Abos, Auftraege und Kunden-Credits
- Medienverwaltung mit Ordnern, Uploads, Cropping und Dateiorganisation

### Support und Kommunikation

- Ticketsystem mit Status, Prioritaeten, Nachrichten und Anhaengen
- Support-E-Mail mit IMAP/SMTP-Konten, Labeln und Abruf-/Versandlogik
- Newsletter-Anbindung und Listen-Synchronisation
- Realtime-Benachrichtigungen fuer neue Auftraege

### Fahrzeug, Tuning und Daten

- Fahrzeugdatenbank fuer Typen, Marken, Baureihen, Modelle, Motoren und Stages
- Website Widget fuer kundenspezifische Einbettungen der Fahrzeugdatenbank inkl. serverseitigem API-Key-Proxy, Datenpool-Auswahl, Anfrageformular und Widget-only Themes `Premium Dunkel` (`widget_premium_dark`), `Premium Hell` (`widget_premium_light`), `V5 Tabelle` (`layout5`) und `V6 Sidebar Hell` (`layout6`)
- Kunden-Portal unter `/kunden-widget` mit eigenem Login und eigener Firewall: Kunden verwalten ihr Widget selbst, ohne CRM-Admin-Zugang
- Scraper-Steuerung im Superadmin mit Hintergrundjobs, JSON-Laufhistorie und Importstrecken fuer OLSX-Daten
- Zwei getrennte Scraper-Importseiten im Superadmin:
  - klassischer Import fuer Root-Dateien wie `scrapy-remote-scraper/olsx_brands_<type>_*.json`
  - Output-Import fuer Einzeldateien unter `scrapy-remote-scraper/data/output/<type>/*.json`
- Output-Import mit Tabellen pro Typ und Checkbox-Auswahl pro Datei inklusive `Alle`-Auswahl je Tabelle
- ECU Intelligence: eigenstaendiger Scraper-/Analyse-Zweig fuer Tool-Anbieter-News (Autotuner, MagicMotorsport), getrennt vom OLSX-Fahrzeugdaten-Scraper
  - Verwaltung von Tool-Anbietern, News-Quellen und importierten Anbieter-News im EasyAdmin
  - Automatische Erkennung von ECU-Hersteller, ECU-Familie/-Name, MCU, Lese-/Schreibmethode, Protokoll und Fahrzeug-Hinweis aus dem News-Text inkl. Konfidenz-Wert
  - Erkannte ECUs mit Status Neu/Bestaetigt/Abgelehnt/Ignoriert, eigene pflegbare Erkennungsmuster
- VIN-Decoder und vPIC-Explorer
- Dyno-/Telemetrie-Module mit CRM- und API-Endpunkten
- DTC-Kataloge und Import-Kommandos

### Vertrieb, Portale und Commerce

- Profit Calculator und zugehoerige Stammdaten
- Shop-/eCommerce-Module fuer Produkte, Kategorien, Bestellungen und Steuern
- Portale und chiptuningfile-spezifische Portal-Funktionen
- Website-Sniffer fuer Crawl-/Parse-Laeufe und Seitenauswertung

### AI und Integrationen

- AI-Module fuer Chat, Agenten, Bild-/PDF-Analyse und Firecrawl-Workflows
- OpenAI-, Firecrawl-, SerpAPI- und YouTube-Tooling ueber Symfony AI
- Lexware/Lexoffice-Anbindung
- Brevo-Integration
- Instagram-Integration ueber lokales Bundle
- Webhook- und API-Token-Funktionen

## Technik und Architektur

- Klassische Symfony-Monolith-Architektur mit klar getrennten Bereichen in `src/Entity`, `src/Repository`, `src/Service` und `src/Controller`
- EasyAdmin-CRUD-Controller liegen in `src/Controller/Admin`
- Attribut-Routing fuer Controller in `src/Controller`
- Zusaetzliche Marken-/Portalstruktur unter `src/Brand`, `src/Portals` und `src/Websites`
- Twig-Templates in `templates/`, Admin-Templates schwerpunktmaessig unter `templates/admin`
- Datenbankschema und Updates ueber `migrations/`

## Projektstruktur

```text
src/
  Controller/
    Admin/
    Api/
  Entity/
  Repository/
  Service/
  Portals/
  Websites/
  Command/
config/
templates/
public/
migrations/
assets/
docs/
tuningfux-instagram-bundle/
```

## Lokale Entwicklung

### Voraussetzungen

- PHP 8.4+
- Composer
- MySQL oder PostgreSQL
- Optional Docker fuer Infrastruktur-Dienste

### Installation

```bash
composer install
php bin/console cache:clear
php bin/console doctrine:migrations:migrate
```

### Konfiguration

- Die Anwendung nutzt Umgebungsvariablen aus `.env`, `.env.local` und optional weiteren env-Dateien.
- `DATABASE_URL` muss zur lokal verwendeten Datenbank passen.
- Das Projekt ist mit Doctrine grundsaetzlich fuer MySQL und PostgreSQL aufgestellt.
- Externe Integrationen wie Mercure, Mailer, Lexware, Brevo, OpenAI, Firecrawl, Instagram oder PayPal werden ebenfalls ueber Env-Variablen konfiguriert.
- Zugangsdaten gehoeren nicht in die README und sollten ausschliesslich lokal bzw. im Deployment hinterlegt werden.

### Infrastruktur via Docker Compose

Im Repository liegt ein `compose.yaml`, das derzeit Hilfsdienste fuer:

- PostgreSQL
- Mercure

bereitstellt.

Starten:

```bash
docker compose up -d
```

Hinweis: In vielen lokalen Setups laeuft die eigentliche Symfony-Anwendung ueber einen bestehenden VirtualHost/Webserver mit `public/` als Document Root.

## Nuetzliche Befehle

```bash
php bin/console --env=test
composer test
php bin/console doctrine:migrations:migrate
php bin/console make:entity
php bin/console debug:router
php bin/console cache:clear
```

Weitere projektbezogene Commands in `src/Command/`:

- `app:cache:update`
- `app:credit:seed`
- `app:firecrawl:run`
- `app:import:dtc-catalog`
- `app:import:dtc-json`
- `app:import:shop-catalog`
- `app:support-email:fetch`
- `app:website-sniffer:run`
- `app:tool-news:run {provider} [--year=]` – startet den Tool-News-Scraper (Autotuner/MagicMotorsport)
- `app:tool-news:import {file}` – importiert eine gescrapte JSON-Datei nach `ToolNewsItem`
- `app:tool-news:analyze [--news-id=] [--provider=] [--only-new] [--force]` – erkennt ECUs in Anbieter-News
- `app:ecu-patterns:seed` – legt Standard-Erkennungsmuster fuer die ECU-Erkennung an

## Routing und Zugriffsmodell

- Der Standard-Routenimport fuer `src/Controller` ist an das Brand-/System-Merkmal `superadmin` gekoppelt.
- Das Backoffice ist ueber `/admin` organisiert.
- Relevante Superadmin-Routen fuer den Scraperbereich:
  - `/admin/fahrzeugdatenbank/scraper`
  - `/admin/fahrzeugdatenbank/scraper/import`
  - `/admin/fahrzeugdatenbank/scraper/import-output`
- Kunden-Widget-Routen:
  - `/kunden-widget/login`: Login fuer Kunden (Firewall `customer_widget`)
  - `/kunden-widget`: Bearbeitung des eigenen Widgets durch den eingeloggten Kunden
  - `/kunden-widget/logout`
- Rollen, Benutzerstatus und seitenbezogene Rechte werden im Projekt zusaetzlich ueber eigene Security-/Access-Mechanismen abgesichert.

## Testing-Status

- Aktuell ist keine separate `tests/`-Suite im Repository vorhanden.
- `composer test` ist als Referenzkommando dokumentiert, sollte aber nur verwendet werden, wenn im jeweiligen Stand des Projekts ein passendes Test-Script hinterlegt ist.
- Fuer technische Verifikation werden daher haeufig `php bin/console`, Migrations, manuelle UI-Pruefung und gezielte Funktionschecks genutzt.

## Relevante Doku im Repository

- `docs/`: Zusatzdokumentation und API-bezogene Unterlagen
- `docs/system-uebersicht-portale-admins.md`: Uebersicht ueber EasyAdmin-Backoffice, Superadmin-Portal und das Chiptuningfile-System mit Portal plus Website `cpt.tuningfux.de`
- `docs/system-datenkontexte-pools.md`: Zuordnung, welches System direkt Pools nutzt und wo `VehicleDataContext` greift
- `docs/website-widget.md`: technische und fachliche Doku zum einbettbaren Fahrzeugdatenbank-Widget inkl. Kunden-Portal
- `docs/website-widget-kunden-anleitung.md`: Anleitung fuer Kunden zur Nutzung des Kunden-Portals
- `README.html`: HTML-Variante bzw. exportierte Projektbeschreibung
- `TODO.md`: offene Punkte / Notizen
- `tuningfux-instagram-bundle/README.md`: Doku des lokalen Instagram-Bundles

## Entwicklungsrichtlinien in Kurzform

- PHP 8.4+, konsequente Type Hints
- Doctrine-Mapping ueber PHP-Attribute
- Setter in Entities geben `static` zurueck
- EasyAdmin-CRUDs auf Basis von `AbstractCrudController`
- 4 Leerzeichen Einrueckung, LF, UTF-8 gemaess `.editorconfig`

## Pflegehinweis

Diese README beschreibt bewusst den aktuellen technischen und fachlichen Rahmen statt jede Einzelfunktion bis ins Detail aufzulisten. Wenn neue Module, Commands, Integrationen oder Betriebsanforderungen hinzukommen, sollte die README entlang der Abschnitte "Funktionsbereiche", "Lokale Entwicklung" und "Nuetzliche Befehle" aktualisiert werden.
