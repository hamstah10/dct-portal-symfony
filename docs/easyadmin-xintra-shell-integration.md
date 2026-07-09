# EasyAdmin Xintra Shell Integration

## Finale Layoutkette

Die globale Admin-Layoutkette ist jetzt:

`templates/admin/layout/base.html.twig`
-> `templates/bundles/EasyAdminBundle/layout.html.twig`
-> EasyAdmin-Seiteninhalt

## Neu

- `templates/bundles/EasyAdminBundle/menu.html.twig`
- `docs/easyadmin-xintra-shell-integration.md`

## Angepasst

- `templates/admin/layout/base.html.twig`
- `assets/styles/admin-theme.css`
- `assets/js/admin-theme.js`

## Integration

`templates/admin/layout/base.html.twig` ist die zentrale Shell und nutzt die Xintra-Struktur fuer:

- `div.page`
- `header.app-header`
- `aside.app-sidebar`
- `section.main-content.app-content`
- `div.container-fluid`

Die Shell enthaelt nur Slots fuer:

- Sidebar
- Topbar
- Main-Content

Keine CRUD-spezifischen Inhalte wurden in die Basisdatei verlagert.

## EasyAdmin-Adapter

`templates/bundles/EasyAdminBundle/layout.html.twig` bleibt der EasyAdmin-Adapter und erweitert die Shell.

Darin bleiben erhalten:

- EasyAdmin-Suche in der Topbar
- User-Menue und Settings-Dropdown in der Topbar
- Flash-Auto-Hide
- Toast-Container
- Mercure-/Notify-Handling
- Live-Navigationsbadges
- projektspezifische Quick Actions

## Sidebar-Anbindung

Die Sidebar rendert jetzt das echte EasyAdmin-Menue ueber `templates/bundles/EasyAdminBundle/menu.html.twig` im Xintra-Markup.

Dabei bleiben erhalten:

- aktive Menuepunkte
- Untermenues
- Gruppen/Sektionen
- EasyAdmin-Icons
- EasyAdmin-Badges und CSS-Klassen

Die projektspezifischen Badge-Selektoren funktionieren weiter, weil die EasyAdmin-CSS-Klassen jetzt auf den Sidebar-Listenelementen und Links vorhanden sind.

## Topbar-Anbindung

Die Topbar nutzt Xintra-Wrapper und bindet darin die bestehenden EasyAdmin-Bloecke ein:

- `search_wrapper`
- `header_custom_menu_wrapper`
- `settings_dropdown_wrapper`

## Asset-System

AssetMapper bleibt das fuehrende System.

Aktiv verwendet:

- `importmap.php`
- `assets/admin.js`
- `assets/styles/admin-theme.css`
- `assets/js/admin-theme.js`

Es wurde kein zweites produktives Xintra-Build-System aktiviert.

## Bewusst auf Phase 2 verschoben

- CRUD-spezifische Redesigns
- Detailseiten-Umbauten
- Listenansichten-Umbauten
- neue Dashboard-Demo-Inhalte
- Uebernahme von Xintra-Demo-Menues oder Fake-Widgets
