TASKS – Umbau des kompletten Portals portal/chiptuningfile auf Emergent (React)

Ziel

Das neue Emergent-Frontend (React) soll fuer das gesamte bestehende Portal portal/chiptuningfile eingebaut werden – nicht nur fuer das Dashboard.

Der von dir gelieferte IST-Zustand ist die verbindliche Grundlage fuer die Migration.

Ausgangslage:
	•	das Portal portal/chiptuningfile existiert bereits produktiv als Symfony/Twig-Bereich
	•	zentrales Layout ist templates/portal/chiptuningfile/base.html.twig
	•	die Controller liegen unter src/Portals/Chiptuningfile/Controller
	•	die Templates liegen unter templates/portal/chiptuningfile
	•	das Portal deckt Login, Registrierung, Passwort-Reset, Dashboard, Uploads, Credits, Profil, Fahrzeuge und Auftrags-Wizard ab
	•	der Bereich hat bestehende Security- und Runtime-Checks wie requirePortalSystem(), requirePortalModule(...) und requireRegistrationEnabled(...)

Ziel ist eine vollstaendige, aber schrittweise Migration des kompletten Portals portal/chiptuningfile auf React/Emergent, waehrend Symfony weiterhin Backend, Security, Runtime-Checks, Form-Verarbeitung, Business-Logik und API-Endpunkte liefert.

Das bedeutet:
	•	Twig wird im Zielbild fuer portal/chiptuningfile weitgehend auf Shell-/Bridge-Aufgaben reduziert
	•	React/Emergent uebernimmt die Oberflaechen des kompletten Portals
	•	bestehende Fachlogik in Symfony bleibt erhalten und wird ueber API/Adapter freigelegt

⸻

Kontext
	•	Backend bleibt Symfony
	•	Zielbereich ist komplett portal/chiptuningfile
	•	zentrales Layout ist templates/portal/chiptuningfile/base.html.twig
	•	zu migrierende Templates sind aktuell:
	•	dashboard/index.html.twig
	•	upload/index.html.twig
	•	credits/index.html.twig
	•	order/index.html.twig
	•	profile/edit.html.twig
	•	vehicle/index.html.twig
	•	security/login.html.twig
	•	security/register.html.twig
	•	security/password_reset_request.html.twig
	•	security/password_reset_confirm.html.twig
	•	relevante Controller liegen unter src/Portals/Chiptuningfile/Controller
	•	bestehende Services wie PortalDashboardBuilder, FileUploadPortalService, PortalVehicleDatabaseService, PortalRegistrationService, PortalPasswordResetService, PaypalOrderService, PortalCreditTopupService und PortalHeaderSearchService sollen weiterverwendet werden
	•	bestehende Form Types sollen nur dort ersetzt werden, wo eine React/API-Variante sinnvoll ist
	•	bestehende Portal-Runtime-Checks und Security muessen erhalten bleiben

⸻

Arbeitsweise für Codex

Wichtige Regeln bei der Umsetzung:
	1.	Immer zuerst die bestehende Projektstruktur analysieren.
	2.	Keine bestehenden produktiven Routen oder Templates blind überschreiben.
	3.	Neue Integration so bauen, dass Symfony und React klar getrennt bleiben.
	4.	Änderungen in kleinen, nachvollziehbaren Schritten umsetzen.
	5.	Nach jedem größeren Schritt kurz prüfen, ob Build, Routing und Assets funktionieren.
	6.	Bestehende Backend-Logik weiterverwenden, wenn möglich.
	7.	Lieber Adapter/Bridge bauen statt bestehende Symfony-Logik hart umzuschreiben.

⸻

PHASE 1 – Bestandsaufnahme

Task 1.1 – Kompletten Portalbereich portal/chiptuningfile analysieren

Analysiere den gesamten Portalbereich portal/chiptuningfile anhand der gelieferten IST-Doku und des realen Codes.

