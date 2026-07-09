# System-Uebersicht: Admins, Portale, Websites

Stand: 21.04.2026

Diese Datei beschreibt die aktuell vorhandenen Hauptsysteme in der Anwendung: das klassische EasyAdmin-Backoffice unter `/admin`, das React-basierte Superadmin-Portal und das System `chiptuningfile`, zu dem sowohl Kundenportal als auch oeffentliche Website gehoeren.

## 1. EasyAdmin-Backoffice

Zweck:

- internes Verwaltungs- und Operations-Backend
- klassische CRUD-Verwaltung
- Spezialseiten fuer Dashboards, Kommunikation, Fahrzeugdatenbank, Scraper, Dyno, AI, Medien und Finanzen

Zugang:

- Route-Basis: `/admin`
- Login: `/login`

Technik:

- Symfony + EasyAdmin
- Controller schwerpunktmaessig unter `src/Controller/Admin`
- Templates schwerpunktmaessig unter `templates/admin`

Typische Bereiche:

- Fahrzeugdatenbank
- Kunden / Auftraege / Firmen
- Support / E-Mail / Newsletter
- Shop / eCommerce
- Credits / Abos / Zahlungsarten
- AI-Tools
- Media / Website-Sniffer
- Scraper-Steuerung und Imports

Wichtige Spezialseiten im Fahrzeug-/Scraperbereich:

- `/admin/fahrzeugdatenbank/scraper`
- `/admin/fahrzeugdatenbank/scraper/import`
- `/admin/fahrzeugdatenbank/scraper/import-output`

Relevante Dateien:

- `src/Controller/Admin/DashboardController.php`
- `src/Service/AdminPageRegistry.php`
- `templates/admin/`

## 2. Superadmin-Portal

Zweck:

- separates Portal-System fuer den Portaltyp `superadmin_portal`
- React-basierte Portaloberflaeche statt klassischer EasyAdmin-Seite
- eigenes Portal-Frontend innerhalb derselben Symfony-Codebasis

Technik:

- Controller unter `src/Portals/SuperadminPortal/Controller`
- React-Host-Template unter `templates/portal/superadmin_portal/dashboard/index.html.twig`
- Bootstrap-Daten ueber `SuperadminPortalBootstrapBuilder`

Aktuelle Kernbausteine:

- Dashboard-/App-Host
- Vehicle-API-Proxy
- Operations-API
- React-Asset-Auslieferung

Relevante Dateien:

- `src/Portals/SuperadminPortal/Controller/PortalController.php`
- `src/Portals/SuperadminPortal/Controller/VehicleApiProxyController.php`
- `src/Portals/SuperadminPortal/Controller/OperationsDataController.php`
- `src/Portals/SuperadminPortal/Controller/ReactAssetController.php`
- `src/Portals/SuperadminPortal/Service/SuperadminPortalBootstrapBuilder.php`
- `templates/portal/superadmin_portal/dashboard/index.html.twig`
- `templates/portal/theme_react_superadmin_portal/superadmin_portal/frontend/`

Wichtige Routen:

- `superadmin_portal_dashboard`
- `superadmin_portal_app`

Hinweis:

- Das Superadmin-Portal ist ein eigenes Portal-System und nicht dasselbe wie das EasyAdmin-Backoffice unter `/admin`.

## 3. Chiptuningfile-System

Das System `chiptuningfile` besteht aus zwei fachlich zusammengehoerigen Oberflaechen:

- Kundenportal
- oeffentliche Website unter `https://cpt.tuningfux.de/`

Beide gehoeren zum selben Markensystem und teilen sich die zugrunde liegende Portal-/Brand-Konfiguration.

### 3.1 Kundenportal

Zweck:

- eingeloggter Kundenbereich fuer `chiptuningfile`
- Portal fuer Kundenlogin, Credits, Uploads, Auftraege, Profil und Fahrzeugdatenbank

Technik:

- Controller unter `src/Portals/Chiptuningfile/Controller`
- Templates unter `templates/portal/chiptuningfile`
- eigene Security-Schicht fuer Portal-Kunden

Aktuelle Module:

