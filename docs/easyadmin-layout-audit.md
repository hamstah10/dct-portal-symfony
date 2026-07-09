# EasyAdmin Layout Audit

## Ziel dieses Audits

Dieses Dokument beschreibt den aktuellen Stand der EasyAdmin-Layoutintegration vor dem Umbau auf eine neue, saubere Admin-Shell auf Basis von `templates/easyadmin/Xintra_with_esbuild`.

Phase 1 betrifft nur:

- Layout
- Struktur
- Integration

Nicht Teil dieser Phase:

- CRUD-Logik
- inhaltliche Seitenumbauten
- Detail- oder Listen-Redesigns pro Seite

## Aktuelle EasyAdmin-Layoutkette

Der Admin verwendet derzeit mehrere parallele Override-Ebenen:

1. `DashboardController`
2. `templates/bundles/EasyAdminBundle/layout.html.twig`
3. `templates/admin/layout.html.twig`

Das erzeugt im Ist-Zustand keine klare, einzige Shell-Quelle.

## DashboardController

Relevante Datei:

- `src/Controller/Admin/DashboardController.php`

Aktueller Stand:

- `configureDashboard()` verwendet `renderContentMaximized()`
- das Dashboard setzt Light-Mode über `disableDarkMode()` und `setDefaultColorScheme(ColorScheme::LIGHT)`
- `configureCrud()` überschreibt global das EasyAdmin-Layout mit:
  - `->overrideTemplate('layout', 'admin/layout.html.twig')`
- `configureAssets()` lädt:
  - externe Select2-CSS
  - jQuery CDN
  - CKEditor CDN
  - Select2 CDN JS
  - `css/admin-toolbar.css`
  - AssetMapper-Entrypoint `admin`

Bewertung:

- Der Controller greift bereits global in die Layoutauswahl ein.
- Für eine saubere Shell darf es später nicht gleichzeitig einen konkurrierenden Bundle-Override und einen separaten Controller-Layoutpfad geben.

## Vorhandene EasyAdmin-Overrides

Relevante Dateien:

- `templates/bundles/EasyAdminBundle/layout.html.twig`
- `templates/bundles/EasyAdminBundle/crud/index.html.twig`
- `templates/bundles/EasyAdminBundle/label/null.html.twig`

### `templates/bundles/EasyAdminBundle/layout.html.twig`

Aktueller Stand:

- erweitert `@!EasyAdmin/layout.html.twig`
- baut im Block `content_top_header` zusätzliche Toolbar-Buttons ein
- lässt Suchbereich und Teile des EasyAdmin-Headers bestehen
- enthält umfangreiches benutzerdefiniertes JavaScript für:
  - Flash-Auto-Hide
  - Toast-Container
  - Mercure-/Notify-Handling
  - Navigation-Badges

Bewertung:

- Diese Datei ist aktuell der stärkste globale Override.
- Sie enthält nicht nur Layout, sondern auch projektkritische Header-/Notify-Logik.
- Sie kann nicht einfach gelöscht werden, ohne vorhandene Admin-Funktionen zu verlieren.

### `templates/bundles/EasyAdminBundle/crud/index.html.twig`

Aktueller Stand:

- gemeinsamer Override für Listenansichten
- ergänzt strukturierende Wrapper um Tabellen- und Paginator-Bereiche

Bewertung:

- Diese Datei betrifft Seiteninhalt innerhalb des Layouts, nicht die App-Shell selbst.
- Für Phase 1 muss sie nur weiterhin innerhalb der neuen Shell rendern.

## Aktuelles projektinternes Admin-Layout

Relevante Datei:

- `templates/admin/layout.html.twig`

Aktueller Stand:

- erweitert `@EasyAdmin/layout.html.twig`
- ergänzt nur:
  - `body_class`
  - `content_top_header`
  - `content_header`
- keine vollständige Shell mit eigener Sidebar, Topbar und Main-Container

Bewertung:

- `templates/admin/layout.html.twig` ist derzeit kein echtes Basislayout.
- Es ist eher ein Header-Wrapper.
- Für die Zielarchitektur muss diese Rolle in ein echtes `templates/admin/layout/base.html.twig` überführt werden.

