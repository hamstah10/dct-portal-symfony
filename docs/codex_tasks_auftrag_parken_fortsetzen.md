# Codex Tasks – Auftrag parken und später fortsetzen

## Ziel
Ein User soll einen begonnenen Auftrag **parken** können, um ihn später bequem weiterzuführen.

Der Flow soll dafür:
- einen neuen Status für geparkte Aufträge erhalten
- einen Button im `Neuer Auftrag`-Flow bekommen
- geparkte Aufträge später wieder öffnen und weiterführen können
- eine eigene Anzeige im **Dashboard** bekommen
- eine eigene Anzeige im Bereich **Aufträge** bekommen

---

## 1. Neuen Status für geparkte Aufträge einführen
**Task:**  
Führe einen neuen Auftragsstatus für geparkte, noch nicht final abgeschickte Aufträge ein.

**Empfohlener Status:**
- `parked`

**Alternativ möglich:**
- `draft_parked`
- `paused`

**Akzeptanzkriterien:**
- Geparkte Aufträge sind klar von:
  - final eingereichten Aufträgen
  - reinen Drafts
  - abgebrochenen Aufträgen
  unterscheidbar
- Status ist backendseitig sauber gespeichert
- Status kann in API, Datenbank und UI konsistent verwendet werden

---

## 2. Auftrag im Flow parkbar machen
**Task:**  
Baue im `Neuer Auftrag`-Flow einen Button ein, mit dem der aktuelle Auftrag geparkt werden kann.

**Vorschlag Button-Label:**
- **„Auftrag parken“**

**Platzierung:**
- sichtbar in sinnvollen Schritten des Flows
- ideal mindestens ab dem Moment, ab dem genug Daten vorhanden sind, um sinnvoll zu speichern

**Akzeptanzkriterien:**
- User kann den aktuellen Stand des Auftrags per Button speichern
- Bereits eingegebene Daten gehen nicht verloren
- Auftrag wechselt in Status `parked`
- User bekommt direkt eine Bestätigung, dass der Auftrag geparkt wurde

---

## 3. Zwischenspeichern aller relevanten Auftragsdaten
**Task:**  
Sorge dafür, dass beim Parken alle bisher eingegebenen Daten vollständig gespeichert werden.

**Akzeptanzkriterien:**
- Daten aus Schritt 1, Schritt 2 und ggf. weiteren Schritten werden übernommen
- Unvollständige, aber gültig speicherbare Daten dürfen erhalten bleiben
- Späteres Wiederöffnen lädt genau den letzten gespeicherten Stand
- Keine Datenverluste beim Parken

---

## 4. Fortsetzen eines geparkten Auftrags ermöglichen
**Task:**  
Implementiere die Möglichkeit, einen geparkten Auftrag später wieder zu öffnen und direkt weiterzuführen.

**Akzeptanzkriterien:**
- Geparkter Auftrag kann über Dashboard und Auftragsübersicht geöffnet werden
- Flow startet wieder am passenden Schritt oder am zuletzt gespeicherten Stand
- Bereits ausgefüllte Felder sind vorbelegt
- User kann normal weiterbearbeiten und final absenden

---

## 5. Resume-Logik im Flow einbauen
**Task:**  
Baue eine saubere Resume-Logik für geparkte Aufträge ein.

**Akzeptanzkriterien:**
- System erkennt, dass ein Auftrag geparkt ist
- Beim Öffnen wird nicht versehentlich ein neuer Auftrag erzeugt
- Der bestehende geparkte Auftrag wird fortgeführt
- Änderungen überschreiben den geparkten Datensatz sauber
- Finales Absenden ändert den Status korrekt von `parked` auf den finalen Zielstatus

---

## 6. Sichtbare Kennzeichnung im Flow
**Task:**  
Zeige im Flow klar an, wenn der User einen geparkten Auftrag weiterbearbeitet.

**Vorschlag UI-Hinweis:**
- „Du bearbeitest einen geparkten Auftrag.“

**Akzeptanzkriterien:**
- User erkennt sofort, dass es sich nicht um einen komplett neuen Auftrag handelt
- UI macht klar, dass bereits ein gespeicherter Zwischenstand geladen wurde

