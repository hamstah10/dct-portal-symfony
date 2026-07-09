# Template-Uebersicht der einzelnen Apps

Stand: 24.03.2026

Diese Datei beschreibt die Template-Struktur der einzelnen App-Bereiche im Projekt. Gemeint sind hier die unterschiedlichen UI-Systeme innerhalb der Symfony-Anwendung, also insbesondere Admin, oeffentliche Website, markenspezifische Website und Kundenportal.

## 1. Gemeinsame Basis

### `templates/base.html.twig`

Zentrale Basis fuer die nicht-EasyAdmin-basierten Bereiche.

- Stellt das globale HTML-Grundgeruest bereit
- Bindet Bootstrap, Importmap und globale JS/CSS-Bloecke ein
- Initialisiert den Symfony-UX-Notify-Controller fuer Mercure/Realtime-Themen
- Definiert die zentralen Twig-Blocks:
  - `html_attributes`
  - `stylesheets`
  - `javascripts`
  - `body_attributes`
  - `body`

Diese Basis wird direkt oder indirekt verwendet von:

- `templates/security/login.html.twig`
- `templates/website/*.html.twig`
- `templates/website/chiptuningfile/*.html.twig`
- `templates/portal/chiptuningfile/*.html.twig`

## 2. Admin-App

Die Admin-App ist das Backoffice unter `/admin` und basiert hauptsaechlich auf EasyAdmin.

### Layout und Overrides

#### `templates/bundles/EasyAdminBundle/layout.html.twig`

- globaler EasyAdmin-Override
- erweitert das Standardlayout `@!EasyAdmin/layout.html.twig`
- der richtige Ort fuer projektweite EasyAdmin-Anpassungen

#### `templates/admin/layout.html.twig`

- aktuell leer bzw. ohne eigene Implementierung
- momentan kein produktiv genutztes zentrales Admin-Basislayout

#### `templates/admin/embedded/layout.html.twig`

- vorgesehen fuer eingebettete Admin-Ansichten
- relevant fuer Spezialfaelle, in denen Inhalte nicht voll im Standard-Backend laufen sollen

### Typische Admin-Template-Arten

#### Dashboard- und Seiten-Templates

Diese Templates erweitern in der Regel `@EasyAdmin/page/content.html.twig` und bilden eigenstaendige Admin-Seiten:

- `templates/admin/dashboard.html.twig`
- `templates/admin/communication.html.twig`
- `templates/admin/project_board.html.twig`
- `templates/admin/media_manager.html.twig`
- `templates/admin/vehicle_database_overview.html.twig`
- `templates/admin/vehicle_tools.html.twig`
- `templates/admin/vehicle_ecu.html.twig`
- `templates/admin/vehicle_gearbox.html.twig`
- `templates/admin/vehicle_usage_statistics.html.twig`
- `templates/admin/scraper_control/index.html.twig`
- `templates/admin/scraper_import/index.html.twig`
- `templates/admin/scraper_import/output.html.twig`
- `templates/admin/abo_statistics.html.twig`
- `templates/admin/credits_statistics.html.twig`
- `templates/admin/revenue_statistics.html.twig`
- `templates/admin/ecommerce_statistics.html.twig`
- `templates/admin/statistics/index.html.twig`

Einsatz:

- KPI-Ansichten
- Dashboards
- Auswertungen
- Spezialtools ausserhalb des normalen CRUD-Flows

Speziell im Scraper-Bereich:

- `templates/admin/scraper_control/index.html.twig`
  - Starten und Ueberwachen von Scraper-Laeufen
  - Anzeige der Laufhistorie und erzeugter JSON-Dateien
- `templates/admin/scraper_import/index.html.twig`
  - klassischer Import fuer zusammengefasste Root-Dateien wie `olsx_brands_car_20260416_024939.json`
  - Vorschau fuer Marken/Pool-Mapping und Importhistorie fuer diesen Importpfad
- `templates/admin/scraper_import/output.html.twig`
  - Importoberflaeche fuer Einzeldateien aus `scrapy-remote-scraper/data/output/<type>/`
  - Tabellen pro Typ (`car`, `moto`, `atv`, usw.) mit Checkbox je Datei und `Alle`-Checkbox pro Tabelle
  - Mehrfachauswahl, Sammelvorschau und Mehrfachimport in einen Datenpool