Pruefen:
	•	templates/portal/chiptuningfile/base.html.twig
	•	alle Untertemplates in templates/portal/chiptuningfile/**
	•	alle Controller unter src/Portals/Chiptuningfile/Controller
	•	alle Route-Namen und Pfade
	•	welche Templates welche Daten erwarten
	•	welche Services/FormTypes pro Seite beteiligt sind
	•	welche Teile oeffentlich sind und welche authentifiziert sind

Ergebnis:
	•	belastbare Ist-Aufnahme des kompletten Portals
	•	Mapping je Seite: Route -> Controller -> Template -> Daten -> Service/FormType -> Security/Runtimes

⸻

Task 1.2 – Oeffentliche Screens und eingeloggte Screens trennen

Trenne in der Analyse klar zwischen:

Public-Screens:
	•	Login
	•	Registrierung
	•	Passwort-Reset-Request
	•	Passwort-Reset-Confirm

Authenticated-Screens:
	•	Dashboard
	•	Uploads
	•	Credits
	•	Profil
	•	Fahrzeuge
	•	Auftrag/Wizard

Wichtig:
	•	beide Bereiche sollen migriert werden
	•	aber mit unterschiedlicher Behandlung im Layout und Routing

Ergebnis:
	•	klare Zielarchitektur fuer Public-Area und Authenticated-Area

⸻

Task 1.3 – Zentrales Layout base.html.twig technisch zerlegen

Analysiere templates/portal/chiptuningfile/base.html.twig im Detail.

Pruefen:
	•	welche Layout-Blocks fuer Inhalte existieren
	•	wie Public-Screens von eingeloggten Screens unterschieden werden
	•	welche Xintra-/Theme-Assets eingebunden werden
	•	welche html-/body-Attribute gesetzt werden
	•	wie Light/Dark-Theme per localStorage arbeitet
	•	wie vertikale/horizontale Navigation gesteuert wird
	•	welche Sonderlogik fuer cpt_portal_order existiert
	•	welche Header-/Navigation-/JS-Initialisierungen erhalten werden muessen

Ergebnis:
	•	Entscheidung, ob base.html.twig als Host-Shell bleibt oder auf mehrere React-Shells aufgeteilt wird

⸻

Task 1.4 – Emergent-Frontend komplett analysieren

Analysiere das neue Emergent-Frontend als Basis fuer das komplette Portal.

Pruefen:
	•	Build-Tool
	•	Entry-Point
	•	Router
	•	Layout-System
	•	Seitenstruktur
	•	State-Management
	•	Formularhandling
	•	API-Zugriffe
	•	Environment-Variablen
	•	Produktions-Build-Befehl

Ergebnis:
	•	Uebersicht, wie das Emergent-Frontend auf das komplette Portal gemappt werden kann

⸻

Task 1.5 – Migrationsstrategie fuer das ganze Portal festlegen

Treffe die Architekturentscheidung explizit fuer eine vollstaendige Portal-Migration.

Bevorzugte Strategie:
	•	Symfony bleibt Backend, Security, Runtime-Check- und API-Schicht
	•	React/Emergent wird das neue UI fuer portal/chiptuningfile
	•	Public-Screens und eingeloggte Portal-Screens werden in React gebaut
	•	Twig bleibt nur noch als Shell/Bridge/Fallback, bis die Migration abgeschlossen ist

Ergebnis:
	•	klare Architekturentscheidung mit Migrationsreihenfolge

⸻

PHASE 2 – Technische Basis schaffen

Task 2.1 – Frontend-Ordner sauber einhängen

Falls noch nicht vorhanden, das neue Emergent-Frontend sauber in die Symfony-Struktur integrieren.

Bevorzugt z. B.:
	•	/frontend/customer-portal/
	•	oder /assets/customer-portal-react/

Anforderungen:
	•	Klare Trennung vom bestehenden Symfony-Frontend
	•	Keine Vermischung mit Admin-Assets
	•	Eindeutige Struktur für Components, Pages, Router, Services, Styles

Ergebnis:
	•	Saubere Zielstruktur angelegt oder angepasst

⸻

Task 2.2 – Build- und Dev-Workflow definieren

Richte einen klaren Workflow ein.

Prüfen/umsetzen:
	•	dev server für React
	•	production build
	•	Ausgabeordner für gebaute Assets
	•	Einbindung in Symfony public-Verzeichnis oder über Manifest
	•	NPM Scripts vereinheitlichen

Beispiel-Ziel:
	•	npm run dev
	•	npm run build
	•	Symfony kann gebaute Dateien sauber ausliefern

Ergebnis:
	•	Frontend ist lokal buildbar
	•	Symfony kann die gebauten Assets referenzieren

⸻

Task 2.3 – Symfony-Bridge fuer das komplette React-Portal erstellen

Erstelle keine reine Dashboard-Bridge, sondern eine Host-Struktur fuer das komplette Portal portal/chiptuningfile.

Anforderungen:
	•	bestehende Portal-Routen bleiben vorerst erhalten oder werden sauber auf neue Shells gemappt
	•	Public-Screens und Authenticated-Screens koennen jeweils ueber passende React-Hosts geladen werden
	•	React-Mounts werden sauber in Twig/Symfony eingebettet
	•	Build-Assets werden nur dort geladen, wo sie gebraucht werden

Bevorzugtes Ziel:
	•	eine React-App fuer das Portal mit sauberem Routing
	•	optional getrennte Hosts fuer:
	•	Public-Portal-Screens
	•	eingeloggten Portalbereich

Ergebnis:
	•	das komplette Emergent-Portal kann innerhalb von Symfony geladen werden

⸻

PHASE 3 – Routing und Portalstruktur

Task 3.1 – Komplettes Route-Mapping fuer portal/chiptuningfile erstellen

Erstelle ein verbindliches Mapping fuer alle bestehenden Portal-Routen.

Abzudecken:
	•	cpt_portal_dashboard
	•	cpt_portal_uploads
	•	cpt_portal_credits
	•	cpt_portal_credits_paypal_order
	•	cpt_portal_credits_paypal_capture
	•	cpt_portal_order
	•	cpt_portal_profile
	•	cpt_portal_vehicles
	•	cpt_portal_login
	•	cpt_portal_register
	•	cpt_portal_password_reset_request
	•	cpt_portal_password_reset_confirm
	•	cpt_portal_logout
	•	cpt_portal_header_search_autocomplete

Ergebnis:
	•	Alt-Routen -> neue React-Screens / API-Endpunkte / Shell-Zustaendigkeiten

⸻

Task 3.2 – React-Routing fuer das komplette Portal aufsetzen

Definiere, wie Symfony-Routing und React-Routing fuer portal/chiptuningfile zusammenspielen.

Beachten:
	•	direkte URL-Aufrufe muessen funktionieren
	•	Public-Screens muessen separat sauber funktionieren
	•	eingeloggte Screens muessen innerhalb des Portal-Layouts funktionieren
	•	Sonderfall cpt_portal_order mit horizontaler Navigation beruecksichtigen

Ergebnis:
	•	stabiles Routing-Konzept fuer das komplette Portal

⸻

Task 3.3 – Security und Runtime-Checks fuer alle Bereiche erhalten

Stelle sicher, dass die bisherige Zugriffskontrolle nicht umgangen wird.

Beachten:
	•	authentifizierter PortalCustomerUser
	•	bestehende Security des Portals
	•	requirePortalSystem()
	•	requirePortalModule(...)
	•	requireRegistrationEnabled(...)
	•	korrektes Verhalten fuer Login/Logout/Register/Password-Reset

Ergebnis:
	•	das neue React-Portal ist genauso geschuetzt und freischaltbar wie das bestehende Twig-Portal

⸻

PHASE 4 – Datenanbindung

Task 4.1 – Datenquellen aller Portal-Seiten erfassen

Analysiere fuer jede Seite, welche Daten aktuell aus Controller, Services und Forms kommen.

Abzudecken:
	•	Dashboard
	•	Uploads
	•	Credits
	•	Profil
	•	Fahrzeuge
	•	Auftrag/Wizard
	•	Login
	•	Registrierung
	•	Passwort-Reset-Request
	•	Passwort-Reset-Confirm

Ergebnis:
	•	Mapping Alt-Daten -> React-Datenmodell pro Screen

⸻

Task 4.2 – API-Schicht fuer das komplette Portal vorbereiten

Wo noch keine saubere API existiert, sollen gezielte JSON-Endpunkte fuer das React-Portal geschaffen werden.

Mindestens vorbereiten/pruefen:
	•	Dashboard-Daten
	•	Upload-Listen und Upload-Erstellung
	•	Credit-Pakete, Transaktionen und PayPal-Flow
	•	Profil lesen/speichern
	•	Fahrzeugsuche
	•	Wizard-Daten fuer den Auftragsbereich
	•	Registrierung
	•	Passwort-Reset-Request
	•	Passwort-Reset-Confirm
	•	Header-Suche/Autocomplete

Wichtig:
	•	bestehende Fachlogik weiterverwenden
	•	JSON Responses mit sauberen Statuscodes
	•	keine Umgehung bestehender Security-/Runtime-Checks
	•	CSRF/Session/Validation sauber behandeln

Ergebnis:
	•	React-Frontend bekommt stabile Daten- und Aktions-Endpunkte fuer das komplette Portal

⸻

Task 4.3 – Frontend-Service-Layer fuer das komplette Portal bauen

Im React-Frontend einen sauberen API-Layer anlegen.

Anforderungen:
	•	zentrale API-Basis
	•	getrennte Services pro Modul, z. B.:
	•	authService
	•	dashboardService
	•	uploadService
	•	creditsService
	•	profileService
	•	vehicleService
	•	orderService
	•	searchService
	•	Fehlerbehandlung
	•	Ladezustand
	•	Session-basierte Requests sauber unterstuetzen

Ergebnis:
	•	wiederverwendbare API-Kommunikation fuer das komplette Portal

⸻

PHASE 5 – UI-Integration

Task 5.1 – Portal-Designsystem aus dem bestehenden Layout ableiten

Das neue Emergent-Portal soll visuell aus portal/chiptuningfile abgeleitet werden.

Pruefen:
	•	Farben
	•	Abstaende
	•	Typografie
	•	Karten / Tabellen / Formulare
	•	Light/Dark-Verhalten
	•	Header-/Sidebar-/Navigation-Logik
	•	Unterschiede zwischen Public- und Authenticated-Screens
	•	Sonderlayout fuer den Auftrags-Wizard

Ergebnis:
	•	Design- und Layout-Regeln fuer das neue Portal-Frontend

⸻

Task 5.2 – Portal-Layouts im React-Frontend bauen

Baue im React-Frontend getrennte Layouts fuer:
	•	PublicPortalLayout
	•	AuthenticatedPortalLayout
	•	optional OrderWizardLayout, falls die horizontale Navigation separat behandelt werden muss

Ergebnis:
	•	saubere Layout-Basis fuer alle Portal-Seiten

⸻

Task 5.3 – Wiederverwendbare Portal-Komponenten bauen

Baue wiederverwendbare UI-Komponenten fuer das komplette Portal.

Beispiele:
	•	PortalHeader
	•	PortalSidebar
	•	PortalTopbar
	•	PortalPageHeader
	•	PortalCard
	•	PortalDataTable
	•	PortalFormSection
	•	LoadingState
	•	EmptyState
	•	ErrorState
	•	SearchAutocomplete

Ergebnis:
	•	erweiterbare Komponentenbasis fuer alle Screens

⸻

PHASE 6 – Schrittweise Migration der Seiten

Task 6.1 – Public-Screens migrieren

Migriere zuerst die oeffentlichen Screens in React:
	•	Login
	•	Registrierung
	•	Passwort-Reset-Request
	•	Passwort-Reset-Confirm

Ziel:
	•	bestehende Symfony-Security und Form-/Service-Logik beibehalten
	•	React uebernimmt die UI
	•	Fehlermeldungen, Validation und Redirects sauber nachbilden

Ergebnis:
	•	oeffentlicher Einstieg ins Portal laeuft in React

⸻

Task 6.2 – Authenticated-Screens migrieren

Migriere danach die eingeloggten Screens in sinnvoller Reihenfolge:
	1.	Dashboard
	2.	Profil
	3.	Fahrzeuge
	4.	Uploads
	5.	Credits
	6.	Auftrags-Wizard

Fuer jede Seite:
	•	Altstruktur analysieren
	•	Datenquellen und Aktionen erfassen
	•	API bauen/anpassen
	•	React-Screen umsetzen
	•	Route/Shell anbinden
	•	Testen

Ergebnis:
	•	kompletter eingeloggter Portalbereich wird schrittweise in React ueberfuehrt

⸻

Task 6.3 – Alte Twig-Views kontrolliert abbauen

Sobald ein Bereich stabil in React laeuft:
	•	pruefen, welche Teile der alten Twig-Views nur noch als Host gebraucht werden
	•	Templates kontrolliert auf Shell-/Bridge-Funktion reduzieren
	•	nichts loeschen, bevor Route, Security, Runtime-Checks und Datenfluss stabil sind

Wichtig:
	•	base.html.twig darf erst spaeter reduziert werden, wenn Public- und Authenticated-Area sicher migriert sind

⸻

PHASE 7 – Auth, Sicherheit, Sessions

Task 7.1 – Dashboard mit bestehender Session testen

Pruefen, ob das React-Dashboard sauber mit der bestehenden Symfony-Authentifizierung arbeitet.

Pruefen:
	•	eingeloggter Zugriff funktioniert
	•	API-Calls im eingeloggten Zustand funktionieren
	•	Redirect bei Session-Ablauf
	•	kein Sicherheitsloch durch neue JSON-Endpunkte

Ergebnis:
	•	sicheres Verhalten des neuen Dashboards

⸻

Task 7.2 – Portal-Freischaltung respektieren

Das Dashboard darf nur verfuegbar sein, wenn die bestehende Portal-Konfiguration es erlaubt.

Beachten:
	•	requirePortalSystem()
	•	requirePortalModule(...)
	•	sonstige Runtime-Checks im bestehenden Controller-/Portal-Kontext

Ergebnis:
	•	bestehende Freischaltlogik bleibt intakt

⸻

PHASE 8 – Qualität, Build und Deployment

Task 8.1 – Fehler- und Ladezustände verbessern

Für alle zentralen Screens umsetzen:
	•	Loading Spinner / Skeletons
	•	Empty States
	•	API-Fehleranzeigen
	•	Fallback-Komponenten

Ergebnis:
	•	Bessere UX und stabileres Frontend

⸻

Task 8.2 – Build in Deployment integrieren

Sicherstellen, dass das React-Frontend im Deployment-Prozess korrekt gebaut wird.

Prüfen:
	•	Build vor Deploy oder im Deploy
	•	Ausgabe ins richtige Verzeichnis
	•	Cache Busting / Manifest
	•	Symfony referenziert aktuelle Assets

Ergebnis:
	•	Produktionssicherer Build-Prozess

⸻

Task 8.3 – Entwickler-Dokumentation erstellen

Dokumentiere kurz:
	•	wo das React-Frontend liegt
	•	wie man lokal startet
	•	wie Build funktioniert
	•	wie Routing funktioniert
	•	wie neue Seiten ergänzt werden
	•	wie Symfony-API und React zusammenspielen

Ergebnis:
	•	Wartbare Übergabe für spätere Weiterentwicklung

⸻

PHASE 9 – Cleanup

Task 9.1 – Alte Asset-Reste prüfen

Prüfen, welche alten CSS/JS/Twig-Abhängigkeiten im Kundenportal nicht mehr benötigt werden.

Ergebnis:
	•	unnötige Altlasten identifiziert

⸻

Task 9.2 – Doppelte Logik vermeiden

Prüfen, ob Logik aktuell doppelt existiert:
	•	einmal in Symfony/Twig
	•	einmal in React

Ziel:
	•	Geschäftslogik ins Backend
	•	UI-Logik ins Frontend

Ergebnis:
	•	klarere Verantwortlichkeiten

⸻

Task 9.3 – Finale Integrationsprüfung

Am Ende komplett testen:
	•	Login
	•	Navigation
	•	Reload auf Unterseiten
	•	Direktaufrufe von Deep Links
	•	API Calls
	•	Logout
	•	Mobile Darstellung
	•	Fehlerfälle

Ergebnis:
	•	Abnahmefähiger Stand

⸻

Konkrete Deliverables

Codex soll am Ende idealerweise Folgendes liefern:
	1.	Saubere Emergent-Integration fuer das komplette Portal portal/chiptuningfile
	2.	Routing- und Shell-Konzept fuer Public- und Authenticated-Area
	3.	React-Layouts fuer Public, Authenticated und optional Order-Wizard
	4.	API-Endpunkte bzw. Adapter fuer alle Portal-Module
	5.	Migrierte Screens fuer Login, Registrierung, Passwort-Reset, Dashboard, Profil, Fahrzeuge, Uploads, Credits und Order-Wizard
	6.	Build-Integration fuer das gesamte Portal-Frontend
	7.	Dokumentation zur Architektur und Nutzung

⸻

Technische Leitplanken

Bitte vermeiden
	•	Bestehende produktive Twig-Dateien hart überschreiben
	•	React und alte Symfony-UI chaotisch vermischen
	•	Backend-Logik ins Frontend kopieren
	•	ungesicherte JSON-Endpunkte bauen
	•	Big-Bang-Migration ohne Zwischenstände

Bitte bevorzugen
	•	schrittweise Migration
	•	klare Trennung von Backend und Frontend
	•	wiederverwendbare React-Komponenten
	•	stabile Symfony-API-Endpunkte
	•	saubere Security und Routing-Bridge

⸻

Optional: sinnvolle Zielstruktur

project-root/
├── src/
│   └── Portals/
│       └── Chiptuningfile/
│           ├── Controller/
│           ├── Api/
│           └── Service/
├── templates/
│   └── portal/
│       └── chiptuningfile/
│           ├── base.html.twig
│           ├── dashboard/
│           ├── upload/
│           ├── credits/
│           ├── order/
│           ├── profile/
│           ├── vehicle/
│           └── security/
├── frontend/
│   └── portal-chiptuningfile/
│       ├── src/
│       │   ├── components/
│       │   ├── layouts/
│       │   ├── pages/
│       │   ├── router/
│       │   ├── services/
│       │   ├── forms/
│       │   └── main.jsx
│       ├── package.json
│       └── vite.config.js
└── public/
    └── build/
        └── portal-chiptuningfile/


⸻

Erste empfohlene Umsetzungsschritte fuer Codex
	1.	gesamten Bereich portal/chiptuningfile analysieren
	2.	base.html.twig technisch zerlegen
	3.	alle Controller, Templates, Services und FormTypes mappen
	4.	Emergent-Frontend analysieren
	5.	Zielstruktur fuer das komplette Portal-Frontend anlegen
	6.	Build-Ausgabe fuer Symfony definieren
	7.	React-Shells/Layouts fuer Public und Authenticated bauen
	8.	API-/Adapter-Schicht fuer alle Module vorbereiten
	9.	Public-Screens migrieren
	10.	Dashboard migrieren
	11.	Profil und Fahrzeuge migrieren
	12.	Uploads und Credits migrieren
	13.	Order-Wizard migrieren
	14.	alte Twig-Views auf Host-/Bridge-Funktion reduzieren
	15.	Session, Runtime-Checks und Zugriffsschutz pruefen
	16.	Doku fuer die komplette Portal-Migration ergaenzen

⸻

Definition of Done

Das Task-Paket ist fertig, wenn:
	•	das komplette Portal portal/chiptuningfile im neuen Emergent-Frontend abbildbar ist
	•	Public-Screens und eingeloggte Screens in React laufen
	•	bestehende Symfony-Security, Runtime-Checks und Business-Logik weiterverwendet werden
	•	Dashboard, Uploads, Credits, Profil, Fahrzeuge und Order-Wizard ueber React nutzbar sind
	•	Login, Registrierung und Passwort-Reset im neuen Frontend funktionieren
	•	Header-Suche und PayPal-Flow korrekt weiterarbeiten
	•	direkte URL-Aufrufe funktionieren
	•	Build und Deployment nachvollziehbar dokumentiert sind

⸻

Hinweis an Codex

Arbeite pragmatisch, aber sauber.
Nicht alles auf einmal umbauen.
Zuerst die tragfähige Integrationsbasis schaffen, danach Seite für Seite migrieren.
Bei Unsicherheiten immer die Lösung bevorzugen, die langfristig wartbar ist und Symfony/React klar trennt.

⸻

UMSETZUNGSVERSION FUER CODEX (direkt ausfuehrbar)

Ziel dieser Version

Diese Version ist operativ formuliert. Codex soll das komplette Portal portal/chiptuningfile schrittweise von Twig auf Emergent/React umstellen, ohne Symfony-Backend, Security, Services und Runtime-Checks zu zerstoeren.

⸻

Task 01 – Ist-Zustand 1:1 gegen den Code verifizieren

Ziel: Die gelieferte Portal-Doku technisch gegen den echten Code abgleichen.

Codex soll:
	•	alle genannten Templates verifizieren
	•	alle genannten Controller verifizieren
	•	alle genannten Routen verifizieren
	•	pruefen, ob Route-Pfade, Namen und Templates exakt passen
	•	Abweichungen dokumentieren

Erwartetes Ergebnis:
	•	verifizierte Ist-Aufnahme als Basis fuer die Migration

⸻

Task 02 – Vollstaendiges Seiteninventar erstellen

Ziel: Jede Portal-Seite als eigene Migrationseinheit definieren.

Seiteninventar:
	•	Login
	•	Registrierung
	•	Passwort-Reset-Request
	•	Passwort-Reset-Confirm
	•	Dashboard
	•	Uploads
	•	Credits
	•	Profil
	•	Fahrzeuge
	•	Auftrag/Wizard

Pro Seite erfassen:
	•	Route
	•	Controller
	•	Template
	•	Datenquellen
	•	FormType
	•	Service
	•	Security/Runtime-Checks
	•	UI-Besonderheiten

Erwartetes Ergebnis:
	•	vollstaendiges Migrationsinventar

⸻

Task 03 – base.html.twig in Architekturbausteine zerlegen

Ziel: Verstehen, was fuer die React-Migration uebernommen werden muss.

Codex soll analysieren:
	•	Public-vs-Authenticated-Verhalten
	•	Theme-Logik
	•	Xintra-Assets
	•	Navigation
	•	Header-Suche
	•	JS-Initialisierung
	•	Sonderfall cpt_portal_order

Erwartetes Ergebnis:
	•	Liste der Funktionen, die im React-Portal nachgebildet oder weiterverwendet werden muessen

⸻

Task 04 – Zielstruktur fuer das neue Portal-Frontend anlegen

Ziel: Ein dediziertes Frontend fuer portal/chiptuningfile schaffen.

Bevorzugte Struktur:

frontend/portal-chiptuningfile/

Codex soll anlegen bzw. konsolidieren:
	•	src/layouts/
	•	src/pages/
	•	src/components/
	•	src/services/
	•	src/router/
	•	src/forms/

Erwartetes Ergebnis:
	•	sauberer React/Emergent-Arbeitsbereich fuer das Portal

⸻

Task 05 – Build- und Asset-Integration fuer Symfony definieren

Ziel: Symfony soll das neue Portal-Frontend ausliefern koennen.

Codex soll:
	•	Build-Ausgabe definieren, bevorzugt nach public/build/portal-chiptuningfile/
	•	Manifest aktivieren, falls sinnvoll
	•	NPM-Scripts fuer Dev/Build vereinheitlichen
	•	Asset-Einbindung in Twig/Symfony vorbereiten

Erwartetes Ergebnis:
	•	npm run build erzeugt produktionsfaehige Assets fuer das komplette Portal

⸻

Task 06 – React-Shells fuer Public und Authenticated Portal bauen

Ziel: Das komplette Portal soll ueber React geladen werden koennen.

Codex soll:
	•	Host-/Shell-Konzept fuer Public-Screens bauen
	•	Host-/Shell-Konzept fuer eingeloggte Screens bauen
	•	optional separates Shell-/Layout-Verhalten fuer cpt_portal_order beruecksichtigen

Erwartetes Ergebnis:
	•	React kann alle Portal-Screens sauber hosten

⸻

Task 07 – React-Routing fuer alle Portal-Screens aufsetzen

Ziel: Alle Bereiche des Portals in React abbilden.

Abzudecken:
	•	Login
	•	Registrierung
	•	Passwort-Reset-Request
	•	Passwort-Reset-Confirm
	•	Dashboard
	•	Uploads
	•	Credits
	•	Profil
	•	Fahrzeuge
	•	Auftrag/Wizard

Erwartetes Ergebnis:
	•	stabiles Routing fuer das komplette Portal

⸻

Task 08 – API-/Adapter-Schicht fuer alle Module vorbereiten

Ziel: Twig-Datenuebergaben durch React-faehige Endpunkte/Adapter ersetzen.

Mindestens abzudecken:
	•	Auth/Login-Status
	•	Registrierung
	•	Passwort-Reset
	•	Dashboard-Daten
	•	Upload-Liste + Upload-Erstellung
	•	Credit-Pakete + Transaktionen + PayPal-Order/Capture
	•	Profil lesen/speichern
	•	Fahrzeugsuche
	•	Wizard-Daten / Wizard-Aktionen
	•	Header-Suche-Autocomplete

Erwartetes Ergebnis:
	•	stabile JSON-/Action-Schnittstellen fuer alle Portalmodule

⸻

Task 09 – Public-Screens in React umsetzen

Ziel: Oeffentlicher Einstieg ins Portal wird in React nachgebaut.

Umsetzen:
	•	Login
	•	Registrierung
	•	Passwort-Reset-Request
	•	Passwort-Reset-Confirm

Wichtig:
	•	Symfony-Security und existierende Services/Formvalidierung weiterverwenden

Erwartetes Ergebnis:
	•	kompletter Public-Einstieg funktioniert im neuen Frontend

⸻

Task 10 – Dashboard in React umsetzen

Ziel: Startseite des eingeloggten Portals modernisieren.

Datenbasis:
	•	PortalDashboardBuilder
	•	bisherige Dashboard-Template-Daten

Erwartetes Ergebnis:
	•	Dashboard laeuft vollstaendig im neuen Portal-Frontend

⸻

Task 11 – Profil und Fahrzeuge migrieren

Ziel: Kernseiten fuer Kundenbereich als naechstes migrieren.

Umsetzen:
	•	Profilseite mit ProfileFormType / passendem API-Adapter
	•	Fahrzeugsuche mit PortalVehicleDatabaseService

Erwartetes Ergebnis:
	•	Profil und Fahrzeugansicht laufen in React

⸻

Task 12 – Uploads migrieren

Ziel: Datei-Upload-Bereich in React abbilden.

Beachten:
	•	FileUploadFormType
	•	PortalUploadJobRepository
	•	FileUploadPortalService
	•	Anzeige juengster Upload-Jobs

Erwartetes Ergebnis:
	•	Upload-Bereich laeuft im neuen Frontend

⸻

Task 13 – Credits migrieren

Ziel: Credit-Kauf und Historie in React abbilden.

Beachten:
	•	PackageCards / Transaktionen
	•	CreditPackagePricingCalculator
	•	PaypalOrderService
	•	PortalCreditTopupService
	•	bestehende Order/Capture-Routen

Erwartetes Ergebnis:
	•	Credits-Bereich inkl. PayPal-Flow laeuft im neuen Frontend

⸻

Task 14 – Auftrags-Wizard migrieren

Ziel: Bestehende Wizard-Struktur in React uebernehmen und fuer spaetere Fachlogik vorbereiten.

Beachten:
	•	aktueller Dummy-/Vorbereitungsstatus
	•	OrderDummyUploadType
	•	horizontale Navigationslogik
	•	Schrittlogik / Progress / Navigation

Erwartetes Ergebnis:
	•	Wizard-UI laeuft in React und bleibt fuer spaetere Produktivlogik erweiterbar

⸻

Task 15 – Header-Suche und Portal-Interaktion integrieren

Ziel: Bestehende Interaktionspunkte nicht verlieren.

Beachten:
	•	cpt_portal_header_search_autocomplete
	•	PortalHeaderSearchService
	•	Header-Verhalten aus dem bisherigen Portal

Erwartetes Ergebnis:
	•	globale Portalsuche funktioniert weiter im neuen Frontend

⸻

Task 16 – Alte Twig-Views kontrolliert auf Host-Funktion reduzieren

Ziel: Nach Migration sollen alte Views nicht mehr die Haupt-UI tragen.

Codex soll:
	•	pro migriertem Bereich alte Templates kontrolliert reduzieren
	•	base.html.twig erst spaet und vorsichtig reduzieren
	•	keine Datei fruehzeitig entfernen, die noch fuer Security, Assets oder Fallback benoetigt wird

Erwartetes Ergebnis:
	•	Twig bleibt nur noch, wo es als Bridge/Host/Fallback gebraucht wird

⸻

Task 17 – Vollstaendige Sicherheits- und Funktionspruefung durchfuehren

Codex soll testen/pruefen:
	•	Login
	•	Logout
	•	Registrierung
	•	Passwort-Reset
	•	Direktaufrufe aller Screens
	•	Runtime-Checks / Modul-Freischaltung
	•	Dashboard
	•	Profil
	•	Fahrzeuge
	•	Uploads
	•	Credits inkl. PayPal-Flow
	•	Order-Wizard
	•	Header-Suche

Erwartetes Ergebnis:
	•	funktional vergleichbarer oder besserer Stand gegenueber dem alten Portal

⸻

Task 18 – Doku fuer die neue Portal-Architektur erstellen

Ziel: Die Migration soll spaeter nachvollziehbar bleiben.

Dokumentieren:
	•	neue Frontend-Struktur
	•	Build-/Dev-Workflow
	•	Routing-Konzept
	•	API-/Adapter-Schicht
	•	Public-vs-Authenticated-Layouts
	•	Sonderfall Order-Wizard
	•	verbleibende Twig-Host-Dateien

Erwartetes Ergebnis:
	•	klare Entwicklerdokumentation fuer das neue Portal

⸻

Reihenfolge der tatsaechlichen Umsetzung

Codex soll nach Moeglichkeit in genau dieser Reihenfolge arbeiten:
	1.	Ist-Doku verifizieren
	2.	Seiteninventar erstellen
	3.	base.html.twig zerlegen
	4.	Emergent-Frontend analysieren
	5.	Zielstruktur anlegen
	6.	Build/Assets definieren
	7.	React-Shells bauen
	8.	API-/Adapter-Schicht vorbereiten
	9.	Public-Screens migrieren
	10.	Dashboard migrieren
	11.	Profil/Fahrzeuge migrieren
	12.	Uploads migrieren
	13.	Credits migrieren
	14.	Order-Wizard migrieren
	15.	Header-Suche integrieren
	16.	Twig reduzieren
	17.	Gesamtpruefung
	18.	Doku schreiben

⸻

Harte Regeln fuer Codex

Nicht tun
	•	keine Business-Logik aus Symfony ins Frontend kopieren
	•	keine Security-/Runtime-Checks umgehen
	•	keine PayPal-Logik neu erfinden, wenn sie bereits im Backend existiert
	•	keine globale Portalzerstoerung durch blindes Ersetzen von base.html.twig
	•	keine Big-Bang-Loeschungen alter Twig-Dateien

Stattdessen tun
	•	komplette Portalmigration, aber in kontrollierten Etappen
	•	bestehende Services/FormTypes/Controller als Fachbasis weiterverwenden
	•	Public- und Authenticated-Bereich sauber trennen
	•	React fuer UI, Symfony fuer Fachlogik
	•	nach jeder Modulmigration Funktionspruefung durchfuehren

⸻

Minimaler Soll-Zustand nach diesem Task-Paket

Mindestens dieser Stand soll erreicht werden:
	•	das komplette Portal portal/chiptuningfile ist im neuen Emergent-Frontend benutzbar
	•	Public-Screens funktionieren
	•	eingeloggte Kernseiten funktionieren
	•	Uploads, Credits, Profil, Fahrzeuge und Order-Wizard sind migriert
	•	Header-Suche funktioniert
	•	Security, Runtime-Checks und PayPal-Flow sind intakt
	•	Build ist produktionsnah ausfuehrbar
	•	Doku ist vorhanden