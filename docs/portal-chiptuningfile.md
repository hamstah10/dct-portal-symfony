# Portal-Doku: `portal/chiptuningfile`

Stand: 24.03.2026

Diese Datei beschreibt ausschliesslich den Portal-Bereich `portal/chiptuningfile`. Gemeint sind damit die Twig-Templates unter `templates/portal/chiptuningfile`, die zugehoerigen Controller unter `src/Portals/Chiptuningfile/Controller` sowie die aktuelle Struktur des Kundenportals.

## 1. Zweck des Portal-Bereichs

Das Chiptuningfile-Portal ist der eingeloggte Kundenbereich fuer Portal-Kunden.

Der Bereich deckt aktuell folgende Themen ab:

- Login und Registrierung
- Passwort-Reset
- Portal-Dashboard
- Datei-Uploads
- Credits und PayPal-Aufladung
- Profilbearbeitung
- fahrzeugbezogene Portalansichten
- Auftrags-Wizard

## 2. Zentrales Layout

### `templates/portal/chiptuningfile/base.html.twig`

Das ist das Hauptlayout fuer nahezu alle Portal-Seiten.

Verantwortung des Layouts:

- erweitert `templates/base.html.twig`
- bindet das Portal-Theme und die Xintra-Assets ein
- setzt `html`- und `body`-Attribute fuer Layout, Navigation und Theme
- unterscheidet zwischen oeffentlichen Portal-Seiten und eingeloggten Bereichen
- steuert Light-/Dark-Theme per `localStorage`
- steuert vertikale oder horizontale Navigation
- initialisiert Header-Suche und weitere Portal-JavaScript-Logik

Wichtige Besonderheiten:

- der Auftragsbereich `cpt_portal_order` erzwingt eine horizontale Navigation
- Login, Registrierung und Passwort-Reset laufen optisch als oeffentliche Portal-Seiten
- eingeloggte Portal-Seiten laden zusaetzliche JS-Bibliotheken fuer Navigation, Header und Interaktion

## 3. Template-Struktur

Der Portal-Ordner besteht aktuell aus diesen Templates:

- `templates/portal/chiptuningfile/base.html.twig`
- `templates/portal/chiptuningfile/dashboard/index.html.twig`
- `templates/portal/chiptuningfile/upload/index.html.twig`
- `templates/portal/chiptuningfile/credits/index.html.twig`
- `templates/portal/chiptuningfile/order/index.html.twig`
- `templates/portal/chiptuningfile/profile/edit.html.twig`
- `templates/portal/chiptuningfile/vehicle/index.html.twig`
- `templates/portal/chiptuningfile/security/login.html.twig`
- `templates/portal/chiptuningfile/security/register.html.twig`
- `templates/portal/chiptuningfile/security/password_reset_request.html.twig`
- `templates/portal/chiptuningfile/security/password_reset_confirm.html.twig`

Alle Seiten-Templates erweitern aktuell `portal/chiptuningfile/base.html.twig`.

## 4. Route -> Controller -> Template

### Dashboard

- Route: `/`
- Name: `cpt_portal_dashboard`
- Controller: `src/Portals/Chiptuningfile/Controller/PortalDashboardController.php`
- Template: `templates/portal/chiptuningfile/dashboard/index.html.twig`

Beschreibung:

- Startseite des eingeloggten Portals
- laedt Daten ueber `PortalDashboardBuilder`
- erwartet einen authentifizierten `PortalCustomerUser`

### Uploads

- Route: `/uploads`
- Name: `cpt_portal_uploads`
- Controller: `src/Portals/Chiptuningfile/Controller/FileUploadController.php`
- Template: `templates/portal/chiptuningfile/upload/index.html.twig`

Beschreibung:

- Upload-Bereich fuer Kundendateien
- verwendet `FileUploadFormType`
- zeigt kuerzlich erzeugte Upload-Jobs ueber `PortalUploadJobRepository`
- nutzt `FileUploadPortalService` fuer die Erstellung eines Upload-Jobs

### Credits

- Route: `/credits`
- Name: `cpt_portal_credits`
- Controller: `src/Portals/Chiptuningfile/Controller/CreditsController.php`
- Template: `templates/portal/chiptuningfile/credits/index.html.twig`

Beschreibung:

- zeigt verfuegbare Credit-Pakete
- zeigt die letzten Credit-Transaktionen des Kunden
- integriert Preisberechnung ueber `CreditPackagePricingCalculator`
- integriert PayPal-Bestellung und Capture-Flow