#### CRUD-Erweiterungen

Diese Templates erweitern EasyAdmin-CRUD-Views wie `index`, `edit` oder `detail`:

- `templates/admin/abo/index.html.twig`
- `templates/admin/auftrag/index.html.twig`
- `templates/admin/auftrag/edit.html.twig`
- `templates/admin/credits/history_index.html.twig`
- `templates/admin/ecu/index.html.twig`
- `templates/admin/kunde/index.html.twig`
- `templates/admin/kunde/detail.html.twig`
- `templates/admin/profit_calculator/options_index.html.twig`
- `templates/admin/shop_order/index.html.twig`
- `templates/admin/shop_product/index.html.twig`
- `templates/admin/shop_product/detail.html.twig`
- `templates/admin/vehicle_engine/index.html.twig`
- `templates/admin/vehicle_model/index.html.twig`
- `templates/admin/vehicle_series/index.html.twig`
- `templates/admin/vehiclestage/index.html.twig`
- `templates/admin/vehiclestage/edit.html.twig`
- `templates/admin/website_sniffer/index.html.twig`
- `templates/admin/media/asset_detail.html.twig`
- `templates/admin/dyno/trip_detail.html.twig`
- `templates/admin/dyno/sprint_detail.html.twig`

Einsatz:

- Listenansichten mit projektspezifischen Spalten und Widgets
- Formularerweiterungen
- Detailansichten fuer Spezialdaten

#### Fachmodule im Admin

Diese Verzeichnisse oder Dateien repraesentieren eigenstaendige Admin-Apps bzw. Toolbereiche:

- `templates/admin/ai_agent/index.html.twig`
  - Agent-Oberflaeche
- `templates/admin/ai_chat/index.html.twig`
  - AI-Chat
- `templates/admin/ai_dalle/index.html.twig`
  - Bildgenerierung
- `templates/admin/ai_firecrawl/index.html.twig`
  - Firecrawl-Laeufe
- `templates/admin/ai_image_analysis/index.html.twig`
  - Bildanalyse
- `templates/admin/ai_pdf_analysis/index.html.twig`
  - PDF-Analyse
- `templates/admin/ai_project/workspace.html.twig`
  - AI-Projekt-Workspace
- `templates/admin/ai_vehicle/index.html.twig`
  - KI-bezogene Fahrzeugfunktionen
- `templates/admin/dyno/stats.html.twig`
  - Dyno-Statistik
- `templates/admin/instagram/*.html.twig`
  - Instagram-Verwaltung, Profil, Medien, Publishing
- `templates/admin/lexware/invoice.html.twig`
  - Lexware-/Lexoffice-Rechnungsoberflaeche
- `templates/admin/support/tickets.html.twig`
  - Ticketsystem
- `templates/admin/support/email.html.twig`
  - Support-Mail-App
- `templates/admin/support/newsletter.html.twig`
  - Newsletter-Sync und Uebersicht
- `templates/admin/support/chats.html.twig`
  - Chat-Bereich, aktuell eher als Platzhalter/Deaktivierungsansicht
- `templates/admin/test_actions/index.html.twig`
  - Test- und Simulationsaktionen
- `templates/admin/vin_decoder/index.html.twig`
  - VIN-Decoder
- `templates/admin/vpic_explorer/index.html.twig`
  - vPIC-Explorer

### Statistik-Teiltemplates

#### `templates/admin/statistics/*.html.twig`

Teiltemplates fuer zusammengesetzte Statistikseiten:

- `_abo.html.twig`
- `_credits.html.twig`
- `_dyno.html.twig`
- `_ecommerce.html.twig`
- `_revenue.html.twig`
- `_vehicle_usage.html.twig`

Diese Dateien kapseln Teilbereiche einer groesseren Statistik-Oberflaeche.

### Partials im Admin

#### `templates/admin/partials/*.html.twig`

- `_media_folder_tree_node.html.twig`
  - rekursive Ordnerdarstellung im Media Manager
- `sidebar.html.twig`
  - benutzerdefinierte Sidebar-Bestandteile
