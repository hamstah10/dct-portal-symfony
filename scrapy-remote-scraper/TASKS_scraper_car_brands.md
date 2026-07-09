# TASKS.md – Scraper Erweiterung für Car Brands

## Ziel

Der bestehende Scraper ist bereits fertig und soll jetzt so erweitert werden, dass er:

1. zuerst **alle verfügbaren Car Brands** scraped oder aus der vorhandenen Quelle lädt,
2. daraus eine **saubere Liste mit nur Car Brands** erstellt,
3. anschließend **jeden Brand einzeln** anhand dieser Liste abarbeitet,
4. für **jeden Brand eine eigene Datei** erzeugt,
5. und dabei jede Datei immer nach dem jeweiligen **Brand-Namen** benennt.

---

## Grundprinzip

Der Scraper soll künftig in zwei klar getrennten Schritten arbeiten:

### Schritt 1 – Car-Brand-Liste erstellen
- Alle verfügbaren Fahrzeugmarken aus der Quelle holen
- Nur echte Car Brands übernehmen
- Dubletten entfernen
- Liste normalisieren
- Ergebnis zentral speichern

### Schritt 2 – Brands einzeln verarbeiten
- Die gespeicherte Car-Brand-Liste laden
- Jeden Brand nacheinander einzeln abarbeiten
- Pro Brand die bestehende Scraper-Logik ausführen
- Ergebnis pro Brand in einer eigenen Datei speichern

---

## Erwartete Struktur

```txt
/data
  /brands
    car_brands.json
  /output
    audi.json
    bmw.json
    mercedes-benz.json
    volkswagen.json
  /logs
  /tmp
```

---

## Dateinamens-Regeln

Die Ausgabedateien müssen immer anhand des Brand-Namens erzeugt werden.

### Regeln
- alles klein schreiben
- Leerzeichen durch `-` ersetzen
- Sonderzeichen bereinigen
- Umlaute umwandeln:
  - `ä -> ae`
  - `ö -> oe`
  - `ü -> ue`
  - `ß -> ss`
- keine doppelten Bindestriche
- keine führenden oder endenden Bindestriche

### Beispiele
- `Audi` → `audi.json`
- `BMW` → `bmw.json`
- `Mercedes-Benz` → `mercedes-benz.json`
- `Alfa Romeo` → `alfa-romeo.json`
- `Škoda` → `skoda.json`

---

## Aufgabenliste

## 1. Brand-Quelle prüfen
- Prüfen, woher die Liste der Car Brands aktuell stammt
- Sicherstellen, dass wirklich nur Fahrzeugmarken verwendet werden
- Kategorien, Platzhalter oder sonstige Nicht-Brand-Einträge herausfiltern

**Ergebnis:**
- klare Quelle für die Car-Brand-Liste definiert

---

## 2. Separate Car-Brand-Liste erstellen
- Einen festen Prozess einbauen, der ausschließlich die Car Brands sammelt
- Ausgabe in einer zentralen Datei speichern
- Bevorzugtes Format: `JSON`

### Beispiel
```json
[
  "Audi",
  "BMW",
  "Mercedes-Benz",
  "Volkswagen"
]
```

**Ergebnis:**
- Datei `car_brands.json` existiert und enthält nur valide Fahrzeugmarken

---

## 3. Brand-Liste bereinigen und validieren
- Leere Werte entfernen
- Dubletten entfernen
- Schreibweisen normalisieren
- Optional alphabetisch sortieren

**Ergebnis:**
- saubere, eindeutige Liste mit nur Car Brands

---

## 4. Brand-List-Loader bauen
- Funktion erstellen, die die Datei `car_brands.json` lädt
- Dateiinhalt validieren
- Fehlerbehandlung ergänzen, falls Datei fehlt oder ungültig ist

**Ergebnis:**
- Scraper kann die Brand-Liste als einzige Quelle einlesen

---