## Vorhandene Admin-Teiltemplates

Relevante Dateien:

- `templates/admin/partials/sidebar.html.twig`
- `templates/admin/partials/topbar.html.twig`

Aktueller Stand:

- beide Dateien sind sehr einfach und statisch
- sie verwenden nicht die echte EasyAdmin-Menüstruktur
- sie sind nicht die derzeitige zentrale Shell

Bewertung:

- Diese Dateien sind keine belastbare Grundlage für die neue Integration.
- Sie sind eher Altbestand oder frühe Prototypen.

## Vorhandene seitenbezogene Admin-Overrides

Unter `templates/admin/` gibt es viele seiten- oder CRUD-spezifische Overrides, unter anderem für:

- Dashboard
- Auftrag
- Kunde
- Shop Order
- Shop Product
- ECU
- Vehicle Model / Series / Engine
- diverse Statistik- und Supportseiten

Zusätzlich setzen viele CRUD-Controller eigene Template-Overrides, z.B. für:

- `crud/index`
- `crud/detail`
- `crud/edit`

Bewertung:

- Diese Seiten dürfen in Phase 1 inhaltlich nicht neu gestaltet werden.
- Die neue Shell muss sie nur in einen gemeinsamen App-Frame einbetten.

## Asset-System des Projekts

Relevante Dateien:

- `importmap.php`
- `assets/admin.js`
- `composer.json`

Aktueller Stand:

- Das Projekt nutzt Symfony AssetMapper und Importmap
- `importmap.php` definiert:
  - `app`
  - `admin`
- `admin` zeigt auf:
  - `./assets/admin.js`
- `assets/admin.js` lädt aktuell:
  - `./stimulus_bootstrap.js`
  - `@hotwired/turbo`
  - `bootstrap`
  - DataTables
  - `./styles/admin-theme.css`
  - `./js/admin-theme.js`

Bewertung:

- Das reale Admin-Frontend läuft über AssetMapper, nicht über Xintras eigenes Build-System.
- Jede Xintra-Integration muss sich an dieses Modell anpassen.

## Struktur von Xintra

Relevante Quelle:

- `templates/easyadmin/Xintra_with_esbuild/dist/html/index.html`

Xintra liefert ein statisches Admin-Shell-Muster mit dieser Grundstruktur:

- `div.page`
- `header.app-header`
- `aside.app-sidebar`
- `div.main-content.app-content`
- `div.container-fluid`

Im `head` werden unter anderem eingebunden:

- Bootstrap CSS
- `styles.css`
- `icons.css`
- mehrere JS-/CSS-Libs

Die Topbar enthält strukturell:

- Logo / Sidebar-Toggle
- Suchfeld
- rechte Utility-Zone
- User-Menü

Die Sidebar enthält strukturell:

- Logo-Bereich
- scrollbaren Menübereich
- Kategorien
- verschachtelte Navigation

Bewertung:

- Xintra ist als Shell-Vorlage geeignet.
- Xintra ist nicht als 1:1-Ersatzseite geeignet.
- Demo-Inhalte aus Header und Sidebar dürfen nicht direkt übernommen werden.
- Übernommen werden sollten nur:
  - Shell-Struktur
  - Containerlogik
  - Sidebar-Grundmarkup
  - Topbar-Grundmarkup

## Xintra-Buildsystem

Relevante Dateien:

- `templates/easyadmin/Xintra_with_esbuild/esbuild.config.js`
- `templates/easyadmin/Xintra_with_esbuild/package.json`

Aktueller Stand:

- Xintra bringt ein eigenes Node/esbuild-Projekt mit
- dieses kompiliert eigene SCSS-/CSS-Dateien und kopiert Demo-Assets nach `dist`
- es ist nicht in Symfony AssetMapper integriert

Bewertung:

- Für dieses Projekt ist Xintras Buildsystem aktuell nur Referenzmaterial.
- In Phase 1 sollte kein zweites paralleles Frontend-System aufgebaut werden.
- Die Integration sollte auf dem bestehenden Symfony-Entrypoint `admin` aufsetzen.

## Technische Hauptrisiken

