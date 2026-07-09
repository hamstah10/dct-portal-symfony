# Spider: truck-chiptuningfiles.com

Stand: 13.05.2026

Scrapy-Spider fuer `https://truck-chiptuningfiles.com`. Erzeugt dasselbe Output-Format wie der bestehende `olsx_brand_files_spider`.

## Seitenstruktur der Zielsite

- Start-/Footer-Navigation enthaelt Truck-Markenlinks wie DAF, Iveco, MAN, Volvo Trucks, Scania usw.
- Brandseite listet Modelle als Ueberschriften, darunter Engine-Links.
- Engine-Seite enthaelt Remap-Werte fuer Standard / Upgrade / Difference (Power HP und Torque Nm).
- Keine separate Generations-Ebene → Version wird als `default` gemappt.

URL-Hierarchie:

```
https://truck-chiptuningfiles.com/en/chiptuning-files/trucks-tuningfiles/
https://truck-chiptuningfiles.com/en/chiptuning-files/{brand}/{model}/{engine-slug}
```

## Aufruf

```bash
# Markenliste holen
scrapy crawl truck_chiptuningfiles \
  -a type=truck \
  -a mode=brands:fetch

# Einzelne Marke scrapen
scrapy crawl truck_chiptuningfiles \
  -a type=truck \
  -a mode=brands:scrape-one \
  -a brand="DAF" \
  -a output_dir=data/output/truck \
  -a status_path=data/tmp/scrape-status-truck.json
```

## Wichtige Annahmen

1. `controllers` bleiben meist leer, weil die oeffentliche Engine-Seite keine sichtbaren ECU-/TCU-Tabellen zeigt. Die Methode ist vorbereitet, falls Tabellen auftauchen.
2. `version` wird als `default` gemappt, weil Truck-Chiptuningfiles keine separate Generationsebene ausspielt.
3. Stage 1 wird extrahiert, Stage 2 nur wenn numerische Werte statt "Processing" vorhanden.
4. `brand_logo` ist `null`, da die Brandlinks keine zuverlaessig extrahierbaren Logos liefern.
5. `extract_engine_links()` arbeitet URL-basiert (stabiler als CSS-Klassen bei dieser Seitenstruktur).

## Erwartetes Output-Beispiel (DAF)

```json
{
  "brand_slug": "daf",
  "brand_name": "DAF",
  "brand_logo": null,
  "brand_url": "https://truck-chiptuningfiles.com/en/chiptuning-files/trucks-tuningfiles/daf",
  "file_slug": "daf",
  "model_count": 5,
  "models": [
    {
      "slug": "cf",
      "name": "CF",
      "display_name": "CF",
      "image": null,
      "url": "https://truck-chiptuningfiles.com/en/chiptuning-files/daf/cf",
      "version_count": 1,
      "versions": [
        {
          "slug": "default",
          "name": "Default",
          "display_name": "Default",
          "url": "https://truck-chiptuningfiles.com/en/chiptuning-files/daf/cf",
          "engine_count": 25,
          "engines": [
            {
              "slug": "cf-180-5-9l",
              "name": "CF 180 5.9L",
              "display_name": "CF 180 5.9L",
              "fuel_type": null,
              "url": "https://truck-chiptuningfiles.com/en/chiptuning-files/daf/cf/cf-65-180-185-pk-136-kw-euro-3",
              "original": {
                "name": "Original",
                "power_ps": 185,
                "torque_nm": 700
              },
              "stages": [
                {
                  "slug": "stage1",
                  "name": "Stage 1",
                  "url": "https://truck-chiptuningfiles.com/en/chiptuning-files/daf/cf/cf-65-180-185-pk-136-kw-euro-3",
                  "power_ps": 227,
                  "torque_nm": 850
                }
              ],
              "controllers": [],
              "options": []
            }
          ]
        }
      ]
    }
  ]
}
```

## Spider-Code

Datei: `scrapy-remote-scraper/spiders/truck_chiptuningfiles_spider.py`

