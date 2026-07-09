# Scrapy Remote Scraper (Production-ready Base)

Ein robustes Scrapy-Basisprojekt für strukturiertes Web-Scraping auf separatem Host (VPS/Server/Docker).

## Features

- Saubere Scrapy-Struktur (Spider, Items, Pipelines, Settings)
- `.env`-basierte Konfiguration (keine hartcodierten Secrets)
- Retry/Timeout/AutoThrottle
- Optional User-Agent Rotation
- Optional Proxy Rotation
- Export in JSON/CSV/JL/XML
- Optional API-POST Pipeline
- Docker + docker-compose
- Remote Trigger via SSH-Script
- Headless/CLI-ready für Cron/Systemd

## Lokales Testen

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
mkdir -p output
python run_spider.py
```

Alternative:
```bash
scrapy crawl products_spider
```

Registrierte Spider anzeigen:
```bash
scrapy list
# olsx_brand_files_spider, olsx_brands_spider, olsx_spider, products_spider
```

### `run_spider.py` und `SCRAPER_NAME`

[`run_spider.py`](run_spider.py) startet den Spider aus der Umgebungsvariable **`SCRAPER_NAME`** (oder aus `.env` via `python-dotenv`). Standard ist **`products_spider`**.

Für OLSX z. B. in `.env`:
```env
SCRAPER_NAME=olsx_brands_spider
```
oder direkt:
```bash
SCRAPER_NAME=olsx_brands_spider python run_spider.py
```

Optionaler Fahrzeugtyp für `olsx_brands_spider` via Spider-Argument:
```bash
scrapy crawl olsx_brands_spider -a type=truck
```
Unterstützte Werte:
`car`, `truck`, `moto`, `atv`, `agricultural`, `boat`, `jetski`

### OLSX (`olsx_brands_spider`)

Die Ausgabe ist **eine hierarchische JSON-Datei** im **Projektroot** mit Zeitstempel im Dateinamen, z. B. **`olsx_brands_car_20260403_001851.json`**. Pro Lauf wird genau **ein JSON-Array** geschrieben; jede **Marke kommt nur einmal** vor, mit verschachtelten Modellen → Versionen → Motoren → Stufen. Marken, Modelle, Versionen, Motoren und Stufen sind **alphabetisch** (nach Anzeigename, case-insensitive) sortiert. Die Datei wird beim **Spider-Ende** (`closed`) geschrieben, nicht über Scrapy-`FEEDS`. Zusätzlich: **`olsx_brands_spider.log`**.

Schnelltest (nur erste Marke, z. B. Abarth):
```bash
scrapy crawl olsx_brands_spider -a limit_brands=1
```

Vollständiger Lauf (alle Marken): Argument `limit_brands` weglassen.

Nur Marken mit Anzahlbegrenzung, ohne Modelle/Versionen/Engines:
```bash
scrapy crawl olsx_brands_spider -a brands_only=true -a limit_brands=10
```

Beispiel für anderen Typ:
```bash
scrapy crawl olsx_brands_spider -a type=truck -a brands_only=true
```

### OLSX Brand-Dateien (`olsx_brand_files_spider`)

Dieser Spider arbeitet in zwei Schritten:

1. Markenliste erzeugen
2. Jede Marke einzeln in eine eigene Datei schreiben

Standardpfade:

- Markenliste: `data/brands/<type>_brands.json`
- Einzelfiles: `data/output/<type>/<brand>.json`
- Status: `data/tmp/scrape-status-<type>.json`
- Log: `data/logs/olsx_brand_files_<type>.log`

Markenliste laden/aktualisieren:
```bash
scrapy crawl olsx_brand_files_spider -a mode=brands:fetch
```

Alle Marken aus der gespeicherten Liste verarbeiten:
```bash
scrapy crawl olsx_brand_files_spider -a mode=brands:scrape-all
```

Nur eine Marke verarbeiten:
```bash
scrapy crawl olsx_brand_files_spider -a mode=brands:scrape-one -a brand="Audi"
```

Nur fehlende Marken nachziehen:
```bash
scrapy crawl olsx_brand_files_spider -a mode=brands:scrape-missing
```

Optional:

- anderer Typ: `-a type=truck`
- bestehende Dateien überspringen: `-a overwrite=skip`
- Resume deaktivieren: `-a resume=false`
- Testlauf begrenzen: `-a limit_brands=5`

### Integration in den Superadmin

Die Symfony-Anwendung nutzt inzwischen zwei getrennte Importwege im Superadmin:

- klassischer Import:
  - Quelle: Root-Dateien wie `olsx_brands_car_20260403_001851.json`
  - Route: `/admin/fahrzeugdatenbank/scraper/import`
- Output-Import:
  - Quelle: Einzeldateien unter `data/output/<type>/<brand>.json`
  - Route: `/admin/fahrzeugdatenbank/scraper/import-output`
  - UI: eine Tabelle pro Typ mit Checkbox-Auswahl pro Datei und `Alle`-Checkbox pro Tabelle

Damit lassen sich sowohl komplette Sammeldateien als auch markenweise erzeugte JSON-Dateien direkt in Vehicle Data Pools importieren.

### Brand-Runner mit Retry und Delay

Für sequenzielle Läufe mit Pause zwischen Marken und echten Wiederholungsversuchen pro Marke gibt es zusätzlich:

```bash
python3 run_olsx_brand_jobs.py --mode fetch --type car
python3 run_olsx_brand_jobs.py --mode scrape-all --type car --delay 2 --retries 2
python3 run_olsx_brand_jobs.py --mode scrape-one --type car --brand "Audi" --retries 3
python3 run_olsx_brand_jobs.py --mode scrape-missing --type car --delay 1
```

Optional:

- Markenliste vor dem Lauf aktualisieren: `--fetch-first`
- Pfade überschreiben: `--brands-path`, `--output-dir`, `--status-path`
- Resume steuern: `--resume true|false`
- Overwrite steuern: `--overwrite overwrite|skip|update`

## ENV-Konfiguration

In `.env`:

- `SCRAPER_NAME=products_spider` (optional; steuert [`run_spider.py`](run_spider.py))
- `START_URLS=https://example.com/products,https://example.com/new`
- `ALLOWED_DOMAINS=example.com`
- `OUTPUT_FORMAT=json` oder `csv`
- `OUTPUT_PATH=output/data.json`
- `DOWNLOAD_TIMEOUT=20`
- `RETRY_TIMES=3`
- optional:
  - `UA_ROTATION_ENABLED=true`
  - `PROXY_ROTATION_ENABLED=true`
  - `API_POST_ENABLED=true`

