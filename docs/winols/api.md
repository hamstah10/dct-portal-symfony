# AutoMod Job API — Schnittstellendokumentation

# Intro

**Version:** 1.0
**Zweck:** Diese Dokumentation beschreibt die API-Spezifikation, die das TuningFiles-Portal implementiert und die von **WinOLS als zentraler Verarbeitungsinstanz** abgerufen wird. Ein neues Drittsystem, das ebenfalls Aufträge über WinOLS verarbeiten lassen möchte, muss diese API **identisch nachbauen** — so kann WinOLS ohne jede Anpassung am bestehenden Lua-Script beide Systeme bedienen.

**Referenzimplementierung:** `https://portal.tuningfiles-download.com/api/v1`

**Postman Collection:** `docs/winols/postman.collection.json`
* * *

## Kontext

WinOLS läuft als zentrale Tuning-Instanz und fragt in einem festen Intervall (alle **2 Minuten**) aktiv per Polling an, ob ein neuer Auftrag zur Verarbeitung vorliegt. Es gibt keine Push-Benachrichtigung — WinOLS initiiert jeden Abruf selbst.

Das neue System muss daher die folgenden beiden Endpunkte unter demselben Pfadschema (`/api/v1/job`) bereitstellen und dasselbe Request/Response-Format einhalten.
* * *

## Authentifizierung

Alle Endpunkte erfordern einen statischen Bearer Token im `Authorization`\-Header:

```gherkin
Authorization: Bearer <API_TOKEN>
```

Der Token wird zwischen dem neuen System und dem Betreiber der WinOLS-Instanz vorab vereinbart. Anfragen ohne gültigen Token werden mit `401 Unauthorized` abgelehnt.
* * *

## Workflow-Übersicht

```markdown
1. WinOLS → GET /api/v1/job        → Nächsten offenen Auftrag abrufen
2. WinOLS lädt die Datei von der URL in „file" herunter
3. WinOLS verarbeitet / tuned die Datei
4. WinOLS → POST /api/v1/job       → Ergebnis-Datei + Status zurückliefern
```

Das System stellt immer genau **einen** Auftrag zur Zeit bereit (der älteste zuerst).
* * *

## Endpunkte

### 1\. Auftrag abrufen

```sql
GET /api/v1/job
```

Liefert den nächsten zu verarbeitenden Auftrag. Wenn kein Auftrag vorliegt, ist `order` gleich `null`.

#### Request

Kein Body erforderlich.

**Header:**

```gherkin
Authorization: Bearer <API_TOKEN>
```

#### Response `200 OK`

```json
{
  "status": true,
  "order": {
    "ulid": "01HXYZ1234567890ABCDEF",
    "file_name": "auftrag_12345.bin",
    "file": "https://portal.tuningfiles-download.com/storage/tmp/decode/auftrag_12345.bin",
    "auto_mod_status": 2,
    "modification": {
      "name": "Stage 2",
      "options": ["DTC", "EGR"]
    }
  }
}
```

Wenn kein offener Auftrag vorliegt:

```json
{
  "status": true,
  "order": null
}
```

#### Felder

| Feld | Typ | Beschreibung |
| ---| ---| --- |
| `ulid` | string | Eindeutige Auftrags-ID. Wird für den Callback (POST) benötigt. |
| `file_name` | string | Dateiname der zu verarbeitenden Datei. |
| `file` | string (URL) | Vollständige URL zum Herunterladen der Quelldatei. Die Datei liegt im Zwischenspeicher des Portals. |
| `auto_mod_status` | integer | Aktueller Status. Ist bei Abruf immer `2` (entschlüsselt, bereit zur Verarbeitung). |
| `modification.name` | string | Gewünschte Modifikationsstufe, z.B. `"Stage 1"`, `"Stage 2"`, `"Stage 1 + DTC"`. Eco-/Truck-Eco-Aufträge werden systemseitig auf `"Stage 1"` gemappt. |
| `modification.options` | array | Zusätzliche explizite Optionen als Slugs, z.B. `["DTC", "EGR"]`. Kann leer sein. Vmax wird nie in diesem Array übergeben — WinOLS behandelt Vmax als implizit aktiv für alle Aufträge mit einer echten Modifikation. Das neue System muss dieses Verhalten identisch abbilden und Vmax ebenfalls weglassen. |

