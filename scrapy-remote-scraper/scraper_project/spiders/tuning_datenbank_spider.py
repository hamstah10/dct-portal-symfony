import glob
import json
import logging
import os
import re
import unicodedata

import scrapy
from scrapy.exceptions import CloseSpider


class TuningDatenbankSpider(scrapy.Spider):
    """
    Scraped die AJAX-API von tuning-datenbank.de (Demo-Konfigurator), die die
    Fahrzeughierarchie Hersteller -> Modell -> Generation -> Motor sowie pro
    Motor Spezifikationen, Tuning-Stages (inkl. Preise), Optionen und
    Dyno-Kurvendaten liefert.

    API-Kette:
        GET  /api/get-vehicle-types
        POST /api/get-manufactures   {vehicletype_id}
        POST /api/get-modells        {manufractures_id}
        POST /api/get-generations    {model_id}
        POST /api/get-engines        {generation_id}
        POST /api/get-engine         {engine}              -> Details, Stages, Optionen
        POST /api/get-dyno           {engine, stage}        -> Kurvendaten (RPM/kW)
    """

    name = "tuning_datenbank"
    allowed_domains = ["anfrage.tuning-datenbank.de"]

    BASE_URL = "https://anfrage.tuning-datenbank.de"

    custom_settings = {
        "ROBOTSTXT_OBEY": False,
        "DOWNLOAD_DELAY": 1.0,
        "RANDOMIZE_DOWNLOAD_DELAY": True,
        "CONCURRENT_REQUESTS": 2,
        "CONCURRENT_REQUESTS_PER_DOMAIN": 2,
        "AUTOTHROTTLE_ENABLED": True,
        "AUTOTHROTTLE_START_DELAY": 1.0,
        "AUTOTHROTTLE_MAX_DELAY": 15.0,
        "AUTOTHROTTLE_TARGET_CONCURRENCY": 1.0,
        "RETRY_TIMES": 5,
        "RETRY_HTTP_CODES": [429, 500, 502, 503, 504, 522, 524, 408],
        "FEEDS": {},
        "LOG_LEVEL": "INFO",
        "USER_AGENT": (
            "Mozilla/5.0 (X11; Linux x86_64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/122.0.0.0 Safari/537.36"
        ),
        "DEFAULT_REQUEST_HEADERS": {
            "Accept": "application/json, text/html, */*",
            "Accept-Language": "de-DE,de;q=0.9,en;q=0.8",
            "Referer": "https://www.tuning-datenbank.de/",
            "X-Requested-With": "XMLHttpRequest",
        },
    }

    def __init__(
        self,
        vehicletype=None,
        manufacturer=None,
        output_dir=None,
        logs_dir=None,
        with_dyno=None,
        force=None,
        dyno_only=None,
        *args,
        **kwargs,
    ):
        super().__init__(*args, **kwargs)
        self.only_vehicletype_id = str(vehicletype).strip() if vehicletype else None
        self.only_manufacturer_name = self.clean_text(manufacturer) if manufacturer else None
        self.with_dyno = self.parse_bool(with_dyno, default=False)
        self.force = self.parse_bool(force, default=False)
        self.dyno_only = self.parse_bool(dyno_only, default=False)

        base_dir = os.path.join(os.getcwd(), "data")
        self.output_dir = output_dir or os.path.join(base_dir, "output", self.name)
        self.logs_dir = logs_dir or os.path.join(base_dir, "logs", self.name)
        for path in (self.output_dir, self.logs_dir):
            os.makedirs(path, exist_ok=True)

        self.configure_file_logger()

        # brand_slug -> serialisierbare Struktur (gleiche Form wie unsere anderen Scraper-Outputs)
        self.brand_data = {}
        self.vehicle_types = []

    def start_requests(self):
        if self.dyno_only:
            yield from self.start_dyno_only_requests()
            return

        yield scrapy.Request(
            f"{self.BASE_URL}/api/get-vehicle-types?f_code=tdb",
            callback=self.parse_vehicle_types,
        )

    def start_dyno_only_requests(self):
        """Backfill mode: reads already-scraped brand JSON files and fetches only the missing
        dyno_chart per engine/first-stage via source_id/stage_id, without re-walking the full
        vehicletype -> manufacturer -> model -> generation -> engine hierarchy."""
        for type_dir in sorted(glob.glob(os.path.join(self.output_dir, "*"))):
            if not os.path.isdir(type_dir):
                continue
            type_slug = os.path.basename(type_dir)

            for file_path in sorted(glob.glob(os.path.join(type_dir, "*.json"))):
                brand_slug = os.path.splitext(os.path.basename(file_path))[0]
                if self.only_manufacturer_name and brand_slug != self.slugify(self.only_manufacturer_name):
                    continue

                try:
                    with open(file_path, "r", encoding="utf-8") as handle:
                        payload = json.load(handle)
                except (OSError, json.JSONDecodeError):
                    self.file_logger.warning("DYNO-ONLY: cannot read %s", file_path)
                    continue

                self.brand_data[brand_slug] = {
                    "slug": payload.get("brand_slug", brand_slug),
                    "name": payload.get("brand_name", brand_slug),
                    "vehicle_type": payload.get("vehicle_type", type_slug),
                    "models": self.rehydrate_models(payload.get("models", [])),
                    "expected_models": payload.get("expected_model_count", len(payload.get("models", []))),
                    "processed_models": set(self.slugify(m.get("name")) for m in payload.get("models", [])),
                }

                requested = 0
                for model in payload.get("models", []):
                    model_name = model.get("name")
                    for version in model.get("versions", []):
                        generation_name = version.get("name")
                        for engine_row in version.get("engines", []):
                            if engine_row.get("dyno_chart"):
                                continue
                            source_id = engine_row.get("source_id")
                            stages = engine_row.get("stages", [])
                            if not source_id or not stages:
                                continue
                            first_stage = stages[0]
                            stage_id = first_stage.get("stage_id")
                            if not stage_id:
                                continue

                            requested += 1
                            yield scrapy.FormRequest(
                                f"{self.BASE_URL}/api/get-dyno",
                                formdata={"engine": str(source_id), "stage": str(stage_id)},
                                callback=self.parse_dyno,
                                errback=self.handle_request_failure,
                                meta={
                                    "brand_slug": brand_slug,
                                    "model_name": model_name,
                                    "generation_name": generation_name,
                                    "engine_slug": engine_row.get("slug"),
                                },
                            )

                self.file_logger.info(
                    "DYNO-ONLY: brand=%s file=%s queued=%s", brand_slug, file_path, requested
                )

    def rehydrate_models(self, models):
        """Converts the serialized (list-based) models/versions/engines structure back into the
        mutable dict-keyed-by-slug shape append_engine()/find_engine() expect while running."""
        rehydrated = {}
        for model in models:
            model_slug = model.get("slug") or self.slugify(model.get("name"))
            versions = {}
            for version in model.get("versions", []):
                version_slug = version.get("slug") or self.slugify(version.get("name"))
                versions[version_slug] = {
                    "slug": version_slug,
                    "name": version.get("name"),
                    "engines": version.get("engines", []),
                }
            rehydrated[model_slug] = {
                "slug": model_slug,
                "name": model.get("name"),
                "versions": versions,
            }
        return rehydrated

    def parse_vehicle_types(self, response):
        payload = self.load_json(response)
        if not payload or payload.get("status") != "ok":
            raise CloseSpider(f"get-vehicle-types failed: {response.status}")

        self.vehicle_types = payload.get("vehicletypes", [])
        self.file_logger.info("Vehicle types: %s", [(t["id"], t["name"]) for t in self.vehicle_types])

        for vtype in self.vehicle_types:
            if self.only_vehicletype_id and str(vtype["id"]) != self.only_vehicletype_id:
                continue
            yield scrapy.FormRequest(
                f"{self.BASE_URL}/api/get-manufactures",
                formdata={"vehicletype_id": str(vtype["id"])},
                callback=self.parse_manufacturers,
                meta={"vehicle_type": vtype},
            )

    def parse_manufacturers(self, response):
        vehicle_type = response.meta["vehicle_type"]
        entries = self.extract_options(response.text)
        self.file_logger.info(
            "MANUFACTURERS type=%s status=%s count=%s", vehicle_type["name"], response.status, len(entries)
        )

        for manufacturer_id, manufacturer_name in entries:
            if self.only_manufacturer_name and manufacturer_name.lower() != self.only_manufacturer_name.lower():
                continue

            brand_slug = self.slugify(manufacturer_name)
            type_slug = self.slugify(vehicle_type["name"])
            if not self.force and self.is_existing_output_valid(type_slug, brand_slug):
                self.file_logger.info("SKIP type=%s brand=%s (output already exists)", type_slug, brand_slug)
                continue

            self.ensure_brand_stub(brand_slug, manufacturer_name, vehicle_type)

            yield scrapy.FormRequest(
                f"{self.BASE_URL}/api/get-modells",
                formdata={"manufractures_id": manufacturer_id},
                callback=self.parse_models,
                meta={
                    "vehicle_type": vehicle_type,
                    "brand_slug": brand_slug,
                    "manufacturer_id": manufacturer_id,
                    "manufacturer_name": manufacturer_name,
                },
            )

    def parse_models(self, response):
        brand_slug = response.meta["brand_slug"]
        entries = self.extract_options(response.text)
        self.file_logger.info(
            "MODELS brand=%s status=%s count=%s", brand_slug, response.status, len(entries)
        )

        self.brand_data[brand_slug]["expected_models"] = len(entries)

        if not entries:
            return

        for model_id, model_name in entries:
            yield scrapy.FormRequest(
                f"{self.BASE_URL}/api/get-generations",
                formdata={"model_id": model_id},
                callback=self.parse_generations,
                errback=self.handle_request_failure,
                meta={**response.meta, "model_id": model_id, "model_name": model_name},
            )

    def parse_generations(self, response):
        brand_slug = response.meta["brand_slug"]
        model_name = response.meta["model_name"]
        entries = self.extract_options(response.text)
        self.file_logger.info(
            "GENERATIONS model=%s status=%s count=%s", model_name, response.status, len(entries)
        )

        if not entries:
            self.mark_model_processed(brand_slug, model_name, has_engines=False)
            return

        for generation_id, generation_name in entries:
            yield scrapy.FormRequest(
                f"{self.BASE_URL}/api/get-engines",
                formdata={"generation_id": generation_id},
                callback=self.parse_engines,
                errback=self.handle_request_failure,
                meta={**response.meta, "generation_id": generation_id, "generation_name": generation_name},
            )

    def parse_engines(self, response):
        brand_slug = response.meta["brand_slug"]
        model_name = response.meta["model_name"]
        entries = self.extract_options(response.text)
        self.file_logger.info(
            "ENGINES generation=%s status=%s count=%s",
            response.meta["generation_name"],
            response.status,
            len(entries),
        )

        if not entries:
            self.mark_model_processed(brand_slug, model_name, has_engines=False)
            return

        for engine_id, _engine_label in entries:
            yield scrapy.FormRequest(
                f"{self.BASE_URL}/api/get-engine",
                formdata={"engine": engine_id},
                callback=self.parse_engine_details,
                errback=self.handle_request_failure,
                meta={**response.meta, "engine_id": engine_id},
            )

    def handle_request_failure(self, failure):
        """Requests that error out on the transport level (timeout, connection reset, etc.)
        never reach their callback, so without this the affected model/generation/engine
        would silently vanish from the output while scrape_status still said "complete"."""
        request = failure.request
        meta = request.meta
        brand_slug = meta.get("brand_slug")
        model_name = meta.get("model_name")
        self.file_logger.warning(
            "REQUEST FAILED url=%s brand=%s model=%s error=%s",
            request.url, brand_slug, model_name, repr(failure.value),
        )
        if brand_slug and model_name:
            self.mark_model_processed(brand_slug, model_name, has_engines=False)

    def parse_engine_details(self, response):
        payload = self.load_json(response)
        if not payload or "engine" not in payload:
            self.file_logger.warning("get-engine failed engine_id=%s status=%s", response.meta["engine_id"], response.status)
            return

        engine = payload["engine"]
        brand_slug = response.meta["brand_slug"]
        model_name = response.meta["model_name"]
        generation_name = response.meta["generation_name"]

        stages = []
        for stage in engine.get("tuning_stages", []):
            stages.append(
                {
                    "slug": self.slugify(stage.get("stage_name") or f"stage-{stage.get('stage_id')}"),
                    "name": stage.get("stage_name"),
                    "stage_id": stage.get("stage_id"),
                    "power_ps": self.to_number(stage.get("tuned_power_ps")),
                    "torque_nm": self.to_number(stage.get("tuned_torque_nm")),
                    "dynamic_power_ps": self.to_number(stage.get("dynamic_power_ps")),
                    "dynamic_torque_nm": self.to_number(stage.get("dynamic_torque_nm")),
                    "price": self.to_number(stage.get("stage_price")),
                }
            )

        options = [
            {
                "slug": option.get("slug"),
                "name": option.get("name"),
            }
            for option in engine.get("addons", [])
        ]

        read_methods = [method.get("name") for method in engine.get("read_methods", []) if method.get("name")]
        controllers = self.build_controllers(engine.get("ecus", []), read_methods)

        engine_row = {
            "slug": engine.get("slug"),
            "source_id": engine.get("id"),
            "name": self.build_engine_name(engine),
            "display_name": engine.get("name"),
            "fuel_type": engine.get("fuel_type"),
            "original": {
                "name": "Original",
                "power_ps": self.to_number(engine.get("original_power_ps")),
                "torque_nm": self.to_number(engine.get("original_torque_nm")),
                "power_kw": self.to_number(engine.get("original_kw")),
            },
            "specifications_de": {
                "hubraum": f"{engine['displacement']} CC" if engine.get("displacement") else None,
                "verdichtung": self.normalize_ratio(engine.get("compression")),
                "bohrung_x_hub": self.normalize_bore(engine.get("bore")),
                "motorkennbuchstabe": engine.get("engine_code"),
            },
            "read_methods": read_methods,
            "controllers": controllers,
            "stages": stages,
            "options": options,
        }

        self.append_engine(brand_slug, model_name, generation_name, engine_row)

        if self.with_dyno and stages:
            first_stage = stages[0]
            yield scrapy.FormRequest(
                f"{self.BASE_URL}/api/get-dyno",
                formdata={"engine": str(response.meta["engine_id"]), "stage": str(first_stage["stage_id"])},
                callback=self.parse_dyno,
                meta={"brand_slug": brand_slug, "model_name": model_name, "generation_name": generation_name, "engine_slug": engine_row["slug"]},
            )
        else:
            self.save_brand(brand_slug)

    def parse_dyno(self, response):
        payload = self.load_json(response)
        if not payload or payload.get("status") != "ok":
            return

        dynodata = payload.get("dynodata", {})
        brand_slug = response.meta["brand_slug"]
        model_name = response.meta["model_name"]
        generation_name = response.meta["generation_name"]
        engine_slug = response.meta["engine_slug"]

        engine_row = self.find_engine(brand_slug, model_name, generation_name, engine_slug)
        if engine_row is not None:
            engine_row["dyno_chart"] = dynodata
        self.save_brand(brand_slug)

    def brand_output_path(self, type_slug, brand_slug):
        # Eigener Unterordner pro Fahrzeugtyp, da Herstellernamen zwischen Typen kollidieren
        # können (z. B. "Mercedes" existiert sowohl bei Car als auch bei Truck).
        type_dir = os.path.join(self.output_dir, type_slug)
        os.makedirs(type_dir, exist_ok=True)
        return os.path.join(type_dir, f"{brand_slug}.json")

    def save_brand(self, brand_slug):
        """Schreibt die Marken-Datei sofort nach jedem verarbeiteten Motor neu (Zwischenstand),
        damit bei Abbruch/Absturz eines langen Laufs kein bereits gescrapter Fortschritt verloren geht."""
        brand = self.brand_data[brand_slug]
        type_slug = self.slugify(brand["vehicle_type"] or "")
        payload = self.serialize_brand(brand_slug)
        output_path = self.brand_output_path(type_slug, brand_slug)
        tmp_path = output_path + ".tmp"
        with open(tmp_path, "w", encoding="utf-8") as handle:
            json.dump(payload, handle, ensure_ascii=False, indent=2)
        os.replace(tmp_path, output_path)

    def closed(self, reason):
        self.file_logger.info("Run finished reason=%s", reason)
        for brand_slug in self.brand_data:
            self.save_brand(brand_slug)
        self.file_logger.info("Saved %s brand file(s) to %s", len(self.brand_data), self.output_dir)

    def is_existing_output_valid(self, type_slug, brand_slug):
        output_path = self.brand_output_path(type_slug, brand_slug)
        if not os.path.exists(output_path):
            return False
        try:
            with open(output_path, "r", encoding="utf-8") as handle:
                payload = json.load(handle)
            if not bool(payload.get("brand_name")) or not isinstance(payload.get("models"), list):
                return False
            if payload.get("scrape_status") != "complete":
                return False
            # Ein Stub mit 0 Motoren zählt nicht als fertig verarbeitet (z. B. wenn ein vorheriger
            # Lauf mitten in der Marke abgebrochen wurde, bevor Engines gefunden wurden).
            total_engines = sum(
                len(v.get("engines", []))
                for m in payload["models"]
                for v in m.get("versions", [])
            )
            return total_engines > 0
        except (OSError, json.JSONDecodeError, ValueError, TypeError):
            return False

    # ------------------------------------------------------------------
    # Datenstruktur-Helfer
    # ------------------------------------------------------------------

    def ensure_brand_stub(self, brand_slug, brand_name, vehicle_type):
        if brand_slug in self.brand_data:
            return
        self.brand_data[brand_slug] = {
            "slug": brand_slug,
            "name": brand_name,
            "vehicle_type": vehicle_type.get("name"),
            "models": {},
            "expected_models": 0,
            "processed_models": set(),
        }

    def mark_model_processed(self, brand_slug, model_name, has_engines):
        """Records that a model's request chain reached a terminal state (engines found,
        empty generations/engines list, or a failed request) so scrape completeness can be
        judged by comparing this against expected_models, instead of trusting a hardcoded status."""
        brand = self.brand_data.get(brand_slug)
        if brand is None:
            return
        brand["processed_models"].add(self.slugify(model_name))

    def append_engine(self, brand_slug, model_name, generation_name, engine_row):
        brand = self.brand_data[brand_slug]
        model_slug = self.slugify(model_name)
        model = brand["models"].setdefault(
            model_slug, {"slug": model_slug, "name": model_name, "versions": {}}
        )
        version_slug = self.slugify(generation_name)
        version = model["versions"].setdefault(
            version_slug, {"slug": version_slug, "name": generation_name, "engines": []}
        )
        version["engines"].append(engine_row)
        self.mark_model_processed(brand_slug, model_name, has_engines=True)

    def find_engine(self, brand_slug, model_name, generation_name, engine_slug):
        brand = self.brand_data.get(brand_slug)
        if not brand:
            return None
        model = brand["models"].get(self.slugify(model_name))
        if not model:
            return None
        version = model["versions"].get(self.slugify(generation_name))
        if not version:
            return None
        for engine in version["engines"]:
            if engine["slug"] == engine_slug:
                return engine
        return None

    def serialize_brand(self, brand_slug):
        brand = self.brand_data[brand_slug]
        models = []
        for model in brand["models"].values():
            versions = []
            for version in model["versions"].values():
                versions.append(
                    {
                        "slug": version["slug"],
                        "name": version["name"],
                        "engine_count": len(version["engines"]),
                        "engines": version["engines"],
                    }
                )
            models.append(
                {
                    "slug": model["slug"],
                    "name": model["name"],
                    "version_count": len(versions),
                    "versions": versions,
                }
            )
        expected_models = brand.get("expected_models", 0)
        processed_models = len(brand.get("processed_models", ()))
        scrape_status = "complete" if expected_models > 0 and processed_models >= expected_models else "partial"

        return {
            "brand_slug": brand["slug"],
            "brand_name": brand["name"],
            "file_slug": brand["slug"],
            "vehicle_type": brand["vehicle_type"],
            "scraper_source": self.name,
            "scraper_type": "mixed",
            "scrape_status": scrape_status,
            "expected_model_count": expected_models,
            "processed_model_count": processed_models,
            "model_count": len(models),
            "models": models,
        }

    def build_engine_name(self, engine):
        display_name = engine.get("name") or ""
        fuel = engine.get("fuel_type") or ""
        if fuel and fuel.lower() not in display_name.lower():
            return f"{display_name} - {fuel}"
        return display_name

    def build_controllers(self, ecus, read_methods):
        """
        Wandelt die von der API gelieferten ECUs (z. B. "Bosch EDC17CP54", type="ecu"/"tcu")
        in dasselbe controllers-Format um, das unser bestehender dyno_chiptuningfiles-Importer
        erwartet: [{"manufacturer": ..., "items": [{"model": ..., "ecu_type": "MCU"/"TCU", "tools": [...]}]}]
        """
        grouped = {}
        for ecu in ecus:
            name = self.clean_text(ecu.get("name") or "")
            if not name:
                continue
            parts = name.split(" ", 1)
            manufacturer = parts[0]
            model = parts[1] if len(parts) > 1 else parts[0]
            ecu_type = "TCU" if (ecu.get("type") or "").lower() == "tcu" else "MCU"

            grouped.setdefault(manufacturer, []).append(
                {"model": model, "ecu_type": ecu_type, "tools": read_methods or ["--"]}
            )

        return [{"manufacturer": manufacturer, "items": items} for manufacturer, items in grouped.items()]

    # ------------------------------------------------------------------
    # Parsing-Helfer
    # ------------------------------------------------------------------

    def extract_options(self, html):
        """Parst rohes <option value="ID">Name</option> HTML aus den Dropdown-Endpunkten."""
        entries = []
        for match in re.finditer(r'<option(?:\s+value="(\d+)")?[^>]*>([^<]+)</option>', html):
            value_id, label = match.group(1), self.clean_text(match.group(2))
            if not value_id or not label:
                continue
            entries.append((value_id, label))
        return entries

    def normalize_ratio(self, value):
        """'16,0 : 1' -> '16.0:1'"""
        if not value:
            return None
        value = value.replace(",", ".")
        match = re.search(r"([\d.]+)\s*:\s*([\d.]+)", value)
        if not match:
            return None
        return f"{match.group(1)}:{match.group(2)}"

    def normalize_bore(self, value):
        """'83,0 X 91,4 mm' -> '83.0 x 91.4 mm'"""
        if not value:
            return None
        value = value.replace(",", ".")
        match = re.search(r"([\d.]+)\s*[xX]\s*([\d.]+)\s*mm", value)
        if not match:
            return None
        return f"{match.group(1)} x {match.group(2)} mm"

    def to_number(self, value):
        if value is None:
            return None
        try:
            number = float(value)
        except (TypeError, ValueError):
            return None
        return int(number) if number.is_integer() else number

    def load_json(self, response):
        try:
            return json.loads(response.text)
        except (json.JSONDecodeError, TypeError):
            self.file_logger.warning("Invalid JSON from %s (status=%s)", response.url, response.status)
            return None

    def configure_file_logger(self):
        log_path = os.path.join(self.logs_dir, "tuning_datenbank.log")
        self.file_logger = logging.getLogger(self.name)
        self.file_logger.setLevel(logging.DEBUG)
        self.file_logger.handlers = []
        self.file_logger.propagate = False
        formatter = logging.Formatter("%(asctime)s - %(levelname)s - %(message)s")
        handler = logging.FileHandler(log_path)
        handler.setLevel(logging.DEBUG)
        handler.setFormatter(formatter)
        self.file_logger.addHandler(handler)

    def parse_bool(self, value, default):
        if value is None:
            return default
        return str(value).strip().lower() in {"1", "true", "yes", "on"}

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
        return re.sub(r"\s+", " ", value).strip()