- `topbar.html.twig`
  - benutzerdefinierte Topbar-Bestandteile

#### `templates/admin/support/_embed_base.html.twig`

- Basislayout fuer eingebettete Support-Ansichten
- wird von Tickets und E-Mail verwendet, wenn die Seite im Embed-Modus laeuft

## 3. Oeffentliche Standard-Website-App

Diese Templates bilden die generischen, nicht markenspezifischen Frontend-Seiten der Hauptanwendung.

### `templates/website/home.html.twig`

- allgemeine Startseite fuer `/`
- wird von `src/Controller/HomeController.php` gerendert

### `templates/website/vehicle_database.html.twig`

- Frontend der Fahrzeugdatenbank unter `/chiptuning`
- wird auch fuer `/chiptuning/api-test` wiederverwendet
- kombiniert UI und API-Anbindung fuer die kaskadierende Fahrzeugauswahl

### `templates/website/profit_calculator.html.twig`

- Frontend fuer den Profit Calculator unter `/chiptuning/profitcalculator`
- arbeitet mit konfigurierbaren Fahrzeugtypen, Optionen und Preisstaffeln

### `templates/website/eco_tuning_calculator.html.twig`

- Frontend fuer den Eco-Tuning-Rechner unter `/chiptuning/eco-tuning-calculator`
- nutzt zusaetzlich einen JSON-Endpunkt fuer Kraftstoffpreise

## 4. Chiptuningfile Website-App

Das ist die oeffentliche markenspezifische Website fuer Chiptuningfile.

### `templates/website/chiptuningfile/base.html.twig`

Basislayout der Chiptuningfile-Website.

- erweitert `templates/base.html.twig`
- setzt Branding-Farben, Header, Footer und CTA-Struktur
- bindet `portalUrl` und `portalConfig` ein
- stellt den Block `website_body` fuer konkrete Seiten bereit

### `templates/website/chiptuningfile/home/index.html.twig`

- Twig-Startseite der Chiptuningfile-Website
- erweitert `website/chiptuningfile/base.html.twig`

Hinweis:

- parallel dazu existiert ein statischer HTML-Renderer in `src/Websites/Chiptuningfile/Service/StaticWebsiteRenderer.php`
- die Routen aus `src/Websites/Chiptuningfile/Controller/HomeController.php` liefern Seiten wie `index`, `faq`, `kontakt`, `services` oder `ueber-uns` aus statischen HTML-Dateien unter `public/assets/portals/chiptuningfile/website/`
- fuer diese statischen Seiten existieren daher nicht zwingend gleichnamige Twig-Templates

### Legal-Bereich

#### `templates/website/chiptuningfile/legal/_base.html.twig`

- gemeinsame Basis fuer Impressum und Datenschutz
- erweitert `templates/base.html.twig`

#### `templates/website/chiptuningfile/legal/impressum.html.twig`

- Impressumsseite
- wird von `src/Websites/Chiptuningfile/Controller/LegalController.php` gerendert

#### `templates/website/chiptuningfile/legal/datenschutz.html.twig`

- Datenschutzseite
- wird ebenfalls vom LegalController gerendert

## 5. Chiptuningfile Portal-App

Das ist das eingeloggte Kundenportal unter der Portal-/Brand-Struktur.

### `templates/portal/chiptuningfile/base.html.twig`

Zentrales Portal-Layout.

- erweitert `templates/base.html.twig`
- setzt das komplette Portal-Skinning
- bindet die Xintra-Assets und das Portal-CSS ein
- steuert Theme, Navigation, Header-Interaktion und Portal-spezifische JavaScript-Initialisierung
- differenziert zwischen oeffentlichen Portal-Seiten und eingeloggten Bereichen

Alle folgenden Portal-Seiten bauen darauf auf.

### Dashboard und Arbeitsbereiche

#### `templates/portal/chiptuningfile/dashboard/index.html.twig`

- Startseite des Kundenportals
- wird von `src/Portals/Chiptuningfile/Controller/PortalDashboardController.php` gerendert
- zeigt Dashboard-Daten, die durch `PortalDashboardBuilder` aufgebaut werden

