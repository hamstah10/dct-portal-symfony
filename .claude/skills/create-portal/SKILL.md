---
name: create-portal
description: Erstellt ein neues TFD-Kundenportal aus dem bestehenden Chiptuningfile-Portal, nachdem der Portal-Datensatz in EasyAdmin angelegt wurde. Verwenden bei Aufträgen wie „neues Portal erstellen“, „Chiptuningfile für einen neuen Anbieter kopieren“ oder „Portal anhand des portalKey aufbauen und anpassen“.
---

# Neues TFD-Portal erstellen

Den Portal-Key oder die Portal-ID aus der Benutzeranfrage verwenden. Fehlt beides, genau danach fragen.

Die vorhandene `App\Entity\Portal` als einzige Quelle für Mandantenkonfiguration behandeln. Keine zweite YAML-, JSON- oder PHP-Konfiguration für Werte anlegen, die bereits in der Entity gespeichert sind.

Vor Beginn [references/portal-model.md](references/portal-model.md) lesen.

## 1. Projekt und Vorlage prüfen

1. `CLAUDE.md`, Portal-Dokumentation und `git status --short` prüfen.
2. Diese Bereiche untersuchen:
   - `src/Entity/Portal.php`
   - `src/Repository/PortalRepository.php`
   - `src/Controller/Admin/PortalCrudController.php`
   - `src/Portals/Chiptuningfile/`
   - `src/Websites/Chiptuningfile/`
   - `templates/portal/theme_react_chiptuningfile/chiptuningfilede/`
   - `public/assets/portals/chiptuningfile/react/`
   - Portal-Routen, Request-Matcher, Firewalls und Services
3. Mit `rg` Referenzen auf Chiptuningfile, Portal-Hosts und Portal-Keys erfassen.
4. Die vorhandene React-Build-Anleitung lesen.
5. Fremde Änderungen niemals überschreiben.

## 2. Portal-Datensatz auflösen

Den Datensatz bevorzugt über die bestehende Symfony-Anwendung und `PortalRepository` lesen. Keine Produktionsdaten verändern.

Mindestens erfassen:

- `id`, `portalKey`, `name`, `portalType`
- `firma`, `vehicleDataPool`, `zahlungsarten`
- `portalHost`, `websiteHost`
- `active`, `portalEnabled`, `websiteEnabled`, `customerRegistrationEnabled`
- `maintenanceMode`, `creditPrice`, `enabledModules`
- `themePreset`, `customDesignEnabled`
- Farben, Logo und Schriften

Portal-Key, Name und Hosts müssen eindeutig sein. Bei fehlendem Datensatz oder kritischen Angaben stoppen.

Vor Änderungen Quelle, Ziel, Portal-Key, Hosts, Firma, Datenpool, Module und Theme zusammenfassen.

## 3. Architektur bestimmen

Zuerst prüfen, ob hostbasierte Portalauflösung und gemeinsame Komponenten ohne vollständige Kopie ausreichen. Gemeinsamen Code bevorzugen, sofern keine Funktion oder Gestaltung verloren geht.

Eine eigene Kopie nur erstellen bei:

- eigenem Routing oder Firewall-Kontext
- abweichender Geschäftslogik
- eigenständigem React-Bundle
- portalspezifischen Seiten oder Abläufen
- ausdrücklich gewünschter getrennter Weiterentwicklung

Die Entscheidung kurz begründen.

## 4. Kopierplan erstellen

Vor dem Kopieren eine exakte Quell-/Zielliste erstellen.

Zielnamen aus `portalKey` ableiten:

- PHP-Namespace: PascalCase
- Asset-Verzeichnis: normalisierter `portalKey`
- sichtbarer Name: ausschließlich `Portal.name`

Nicht kopieren: `.env*`, Schlüssel, Tokens, `vendor/`, `node_modules/`, Cache, Logs, Uploads, Kundendaten, Datenbank-Dumps und reproduzierbare Build-Ausgaben. Existierende Ziele nicht ungefragt überschreiben.

## 5. Portal implementieren

Die Vorlage strukturerhaltend übernehmen und nur eindeutig portalspezifische Namen ändern. Keine globale Ersetzung kurzer Markenbegriffe durchführen.

Bei einer eigenen Kopie mindestens anpassen:

1. PHP-Namespace unter `src/Portals/<PortalName>/`
2. Controller, Services, Request-Matcher und Routen
3. Firewall und Access-Control
4. Twig-Hosttemplate und React-Frontend
5. Build-Ziel `public/assets/portals/<portalKey>/react/`
6. Website nur bei `websiteEnabled=true` und Modul `public_website`
7. Datenpool über `Portal.vehicleDataPool` und bestehende Proxy-Logik
8. Firma, Zahlungsarten und Credits aus dem Portal-Datensatz
9. Registrierung nur bei aktivierter Einstellung und Modul
10. Wartungsmodus, Aktiv-Flags und Module serverseitig durchsetzen

Design nicht duplizieren. Für Name, Logo, Farben und Schriften `Portal::getResolvedDesign()` oder den vorhandenen Resolver verwenden.

Hosts über `findOneByPortalHost()`, `findOneByWebsiteHost()` oder `findOneByKnownHost()` auflösen. Keine Hosts fest in Controllern verteilen.

## 6. Sicherheit

- Mandantentrennung bei jedem Zugriff prüfen.
- Benutzer, Aufträge, Tickets, Credits, Uploads und Fahrzeugdaten auf Portal/Firma begrenzen.
- Bei unbekanntem Host niemals auf ein anderes Portal zurückfallen.
- Inaktive Hosts kontrolliert ablehnen.
- Secrets nur über vorhandene Umgebungsvariablen verwenden.
- Keine Migration, kein Deployment und keinen Commit ohne ausdrücklichen Auftrag ausführen.

## 7. Verifizieren

Nur vorhandene Befehle ausführen, mindestens:

```bash
php bin/console lint:yaml config
php bin/console lint:twig templates
php bin/console cache:clear
php bin/console debug:router
```

Den vorhandenen React-Build für das Zielportal ausführen. Danach PHP-Syntax, Container, Routen, Hostauflösung, Login, Registrierung, Module, Theme, Datenpool, Firma und Mandantentrennung prüfen.

Abschließend in allen neuen Portaldateien nach `chiptuningfile` und alten Hosts suchen und jeden Treffer bewerten. Keine Secrets oder Abhängigkeiten im Diff zulassen.

## 8. Abschluss

Kompakt berichten:

- verwendeter Portal-Datensatz
- gemeinsame Engine oder eigene Kopie
- neue und geänderte Dateien
- dynamisch aus `Portal` bezogene Werte
- Testergebnisse
- begründete verbleibende Referenzen
- fehlende Assets und manuelle Schritte
- nicht ausgeführte Migrationen, Builds oder Deployments