* * *

### 2\. Ergebnis zurückliefern

```plain
POST /api/v1/job
```

Liefert die fertig verarbeitete Datei zurück und setzt den Auftragsstatus.

#### Request

**Content-Type:** `multipart/form-data`

**Header:**

```gherkin
Authorization: Bearer <API_TOKEN>
```

**Body-Parameter:**

| Parameter | Typ | Pflicht | Beschreibung |
| ---| ---| ---| --- |
| `ulid` | string | ✓ | Die `ulid` des Auftrags, die aus dem GET-Abruf stammt. |
| `auto_mod_status` | integer | ✓ | `3` = Verarbeitung erfolgreich, `9` = Verarbeitung fehlgeschlagen. |
| `file` | file | — | Die fertig bearbeitete Datei. Muss mitgesendet werden wenn `auto_mod_status = 3`. |
| `winols_id` | string | — | Optionale interne ID des Dienstleisters (für Nachverfolgung). |

#### Beispiel: Erfolg

```gherkin
POST /api/v1/job
Authorization: Bearer <API_TOKEN>
Content-Type: multipart/form-data

ulid=01HXYZ1234567890ABCDEF
auto_mod_status=3
file=<binäre Datei>
```

#### Beispiel: Fehler

```haskell
POST /api/v1/job
Authorization: Bearer <API_TOKEN>
Content-Type: multipart/form-data

ulid=01HXYZ1234567890ABCDEF
auto_mod_status=9
```

#### Response `200 OK`

```json
{
  "status": true,
  "message": "Order updated successfully."
}
```

#### Response Fehler

```json
{
  "status": false,
  "message": "Fehlerbeschreibung"
}
```

* * *

## Status-Codes (auto\_mod\_status)

| Wert | Bezeichnung | Bedeutung |
| ---| ---| --- |
| `0` | UNAVAILABLE | Auftrag nicht für AutoMod vorgesehen |
| `1` | PENDING | Wartet auf Entschlüsselung |
| `2` | DECRYPTED | Bereit zur Verarbeitung — wird über GET geliefert |
| `3` | ENCRYPTED | Ergebnis eingegangen, wird vom Portal finalisiert |
| `4` | COMPLETED | Vollständig abgeschlossen |
| `9` | FAILED | Verarbeitung fehlgeschlagen |

* * *

## Verarbeitungslogik

*   Der externe Dienstleister fragt `GET /api/v1/job` regelmäßig ab.
*   Liefert `order = null` → kein offener Auftrag, erneut nach Intervall anfragen.
*   Liefert `order != null` → Datei herunterladen, tunen, Ergebnis via `POST /api/v1/job` zurücksenden.
*   Bei `auto_mod_status = 9` (Fehler) wird keine Datei erwartet. Das Portal benachrichtigt den Administrator per E-Mail.
*   Bei `auto_mod_status = 3` (Erfolg) übernimmt das Portal die weitere Verarbeitung und Zustellung an den Endkunden automatisch.
* * *

## Hinweise zur Implementierung

*   **Polling:** WinOLS fragt alle **2 Minuten** aktiv an. Das neue System muss keine eigene Polling-Logik implementieren — es muss nur die API bereitstellen und auf Anfragen von WinOLS antworten.
*   **Genau ein Auftrag gleichzeitig:** Das System darf immer nur einen Auftrag zurückliefern (den ältesten offenen). WinOLS verarbeitet sequenziell.
*   **Dateiformat:** Die Quelldatei ist eine `.bin`\-Datei (bereits entschlüsselt). Das Format der zurückgelieferten Ergebnis-Datei muss vorab mit dem Betreiber der WinOLS-Instanz abgestimmt werden.
*   **Fehlerbehandlung:** Wenn kein Auftrag vorliegt, muss `order: null` zurückgegeben werden — kein HTTP-Fehler. WinOLS fragt beim nächsten Intervall erneut an.
*   **Authentifizierung:** Bearer Token muss bei jedem Request geprüft werden. Ungültige Tokens → `401`.
