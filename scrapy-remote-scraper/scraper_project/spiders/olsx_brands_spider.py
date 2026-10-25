import json
import logging
import os
import re
from datetime import datetime
from urllib.parse import urlparse

import scrapy


def _sort_key_name_slug(obj: dict) -> str:
    return ((obj.get("name") or obj.get("slug") or "")).lower()


def _controller_sort_key(controller: dict) -> tuple[int, str, str]:
    ecu_type = (controller.get("ecu_type") or "").upper()
    type_rank = 0 if ecu_type == "MCU" else 1
    manufacturer = (controller.get("manufacturer") or "").lower()
    model = (controller.get("model") or "").lower()
    return (type_rank, manufacturer, model)


def _manufacturer_sort_key(group: dict) -> str:
    return (group.get("manufacturer") or "").lower()


class OlsxBrandsSpider(scrapy.Spider):
    name = "olsx_brands_spider"
    allowed_domains = ["olsx.lu"]
    supported_types = {"car", "truck", "moto", "atv", "agricultural", "boat", "jetski"}
    stage_label_by_slug = {
        "stage1": "Stage 1",
        "stage2": "Stage 2",
        "stage3": "Stage 3",
        "e85": "E85",
        "e85plus": "E85 Plus",
    }
    non_stage_slugs = {"e85", "e85plus"}
    
    def __init__(
        self,
        limit_brands=None,
        brand_slug=None,
        model_prefix=None,
        model_prefixes=None,
        model_suffix=None,
        model_suffixes=None,
        brands_only=None,
        type=None,
        checkpoint=None,
        fill_missing=None,
        fill_source=None,
        *args,
        **kwargs,
    ):
        super().__init__(*args, **kwargs)
        requested_type = (type or "car").lower()
        if requested_type not in self.supported_types:
            raise ValueError(
                f"Unsupported type '{requested_type}'. Supported values: {', '.join(sorted(self.supported_types))}"
            )

        self.scraper_type = requested_type
        self.start_urls = [f"https://olsx.lu/de/chiptuning/{self.scraper_type}"]
        self.limit_brands = int(limit_brands) if limit_brands else None
        self.brand_slug_filter = brand_slug.lower() if brand_slug else None
        prefixes = []
        for raw_prefixes in (model_prefix, model_prefixes):
            if not raw_prefixes:
                continue
            prefixes.extend(
                prefix.strip().lower()
                for prefix in str(raw_prefixes).split(",")
                if prefix.strip()
            )
        self.model_prefix_filters = tuple(dict.fromkeys(prefixes))
        suffixes = []
        for raw_suffixes in (model_suffix, model_suffixes):
            if not raw_suffixes:
                continue
            suffixes.extend(
                suffix.strip().lower()
                for suffix in str(raw_suffixes).split(",")
                if suffix.strip()
            )
        self.model_suffix_filters = tuple(dict.fromkeys(suffixes))
        self.brands_only = str(brands_only).lower() in {"1", "true", "yes", "on"} if brands_only is not None else False
        self.brand_data = {}  # Store brand data by slug

        # Nachfüllen: bestehende Ausgabe laden und nur Motoren ohne Stages/Optionen abrufen
        self.fill_missing = str(fill_missing).lower() in {"1", "true", "yes", "on"} if fill_missing is not None else False
        self.fill_source = fill_source or None
        self.fill_data = None
        self._fill_output_path = None
        self._fill_done = 0
        if self.fill_missing:
            self.fill_data = self._load_fill_source()
            self.logger.info("Nachfüllen aktiv: %s Marken geladen aus %s", len(self.fill_data), self._fill_source_path())

        # Zwischenstand: bei Abbruch kann der Lauf mit demselben Checkpoint (+ JOBDIR) fortgesetzt werden
        self.checkpoint_path = checkpoint or None
        self._changes_since_checkpoint = 0
        if self.checkpoint_path and os.path.exists(self.checkpoint_path):
            with open(self.checkpoint_path, encoding="utf-8") as f:
                self.brand_data = json.load(f)
            self.logger.info("Checkpoint geladen: %s (%s Marken)", self.checkpoint_path, len(self.brand_data))

        # Create file logger
        log_path = os.path.join(os.getcwd(), 'olsx_brands_spider.log')
        
        self.file_logger = logging.getLogger(f"{self.__class__.__name__}_file")
        self.file_logger.setLevel(logging.DEBUG)
        self.file_logger.handlers = []
        
        handler = logging.FileHandler(log_path)
        handler.setLevel(logging.DEBUG)
        formatter = logging.Formatter('%(asctime)s - %(levelname)s - %(message)s')
        handler.setFormatter(formatter)
        self.file_logger.addHandler(handler)
        
        self.file_logger.info(
            "Logger initialized. type=%s limit_brands=%s brand_slug=%s model_prefixes=%s model_suffixes=%s brands_only=%s",
            self.scraper_type,
            self.limit_brands,
            self.brand_slug_filter,
            list(self.model_prefix_filters),
            list(self.model_suffix_filters),
            self.brands_only,
        )

    custom_settings = {
        "ROBOTSTXT_OBEY": False,
        "DOWNLOAD_DELAY": 0.5,
        "CONCURRENT_REQUESTS_PER_DOMAIN": 2,
        "USER_AGENT": (
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/122.0.0.0 Safari/537.36"
        ),
        "DEFAULT_REQUEST_HEADERS": {
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language": "de-DE,de;q=0.9,en;q=0.8",
            "Referer": "https://olsx.lu/",
        },
        # Ausgabe: eine hierarchische JSON-Datei wird in closed() geschrieben (s. README)
        "LOG_LEVEL": "DEBUG",
    }

    def parse(self, response):
        self.logger.info("START %s status=%s", response.url, response.status)

        # Extract brands directly from the brand links on the start page
        all_brand_links = response.css(f"a[href*='/de/chiptuning/{self.scraper_type}/']")
        self.logger.info(f"Found {len(all_brand_links)} total brand links")
        
        # Limit brands if specified
        brand_count = 0
        for link in all_brand_links:
            if self.limit_brands and brand_count >= self.limit_brands:
                break
                
            href = link.css("::attr(href)").get()
            if not href:
                continue
            
            parts = self.path_parts(href)
            
            # Erwartet: /de/chiptuning/<type>/<brand>
            if len(parts) == 4 and parts[:3] == ["de", "chiptuning", self.scraper_type]:
                brand_slug = parts[3]

                if self.brand_slug_filter and brand_slug.lower() != self.brand_slug_filter:
                    continue

                brand_count += 1
                
                # Extract brand name from link text or img alt
                brand_name = self.clean(link.css("p::text").get()) or \
                             link.css("img::attr(alt)").get() or \
                             brand_slug
                
                # Extract logo from img tag within the link
                brand_logo = link.css("img::attr(src)").get()
                if brand_logo:
                    brand_logo = response.urljoin(brand_logo)
                
                # Initialize brand data
                self.brand_data[brand_slug] = {
                    "slug": brand_slug,
                    "name": brand_name,
                    "logo": brand_logo,
                    "url": response.urljoin(href),
                    "models": {},
                    "model_count": 0,
                }
                
                self.file_logger.info(f"Following brand: {brand_name} ({brand_slug})")

                if self.brands_only:
                    continue
                
                # Follow the brand link to get its models
                yield response.follow(
                    href,
                    callback=self.parse_brand,
                    meta={"brand_slug": brand_slug},
                )

    def parse_brand(self, response):
        self.logger.info("BRAND %s status=%s", response.url, response.status)
        
        brand_slug = response.meta["brand_slug"]
        
        # Extract models from the brand page
        model_links = response.css(f"a[href*='/de/chiptuning/{self.scraper_type}/{brand_slug}/']")
        self.logger.info("Found %s models for brand %s", len(model_links), brand_slug)
        
        for link in model_links:
            href = link.css("::attr(href)").get()
            
            if not href:
                continue
            
            parts = self.path_parts(href)
            
            # Erwartet: /de/chiptuning/<type>/<brand>/<model>
            if len(parts) == 5 and parts[3] == brand_slug:
                model_slug = parts[4]

                model_slug_lower = model_slug.lower()
                prefix_match = any(
                    model_slug_lower.startswith(prefix) for prefix in self.model_prefix_filters
                )
                suffix_match = any(
                    model_slug_lower.endswith(suffix) for suffix in self.model_suffix_filters
                )

                if self.model_prefix_filters or self.model_suffix_filters:
                    if not (prefix_match or suffix_match):
                        continue
                
                # Extract model name from the visible listing label.
                model_display_name = self.clean(" ".join(link.xpath(".//h3//text()").getall())) or \
                                     self.clean(" ".join(link.xpath(".//text()").getall())) or \
                                     link.css("p::text").get() or \
                                     link.css("img::attr(alt)").get() or \
                                     model_slug
                model_name = model_display_name
                
                # Extract model image
                model_image = link.css("img::attr(src)").get()
                if model_image:
                    model_image = response.urljoin(model_image)
                
                # Initialize model data
                self.brand_data[brand_slug]["models"][model_slug] = {
                    "slug": model_slug,
                    "name": model_name,
                    "display_name": model_display_name,
                    "image": model_image,
                    "url": response.urljoin(href),
                    "versions": {},
                    "version_count": 0,
                }
                self.brand_data[brand_slug]["model_count"] += 1
                
                self.file_logger.info(f"Following model: {model_name} ({model_slug})")
                
                # Follow the model link to get versions
                yield response.follow(
                    href,
                    callback=self.parse_model,
                    meta={
                        "brand_slug": brand_slug,
                        "model_slug": model_slug,
                    },
                )

    def parse_model(self, response):
        self.logger.info("MODEL %s status=%s", response.url, response.status)
        
        brand_slug = response.meta["brand_slug"]
        model_slug = response.meta["model_slug"]
        
        # Extract versions from the model page
        version_links = response.css(
            f"a[href*='/de/chiptuning/{self.scraper_type}/{brand_slug}/{model_slug}/']"
        )
        self.logger.info("Found %s versions for model %s", len(version_links), model_slug)
        
        for link in version_links:
            href = link.css("::attr(href)").get()
            
            if not href:
                continue
            
            parts = self.path_parts(href)
            
            # Erwartet: /de/chiptuning/<type>/<brand>/<model>/<version>
            if len(parts) == 6 and parts[3] == brand_slug and parts[4] == model_slug:
                version_slug = parts[5]
                
                # Extract version name from the visible listing label.
                version_display_name = self.clean(" ".join(link.xpath(".//h3//text()").getall())) or \
                                       self.clean(" ".join(link.xpath(".//text()").getall())) or \
                                       link.css("p::text").get() or \
                                       link.css("img::attr(alt)").get() or \
                                       version_slug
                version_name = version_display_name
                
                # Initialize version data
                self.brand_data[brand_slug]["models"][model_slug]["versions"][version_slug] = {
                    "slug": version_slug,
                    "name": version_name,
                    "display_name": version_display_name,
                    "url": response.urljoin(href),
                    "engines": [],
                    "engine_count": 0,
                }
                self.brand_data[brand_slug]["models"][model_slug]["version_count"] += 1
                
                self.file_logger.info(f"Following version: {version_name} ({version_slug})")
                
                # Follow the version link to get engines
                yield response.follow(
                    href,
                    callback=self.parse_version,
                    meta={
                        "brand_slug": brand_slug,
                        "model_slug": model_slug,
                        "version_slug": version_slug,
                    },
                )

    def parse_version(self, response):
        self.logger.info("VERSION %s status=%s", response.url, response.status)
        
        brand_slug = response.meta["brand_slug"]
        model_slug = response.meta["model_slug"]
        version_slug = response.meta["version_slug"]

        # Extract engines from the version page
        engine_links = response.css(
            f"a[href*='/de/chiptuning/{self.scraper_type}/{brand_slug}/{model_slug}/{version_slug}/']"
        )
        self.logger.info("Found %s engines for version %s", len(engine_links), version_slug)

        for link in engine_links:
            href = link.css("::attr(href)").get()
            
            if not href:
                continue
            
            parts = self.path_parts(href)
            
            # Erwartet: /de/chiptuning/<type>/<brand>/<model>/<version>/<engine>
            if len(parts) == 7 and parts[3] == brand_slug and parts[4] == model_slug and parts[5] == version_slug:
                engine_slug = parts[6]
                
                # Extract the visible engine label from the listing column and keep the fuel group.
                engine_label = self.clean(" ".join(link.xpath(".//h3//text()").getall())) or \
                               self.clean(" ".join(link.xpath(".//text()").getall())) or \
                               link.css("img::attr(alt)").get() or \
                               engine_slug
                fuel_type = self.clean(
                    link.xpath(
                        "./ancestor::div[contains(@class, 'grid')][1]"
                        "/preceding-sibling::div[1]//p/text()"
                    ).get()
                )
                engine_name = engine_label if not fuel_type else f"{engine_label} - {fuel_type}"
                
                # Initialize engine data
                self.brand_data[brand_slug]["models"][model_slug]["versions"][version_slug]["engines"].append({
                    "slug": engine_slug,
                    "name": engine_name,
                    "display_name": engine_label,
                    "fuel_type": fuel_type,
                    "url": response.urljoin(href),
                    "original": None,
                    "controllers": [],
                    "options": [],
                    "stages": [],
                })

                self.file_logger.info(f"Added engine: {engine_name} ({engine_slug})")

                # Follow engine link to get stages
                yield response.follow(
                    href,
                    callback=self.parse_engine,
                    meta={
                        "brand_slug": brand_slug,
                        "model_slug": model_slug,
                        "version_slug": version_slug,
                        "engine_slug": engine_slug,
                    },
                )

    def parse_engine(self, response):
        self.logger.info("ENGINE %s status=%s", response.url, response.status)
        self._note_change()
        
        brand_slug = response.meta["brand_slug"]
        model_slug = response.meta["model_slug"]
        version_slug = response.meta["version_slug"]
        engine_slug = response.meta["engine_slug"]
        
        self.file_logger.info(f"Parsing engine page: {brand_slug}/{model_slug}/{version_slug}/{engine_slug}")

        # Find the engine in the data structure
        engine_data = None
        for engine in self.brand_data[brand_slug]["models"][model_slug]["versions"][version_slug]["engines"]:
            if engine["slug"] == engine_slug:
                engine_data = engine
                break
        
        if not engine_data:
            return

        stages = []
        for stage_slug in self._extract_stage_slugs(response):
            stage_metrics = self._extract_stage_metrics(response, stage_slug)

            if not engine_data["original"] and stage_metrics["original"]:
                engine_data["original"] = stage_metrics["original"]

            stage = stage_metrics["stage"]
            if stage:
                stages.append(stage)
                self.file_logger.info(
                    "Added stage metrics: %s (%s PS / %s Nm)",
                    stage["name"],
                    stage["power_ps"],
                    stage["torque_nm"],
                )

        engine_data["stages"] = sorted(stages, key=_sort_key_name_slug)
        engine_data["controllers"] = self._extract_controllers(response)
        engine_data["options"] = self._extract_options(response)
        self.file_logger.info("Engine %s controllers=%s options=%s", engine_slug, len(engine_data["controllers"]), len(engine_data["options"]))
        self.file_logger.info(f"Engine {engine_slug} has {len(engine_data['stages'])} stages")

    def _extract_stage_slugs(self, response) -> list[str]:
        slugs = []
        # Aktuelles Markup: Stage-Panels sind tabpanels mit id="panel-<slug>"
        for panel_id in response.xpath("//*[@role='tabpanel']/@id").getall():
            slug = (panel_id or "").strip().lower()
            if slug.startswith("panel-"):
                slug = slug[len("panel-"):]
            if slug in self.non_stage_slugs or not re.fullmatch(r"stage\d+[a-z0-9+-]*", slug):
                continue
            if slug not in slugs:
                slugs.append(slug)

        # Älteres Markup: <template x-if="selectedTab === '...'">
        for raw_condition in response.xpath("//template/@x-if").getall():
            if not raw_condition:
                continue

            match = re.search(r"selectedTab\s*===\s*'([^']+)'", raw_condition)
            if not match:
                continue

            slug = (match.group(1) or "").strip().lower()
            if slug in self.non_stage_slugs:
                continue
            if slug and slug not in slugs:
                slugs.append(slug)

        return slugs

    def _extract_stage_metrics(self, response, tab_slug: str) -> dict:
        # Aktuelles Markup: Tabelle im Panel id="panel-<slug>". Altes Markup: <template x-if>.
        rows = response.xpath(f'//*[@id="panel-{tab_slug}"]//tbody/tr')
        if not rows:
            rows = response.xpath(f'//template[contains(@x-if, "\'{tab_slug}\'")]//tbody/tr')
        row_map = {}

        for row in rows:
            # Zellen in Reihenfolge: Bezeichnung (th oder td), Leistung, Drehmoment
            cells = row.xpath("./th|./td")
            if len(cells) < 3:
                continue
            label = self.clean(" ".join(cells[0].xpath(".//text()").getall()))
            if not label:
                continue

            power_ps = self._extract_int(" ".join(cells[1].xpath(".//text()").getall()))
            torque_nm = self._extract_int(" ".join(cells[2].xpath(".//text()").getall()))

            row_map[label.lower()] = {
                "name": label,
                "power_ps": power_ps,
                "torque_nm": torque_nm,
            }

        original = row_map.get("original")
        stage_row_name = self.stage_label_by_slug.get(tab_slug, tab_slug.replace("-", " ").title())
        stage_row = row_map.get(stage_row_name.lower())

        if not stage_row:
            for key, row in row_map.items():
                if key in {"original", "difference"} or self._is_delta_row_label(key):
                    continue
                if row["power_ps"] is None and row["torque_nm"] is None:
                    continue
                stage_row = row
                break

        stage = None
        if stage_row and (stage_row["power_ps"] is not None or stage_row["torque_nm"] is not None):
            if self._is_delta_row_label((stage_row.get("name") or "").lower()):
                stage_row = None
        if stage_row and (stage_row["power_ps"] is not None or stage_row["torque_nm"] is not None):
            stage = {
                "slug": tab_slug,
                "name": stage_row.get("name") or stage_row_name,
                "url": response.url,
                "power_ps": stage_row["power_ps"],
                "torque_nm": stage_row["torque_nm"],
            }

        if original and (original["power_ps"] is None and original["torque_nm"] is None):
            original = None

        return {
            "original": original,
            "stage": stage,
        }

    def _is_delta_row_label(self, label: str) -> bool:
        lowered = (label or "").strip().lower()
        return lowered in {"difference", "diff", "gain", "delta", "zuwachs", "steigerung"}

    def _extract_int(self, value: str | None) -> int | None:
        if not value:
            return None

        match = re.search(r"-?\d+", value)
        if not match:
            return None

        return int(match.group(0))

    def _extract_controllers(self, response) -> list[dict]:
        controllers = []

        # Aktuelles Markup: Liste (ul > li) unter der Überschrift "Steuergeräte ...".
        # Read-Methoden stehen in der Zeile "Lesegeräte: A, B, C".
        for heading in response.xpath('//h2[contains(normalize-space(.), "Steuergeräte")]')[:1]:
            for item in heading.xpath("following-sibling::ul[1]/li"):
                manufacturer = self.clean(" ".join(item.xpath(".//strong//text()").getall()))
                full_text = self.clean(" ".join(item.xpath("./p[1]//text()").getall()))
                model = full_text
                if manufacturer and full_text.startswith(manufacturer):
                    model = self.clean(full_text[len(manufacturer):])

                tools_text = self.clean(" ".join(item.xpath("./p[contains(@class, 'text-sm')]//text()").getall()))
                tools_text = re.sub(r"^[^:]*:\s*", "", tools_text or "")
                tools = [tool for tool in (self.clean(part) for part in tools_text.split(",")) if tool]

                if not manufacturer and not model:
                    continue

                ecu_type = "MCU"
                if (manufacturer or "").upper() in {"ZF", "TEMIC"} or "DQ" in (model or "").upper():
                    ecu_type = "TCU"

                controllers.append({
                    "manufacturer": manufacturer,
                    "model": model,
                    "ecu_type": ecu_type,
                    "tools": tools,
                })

        if controllers:
            return sorted(controllers, key=_controller_sort_key)

        # Älteres Markup: Links nach der Überschrift, Read-Methoden im Tooltip
        controller_links = response.xpath(
            '//h2[contains(normalize-space(.), "Steuergeräte")]'
            '/following-sibling::a'
        )

        for link in controller_links:
            manufacturer = self.clean(" ".join(link.xpath(".//strong//text()").getall()))
            full_text = self.clean(" ".join(link.xpath(".//text()").getall()))
            tooltip = self.clean(link.xpath("./@x-tooltip.raw").get())

            model = full_text
            if manufacturer and full_text and full_text.startswith(manufacturer):
                model = self.clean(full_text[len(manufacturer):])

            tools = []
            if tooltip:
                tools = [tool for tool in (self.clean(part) for part in tooltip.replace("&nbsp;", " ").split(",")) if tool]

            ecu_type = "MCU"
            manufacturer_upper = (manufacturer or "").upper()
            model_upper = (model or "").upper()
            if manufacturer_upper in {"ZF", "TEMIC"} or "DQ" in model_upper:
                ecu_type = "TCU"

            controllers.append({
                "manufacturer": manufacturer,
                "model": model,
                "ecu_type": ecu_type,
                "tools": tools,
            })

        return sorted(controllers, key=_controller_sort_key)

    def _extract_options(self, response) -> list[dict]:
        options = []
        # Optionen sind inzwischen Buttons (früher Links). Nur Geschwister bis zur nächsten Überschrift.
        option_links = []
        for heading in response.xpath('//h2[contains(normalize-space(.), "Verfügbare Optionen")]'):
            for sibling in heading.xpath("following-sibling::*"):
                tag = sibling.root.tag if hasattr(sibling.root, "tag") else ""
                if tag == "h2":
                    break
                if tag in ("a", "button"):
                    option_links.append(sibling)

        for link in option_links:
            name = self.clean(" ".join(link.xpath(".//text()").getall()))
            description = self.clean(link.xpath("./@x-tooltip.raw").get())
            if not name:
                continue

            options.append({
                "name": name,
                "description": description,
            })

        return options

    def _group_controllers(self, controllers: list[dict]) -> list[dict]:
        grouped = {}

        for controller in sorted(controllers, key=_controller_sort_key):
            manufacturer = controller.get("manufacturer") or "Unknown"
            if manufacturer not in grouped:
                grouped[manufacturer] = {
                    "manufacturer": manufacturer,
                    "items": [],
                }

            grouped[manufacturer]["items"].append({
                "model": controller.get("model"),
                "ecu_type": controller.get("ecu_type"),
                "tools": controller.get("tools", []),
            })

        return sorted(grouped.values(), key=_manufacturer_sort_key)

    def _serialize_brand(self, brand_slug: str) -> dict:
        """Ein Marken-Objekt: verschachtelte Listen, alphabetisch sortiert."""
        b = self.brand_data[brand_slug]
        models_out = []
        for m_slug in sorted(b["models"].keys(), key=lambda s: _sort_key_name_slug(b["models"][s])):
            m = b["models"][m_slug]
            versions_out = []
            for v_slug in sorted(m["versions"].keys(), key=lambda s: _sort_key_name_slug(m["versions"][s])):
                v = m["versions"][v_slug]
                engines_out = []
                for eng in v.get("engines", []):
                    stages = eng.get("stages", [])
                    if isinstance(stages, dict):
                        stages = sorted(stages.values(), key=_sort_key_name_slug)
                    else:
                        stages = sorted(stages, key=_sort_key_name_slug)
                    engines_out.append({
                        "slug": eng["slug"],
                        "name": eng["name"],
                        "display_name": eng.get("display_name"),
                        "fuel_type": eng.get("fuel_type"),
                        "url": eng["url"],
                        "original": eng.get("original"),
                        "controllers": self._group_controllers(eng.get("controllers", [])),
                        "options": eng.get("options", []),
                        "stage_count": len(stages),
                        "stages": stages,
                    })
                engines_out.sort(key=_sort_key_name_slug)
                versions_out.append({
                    "slug": v["slug"],
                    "name": v["name"],
                    "display_name": v.get("display_name"),
                    "url": v["url"],
                    "engine_count": len(engines_out),
                    "engines": engines_out,
                })
            models_out.append({
                "slug": m["slug"],
                "name": m["name"],
                "display_name": m.get("display_name"),
                "image": m["image"],
                "url": m["url"],
                "version_count": len(versions_out),
                "versions": versions_out,
            })
        models_out.sort(key=_sort_key_name_slug)
        return {
            "brand_slug": b["slug"],
            "brand_name": b["name"],
            "brand_logo": b["logo"],
            "brand_url": b["url"],
            "model_count": len(models_out),
            "models": models_out,
        }

    def _write_hierarchical_json(self, suffix: str = "") -> str:
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        output_dir = os.path.join(os.getcwd(), "data", "output", self.name, self.scraper_type)
        os.makedirs(output_dir, exist_ok=True)
        out_path = os.path.join(
            output_dir,
            f"olsx_brands_{self.scraper_type}_{timestamp}{suffix}.json",
        )
        brands = [
            self._serialize_brand(slug)
            for slug in sorted(
                self.brand_data.keys(),
                key=lambda s: _sort_key_name_slug(self.brand_data[s]),
            )
        ]
        with open(out_path, "w", encoding="utf-8") as f:
            json.dump(brands, f, ensure_ascii=False, indent=2)
        self.logger.info("Hierarchische Ausgabe geschrieben: %s (%s Marken)", out_path, len(brands))
        self.file_logger.info(
            "Hierarchische Ausgabe geschrieben: %s (%s Marken)",
            out_path,
            len(brands),
        )
        return out_path

    def _output_dir(self) -> str:
        return os.path.join(os.getcwd(), "data", "output", self.name, self.scraper_type)

    def _fill_source_path(self) -> str:
        if self.fill_source:
            return self.fill_source
        candidates = [
            os.path.join(self._output_dir(), f)
            for f in os.listdir(self._output_dir())
            if f.startswith(f"olsx_brands_{self.scraper_type}_") and f.endswith(".json") and "_partial" not in f
        ]
        if not candidates:
            raise ValueError(f"Keine Ausgabedatei zum Nachfüllen gefunden in {self._output_dir()}")
        return max(candidates, key=os.path.getmtime)

    def _load_fill_source(self) -> list:
        with open(self._fill_source_path(), encoding="utf-8") as f:
            return json.load(f)

    def start_requests(self):
        if not self.fill_missing:
            yield from super().start_requests()
            return

        for b_i, brand in enumerate(self.fill_data):
            for m_i, model in enumerate(brand.get("models", [])):
                for v_i, version in enumerate(model.get("versions", [])):
                    for e_i, engine in enumerate(version.get("engines", [])):
                        if engine.get("stages_checked") or not engine.get("url"):
                            continue
                        yield scrapy.Request(
                            engine["url"],
                            callback=self.parse_engine_fill,
                            meta={"ref": (b_i, m_i, v_i, e_i)},
                            dont_filter=True,
                        )

    def parse_engine_fill(self, response):
        b_i, m_i, v_i, e_i = response.meta["ref"]
        engine = self.fill_data[b_i]["models"][m_i]["versions"][v_i]["engines"][e_i]

        stages = []
        for stage_slug in self._extract_stage_slugs(response):
            metrics = self._extract_stage_metrics(response, stage_slug)
            if not engine.get("original") and metrics["original"]:
                engine["original"] = metrics["original"]
            if metrics["stage"]:
                stages.append(metrics["stage"])

        engine["stages"] = sorted(stages, key=_sort_key_name_slug)
        engine["stage_count"] = len(engine["stages"])
        engine["options"] = self._extract_options(response)
        # Steuergeräte inkl. Read-Methoden neu, im Ausgabeformat (nach Hersteller gruppiert)
        engine["controllers"] = self._group_controllers(self._extract_controllers(response))
        engine["stages_checked"] = True

        self._fill_done += 1
        self.logger.info("FILL %s stages=%s options=%s controllers=%s", response.url, engine["stage_count"], len(engine["options"]), len(engine["controllers"]))
        if self._fill_done % 200 == 0:
            self._write_filled()

    def _write_filled(self) -> None:
        if self._fill_output_path is None:
            os.makedirs(self._output_dir(), exist_ok=True)
            ts = datetime.now().strftime("%Y%m%d_%H%M%S")
            self._fill_output_path = os.path.join(self._output_dir(), f"olsx_brands_{self.scraper_type}_{ts}_filled.json")
        tmp_path = f"{self._fill_output_path}.tmp"
        with open(tmp_path, "w", encoding="utf-8") as f:
            json.dump(self.fill_data, f, ensure_ascii=False)
        os.replace(tmp_path, self._fill_output_path)

    def _note_change(self, every: int = 25) -> None:
        """Alle `every` Engine-Seiten den Zwischenstand sichern."""
        self._changes_since_checkpoint += 1
        if self._changes_since_checkpoint >= every:
            self._save_checkpoint()

    def _save_checkpoint(self) -> None:
        if not self.checkpoint_path or not self.brand_data:
            return
        tmp_path = f"{self.checkpoint_path}.tmp"
        os.makedirs(os.path.dirname(self.checkpoint_path) or ".", exist_ok=True)
        with open(tmp_path, "w", encoding="utf-8") as f:
            json.dump(self.brand_data, f, ensure_ascii=False)
        os.replace(tmp_path, self.checkpoint_path)  # atomar: nie eine halbe Datei
        self._changes_since_checkpoint = 0

    def closed(self, reason):
        if self.fill_missing:
            self._write_filled()
            self.logger.info("Nachfüllen beendet (reason=%s): %s Motoren geprüft, %s", reason, self._fill_done, self._fill_output_path)
            return
        self._save_checkpoint()
        if not self.brand_data:
            return

        try:
            if reason == "finished":
                self._write_hierarchical_json()
                return

            out_path = self._write_hierarchical_json(suffix="_partial")
            self.logger.warning(
                "Spider vorzeitig beendet (reason=%s). Partieller Export geschrieben: %s",
                reason,
                out_path,
            )
            self.file_logger.warning(
                "Spider vorzeitig beendet (reason=%s). Partieller Export geschrieben: %s",
                reason,
                out_path,
            )
        except OSError as exc:
            self.logger.exception("olsx_brands.json konnte nicht geschrieben werden: %s", exc)

    # -------------------------
    # Helper
    # -------------------------

    def path_parts(self, url):
        path = urlparse(url).path.strip("/")
        return [p for p in path.split("/") if p]

    def clean(self, value):
        if not value:
            return None
        value = " ".join(value.split()).strip()
        return value or None