---

## 7. Dashboard um Bereich für geparkte Aufträge erweitern
**Task:**  
Ergänze das Dashboard um eine extra Anzeige für geparkte Aufträge.

**Mögliche Darstellung:**
- eigene Box/Kachel
- Liste `Geparkte Aufträge`
- Anzeige der Anzahl geparkter Aufträge

**Empfohlene Inhalte:**
- Auftragsreferenz
- Fahrzeug / Datei / Projektname, falls vorhanden
- letzter Bearbeitungsstand
- Datum der letzten Änderung
- Button **„Weiterführen“**

**Akzeptanzkriterien:**
- Geparkte Aufträge sind im Dashboard schnell sichtbar
- User kann direkt von dort in den Auftrag springen
- Anzeige ist von aktiven/finalen Aufträgen getrennt

---

## 8. Auftragsübersicht um Filter/Anzeige für geparkte Aufträge erweitern
**Task:**  
Erweitere den Bereich `Aufträge` um eine separate Anzeige oder Filtermöglichkeit für geparkte Aufträge.

**Akzeptanzkriterien:**
- Geparkte Aufträge sind in der Übersicht sichtbar
- Sie sind klar als `geparkt` gekennzeichnet
- Es gibt mindestens eine der folgenden Lösungen:
  - eigener Tab
  - eigener Status-Filter
  - eigene Sektion in der Liste
- Pro Eintrag gibt es eine Aktion **„Weiterführen“**

---

## 9. API / Backend-Endpunkte für Parken und Fortsetzen ergänzen
**Task:**  
Ergänze Backend/API um die nötigen Operationen für:
- Auftrag parken
- geparkten Auftrag laden
- geparkten Auftrag fortsetzen
- geparkten Auftrag final absenden

**Akzeptanzkriterien:**
- Statuswechsel ist serverseitig validiert
- Nur berechtigte User können ihren geparkten Auftrag sehen und fortsetzen
- Kein anderer User kann auf fremde geparkte Aufträge zugreifen
- Fortsetzen und finales Absenden funktionieren robust

---

## 10. Validierungslogik für geparkte Aufträge anpassen
**Task:**  
Passe die Validierung so an, dass ein Auftrag auch in einem unvollständigen Zustand geparkt werden darf, aber nicht final abgeschickt werden kann.

**Akzeptanzkriterien:**
- Parken erlaubt Teilstände
- Finales Absenden erfordert weiterhin vollständige Pflichtdaten
- Backend unterscheidet sauber zwischen:
  - `save as parked`
  - `submit final`

---

## 11. Button-Logik und UX sauber gestalten
**Task:**  
Baue die CTA-Logik im Flow so, dass der User klar zwischen Parken und finalem Weitergehen unterscheiden kann.

**Empfohlene Buttons:**
- `Weiter`
- `Auftrag parken`
- ggf. `Abbrechen`

**Akzeptanzkriterien:**
- Parken ist als sekundäre Aktion sauber sichtbar
- Kein versehentliches finales Absenden durch falsche Button-Priorisierung
- Nach Parken erfolgt ein sauberer Redirect:
  - ins Dashboard oder
  - in die Auftragsübersicht oder
  - auf eine Bestätigungsseite

---

## 12. Bestätigungsseite / Erfolgsmeldung nach dem Parken
**Task:**  
Zeige nach dem erfolgreichen Parken eine klare Bestätigung.

**Vorschlag Text:**
- „Dein Auftrag wurde geparkt und kann später weiterbearbeitet werden.“

**Akzeptanzkriterien:**
- User erhält direkt Feedback
- Es gibt eine direkte CTA:
  - `Zum Dashboard`
  - `Zu meinen Aufträgen`
  - `Jetzt weiterführen`

---

## 13. Geparkte Aufträge sortieren und priorisieren
**Task:**  
Sorge dafür, dass geparkte Aufträge sinnvoll sortiert angezeigt werden.

**Empfehlung:**
- zuletzt geändert zuerst

