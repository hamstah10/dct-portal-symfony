# Portal-Modell und bestehende Architektur

## Zentrale Dateien

- `src/Entity/Portal.php`
- `src/Repository/PortalRepository.php`
- `src/Controller/Admin/PortalCrudController.php`
- `src/Portals/Chiptuningfile/`
- `src/Websites/Chiptuningfile/`
- `templates/portal/theme_react_chiptuningfile/chiptuningfilede/frontend/`
- `templates/portal/theme_react_chiptuningfile/chiptuningfilede/backend/`
- `public/assets/portals/chiptuningfile/react/`

## Identität und Zuordnung

| Feld | Bedeutung |
|---|---|
| `portalKey` | stabiler, eindeutiger technischer Schlüssel |
| `name` | sichtbarer Portalname |
| `portalType` | Kundenportal, Brand Suite, Marketing-Website oder Superadmin-Portal |
| `firma` | organisatorische Mandantenzuordnung |
| `vehicleDataPool` | direkter Fahrzeugdaten-Pool des Portals |
| `portalHost` | Hostname des Kundenportals ohne Protokoll |
| `websiteHost` | Hostname der öffentlichen Website ohne Protokoll |

Hosts werden in der Entity normalisiert. Das Repository stellt `findOneByPortalHost()`, `findOneByWebsiteHost()` und `findOneByKnownHost()` bereit.

## Aktivierung

- `active`
- `maintenanceMode`
- `portalEnabled`
- `websiteEnabled`
- `customerRegistrationEnabled`
- `enabledModules`

Verfügbare Kernmodule sind Dashboard, Profil, Auftrag, Uploads, Fahrzeugdatenbank, Credits, Registrierung und öffentliche Website. Zusätzlich existieren Operations-Module. Vorhandene Konstanten und `Portal::getModuleChoices()` verwenden, keine zweite Modulliste anlegen.

## Design

- `themePreset`
- `customDesignEnabled`
- `primaryColor`
- `secondaryColor`
- `menuBackgroundColor`
- `contentBackgroundColor`
- `logoPath`
- `headingFont`
- `bodyFont`
- `dataFont`

`Portal::getResolvedDesign()` kombiniert Theme-Preset, individuelle Werte, Portalname und Logo. Diese Methode oder einen vorhandenen Design-Resolver verwenden. Designwerte nicht als zweite Quelle in kopierten Frontend-Dateien festschreiben.

## Geschäftsparameter

- `zahlungsarten`
- `creditPrice`
- `description`
- `sortOrder`

## Datenpool

Das Chiptuningfile-System verwendet `Portal.vehicleDataPool`. `VehicleApiProxy` ergänzt den Pool-Key aus dem Portal. Dieses Verhalten beibehalten und keinen allgemeinen `VehicleDataContext` erzwingen, wenn die Portalarchitektur den direkten Pool verwendet.

## Vorlage

Das Chiptuningfile-Portal besteht aus Symfony-Portalcode, Backend-Hosttemplate und React-SPA. Der React-Build liegt getrennt unter `public/assets/portals/chiptuningfile/react/`. Bei einer eigenständigen Kopie Quellcode und Buildziel unterscheiden: reproduzierbare Buildausgaben nicht als Vorlage behandeln, sondern neu bauen.