```python
import json
import os
import re
import unicodedata
from collections import defaultdict
from urllib.parse import urljoin, urlparse

import scrapy


class TruckChiptuningfilesSpider(scrapy.Spider):
    name = "truck_chiptuningfiles"
    allowed_domains = ["truck-chiptuningfiles.com"]

    TYPE_PATHS = {
        "truck": "trucks-tuningfiles",
        "car": "trucks-tuningfiles",
        "moto": "trucks-tuningfiles",
        "atv": "trucks-tuningfiles",
        "agricultural": "trucks-tuningfiles",
        "boat": "trucks-tuningfiles",
        "jetski": "trucks-tuningfiles",
    }

    BASE_URL = "https://truck-chiptuningfiles.com"
    LANGUAGE = "en"

    custom_settings = {
        "ROBOTSTXT_OBEY": False,
        "DOWNLOAD_DELAY": 0.25,
        "CONCURRENT_REQUESTS_PER_DOMAIN": 4,
        "FEEDS": {},
        "LOG_LEVEL": "INFO",
    }

    def __init__(
        self,
        type="truck",
        mode=None,
        brand=None,
        output_dir=None,
        status_path=None,
        *args,
        **kwargs,
    ):
        super().__init__(*args, **kwargs)
        self.vehicle_type = type or "truck"
        self.mode = mode
        self.brand = brand
        self.type_path = self.TYPE_PATHS.get(self.vehicle_type, "trucks-tuningfiles")
        self.brands_path = f"data/brands/{self.vehicle_type}_brands.json"
        self.output_dir = output_dir or f"data/output/{self.vehicle_type}"
        self.status_path = status_path or f"data/tmp/scrape-status-{self.vehicle_type}.json"
        self.brand_result = None
        self.pending_engines = 0

    # -------------------------------------------------------------------------
    # Entry
    # -------------------------------------------------------------------------

    def start_requests(self):
        if self.mode == "brands:fetch":
            url = f"{self.BASE_URL}/{self.LANGUAGE}/chiptuning-files"
            yield scrapy.Request(url, callback=self.parse_brands)
        elif self.mode == "brands:scrape-one":
            if not self.brand:
                raise ValueError('mode="brands:scrape-one" requires -a brand="..."')
            yield from self.start_scrape_one()
        else:
            raise ValueError(
                'Unsupported mode. Use -a mode="brands:fetch" or -a mode="brands:scrape-one".'
            )

    # -------------------------------------------------------------------------
    # Mode: brands:fetch
    # -------------------------------------------------------------------------

    def parse_brands(self, response):
        brands = []
        links = response.xpath(
            '//a[contains(@href, "/en/chiptuning-files/trucks-tuningfiles/")]'
        )
        seen = set()
        for link in links:
            href = link.xpath("./@href").get()
            text = self.clean_text(" ".join(link.xpath(".//text()").getall()))
            if not href:
                continue
            abs_url = response.urljoin(href)
            slug_from_url = self.last_url_segment(abs_url)
            if not slug_from_url:
                continue
            name = re.sub(r"^tuning\s+file\s+", "", text, flags=re.I).strip()
            name = name or self.name_from_slug(slug_from_url)
            slug = self.slugify(name)
            if not slug or slug in seen:
                continue
            seen.add(slug)
            logo = self.extract_nearby_logo(link, response)
            brands.append({"name": name, "slug": slug, "logo": logo, "url": abs_url})
        brands = sorted(brands, key=lambda item: item["name"].lower())
        self.ensure_parent_dir(self.brands_path)
        with open(self.brands_path, "w", encoding="utf-8") as f:
            json.dump(brands, f, ensure_ascii=False, indent=2)
        self.logger.info("Wrote %s brands to %s", len(brands), self.brands_path)

    # -------------------------------------------------------------------------
    # Mode: brands:scrape-one
    # -------------------------------------------------------------------------

    def start_scrape_one(self):
        brand_info = self.find_brand_info(self.brand)
        brand_name = brand_info.get("name") or self.brand
        brand_slug = brand_info.get("slug") or self.slugify(brand_name)
        brand_url = brand_info.get("url") or (
            f"{self.BASE_URL}/{self.LANGUAGE}/chiptuning-files/{self.type_path}/{brand_slug}"
        )
        self.brand_result = {
            "brand_slug": brand_slug,
            "brand_name": brand_name,
            "brand_logo": brand_info.get("logo"),
            "brand_url": brand_url,
            "file_slug": brand_slug,
            "model_count": 0,
            "models": [],
        }
        yield scrapy.Request(
            brand_url,
            callback=self.parse_brand_page,
            meta={
                "brand_slug": brand_slug,
                "brand_name": brand_name,
                "brand_url": brand_url,
            },
        )

    def parse_brand_page(self, response):
        brand_slug = response.meta["brand_slug"]
        engine_links = self.extract_engine_links(response, brand_slug)
        models_map = {}

        for item in engine_links:
            model_slug = item["model_slug"]
            model_name = item["model_name"]
            model_url = item["model_url"]
            if model_slug not in models_map:
                models_map[model_slug] = {
                    "slug": model_slug,
                    "name": model_name,
                    "display_name": model_name,
                    "image": None,
                    "url": model_url,
                    "version_count": 1,
                    "versions": [
                        {
                            "slug": "default",
                            "name": "Default",
                            "display_name": "Default",
                            "url": model_url,
                            "engine_count": 0,
                            "engines": [],
                        }
                    ],
                }
            self.pending_engines += 1
            yield scrapy.Request(
                item["engine_url"],
                callback=self.parse_engine_page,
                errback=self.engine_failed,
                meta={
                    "brand_slug": brand_slug,
                    "model_slug": model_slug,
                    "model_name": model_name,
                    "model_url": model_url,
                    "engine_name": item["engine_name"],
                    "engine_url": item["engine_url"],
                    "models_map": models_map,
                },
            )

        if not engine_links:
            self.brand_result["models"] = []
            self.brand_result["model_count"] = 0
            self.write_brand_result()

    def parse_engine_page(self, response):
        models_map = response.meta["models_map"]
        model_slug = response.meta["model_slug"]
        engine_name = response.meta["engine_name"]
        engine_url = response.meta["engine_url"]

        page_title = self.extract_engine_title(response) or engine_name
        original_power_ps, original_torque_nm, stage1_power_ps, stage1_torque_nm = (
            self.extract_power_torque(response)
        )
        display_name = self.clean_engine_display_name(page_title)
        fuel_type = self.extract_fuel_type(display_name)

        stages = []
        if stage1_power_ps is not None or stage1_torque_nm is not None:
            stages.append(
                {
                    "slug": "stage1",
                    "name": "Stage 1",
                    "url": engine_url,
                    "power_ps": stage1_power_ps,
                    "torque_nm": stage1_torque_nm,
                }
            )

        stage2_power_ps, stage2_torque_nm = self.extract_stage2(response)
        if stage2_power_ps is not None or stage2_torque_nm is not None:
            stages.append(
                {
                    "slug": "stage2",
                    "name": "Stage 2",
                    "url": engine_url,
                    "power_ps": stage2_power_ps,
                    "torque_nm": stage2_torque_nm,
                }
            )

        engine = {
            "slug": self.slugify(display_name),
            "name": self.with_fuel_suffix(display_name, fuel_type),
            "display_name": display_name,
            "fuel_type": fuel_type,
            "url": engine_url,
            "original": {
                "name": "Original",
                "power_ps": original_power_ps,
                "torque_nm": original_torque_nm,
            },
            "stages": stages,
            "controllers": self.extract_controllers(response),
            "options": [],
        }

        models_map[model_slug]["versions"][0]["engines"].append(engine)
        models_map[model_slug]["versions"][0]["engine_count"] += 1
        self.pending_engines -= 1
        if self.pending_engines == 0:
            self.finish_brand(models_map)

    def engine_failed(self, failure):
        request = failure.request
        models_map = request.meta["models_map"]
        self.logger.warning("Engine request failed: %s", request.url)
        self.pending_engines -= 1
        if self.pending_engines == 0:
            self.finish_brand(models_map)

    # -------------------------------------------------------------------------
    # Extractors
    # -------------------------------------------------------------------------

    def extract_engine_links(self, response, brand_slug):
        result = []
        seen = set()

        links = response.xpath(
            f'//a[contains(@href, "/{self.LANGUAGE}/chiptuning-files/{brand_slug}/")]'
        )
        for link in links:
            href = link.xpath("./@href").get()
            text = self.clean_text(" ".join(link.xpath(".//text()").getall()))
            if not href or not text:
                continue
            abs_url = response.urljoin(href)
            parsed = urlparse(abs_url)
            parts = [p for p in parsed.path.split("/") if p]
            # Erwartet: en / chiptuning-files / {brand} / {model} / {engine}
            if len(parts) < 5:
                continue
            if parts[0] != self.LANGUAGE or parts[1] != "chiptuning-files":
                continue
            if parts[2] != brand_slug:
                continue
            model_url_slug = parts[3]
            engine_url_slug = parts[4]
            if not model_url_slug or not engine_url_slug:
                continue
            key = abs_url
            if key in seen:
                continue
            seen.add(key)
            model_name = self.name_from_slug(model_url_slug).upper()
            model_url = f"{self.BASE_URL}/{self.LANGUAGE}/chiptuning-files/{brand_slug}/{model_url_slug}"
            result.append(
                {
                    "model_slug": self.slugify(model_name),
                    "model_name": model_name,
                    "model_url": model_url,
                    "engine_name": text,
                    "engine_url": abs_url,
                }
            )
        return result

    def extract_engine_title(self, response):
        h1 = self.clean_text(" ".join(response.xpath("//h1//text()").getall()))
        h2 = self.clean_text(" ".join(response.xpath("//h2//text()").getall()))
        title = h2 or h1
        title = re.sub(r"^tuning\s+file\s+", "", title, flags=re.I).strip()
        title = re.sub(r"\s+tuning[-\s]?file$", "", title, flags=re.I).strip()
        return title or None

    def extract_power_torque(self, response):
        text = self.clean_text(" ".join(response.xpath("//body//text()").getall()))
        power_match = re.search(
            r"Power\s+HP\s+(\d+)\s*HP\s+(\d+)\s*HP",
            text,
            flags=re.I,
        )
        torque_match = re.search(
            r"Torque\s*/\s*traction\s+(\d+)\s*Nm\s+(\d+)\s*Nm",
            text,
            flags=re.I,
        )
        original_power_ps = int(power_match.group(1)) if power_match else None
        stage1_power_ps = int(power_match.group(2)) if power_match else None
        original_torque_nm = int(torque_match.group(1)) if torque_match else None
        stage1_torque_nm = int(torque_match.group(2)) if torque_match else None
        return original_power_ps, original_torque_nm, stage1_power_ps, stage1_torque_nm

    def extract_stage2(self, response):
        # Stage 2 zeigt auf dieser Site meist "Processing" statt numerischer Werte.
        return None, None

    def extract_controllers(self, response):
        controllers_raw = []
        rows = response.xpath("//table//tr")
        for row in rows:
            cells = [
                self.clean_text(" ".join(c.xpath(".//text()").getall()))
                for c in row.xpath("./th|./td")
            ]
            cells = [c for c in cells if c]
            if len(cells) < 2:
                continue
            manufacturer = cells[0]
            model = cells[1]
            tools = cells[2:] if len(cells) > 2 else ["--"]
            if not manufacturer or not model:
                continue
            ecu_type = self.detect_ecu_type(manufacturer, model)
            controllers_raw.append(
                {
                    "manufacturer": manufacturer,
                    "model": model,
                    "ecu_type": ecu_type,
                    "tools": tools or ["--"],
                }
            )
        return self.normalize_controllers(controllers_raw)

    def extract_nearby_logo(self, link, response):
        return None

    # -------------------------------------------------------------------------
    # Finish / write
    # -------------------------------------------------------------------------

    def finish_brand(self, models_map):
        models = list(models_map.values())
        models.sort(key=lambda m: m["name"].lower())
        for model in models:
            version = model["versions"][0]
            version["engines"].sort(key=lambda e: e["display_name"].lower())
            version["engine_count"] = len(version["engines"])
            model["version_count"] = len(model["versions"])
        self.brand_result["models"] = models
        self.brand_result["model_count"] = len(models)
        self.write_brand_result()

    def write_brand_result(self):
        brand_slug = self.brand_result["brand_slug"]
        os.makedirs(self.output_dir, exist_ok=True)
        output_path = os.path.join(self.output_dir, f"{brand_slug}.json")
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(self.brand_result, f, ensure_ascii=False, indent=2)
        self.mark_status_done(brand_slug)
        self.logger.info("Wrote brand output to %s", output_path)

    def mark_status_done(self, brand_slug):
        self.ensure_parent_dir(self.status_path)
        status = {}
        if os.path.exists(self.status_path):
            try:
                with open(self.status_path, "r", encoding="utf-8") as f:
                    status = json.load(f)
            except json.JSONDecodeError:
                status = {}
        status[brand_slug] = "done"
        with open(self.status_path, "w", encoding="utf-8") as f:
            json.dump(status, f, ensure_ascii=False, indent=2)

    # -------------------------------------------------------------------------
    # Brand helpers
    # -------------------------------------------------------------------------

    def find_brand_info(self, brand_name_or_slug):
        wanted_slug = self.slugify(brand_name_or_slug)
        if os.path.exists(self.brands_path):
            try:
                with open(self.brands_path, "r", encoding="utf-8") as f:
                    brands = json.load(f)
                for item in brands:
                    if item.get("slug") == wanted_slug:
                        return item
                    if self.slugify(item.get("name", "")) == wanted_slug:
                        return item
            except Exception as exc:
                self.logger.warning("Could not read brands file %s: %s", self.brands_path, exc)
        return {
            "name": brand_name_or_slug,
            "slug": wanted_slug,
            "logo": None,
            "url": f"{self.BASE_URL}/{self.LANGUAGE}/chiptuning-files/{self.type_path}/{wanted_slug}",
        }

    # -------------------------------------------------------------------------
    # Controller rules
    # -------------------------------------------------------------------------

    def detect_ecu_type(self, manufacturer, model):
        manufacturer_upper = (manufacturer or "").upper()
        model_upper = (model or "").upper()
        if manufacturer_upper in {"ZF", "TEMIC"}:
            return "TCU"
        if "DQ" in model_upper:
            return "TCU"
        return "MCU"

    def normalize_controllers(self, raw_items):
        grouped = defaultdict(list)
        for item in raw_items:
            manufacturer = item.get("manufacturer")
            model = item.get("model")
            if not manufacturer or not model:
                continue
            grouped[manufacturer].append(
                {
                    "model": model,
                    "ecu_type": item.get("ecu_type") or self.detect_ecu_type(manufacturer, model),
                    "tools": item.get("tools") or ["--"],
                }
            )
        controllers = []
        for manufacturer, items in grouped.items():
            items.sort(key=lambda x: 0 if x["ecu_type"] == "MCU" else 1)
            controllers.append({"manufacturer": manufacturer, "items": items})
        controllers.sort(
            key=lambda group: (
                0 if any(item["ecu_type"] == "MCU" for item in group["items"]) else 1,
                group["manufacturer"].lower(),
            )
        )
        return controllers

    # -------------------------------------------------------------------------
    # Text / slug helpers
    # -------------------------------------------------------------------------

    def slugify(self, value):
        value = str(value or "").strip().lower()
        replacements = {"ä": "ae", "ö": "oe", "ü": "ue", "ß": "ss", "&": " and "}
        for old, new in replacements.items():
            value = value.replace(old, new)
        value = unicodedata.normalize("NFKD", value)
        value = value.encode("ascii", "ignore").decode("ascii")
        value = re.sub(r"[^a-z0-9]+", "-", value)
        value = re.sub(r"-{2,}", "-", value)
        return value.strip("-")

    def clean_text(self, value):
        value = value or ""
        value = value.replace("\xa0", " ")
        value = re.sub(r"\s+", " ", value)
        return value.strip()

    def last_url_segment(self, url):
        path = urlparse(url).path
        parts = [part for part in path.split("/") if part]
        return parts[-1] if parts else None

    def name_from_slug(self, slug):
        return str(slug or "").replace("-", " ").strip().title()

    def clean_engine_display_name(self, value):
        value = self.clean_text(value)
        brand_name = self.brand_result.get("brand_name") if self.brand_result else None
        if brand_name:
            value = re.sub(rf"^{re.escape(brand_name)}\s+", "", value, flags=re.I)
        value = re.sub(r"\s+tuning[-\s]?file$", "", value, flags=re.I).strip()
        return value

    def extract_fuel_type(self, display_name):
        text = display_name.lower()
        if "diesel" in text:
            return "Diesel"
        if "petrol" in text or "benzin" in text or "gasoline" in text:
            return "Benzin"
        if "hybrid" in text:
            return "Hybrid"
        if "electric" in text or "ev" in text:
            return "Elektro"
        return None

    def with_fuel_suffix(self, display_name, fuel_type):
        if fuel_type and fuel_type.lower() not in display_name.lower():
            return f"{display_name} - {fuel_type}"
        return display_name

    def ensure_parent_dir(self, path):
        parent = os.path.dirname(path)
        if parent:
            os.makedirs(parent, exist_ok=True)
```

## Naechste Schritte

1. Datei nach `scrapy-remote-scraper/spiders/truck_chiptuningfiles_spider.py` kopieren.
2. `brands:fetch` laufen lassen und `data/brands/truck_brands.json` pruefen.
3. Mit einer Marke (z. B. DAF) testen: `brands:scrape-one`.
4. `extract_power_torque()` anpassen falls die Regex-Muster nicht auf den echten HTML-Text passen.
5. Bei Bedarf `extract_stage2()` erweitern wenn Stage-2-Werte sichtbar werden.