## 5. Loop für Einzelverarbeitung pro Brand
- Über alle Brands aus der Liste iterieren
- Für jeden Brand die bestehende Scraper-Logik separat starten
- Pro Durchlauf genau nur einen Brand verarbeiten

**Ergebnis:**
- Brands werden nacheinander einzeln abgearbeitet

---

## 6. Pro Brand eigene Output-Datei erzeugen
- Jede Brand bekommt eine eigene Ausgabedatei
- Keine Sammelausgabe für alle Brands zusammen
- Jede Datei wird direkt im Output-Ordner abgelegt

**Ergebnis:**
- pro Brand genau eine Datei vorhanden

---

## 7. Dateinamen-Sanitizer implementieren
- Hilfsfunktion schreiben, die aus dem Brand-Namen einen sauberen Dateinamen erzeugt
- Einheitliche Slug-Regeln für Dateinamen verwenden

**Beispiele**
- `Mercedes-Benz` → `mercedes-benz`
- `Alfa Romeo` → `alfa-romeo`
- `Škoda` → `skoda`

**Ergebnis:**
- Dateinamen sind sauber, reproduzierbar und systematisch

---

## 8. Output-Format festlegen
- Standardformat definieren, bevorzugt `JSON`
- Optional später `CSV` zusätzlich ermöglichen
- Struktur der Brand-Dateien einheitlich halten

**Ergebnis:**
- alle Dateien haben konsistentes Format

---

## 9. Logging ergänzen
- Beim Start loggen:
  - Anzahl geladener Brands
  - Pfad der Brand-Liste
- Pro Brand loggen:
  - Start
  - Erfolg
  - Fehler
  - erzeugte Datei
- Am Ende loggen:
  - Anzahl erfolgreich verarbeiteter Brands
  - Anzahl Fehler
  - Laufzeit

**Ergebnis:**
- kompletter Lauf ist nachvollziehbar

---

## 10. Fehlerbehandlung pro Brand
- Wenn ein Brand fehlschlägt, darf der Gesamtlauf nicht abbrechen
- Fehler für diesen Brand loggen
- Danach automatisch mit dem nächsten Brand weitermachen

**Ergebnis:**
- einzelner Fehler blockiert nicht den gesamten Run

---

## 11. Retry-Mechanismus einbauen
- Pro Brand optional 1 bis 3 Wiederholungsversuche bei Fehlern
- Besonders sinnvoll bei:
  - Timeouts
  - Netzwerkfehlern
  - temporären Sperren
  - instabilen Responses

**Ergebnis:**
- Scraper ist robuster bei temporären Fehlern

---

## 12. Delay / Rate-Limit ergänzen
- Zwischen Brands eine kurze Pause einbauen
- Delay konfigurierbar machen
- Ziel: geringeres Risiko für Blocking oder Rate Limits

**Ergebnis:**
- kontrollierter und stabiler Lauf

---

## 13. Resume-Funktion einbauen
- Bereits fertig verarbeitete Brands überspringen können
- Nur fehlende oder fehlerhafte Brands erneut ausführen
- Optional über Status-Datei oder Dateiprüfung lösen

**Ergebnis:**
- Abbrüche können effizient fortgesetzt werden

---

## 14. Status-Datei ergänzen
Zusätzliche Datei wie `scrape-status.json` einführen, um den Fortschritt pro Brand festzuhalten.

### Beispiel
```json
{
  "audi": "done",
  "bmw": "done",
  "mercedes-benz": "failed"
}
```

**Ergebnis:**
- Wiederaufnahme und Monitoring werden einfacher

---

## 15. Bestehende Dateien korrekt behandeln
- Verhalten definieren:
  - überschreiben
  - nur aktualisieren
  - überspringen, wenn bereits vorhanden
- Dieses Verhalten per Config steuerbar machen

**Ergebnis:**
- klarer Umgang mit bestehenden Brand-Dateien

---

## 16. CLI-Kommandos ergänzen
Der Scraper soll idealerweise diese Modi unterstützen:

