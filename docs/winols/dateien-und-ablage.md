# WinOLS AutoMod - Dateien und Ablageorte

Diese Datei ergaenzt `docs/winols/api.md` und `docs/winols/tasks.md`. Sie beschreibt, welche Dateien fuer die WinOLS/AutoMod-Integration gebraucht werden, welche Rolle sie haben und wo Laufzeitdateien abgelegt werden.

## API und Backend

| Datei | Zweck |
| --- | --- |
| `src/Controller/Api/AutoModJobApiController.php` | Zentrale WinOLS-API unter `GET /api/v1/job` und `POST /api/v1/job`. Prueft Bearer Token, liefert den naechsten AutoMod-Auftrag aus, materialisiert die Quelldatei und speichert Ergebnisdateien. |
| `src/Entity/Auftrag.php` | Enthaltene AutoMod-Felder und Status-Konstanten: `ulid`, `autoModEnabled`, `autoModStatus`, `winolsId`, `autoModSubmittedAt`, `autoModFailureReason`, `finishedFilePath`, `finishedFileName`, `finishedFiles`. |
| `src/Repository/AuftragRepository.php` | Liefert mit `findNextAutoModJob()` den aeltesten Auftrag mit `autoModEnabled = true` und `autoModStatus = 2`. `findByUlid()` wird fuer den WinOLS-Callback genutzt. |
| `migrations/Version20260703043000.php` | Datenbankmigration fuer die AutoMod-Spalten auf `auftrag` inkl. Unique-Index auf `ulid` und Index auf `(auto_mod_enabled, auto_mod_status)`. |
| `config/services.yaml` | Bindet `WINOLS_API_TOKEN` als `winols.api_token` und injiziert Token plus `%kernel.project_dir%` in `AutoModJobApiController`. |
| `.env.local` / Server-Env | Muss `WINOLS_API_TOKEN=<geheimer-token>` enthalten. Der Token darf nie ins Frontend oder in React-Bundles. |

## Portal-Auftragserstellung

| Datei | Zweck |
| --- | --- |
| `src/Portals/Chiptuningfile/Controller/OrderController.php` | Speichert ChiptuningFile-Auftraege. `applyAutoModEligibility()` setzt AutoMod auf aktiv, erzeugt `ulid` und setzt `autoModStatus = 2`, wenn eine echte Base64-Datei vorhanden ist und der Auftrag nicht `autoReady` ist. |
| `src/Portals/No1tuningfiles/Controller/OrderController.php` | Gleiche Logik fuer No1TuningFiles. Muss parallel gepflegt werden, solange die Controller getrennt sind. |
| `templates/portal/chiptuningfile/order/index.html.twig` | Host-Template fuer den ChiptuningFile React-Auftragswizard. |
| `templates/portal/no1tuningfiles/order/index.html.twig` | Host-Template fuer den No1TuningFiles React-Auftragswizard. |

Wichtig: Die aktuelle Codebase hat keinen separaten Entschluesselungsworker. Die Datei liegt bereits als Base64 in `auftrag.file_data`; deshalb wird beim Submit direkt `autoModStatus = 2` gesetzt, damit WinOLS sie abholen kann.

## Dateispeicher zur Laufzeit

| Pfad | Inhalt | Wird erzeugt von |
| --- | --- | --- |
| `auftrag.file_data` in der DB | Originaldatei als Base64, aus dem Portal-Wizard. | Portal-Submit in den beiden `OrderController.php`. |
| `public/storage/tmp/decode/{ulid}.bin` | Fuer WinOLS abrufbare Quelldatei. Wird aus `auftrag.file_data` erzeugt, sobald WinOLS `GET /api/v1/job` aufruft. | `AutoModJobApiController::ensureDecodedSourceFileOnDisk()` |
| `public/uploads/auftraege/fertig/{auftrag_id}/{originalname}_mod.ext` | Fertig bearbeitete Datei aus dem WinOLS-Callback. | `AutoModJobApiController::storeResultFile()` |
| `auftrag.finished_file_path` | Relativer Pfad zur aktuellen fertigen Datei, z.B. `uploads/auftraege/fertig/123/file_mod.bin`. | `storeResultFile()` |
| `auftrag.finished_files` | Historie/Array der fertig hochgeladenen Dateien mit Pfad, Name, Groesse und Upload-Zeit. | `storeResultFile()` |

Pfadangaben in der Datenbank bleiben relativ zu `public/`. Download-Controller bauen daraus den absoluten Serverpfad.

## Downloads im Portal

| Datei | Routen |
| --- | --- |
| `src/Portals/Chiptuningfile/Controller/PortalDashboardController.php` | `/orders/{id}/download-original`, `/orders/{id}/download-modified` |
| `src/Portals/No1tuningfiles/Controller/PortalDashboardController.php` | `/orders/{id}/download-original`, `/orders/{id}/download-modified` |
| `src/Portals/Chiptuningfile/Controller/ReactNavigationController.php` | Liefert Order-Daten fuer React inkl. `modifiedDownloadUrl`. |
| `src/Portals/No1tuningfiles/Controller/ReactNavigationController.php` | Gleiche Aufgabe fuer No1TuningFiles. |