**Akzeptanzkriterien:**
- Die relevantesten geparkten Aufträge stehen oben
- Sortierung ist in Dashboard und Auftragsübersicht konsistent oder nachvollziehbar

---

## 14. Edge Cases absichern
**Task:**  
Baue sauberes Handling für Sonderfälle ein.

**Abzusichernde Fälle:**
- Auftrag wird mehrfach geparkt
- Geparkter Auftrag wird in mehreren Tabs geöffnet
- Geparkter Auftrag wurde zwischenzeitlich geändert
- Geparkter Auftrag wurde bereits final abgesendet
- Pflichtfelder fehlen beim Fortsetzen weiterhin
- User versucht direkten Zugriff auf fremde geparkte Aufträge

**Akzeptanzkriterien:**
- Keine doppelten Datensätze durch Parken
- Kein versehentliches Überschreiben ohne Kontrolle
- Klare Fehlermeldungen bei Konflikten
- System bleibt konsistent

---

## 15. Audit-Log / Event-Tracking ergänzen
**Task:**  
Tracke die wichtigsten Events rund um das Parken und Fortsetzen.

**Zu loggende Events:**
- Auftrag begonnen
- Auftrag geparkt
- Geparkter Auftrag geöffnet
- Geparkter Auftrag bearbeitet
- Geparkter Auftrag final abgesendet

**Akzeptanzkriterien:**
- Support kann nachvollziehen, wann ein Auftrag geparkt und fortgesetzt wurde
- Events enthalten User-ID und Auftragsreferenz

---

## 16. Tests implementieren
**Task:**  
Erstelle automatisierte Tests für den neuen Parken-/Fortsetzen-Flow.

**Mindestens testen:**
- Auftrag kann im Flow geparkt werden
- Status wird korrekt auf `parked` gesetzt
- Geparkter Auftrag erscheint im Dashboard
- Geparkter Auftrag erscheint in `Aufträge`
- Geparkter Auftrag kann wieder geöffnet werden
- Felder sind nach Wiederaufnahme korrekt vorbelegt
- Finales Absenden nach Fortsetzen funktioniert
- Unvollständige Aufträge können geparkt, aber nicht final abgesendet werden
- Zugriffsschutz auf fremde geparkte Aufträge funktioniert

---

## 17. Optional: Automatische Kennzeichnung alter geparkter Aufträge
**Task:**  
Optional kann die UI ältere geparkte Aufträge gesondert kennzeichnen.

**Beispiel:**
- „Seit 14 Tagen geparkt“

**Nutzen:**
- User erkennt schnell, welche begonnenen Aufträge schon länger offen sind

---

## Technische Notizen für Codex

### Empfohlene Zustände
- `draft`
- `parked`
- `awaiting_credit_payment`
- `submitted`
- `payment_failed`
- `payment_canceled`

### Wichtige Regeln
- Parken darf auch mit Teilständen funktionieren
- Finales Absenden weiterhin nur mit vollständiger Validierung
- Fortsetzen darf keinen neuen Auftrag erzeugen
- Dashboard und Aufträge müssen geparkte Aufträge klar sichtbar machen

---

## Kompakte Übergabe an Codex
**Implementiere im Portal Chiptuningfile die Möglichkeit, einen begonnenen Auftrag zu parken und später fortzuführen.**  
Dafür soll ein neuer Status `parked` eingeführt werden. Im `Neuer Auftrag`-Flow soll ein Button `Auftrag parken` eingebaut werden, der den aktuellen Stand des Auftrags speichert. Geparkte Aufträge sollen später wieder geöffnet und an derselben Stelle weiterbearbeitet werden können. Zusätzlich soll es eine separate Anzeige für geparkte Aufträge im **Dashboard** sowie im Bereich **Aufträge** geben, jeweils mit einer Aktion `Weiterführen`. Die Validierung muss so angepasst werden, dass unvollständige Aufträge geparkt, aber nicht final abgesendet werden können. Der gesamte Flow muss backendseitig sauber validiert, gegen doppelte Datensätze abgesichert und testbar umgesetzt werden.
