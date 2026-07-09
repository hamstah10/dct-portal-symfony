# EasyAdmin UI Audit

## Aktuelle Admin-Struktur

- Einstiegspunkt ist [`src/Controller/Admin/DashboardController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/DashboardController.php).
- Das Projekt nutzt EasyAdmin weiterhin als Basis, ergänzt aber mehrere entity-spezifische Twig-Overrides fuer `index`, `detail`, `edit` und einzelne Embedded-Layouts.
- Die Admin-Assets laufen bereits ueber `configureAssets()` und den AssetMapper-Einstieg `admin`.
- Fuer einzelne Bereiche gibt es bereits spezialisierte Ansichten, z. B. Kunden, Auftraege, Shop-Produkte, Medien und Dyno-Details.

## Vorhandene Overrides

- Globale Admin-Assets:
  - [`assets/admin.js`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/admin.js)
  - [`assets/styles/admin.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/styles/admin.css)
  - [`assets/styles/admin-overrides.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/styles/admin-overrides.css)
- Direkt eingebundene Legacy-CSS-Dateien:
  - [`public/css/admin-toolbar.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/public/css/admin-toolbar.css)
  - [`public/css/admin-contrast.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/public/css/admin-contrast.css)
- Bestehende Detail-Overrides:
  - [`templates/admin/kunde/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/kunde/detail.html.twig)
  - [`templates/admin/shop_product/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/shop_product/detail.html.twig)
  - [`templates/admin/media/asset_detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/media/asset_detail.html.twig)
  - Dyno-Detailseiten
- Bestehende Index-Overrides:
  - Kunden, Auftraege, Shop-Bestellungen, Shop-Produkte, Fahrzeug-Views, Website-Sniffer, Credits u. a.
- Embedded-Layout fuer Teilbereiche:
  - [`templates/admin/embedded/layout.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/embedded/layout.html.twig)

## Optische Schwachstellen

- Das Styling ist derzeit gesplittet zwischen altem hellem Override-Layer und mehreren Inline-Styles in Twig.
- Viele Oberflaechen nutzen uneinheitliche Border-Radien, Schatten, Farben und Abstaende.
- Die globale Admin-Oberflaeche wirkt eher wie ein Patchwork aus EasyAdmin-Standard und punktuellen Sonderloesungen.
- Detailansichten sind inkonsistent:
  - teilweise stark individualisiert,
  - teilweise nur Standard-Feldlisten,
  - kaum wiederverwendbare Hero- oder Meta-Muster.
- Index-Seiten enthalten wiederholt Inline-CSS statt eines zentralen Systems.
- Die leere Datei [`templates/admin/layout.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/layout.html.twig) deutet auf eine nicht fertiggezogene Layout-Idee hin.

## Problematische Stellen

- Kunden- und Auftragslisten arbeiten mit zusatzlicher UI-Logik in Twig; diese darf beim Styling nicht zerbrochen werden.
- Mehrere CRUDs nutzen `overrideTemplate('layout', 'admin/embedded/layout.html.twig')`; globale Layout-Anpassungen muessen damit kompatibel bleiben.
- In einzelnen CRUD-Controllern werden HTML-Fragmente direkt in `formatValue()` erzeugt, teils mit Inline-Styles.
- Shop- und Medien-Detailseiten enthalten viel wertvolle Fachinformation, aber die Darstellung ist derzeit noch nicht in ein wiederverwendbares Designsystem ueberfuehrt.

## Technische Risiken beim Umbau

- Zu aggressive globale CSS-Selektoren koennen EasyAdmin-Formulare, Filter oder Action-Dropdowns destabilisieren.
- Ein kompletter Austausch der Layout-Struktur waere riskant, weil bereits viele Twig-Overrides auf EasyAdmin-Blocknamen aufbauen.
- Die bestehenden `renderAsHtml()`-Felder muessen sichtbar bleiben; Styling darf HTML-Inhalte nicht unlesbar machen.
- Asset-Aenderungen muessen ueber den bestehenden `admin`-Entry laufen, sonst drohen doppelte oder veraltete Styles.

## Empfohlene Umbau-Strategie

- Neues zentrales Theme als eigener Asset-Layer im bestehenden `admin`-Entry.
- Globales Layout nur vorsichtig ueber Block-Overrides erweitern.
- Priority-CRUDs mit `FormField::addFieldset()` neu gruppieren.
- Wiederverwendbare Twig-Bausteine fuer Hero, Status, Meta und Werte einsetzen.
- Bestehende Spezialansichten erhalten, aber visuell in das neue Theme ueberfuehren.