### 1. Doppelte Layoutsteuerung

Aktuell existieren mindestens zwei globale Layoutpfade:

- Bundle-Override `templates/bundles/EasyAdminBundle/layout.html.twig`
- Controller-Override auf `templates/admin/layout.html.twig`

Risiko:

- unklare Priorität
- doppelte Headerlogik
- erschwerte Fehlersuche

### 2. Vermischung von Layout und Verhaltenslogik

Das aktuelle Bundle-Layout enthält:

- Headerstruktur
- Quick Actions
- Flash-Verhalten
- Toast-/Notification-Handling

Risiko:

- ein reiner Layoutaustausch kann bestehende Funktionalität versehentlich entfernen

### 3. Xintra ist Demo-lastig

Header und Sidebar im Theme enthalten viel Beispielinhalt, der nicht zum Projekt passt.

Risiko:

- doppelte Menüs
- tote Links
- kaputte Bedienlogik

### 4. Zwei Asset-Welten

Projekt:

- Symfony AssetMapper

Xintra:

- eigenes esbuild-Projekt

Risiko:

- doppelte Bootstrap-/JS-Einbindung
- Konflikte bei Layoutskripten
- unklare Build-Verantwortung

## Sichere Integrationsstrategie für Phase 1

### Zielstruktur

Es soll später diese Struktur entstehen:

- `templates/admin/layout/base.html.twig`
- `templates/bundles/EasyAdminBundle/layout.html.twig`

### Rolle von `base.html.twig`

Die neue Datei `templates/admin/layout/base.html.twig` soll:

- die App-Shell bereitstellen
- Sidebar-Slot definieren
- Topbar-Slot definieren
- Main-Wrapper definieren
- Page-Container definieren
- Content-Slot definieren

Sie soll keine CRUD-spezifische Logik enthalten.

### Rolle des EasyAdmin-Layout-Overrides

`templates/bundles/EasyAdminBundle/layout.html.twig` soll später:

- das neue Basislayout erweitern
- EasyAdmin-Inhalt in den Content-Slot injizieren
- EasyAdmin-Menü in die Sidebar einspeisen
- Suche, User-Menü und Flash-Messages erhalten
- bestehende notwendige Header-/Notification-Logik weiterführen

### Sidebar-Strategie

Die Sidebar muss auf der echten EasyAdmin-Navigation basieren:

- aktive Zustände
- Icons
- Gruppen
- Unterpunkte

Nicht verwenden:

- statische Alt-Partial-Datei
- Xintra-Demo-Menü 1:1

### Topbar-Strategie

Die Topbar soll strukturell an Xintra angelehnt sein, aber funktional EasyAdmin nutzen:

- Suchbereich aus EasyAdmin
- User-Menü aus EasyAdmin
- optionale Quick-Actions nur als sauberer Platzhalter

### Asset-Strategie

Phase 1 sollte auf dem bestehenden `admin`-Entrypoint aufbauen:

- `assets/admin.js`
- AssetMapper
- `php bin/console asset-map:compile`

Nicht empfohlen für Phase 1:

- Xintras vollständiges eigenes Build-System parallel produktiv zu schalten

## Empfohlene nächste Umsetzungsschritte

1. Neues Basislayout `templates/admin/layout/base.html.twig` anlegen
2. `templates/bundles/EasyAdminBundle/layout.html.twig` zum zentralen Integrationsadapter umbauen
3. die globale Layoutverantwortung konsolidieren
4. Xintra-Struktur in Header, Sidebar und Main-Wrapper übersetzen
5. bestehende EasyAdmin-Funktionalität in die neue Shell einhängen
6. erst danach Strukturfehler beheben

## Fazit

Das Projekt hat derzeit keine einzige saubere EasyAdmin-App-Shell, sondern mehrere konkurrierende Layoutanpassungen.

Xintra ist als strukturelle Vorlage geeignet, aber nicht als direkte Copy/Paste-Lösung. Die sichere Phase-1-Strategie ist:

- Shell aus Xintra ableiten
- EasyAdmin-Funktionalität erhalten
- AssetMapper weiterverwenden
- Layoutkonsolidierung vor jedem weiteren UI-Detail