#### `templates/portal/chiptuningfile/upload/index.html.twig`

- Upload-Bereich fuer Kundendateien
- wird von `src/Portals/Chiptuningfile/Controller/FileUploadController.php` gerendert
- nutzt ein Symfony-Formular plus Liste juengster Upload-Jobs

#### `templates/portal/chiptuningfile/credits/index.html.twig`

- Credit-Ansicht des Portals
- wird von `src/Portals/Chiptuningfile/Controller/CreditsController.php` gerendert
- kombiniert Paketkarten, Preiskalkulation, Transaktionshistorie und PayPal-Flow

#### `templates/portal/chiptuningfile/order/index.html.twig`

- mehrstufiger Auftrags-Wizard
- wird von `src/Portals/Chiptuningfile/Controller/OrderController.php` gerendert
- aktuell stark als UI- und Ablauf-Dummy vorbereitet, aber mit klarer Step-Struktur

#### `templates/portal/chiptuningfile/vehicle/index.html.twig`

- Portalansicht fuer fahrzeugbezogene Inhalte
- wird von `src/Portals/Chiptuningfile/Controller/VehicleController.php` gerendert

#### `templates/portal/chiptuningfile/profile/edit.html.twig`

- Profilbearbeitung des Portal-Kunden
- wird von `src/Portals/Chiptuningfile/Controller/ProfileController.php` gerendert

### Security-Seiten des Portals

#### `templates/portal/chiptuningfile/security/login.html.twig`

- Portal-Login

#### `templates/portal/chiptuningfile/security/register.html.twig`

- Registrierung neuer Portal-Kunden

#### `templates/portal/chiptuningfile/security/password_reset_request.html.twig`

- Anforderung des Passwort-Resets

#### `templates/portal/chiptuningfile/security/password_reset_confirm.html.twig`

- Vergabe eines neuen Passworts nach Reset

Alle vier Templates werden ueber `src/Portals/Chiptuningfile/Controller/PortalSecurityController.php` gesteuert.

## 6. Shared Components und Hilfstemplates

### `templates/components/*.html.twig`

Wiederverwendbare Twig Components bzw. Component-Templates fuer Suchfelder und UI-Bausteine.

Wichtige Beispiele:

- `app_media_slider.html.twig`
- `auftrag_search.html.twig`
- `credit_history_search.html.twig`
- `ecu_search.html.twig`
- `kunde_search.html.twig`
- `portal_header_search.html.twig`
- `shop_order_search.html.twig`
- `support_email_search.html.twig`
- `support_ticket_search.html.twig`
- `vehicle_engine_search.html.twig`
- `vehicle_model_search.html.twig`
- `vehicle_search.html.twig`
- `vehicle_series_search.html.twig`
- `vehicle_stage_search.html.twig`

Sie werden typischerweise in Such-, Filter- oder Auswahloberflaechen eingebunden.

### `templates/form/status_buttons.html.twig`

- Formular-Hilfstemplate fuer Status-/Buttondarstellungen

### `templates/brand/common/portal_unavailable.html.twig`

- Fallback-/Fehleransicht fuer Portal- oder Brand-Szenarien, in denen ein Portal nicht verfuegbar ist

## 7. Welche App nutzt welches Layout?

Kurzzuordnung der wichtigsten Bereiche:

- Standard-Website:
  - `templates/base.html.twig`
- Chiptuningfile-Website:
  - `templates/base.html.twig`
  - `templates/website/chiptuningfile/base.html.twig`
- Chiptuningfile-Portal:
  - `templates/base.html.twig`
  - `templates/portal/chiptuningfile/base.html.twig`
- Admin:
  - EasyAdmin-Layouts
  - optional Projekt-Overrides in `templates/bundles/EasyAdminBundle/`

## 8. Pflegeempfehlung

Wenn neue Apps oder UI-Bereiche dazukommen, sollte diese Datei entlang derselben Struktur erweitert werden:

- Basislayout
- Hauptseiten
- Security-/Spezialseiten
- wiederverwendbare Partials oder Components
- zugehoerige Controller oder Renderpfade

So bleibt nachvollziehbar, welches Template-System fuer welchen Bereich verantwortlich ist.
