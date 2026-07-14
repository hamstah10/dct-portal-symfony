Hier ist eine Erweiterung der Codex‑Tasks, die den AutoMod‑API‑Workflow in den Auftrags‑Wizard der Portale „ChiptuningFile“ und „No1TuningFiles“ einbindet. Du kannst diese Schritte direkt in Codex übernehmen und umsetzen.

# Codex Task: AutoMod‑Integration im Auftrags‑Wizard für ChiptuningFile & No1TuningFiles
## Ziel
Der bestehende Auftrags‑Wizard soll für die Portale **ChiptuningFile** und **No1TuningFiles** die neue AutoMod Job API nutzen. Dadurch kann ein Kunde über den Wizard direkt AutoMod‑Aufträge anlegen, den Status sehen und Ergebnis‑Dateien herunterladen.
Die API ist bereits serverseitig implementiert unter:
- `GET /api/v1/job`
- `POST /api/v1/job`
Diese Endpunkte müssen jetzt in den Frontend‑Workflow integriert werden.
---
## 1. Portal‑Auswahl im Wizard anpassen
- **Schritt „Portal“ des Auftrags‑Wizards** (z.B. `src/components/OrderWizard/PortalStep.vue` oder entsprechender React‑/Twig‑Teil) um die Optionen **„ChiptuningFile“** und **„No1TuningFiles“** erweitern.
- Hinterlege für diese beiden Portale eine Kennzeichnung, dass AutoMod verfügbar ist (z.B. `autoModEnabled: true` im Datenmodell des Wizards).
- Stelle sicher, dass beim Wechseln des Portals die nachfolgenden Felder wie „Modifikationen“ und „Dateiupload“ sichtbar bleiben.
---
## 2. Bestellung vorbereiten
- Sobald der Kunde eine Datei hochgeladen, Modifikation und Optionen ausgewählt hat und das Portal **ChiptuningFile** oder **No1TuningFiles** gewählt wurde, setze intern im Bestellung‑Objekt `autoModStatus = 1` („Wartet auf Entschlüsselung“).  
- Verhindere das Abschicken der Bestellung solange keine Datei hochgeladen wurde oder die erforderlichen Felder fehlen.
---
## 3. AutoMod‑Job über das Backend erzeugen
- **Beim Abschicken der Bestellung** (Submit‑Action) muss per AJAX oder über eine interne API‑Route der Auftrag im Backend gespeichert werden und auf `autoModStatus = 1` gesetzt werden.  
- Das Backend kümmert sich anschließend darum, die Datei zu entschlüsseln und `autoModStatus = 2` zu setzen, sodass WinOLS sie abholen kann.  
- Nach dem Speichern sollte der Wizard dem Nutzer eine Bestätigungsseite mit „AutoMod in Bearbeitung“ anzeigen.
---
## 4. Status‑Polling im Frontend einbauen
- Erstelle einen **Poller** (z.B. mit `setInterval`) der alle 120 Sekunden (`2 Minuten`) die eigene Backend‑Route `/api/v1/job` mit einem gültigen Bearer‑Token aufruft, um den Status des AutoMod‑Jobs zu prüfen.
- **Nur der Admin‑Bereich** oder ein spezieller Status‑Tab im Portal (z.B. „Auftragsstatus“) soll diese Abfrage machen, nicht der normale Kunde.  
- Die Antwort wird wie in der Doku strukturiert sein. Prüfe `status` und `order.auto_mod_status`.
- **Status 2** (Bereit für WinOLS): Zeige „In Bearbeitung“ an.  
- **Status 3** (Ergebnis empfangen): Zeige einen Download‑Button für `order.file` an; setze `autoModStatus` im Frontend auf „Verarbeitet“.  
- **Status 9** (Fehler): Zeige eine rote Fehlermeldung und biete dem Benutzer die Option an, den Auftrag im Backend zu öffnen oder zu löschen.
---
## 5. Ergebnis‑Download im Wizard
- Wenn `autoModStatus = 3`, zeige einen Link bzw. Button zum Download der Ergebnis‑Datei.  
- Erlaube dem Nutzer, den Auftrag abzuschließen oder erneut zu senden (dies kann als separate Aktion im Backend ausgelöst werden).
---
## 6. Fehler‑Handling
- Wird bei der Poll‑Abfrage kein Auftrag gefunden (`{ "status": true, "order": null }`), bleibe auf „In Bearbeitung“ und warte weiter.
- Kommt `autoModStatus = 9`, informiere den Benutzer, dass der Auftrag fehlgeschlagen ist und wende dich an den Support. Biete eine Wiederholungs‑Option an, die intern den Auftrag zurücksetzt (`autoModStatus = 1`) und den Poller neu startet.
---
## 7. Frontend‑Sicherheit
- Der Bearer‑Token für `/api/v1/job` darf **nie im Client hardcodiert** werden. Stattdessen:
  - Über eine interne Backend‑Route (z.B. `/ajax/automod/status`) die Anfrage serverseitig weiterreichen und dem Frontend nur die relevanten Daten schicken.
  - Dadurch bleibt der Token im Backend und die API ist vor Außen zugänglich geschützt.
- Beachte im Frontend CORS‑Beschränkungen; nutze eigene Endpunkte, die den Token an die AutoMod API weiterreichen.
---
## 8. UI‑Darstellung
- Ergänze im Wizard eine Status‑Ansicht mit:
  - **Spinner/Progressbar**, solange `autoModStatus = 1` oder `2`.
  - **Grün hinterlegtem Hinweis** und Download‑Link, wenn `autoModStatus = 3`.
  - **Roter Warnbox** mit Fehlertext und ggf. Support‑Kontakt, wenn `autoModStatus = 9`.
- Achte auf responsive Darstellung und Barrierefreiheit.
---
## 9. Aktionen für den Admin
- Im Admin‑Bereich soll es weiterhin alle Aktionen aus dem EasyAdmin‑Task geben (Status ändern, AutoMod zurücksetzen, Fehler löschen, abschließen).
- Das Frontend‑Polling darf nur den Status anzeigen, aber **keine Admin‑Aktionen** ausführen.  
---
## 10. Tests
- **Wizard‑Durchlauf**: Auftragswunsch mit Portal „ChiptuningFile“ abschicken → Backend legt AutoMod‑Job an → Poller erkennt `autoModStatus = 2` und später `3` → Download möglich.  
- **Fehlerszenario**: AutoMod gibt `autoModStatus = 9` zurück → Poller zeigt Fehlermeldung → „Erneut versuchen“ setzt Status auf `1` und startet Polling neu.  
- **Sicherheitstest**: Versuche, ohne Token direkt `GET /api/v1/job` aufzurufen → Response `401 Unauthorized`.
---
## Ergebnis
Nach dieser Integration können Kunden über den **Auftrags‑Wizard** der Portale **ChiptuningFile** und **No1TuningFiles** direkt AutoMod‑Aufträge erstellen, den Bearbeitungsstatus sehen und Ergebnis‑Dateien herunterladen – ohne WinOLS‑Kenntnisse. Administratoren behalten weiterhin die volle Kontrolle im Backend.

Dieses Task‑Dokument enthält alle notwendigen Schritte für die Integration der AutoMod API in den Frontend‑Wizard der Portale „ChiptuningFile“ und „No1TuningFiles“. Anpassungen im Frontend‐Code, im Backend und in der UI‑Darstellung werden klar beschrieben.