## Deployment auf anderem Host

### Docker (empfohlen)

```bash
git clone <repo-url> /opt/scrapy-remote-scraper
cd /opt/scrapy-remote-scraper
cp .env.example .env
docker compose build
docker compose run --rm scraper
```

Ergebnis liegt in `output/`, sofern `OUTPUT_PATH` gesetzt ist. Beim Spider **`olsx_brands_spider`** liegt die Ausgabe **ausschließlich** im Projektroot als zeitgestempelte Datei **`olsx_brands_<type>_*.json`** (hierarchisch, siehe oben).

### Native Python

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python run_spider.py
```

## Cronjob Beispiel

```cron
0 */6 * * * cd /opt/scrapy-remote-scraper && /usr/bin/docker compose run --rm scraper >> /var/log/scraper-cron.log 2>&1
```

## Systemd Service + Timer

`/etc/systemd/system/scraper.service`
```ini
[Unit]
Description=Scrapy Remote Scraper Job
After=network.target docker.service
Requires=docker.service

[Service]
Type=oneshot
WorkingDirectory=/opt/scrapy-remote-scraper
ExecStart=/usr/bin/docker compose run --rm scraper
StandardOutput=append:/var/log/scraper-service.log
StandardError=append:/var/log/scraper-service.log
```

`/etc/systemd/system/scraper.timer`
```ini
[Unit]
Description=Run Scraper every 6 hours

[Timer]
OnBootSec=5min
OnUnitActiveSec=6h
Unit=scraper.service

[Install]
WantedBy=timers.target
```

Aktivieren:
```bash
sudo systemctl daemon-reload
sudo systemctl enable --now scraper.timer
sudo systemctl list-timers
```

## Remote Trigger (SSH)

```bash
REMOTE_HOST=1.2.3.4 REMOTE_USER=ubuntu REMOTE_DIR=/opt/scrapy-remote-scraper ./scripts/remote_run.sh
```

## Messenger Worker

Symfony verarbeitet die Scraper-Jobs asynchron über Messenger. Ein Worker auf dem Server kümmert sich darum, dass die Queue abgearbeitet wird:

```
MESSENGER_TRANSPORT_DSN="doctrine://default" ./scripts/messenger_worker.sh
```

Das Skript springt ins Projektverzeichnis und startet `php bin/console messenger:consume async` mit einer sinnvollen Timeout-/Memory-Konfiguration. In Produktivsystemen empfiehlt sich ein systemd/Supervisor-Job, der das Skript dauerhaft oder mit Restart laufen lässt.

## Architektur für Remote-Betrieb

- Steuer-System startet nur den Job (z. B. SSH/API)
- Scraper läuft vollständig auf Remote-Host
- Logs liegen auf Remote-Host
- Ergebnisse als Datei oder via API-Versand

## Annahmen zu CSS-Selektoren

Spider erwartet beispielhaft:
- Listing-Links: `a.product-link`
- Next-Link: `a.next` oder `a[rel='next']`
- Detail:
  - Titel: `h1.product-title`
  - Preis: `.price`
  - Beschreibung: `.product-description`
  - Bilder: `.product-gallery img`

Bitte Selektoren für deine Zielseite anpassen.
