# Memory

## Projektkontext

- Projekt: `scrapy-remote-scraper`
- Relevanter Spider: `olsx_brands_spider`
- Zielseite: `https://olsx.lu/de/chiptuning/<type>`
- Unterstützte Typen: `car`, `truck`, `moto`, `atv`, `agricultural`, `boat`, `jetski`

## Umgesetzte Spider-Erweiterungen

### Struktur und Ausgabe

- Der Spider erzeugt eine hierarchische JSON-Ausgabe im Projektroot.
- Die Ausgabe wird pro Lauf in eine neue Datei mit Zeitstempel geschrieben.
- Dateimuster: `olsx_brands_<type>_YYYYMMDD_HHMMSS.json`
- Die Dateien überschreiben sich nicht mehr.
- Zusätzlich wird `olsx_brands_spider.log` geschrieben.

### Unterstützte Filter

- `type=<vehicle_type>`
- `brands_only=true`
- `limit_brands=<n>`
- `brand_slug=<slug>`
- `model_prefix=<prefix>`
- `model_prefixes=rs,s`

Hinweis:
- `model_prefixes` ist kommagetrennt.
- `model_prefix` und `model_prefixes` werden gemeinsam unterstützt.
- Ein Präfixfilter auf `s` schließt auch `sq*`-Modelle ein.

### Markenmodus

- Mit `brands_only=true` werden nur Marken erfasst.
- In diesem Modus werden keine Modelle, Versionen oder Engines nachgeladen.

### Modell-, Versions- und Engine-Namen

- `model` enthält `name` und `display_name` aus dem sichtbaren Modell-Linktext.
- `version` enthält `name` und `display_name` aus dem sichtbaren Versions-Linktext.
- `engine.display_name` kommt aus dem sichtbaren Engine-Linktext.
- `engine.fuel_type` wird aus der Gruppierung wie `Benzin` oder `Diesel` übernommen.
- `engine.name` kombiniert aktuell sichtbaren Engine-Text und Kraftstofftyp.

### Engine-Daten

- Die Engine-Seite wird direkt ausgewertet.
- Stage-Daten werden nicht über Unterlinks geholt.
- Erfasst werden:
  - `original` mit `power_ps` und `torque_nm`
  - `stages` mit `stage1` und optional `stage2`
  - `controllers`
  - `options`

### Controllers

- `controllers` enthalten ein zusätzliches Feld `ecu_type`.
- Logik für `ecu_type`:
  - `manufacturer == "ZF"` oder `manufacturer == "TEMIC"` -> `TCU`
  - `model` enthält `DQ` -> `TCU`
  - sonst -> `MCU`
- Controller werden sortiert:
  - zuerst `MCU`
  - danach `TCU`
- Ausgabe ist hierarchisch nach Hersteller gruppiert:
  - ein Hersteller nur einmal
  - darunter `items` mit den jeweiligen Steuergeräten

## Bisherige Testläufe

### Brands only

- `scrapy crawl olsx_brands_spider -a brands_only=true`
  - Ergebnis: vollständiger Car-Markenlauf
  - Datei: `olsx_brands_car_20260403_002032.json`
  - Marken: `114`

- `scrapy crawl olsx_brands_spider -a type=truck -a brands_only=true`
  - Ergebnis: vollständiger Truck-Markenlauf
  - Datei: `olsx_brands_truck_20260403_002420.json`
  - Marken: `45`

### Audi RS-Test

- Früherer Testlauf:
  - `scrapy crawl olsx_brands_spider -a brand_slug=audi -a model_prefix=rs`

### Audi RS und S komplett

- Letzter vollständiger Lauf:
  - `scrapy crawl olsx_brands_spider -a type=car -a brand_slug=audi -a model_prefixes=rs,s`
- Ergebnisdatei:
  - `olsx_brands_car_20260403_002830.json`
- Inhalt:
  - `1` Marke: `Audi`
  - `18` Modelle
  - `58` Versionen
  - `92` Engines
- Enthaltene Modelle:
  - `RS3`, `RS4`, `RS5`, `RS6`, `RS7`, `RSQ3`, `RSQ8`
  - `S1`, `S3`, `S4`, `S5`, `S6`, `S7`, `S8`
  - `SQ2`, `SQ5`, `SQ7`, `SQ8`

## Wichtige Hinweise für weitere Arbeit

- Wenn nur echte `S`-Modelle ohne `SQ` gewünscht sind, reicht ein reiner Präfixfilter `s` nicht aus.
- Die aktuelle JSON-Struktur verwendet auf oberster Ebene Felder wie:
  - `brand_slug`
  - `brand_name`
  - `brand_logo`
  - `brand_url`
  - `model_count`
  - `models`