Zusaetzliche API-nahe Portal-Routen ohne eigenes Seiten-Template:

- `POST /credits/paypal/order`
  - Name: `cpt_portal_credits_paypal_order`
- `POST /credits/paypal/capture`
  - Name: `cpt_portal_credits_paypal_capture`

### Auftrag

- Route: `/auftrag`
- Name: `cpt_portal_order`
- Controller: `src/Portals/Chiptuningfile/Controller/OrderController.php`
- Template: `templates/portal/chiptuningfile/order/index.html.twig`

Beschreibung:

- mehrstufiger Auftrags-Wizard
- nutzt aktuell vorbereitete Dummy-Daten fuer die einzelnen Schritte
- Schritt 1 bindet ein Formular `OrderDummyUploadType` ein
- ist bereits auf einen spaeteren echten Datei-/Fahrzeug-/Konfigurations-Workflow vorbereitet

Aktueller Status:

- strukturell weit vorbereitet
- fachlich noch kein vollstaendig produktiver Bestellprozess

### Profil

- Route: `/profil`
- Name: `cpt_portal_profile`
- Controller: `src/Portals/Chiptuningfile/Controller/ProfileController.php`
- Template: `templates/portal/chiptuningfile/profile/edit.html.twig`

Beschreibung:

- bearbeitet das Kundenprofil des eingeloggten Portal-Nutzers
- verwendet `ProfileFormType`
- speichert direkt auf dem `Kunde`-Datensatz

### Fahrzeuge

- Route: `/fahrzeuge`
- Name: `cpt_portal_vehicles`
- Controller: `src/Portals/Chiptuningfile/Controller/VehicleController.php`
- Template: `templates/portal/chiptuningfile/vehicle/index.html.twig`

Beschreibung:

- Portalansicht fuer die Fahrzeugsuche bzw. Fahrzeugdatenbank
- verwendet `PortalVehicleDatabaseService`
- unterstuetzt Suchbegriff `q` und Markenfilter `brand`

### Login

- Route: `/login`
- Name: `cpt_portal_login`
- Controller: `src/Portals/Chiptuningfile/Controller/PortalSecurityController.php`
- Template: `templates/portal/chiptuningfile/security/login.html.twig`

Beschreibung:

- Login-Oberflaeche des Portals
- liest `last_username` und Login-Fehler aus `AuthenticationUtils`
- leitet eingeloggte Portal-Kunden direkt auf das Dashboard um

### Registrierung

- Route: `/registrieren`
- Name: `cpt_portal_register`
- Controller: `src/Portals/Chiptuningfile/Controller/PortalSecurityController.php`
- Template: `templates/portal/chiptuningfile/security/register.html.twig`

Beschreibung:

- Registrierungsoberflaeche fuer neue Portal-Kunden
- verwendet `PortalRegistrationFormType`
- verarbeitet die Anlage ueber `PortalRegistrationService`

### Passwort-Reset anfordern

- Route: `/passwort-vergessen`
- Name: `cpt_portal_password_reset_request`
- Controller: `src/Portals/Chiptuningfile/Controller/PortalSecurityController.php`
- Template: `templates/portal/chiptuningfile/security/password_reset_request.html.twig`

Beschreibung:

- Formular zur Anforderung eines Reset-Links
- verwendet `PasswordResetRequestFormType`
- verarbeitet den Flow ueber `PortalPasswordResetService`

### Passwort-Reset bestaetigen

- Route: `/passwort-zuruecksetzen/{token}`
- Name: `cpt_portal_password_reset_confirm`
- Controller: `src/Portals/Chiptuningfile/Controller/PortalSecurityController.php`
- Template: `templates/portal/chiptuningfile/security/password_reset_confirm.html.twig`

Beschreibung:

- neues Passwort ueber gueltigen Reset-Token setzen
- verwendet `PasswordResetConfirmFormType`

### Logout

- Route: `/logout`
- Name: `cpt_portal_logout`
- Controller: `src/Portals/Chiptuningfile/Controller/PortalSecurityController.php`

Beschreibung:

- kein eigenes Template
- wird vom Security-Logout-Handler abgefangen

### Header-Suche

- Route: `/header-search/autocomplete`
- Name: `cpt_portal_header_search_autocomplete`
- Controller: `src/Portals/Chiptuningfile/Controller/HeaderSearchController.php`

Beschreibung:

- JSON-/Autocomplete-Endpunkt fuer die globale Portalsuche im Header
- kein eigenes Template

## 5. Security-Templates

Der Security-Bereich liegt unter:

- `templates/portal/chiptuningfile/security/`