- Dashboard
- Login / Registrierung / Passwort-Reset
- Credits
- Uploads
- Auftragsbereich
- Profil
- Fahrzeugdatenbank
- Header-Suche
- Vehicle-API-Proxy fuer Portal-Kontext

Relevante Dateien:

- `src/Portals/Chiptuningfile/Controller/PortalDashboardController.php`
- `src/Portals/Chiptuningfile/Controller/PortalSecurityController.php`
- `src/Portals/Chiptuningfile/Controller/CreditsController.php`
- `src/Portals/Chiptuningfile/Controller/FileUploadController.php`
- `src/Portals/Chiptuningfile/Controller/OrderController.php`
- `src/Portals/Chiptuningfile/Controller/ProfileController.php`
- `src/Portals/Chiptuningfile/Controller/VehicleController.php`
- `src/Portals/Chiptuningfile/Controller/VehicleApiProxyController.php`
- `templates/portal/chiptuningfile/base.html.twig`
- `templates/portal/chiptuningfile/`

Wichtige Routen mit `cpt_portal_*`:

- `cpt_portal_dashboard`
- `cpt_portal_login`
- `cpt_portal_register`
- `cpt_portal_password_reset_request`
- `cpt_portal_password_reset_confirm`
- `cpt_portal_uploads`
- `cpt_portal_credits`
- `cpt_portal_order`
- `cpt_portal_profile`
- `cpt_portal_vehicles`

Weiterfuehrende Doku:

- `docs/portal-chiptuningfile.md`

### 3.2 Oeffentliche Website

Zweck:

- oeffentliche Website fuer die Marke `chiptuningfile`
- getrennt vom eingeloggten Kundenportal
- aktuell unter:
  - `https://cpt.tuningfux.de/`

Technik:

- Website-Controller unter `src/Websites/Chiptuningfile/Controller`
- Website-Templates unter `templates/website/chiptuningfile`
- je nach Route sowohl Twig-Seiten als auch SPA-/Asset-Auslieferung

Aktuelle Bausteine:

- oeffentliche Start-/SPA-Auslieferung
- Legal-Seiten
- API-/Vehicle-Proxy fuer Website-Kontext
- Verlinkung ins Kundenportal

Relevante Dateien:

- `src/Websites/Chiptuningfile/Controller/HomeController.php`
- `src/Websites/Chiptuningfile/Controller/LegalController.php`
- `src/Websites/Chiptuningfile/Controller/ApiController.php`
- `src/Websites/Chiptuningfile/Controller/VehicleApiProxyController.php`
- `templates/website/chiptuningfile/base.html.twig`
- `templates/website/chiptuningfile/home/index.html.twig`
- `templates/website/chiptuningfile/legal/`

Wichtige Routen:

- `cpt_website_spa`
- Legal- und API-Routen aus `src/Websites/Chiptuningfile/Controller`

Abgrenzung zum Portal:

- Website:
  - oeffentlicher Markenauftritt
  - erreichbar unter `https://cpt.tuningfux.de/`
- Portal:
  - eingeloggter Kundenbereich
  - getrennte Templates, Security und Fachlogik

## 4. Datenmodell fuer Portale und Websites

Die fachliche Konfiguration liegt im Wesentlichen am Entity `Portal`.

Wichtige Felder/Aspekte:

- Portaltyp
- Portal-Host / Portal-URL
- Website-Host / Website-URL
- Aktivierung von Portal und Website
- Branding / Farben / Logo
- aktivierte Module
- Vehicle-Data-Pool-Zuordnung fuer Portal-/Fahrzeugkontext

Relevante Stellen:

- `src/Entity/Portal.php`
- `src/Controller/Admin/PortalCrudController.php`

## 5. Praktische Abgrenzung

Wenn man im Team von "Admin" spricht, kann je nach Kontext Unterschiedliches gemeint sein:

- EasyAdmin-Backoffice:
  - das klassische interne Backend unter `/admin`
- Superadmin-Portal:
  - das separate React-Portal fuer den Portaltyp `superadmin_portal`
- Chiptuningfile-System:
  - Kundenportal plus oeffentliche Website `https://cpt.tuningfux.de/`

Diese Systeme leben in derselben Symfony-Anwendung, sind aber technisch und fachlich klar getrennt.
