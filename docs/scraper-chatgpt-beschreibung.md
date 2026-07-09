# Scraper-Systembeschreibung fuer ChatGPT-Tasks

## Kontext

Wir betreiben ein Scrapy-Projekt (`scrapy-remote-scraper`) das Fahrzeugtuning-Daten von externen Websites abgreift. Der Scraper laeuft in Docker (Python 3.11) und wird von einem Symfony-CRM gesteuert, das die JSON-Ausgabe importiert.

**Aufgabe fuer ChatGPT:** Einen neuen Scrapy-Spider fuer eine andere Zielsite bauen, der exakt dasselbe Output-Format und dieselben Spider-Modi unterstuetzt wie der bestehende Spider.

---

## Spider-Modi

Der Spider wird ueber Scrapy-Parameter gesteuert:

```
-a type=car
-a mode=brands:fetch
-a mode=brands:scrape-one
-a brand="Audi"
-a output_dir=data/output/car
-a status_path=data/tmp/scrape-status-car.json
```

### Modus `brands:fetch`

Laedt die Markenuebersichtsseite der Zielsite und schreibt eine Markenliste nach:

```
data/brands/<type>_brands.json
```

Format:

```json
[
  { "name": "Abarth", "slug": "abarth", "logo": "https://..." },
  { "name": "Alfa Romeo", "slug": "alfa-romeo", "logo": "https://..." }
]
```

### Modus `brands:scrape-one`

Scrapet genau eine Marke vollstaendig (alle Modelle, Versionen, Engines).

Schreibt das Ergebnis nach:

```
data/output/<type>/<brand-slug>.json
```

Aktualisiert den Status nach erfolgreichem Abschluss in:

```
data/tmp/scrape-status-<type>.json
```

```json
{ "abarth": "done", "alfa-romeo": "done" }
```

---

## Output-Format pro Marke

Die JSON-Datei muss diese Pflichtfelder enthalten:

```json
{
  "brand_slug": "abarth",
  "brand_name": "Abarth",
  "brand_logo": "https://...",
  "brand_url": "https://...",
  "file_slug": "abarth",
  "model_count": 3,
  "models": [
    {
      "slug": "124",
      "name": "124",
      "display_name": "124",
      "image": null,
      "url": "https://...",
      "version_count": 1,
      "versions": [
        {
          "slug": "2017",
          "name": "2017",
          "display_name": "2017",
          "url": "https://...",
          "engine_count": 2,
          "engines": [
            {
              "slug": "1.4-turbo-140",
              "name": "1.4 Turbo 140 PS - Benzin",
              "display_name": "1.4 Turbo 140 PS",
              "fuel_type": "Benzin",
              "url": "https://...",
              "original": {
                "name": "Original",
                "power_ps": 140,
                "torque_nm": 230
              },
              "stages": [
                {
                  "slug": "stage1",
                  "name": "Stage 1",
                  "url": "https://...",
                  "power_ps": 175,
                  "torque_nm": 280
                },
                {
                  "slug": "stage2",
                  "name": "Stage 2",
                  "url": "https://...",
                  "power_ps": 195,
                  "torque_nm": 300
                }
              ],
              "controllers": [
                {
                  "manufacturer": "Bosch",
                  "items": [
                    {
                      "model": "EDC17C10",
                      "ecu_type": "MCU",
                      "tools": ["Alientech KESSv2", "Flex", "CMDFlash"]
                    }
                  ]
                },
                {
                  "manufacturer": "ZF",
                  "items": [
                    {
                      "model": "9HP48",
                      "ecu_type": "TCU",
                      "tools": ["--"]
                    }
                  ]
                }
              ],
              "options": []
            }
          ]
        }
      ]
    }
  ]
}
```

---

## Regeln fuer controllers

- Pro Hersteller ein Eintrag mit `items`-Array — kein Hersteller doppelt
- Reihenfolge: erst alle `MCU`, dann alle `TCU`
- `ecu_type`-Logik:
  - Hersteller ist `ZF` oder `TEMIC` → `TCU`
  - Modell-String enthaelt `DQ` → `TCU`
  - Sonst → `MCU`

---

## Regeln fuer fehlende Daten

- `stages` nicht vorhanden → leeres Array `[]`
- `controllers` nicht vorhanden → leeres Array `[]`
- `options` nicht vorhanden → leeres Array `[]`
- `image` nicht vorhanden → `null`
- `torque_nm` nicht vorhanden → `null`
- `brand_logo` nicht vorhanden → `null`

---

## Slugify-Logik fuer brand_slug und file_slug

```
lowercase
Umlaute: ae oe ue ss
Sonderzeichen → Bindestrich
& → and
Nur a-z 0-9 und Bindestrich
Keine fuehrenden oder abschliessenden Bindestriche
```

Beispiele:

| Name | Slug |
|---|---|
| Alfa Romeo | alfa-romeo |
| Mercedes-Benz | mercedes-benz |
| BMW M GmbH | bmw-m-gmbh |
| Škoda | skoda |

---

## Unterstuetzte Fahrzeugtypen

```
car, truck, moto, atv, agricultural, boat, jetski
```

---

## Datei- und Ordnerstruktur

```
scrapy-remote-scraper/
  data/
    brands/
      car_brands.json
      truck_brands.json
      ...
    output/
      car/
        abarth.json
        alfa-romeo.json
        ...
      truck/
        ...
    tmp/
      scrape-status-car.json
      scrape-status-truck.json
    logs/
      car_run_summary.log
```

---

## Was ChatGPT fuer den neuen Spider implementieren muss

1. Spider-Klasse `<neuer-name>_spider` mit Unterstuetzung fuer `-a type` und `-a mode`
2. Modus `brands:fetch`: Markenuebersichtsseite parsen, Markenliste in `data/brands/<type>_brands.json` schreiben
3. Modus `brands:scrape-one`: Eine Marke vollstaendig durchlaufen (Modelle → Versionen → Engines), Ergebnis nach `data/output/<type>/<brand-slug>.json` schreiben, Status in `data/tmp/scrape-status-<type>.json` setzen
4. CSS-Selektoren oder XPath an die neue Zielsite anpassen
5. `ecu_type`-Logik und Sortierung der controllers unveraendert uebernehmen
6. Slugify-Funktion fuer `brand_slug` und `file_slug` unveraendert uebernehmen
7. Valides Output-JSON mit allen Pflichtfeldern gemaess obigem Schema

---

## Zielsite

**URL:**

```
[HIER EINTRAGEN]
```

**Seitenstruktur:**

```
[HIER EINTRAGEN - z.B. wie die Marken-/Modell-/Engine-Seiten aufgebaut sind]
```