### `brands:fetch`
- lädt oder aktualisiert nur die Car-Brand-Liste

### `brands:scrape-all`
- lädt die Brand-Liste und verarbeitet alle Brands nacheinander

### `brands:scrape-one --brand="Audi"`
- verarbeitet gezielt nur einen Brand

### `brands:scrape-missing`
- verarbeitet nur Brands, für die noch keine Datei existiert oder die fehlgeschlagen sind

**Ergebnis:**
- flexible Steuerung des Scrapers

---

## 17. Config erweitern
Folgende Werte in Config auslagern:
- Pfad zur Brand-Liste
- Output-Ordner
- Log-Ordner
- Dateiformat
- Delay
- Retry-Anzahl
- Overwrite-Strategie
- Resume aktiv/inaktiv
- Parallelisierung aktiv/inaktiv

**Ergebnis:**
- Verhalten des Scrapers zentral konfigurierbar

---

## 18. Validierung des Outputs
Nach jedem Brand prüfen:
- Datei wurde erzeugt
- Datei ist nicht leer
- JSON ist gültig
- Struktur entspricht dem erwarteten Format

**Ergebnis:**
- fehlerhafte Outputs werden sofort erkannt

---

## 19. Testlauf mit wenigen Brands
- Einen kontrollierten Test mit 3 bis 5 Brands durchführen
- Prüfen:
  - Brand-Liste korrekt
  - Einzelverarbeitung korrekt
  - Dateinamen korrekt
  - Dateien valide
  - Logging vollständig

**Ergebnis:**
- Funktion vor Full Run verifiziert

---

## 20. Full Run über alle Brands
- Nach erfolgreichem Test die gesamte Brand-Liste verarbeiten
- Ergebnis und Logs prüfen
- Fehlgeschlagene Brands separat erneut starten

**Ergebnis:**
- vollständige Brand-Abdeckung mit Einzelfiles

---

## 21. Definition of Done

Das Feature ist fertig, wenn:

- alle Car Brands einmal sauber gesammelt wurden
- die Brand-Liste zentral gespeichert wird
- ausschließlich diese Liste für die Einzelverarbeitung verwendet wird
- jeder Brand einzeln verarbeitet wird
- pro Brand genau eine Datei erzeugt wird
- jede Datei den Brand-Namen trägt
- Fehler einzelner Brands den Gesamtlauf nicht stoppen
- ein späterer Resume-/Retry-Lauf möglich ist

---

## Technische Anforderungen

- bestehende Scraper-Logik weiterverwenden
- keine manuelle Brand-Übergabe notwendig
- Brand-Liste ist die einzige Quelle für die Abarbeitung
- Output-Dateien immer brandbasiert benennen
- stabiler Lauf auch bei Teilfehlern
- Vorbereitung auf spätere Erweiterungen wie Parallelisierung oder weitere Kategorien

---

## Kurzfassung für Codex / Cursor

```txt
Der bestehende Scraper ist bereits fertig und soll erweitert werden.

Neue Aufgabe:
1. Zuerst alle verfügbaren Car Brands scrapen bzw. aus der Quelle laden
2. Daraus eine zentrale Liste mit nur echten Car Brands erstellen
3. Diese Liste in einer Datei speichern, z. B. car_brands.json
4. Danach die Brand-Liste laden und jeden Brand einzeln abarbeiten
5. Für jeden Brand den bestehenden Scraping-Prozess separat ausführen
6. Pro Brand eine eigene Output-Datei erstellen
7. Der Dateiname muss immer dem Brand-Namen entsprechen (slugified, lowercase, clean)
8. Fehler bei einem Brand dürfen den Gesamtlauf nicht abbrechen
9. Logging, Retry, Delay und Resume-Funktion ergänzen
10. Optional CLI-Kommandos ergänzen:
   - brands:fetch
   - brands:scrape-all
   - brands:scrape-one
   - brands:scrape-missing
```