Diese Templates bilden den oeffentlichen Einstieg ins Portal:

- `login.html.twig`
- `register.html.twig`
- `password_reset_request.html.twig`
- `password_reset_confirm.html.twig`

Charakteristik:

- nutzen dasselbe Basislayout wie der eingeloggte Bereich
- laufen optisch aber als Public-Screens ohne normale Portal-App-Navigation
- werden zentral vom `PortalSecurityController` orchestriert

## 6. Fachliche Templates im eingeloggten Bereich

### `dashboard/index.html.twig`

Rolle:

- Hub-Seite nach dem Login
- Einstieg in die wichtigsten Portalmodule

Typische Daten:

- `brandContext`
- `portalConfig`
- `dashboard`

### `upload/index.html.twig`

Rolle:

- Datei-Upload fuer Kunden
- Uebersicht juengster Uploads

Typische Daten:

- `brandContext`
- `portalConfig`
- `form`
- `jobs`

### `credits/index.html.twig`

Rolle:

- Credit-Kauf und Credit-Historie

Typische Daten:

- `brandContext`
- `portalConfig`
- `customer`
- `packageCards`
- `transactions`
- `paypalConfigured`
- `paypalClientId`
- `paypalCsrfToken`

### `order/index.html.twig`

Rolle:

- Wizard fuer einen spaeteren Portal-Auftrag

Typische Daten:

- `brandContext`
- `portalConfig`
- `wizardSteps`
- `currentStep`
- `currentStepNumber`
- `progressPercent`
- `previousStepUrl`
- `nextStepUrl`
- `orderUploadForm`

### `profile/edit.html.twig`

Rolle:

- Bearbeitung des Kundenprofils

Typische Daten:

- `brandContext`
- `portalConfig`
- `form`

### `vehicle/index.html.twig`

Rolle:

- Portal-Fahrzeugdatenbank bzw. Suchoberflaeche

Typische Daten:

- `brandContext`
- `portalConfig`
- `database`

## 7. Wichtige Services und Form Types hinter den Templates

### Services

- `PortalDashboardBuilder`
  - baut die Dashboard-Daten
- `FileUploadPortalService`
  - verarbeitet Datei-Uploads
- `PortalVehicleDatabaseService`
  - liefert Daten fuer die Fahrzeugansicht
- `PortalRegistrationService`
  - registriert neue Portal-Kunden
- `PortalPasswordResetService`
  - verwaltet Reset-Tokens und Passwort-Reset
- `PaypalOrderService`
  - erstellt und captured PayPal-Orders
- `PortalCreditTopupService`
  - verbucht erfolgreich bezahlte Credits
- `PortalHeaderSearchService`
  - liefert Daten fuer die Header-Suche

### Form Types

- `FileUploadFormType`
- `OrderDummyUploadType`
- `ProfileFormType`
- `PortalRegistrationFormType`
- `PasswordResetRequestFormType`
- `PasswordResetConfirmFormType`

## 8. Zugriff und Portal-Freischaltung

Der Portal-Bereich ist nicht nur ueber Routen, sondern auch ueber Portal-Runtime-Checks abgesichert.

Wichtige Muster:

- `requirePortalSystem()`
- `requirePortalModule(...)`
- `requireRegistrationEnabled(...)`

Das bedeutet:

- einzelne Bereiche koennen portalweit deaktiviert sein
- einzelne Module wie Profil, Dashboard, Uploads oder Fahrzeugdatenbank koennen gezielt vorausgesetzt werden
- Registrierung kann separat freigeschaltet oder gesperrt sein

## 9. Aktueller Reifegrad nach Bereich

Produktiv bzw. fachlich konkret angebunden:

- Login
- Registrierung
- Passwort-Reset
- Dashboard
- Uploads
- Credits
- Profil
- Fahrzeugsuche

Noch klar im Ausbau bzw. als vorbereitete UI-Struktur:

- Auftrags-Wizard unter `order/index.html.twig`

## 10. Pflegehinweise fuer neue Portal-Templates

Wenn neue Seiten im Portal dazukommen, sollte idealerweise immer dieselbe Kette nachvollziehbar sein:

- neue Route im `src/Portals/Chiptuningfile/Controller/...`
- neues Template unter `templates/portal/chiptuningfile/...`
- optional neuer Form Type oder Service
- Eintrag in dieser Doku mit:
  - Route
  - Controller
  - Template
  - Zweck
  - Reifegrad oder Besonderheiten

So bleibt der Portalbereich auch bei weiterem Ausbau nachvollziehbar dokumentiert.