Originaldownloads lesen aus `auftrag.file_data`. Modifizierte Downloads lesen aus `auftrag.finished_file_path`.

## Frontend-Quellen

Die React-Quellen liegen in den Portal-Theme-Verzeichnissen. Built Assets in `public/assets/...` sollten nicht direkt von Hand editiert werden.

| Portal | Source |
| --- | --- |
| ChiptuningFile | `templates/portal/theme_react_chiptuningfile/chiptuningfilede/frontend/src/` |
| No1TuningFiles | `templates/portal/theme_react_no1tuningfiles/no1tuningfiles/frontend/src/` |

Wichtige Dateien:

| Datei | Zweck |
| --- | --- |
| `src/pages/FileWizard.jsx` | Submit des Auftrags; liest aus der Backend-Antwort `autoModEnabled` und `autoModStatus`. |
| `src/pages/OrderLive.jsx` | Live-/Timeline-Darstellung fuer AutoMod-Status. |
| `src/pages/OrderDetail.jsx` | Detailansicht, Statusanzeige und Download-Button fuer fertige Dateien. |
| `src/pages/OrdersNew.jsx` | Auftragsliste; uebernimmt Realtime-Statusupdates. |
| `src/lib/ordersBootstrap.js` | Normalisiert AutoMod-Daten und mappt Statuswerte fuer React. |

## Realtime und Benachrichtigung

| Datei | Zweck |
| --- | --- |
| `src/Controller/Api/AutoModJobApiController.php` | Publiziert nach Erfolg/Fehler ein Realtime-Event mit `kind = order.status_changed`. |
| `src/Service/Realtime/PortalRealtimePublisher.php` | Verteilung der Portal-Realtime-Events. |
| `src/Service/Realtime/PortalMercureTopics.php` | Topic-Namen, insbesondere kundenbezogene Order-Topics. |
| `.env.local` / Server-Env | `PORTAL_MAILER_FROM` und `PORTAL_REGISTRATION_NOTIFY_TO` werden fuer Fehlermails genutzt. |

Bei `auto_mod_status = 9` sendet das Backend eine Admin-Mail und setzt den Auftrag auf fehlgeschlagen.

## WinOLS-Endpunkte

WinOLS spricht nur diese API an:

```text
GET  /api/v1/job
POST /api/v1/job
Authorization: Bearer <WINOLS_API_TOKEN>
```

GET liefert maximal einen Auftrag. POST erwartet `multipart/form-data` mit:

```text
ulid=<auftrag-ulid>
auto_mod_status=3|9
winols_id=<optional>
file=<pflicht bei auto_mod_status=3>
```

## Statuswerte

| Wert | Bedeutung | Ablage/Anzeige |
| --- | --- | --- |
| `0` | Nicht fuer AutoMod vorgesehen | Auftrag bleibt normal/manuell. |
| `1` | Wartet auf Entschluesselung | In dieser Codebase aktuell nicht aktiv genutzt. |
| `2` | Bereit fuer WinOLS | Auftrag wird von `findNextAutoModJob()` gefunden. |
| `3` | Ergebnis eingegangen | Laut API-Doku Zwischenstatus; Controller setzt aktuell direkt auf `4`. |
| `4` | Abgeschlossen | Fertige Datei liegt unter `public/uploads/auftraege/fertig/...`. |
| `9` | Fehlgeschlagen | Fehleranzeige im Portal und Admin-Mail. |

## Mindest-Checkliste fuer neue Umgebung

1. Migration `Version20260703043000` ausfuehren.
2. `WINOLS_API_TOKEN` serverseitig setzen.
3. Webserver muss Dateien unter `public/storage/tmp/decode/` fuer WinOLS erreichbar machen.
4. Webserver/PHP-Prozess braucht Schreibrechte auf:
   - `public/storage/tmp/decode/`
   - `public/uploads/auftraege/fertig/`
5. Portal-Submit muss `fileData`, `fileName`, `fileSize`, Stage und Optionen an `/auftrag/submit` senden.
6. React-Frontend neu bauen, wenn Dateien unter `templates/portal/theme_react_*/.../frontend/src/` geaendert wurden.
7. Mit `docs/winols/postman.collection.json` testen:
   - `GET /api/v1/job` ohne Token -> `401`
   - `GET /api/v1/job` mit Token -> `order` oder `null`
   - `POST /api/v1/job` mit Status `3` und Datei -> Auftrag bekommt fertige Datei
   - `POST /api/v1/job` mit Status `9` -> Auftrag wird als fehlgeschlagen markiert

