# EasyAdmin UI Refactor

## Was geaendert wurde

- Ein neues globales Dark-Theme fuer EasyAdmin wurde als zentraler Asset-Layer eingefuehrt.
- Das globale EasyAdmin-Layout laeuft jetzt ueber ein projektinternes Override, ohne Vendor-Dateien anzufassen.
- Detailseiten fuer wichtige Kernbereiche wurden visuell aufgewertet:
  - Kunden
  - Shop-Bestellungen
  - Shop-Produkte
  - Medien
  - Benutzer
- Wiederverwendbare Twig-Bausteine fuer Hero, Status, Meta und Werte wurden eingefuehrt.
- Mehrere wichtige CRUD-Controller wurden mit `FormField::addFieldset()` sauberer gruppiert, damit `detail`, `edit` und `new` strukturierter bleiben.
- Die Auftrags-Indexseite nutzt keine eigene Inline-Style-Insel mehr fuer die KPI-Karten, sondern das neue Theme-System.

## Neue Templates und Assets

### Assets

- [`assets/styles/admin-theme.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/styles/admin-theme.css)
- [`assets/js/admin-theme.js`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/js/admin-theme.js)

### Layout und Komponenten

- [`templates/admin/layout.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/layout.html.twig)
- [`templates/admin/components/detail_hero.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/components/detail_hero.html.twig)
- [`templates/admin/field/status_badge.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/field/status_badge.html.twig)
- [`templates/admin/field/meta_info.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/field/meta_info.html.twig)
- [`templates/admin/field/money_card.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/field/money_card.html.twig)

### Neue Detailseiten

- [`templates/admin/shop_order/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/shop_order/detail.html.twig)
- [`templates/admin/user/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/user/detail.html.twig)

## Angepasste CRUDs

- [`src/Controller/Admin/DashboardController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/DashboardController.php)
  - globales Layout-Override eingebunden
- [`src/Controller/Admin/KundeCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/KundeCrudController.php)
  - Kundenfelder in Sektionen gruppiert
- [`src/Controller/Admin/ShopOrderCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/ShopOrderCrudController.php)
  - strukturierte Detail-/Form-Felder und neue Detailseite
- [`src/Controller/Admin/ShopProductCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/ShopProductCrudController.php)
  - Produktfelder nach Inhalt, Beziehungen, SEO und Auswertung gruppiert
- [`src/Controller/Admin/MediaAssetCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/MediaAssetCrudController.php)
  - Medienfelder fuer Index und Detail klarer getrennt
- [`src/Controller/Admin/UserCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/UserCrudController.php)
  - Detailseite und Feldgruppen fuer Profil, Medien und Sicherheit

## Angepasste Twig-Views

- [`templates/admin/auftrag/index.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/auftrag/index.html.twig)
- [`templates/admin/kunde/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/kunde/detail.html.twig)
- [`templates/admin/media/asset_detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/media/asset_detail.html.twig)
- [`templates/admin/shop_product/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/shop_product/detail.html.twig)

## Wie die neuen Feldtemplates wiederverwendet werden sollen

- `detail_hero.html.twig` fuer Seitenkopf mit Titel, Status und Meta-Infos.
- `status_badge.html.twig` fuer einheitliche Status- und Typkennzeichnung.
- `meta_info.html.twig` fuer kompakte Metadatenkarten.
- `money_card.html.twig` fuer Preise, Summen, Credits und Kennzahlen.

## Bewusst nicht geaendert

- Keine Vendor-Dateien.
- Keine EasyAdmin-Grundlogik, Filter-Mechanik oder CRUD-Routen.
- Keine Komplett-Neuimplementierung einzelner Spezialseiten in React.
- Keine aggressive Vereinheitlichung aller bestehenden, sehr speziellen Admin-Views in einem Schritt.

## Spaeter sinnvoll ausbaubar

- Weitere Kern-CRUDs wie Auftraege, Firmen und Credit-Transaktionen koennen auf die gleichen Hero-/Fieldset-Muster umgestellt werden.
- Einzelne verbleibende Inline-Styles in komplexen Spezial-Twig-Views koennen in einem zweiten Cleanup-Lauf nach `admin-theme.css` ueberfuehrt werden.
- Falls gewuenscht, kann als naechster Schritt eine gemeinsame Detail-Basis fuer noch mehr CRUDs eingefuehrt werden.

## Liste aller geaenderten Dateien

- [`assets/admin.js`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/admin.js)
- [`assets/js/admin-theme.js`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/js/admin-theme.js)
- [`assets/styles/admin-theme.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/styles/admin-theme.css)
- [`docs/easyadmin-theme-assets.md`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/docs/easyadmin-theme-assets.md)
- [`docs/easyadmin-ui-audit.md`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/docs/easyadmin-ui-audit.md)
- [`docs/easyadmin-ui-refactor.md`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/docs/easyadmin-ui-refactor.md)
- [`src/Controller/Admin/DashboardController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/DashboardController.php)
- [`src/Controller/Admin/KundeCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/KundeCrudController.php)
- [`src/Controller/Admin/MediaAssetCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/MediaAssetCrudController.php)
- [`src/Controller/Admin/ShopOrderCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/ShopOrderCrudController.php)
- [`src/Controller/Admin/ShopProductCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/ShopProductCrudController.php)
- [`src/Controller/Admin/UserCrudController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/UserCrudController.php)
- [`templates/admin/auftrag/index.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/auftrag/index.html.twig)
- [`templates/admin/components/detail_hero.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/components/detail_hero.html.twig)
- [`templates/admin/field/meta_info.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/field/meta_info.html.twig)
- [`templates/admin/field/money_card.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/field/money_card.html.twig)
- [`templates/admin/field/status_badge.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/field/status_badge.html.twig)
- [`templates/admin/kunde/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/kunde/detail.html.twig)
- [`templates/admin/layout.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/layout.html.twig)
- [`templates/admin/media/asset_detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/media/asset_detail.html.twig)
- [`templates/admin/shop_order/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/shop_order/detail.html.twig)
- [`templates/admin/shop_product/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/shop_product/detail.html.twig)
- [`templates/admin/user/detail.html.twig`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/templates/admin/user/detail.html.twig)
