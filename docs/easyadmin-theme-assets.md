# EasyAdmin Theme Assets

## Asset-Einbindung

- Das Admin-Theme wird ueber den bestehenden EasyAdmin-Asset-Hook in [`src/Controller/Admin/DashboardController.php`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/src/Controller/Admin/DashboardController.php) geladen.
- Die zentrale Quelle ist der AssetMapper-Einstieg [`assets/admin.js`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/admin.js).
- Darin werden eingebunden:
  - [`assets/styles/admin-theme.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/styles/admin-theme.css)
  - [`assets/js/admin-theme.js`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/assets/js/admin-theme.js)

## Warum diese Struktur

- Kein direkter Eingriff in Vendor-Templates oder Vendor-Assets.
- Ein einziger Admin-Entry bleibt die zentrale Stelle fuer JS und CSS.
- Bestehende Spezialskripte in `assets/admin.js` bleiben erhalten.
- Das Theme kann spaeter erweitert werden, ohne erneut viele Inline-Styles nachzuziehen.

## Bestehende Legacy-Assets

- [`public/css/admin-toolbar.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/public/css/admin-toolbar.css)
- [`public/css/admin-contrast.css`](/home/verwaltung/htdocs/verwaltung.tuningfux.de/public/public/css/admin-contrast.css)

Diese bleiben vorerst eingebunden, um bestehende Admin-Funktionen nicht zu verlieren. Das neue Theme wird danach geladen und uebernimmt den visuellen Lead.
