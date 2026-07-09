import json
import logging
import os
import unicodedata

from scrapy.exceptions import CloseSpider

from scraper_project.spiders.olsx_brands_spider import OlsxBrandsSpider, _sort_key_name_slug


def _parse_bool(value, default: bool) -> bool:
    if value is None:
        return default
    return str(value).strip().lower() in {"1", "true", "yes", "on"}


class OlsxBrandFilesSpider(OlsxBrandsSpider):
    name = "olsx_brand_files_spider"
    custom_settings = {
        **OlsxBrandsSpider.custom_settings,
        "FEEDS": {},
    }
    supported_modes = {
        "brands:fetch",
        "brands:scrape-all",
        "brands:scrape-one",
        "brands:scrape-missing",
    }
    invalid_brand_slugs = {
        "all",
        "alle",
        "others",
        "other",
        "placeholder",
        "unknown",
    }

    def __init__(
        self,
        mode=None,
        brand=None,
        brands_path=None,
        output_dir=None,
        logs_dir=None,
        tmp_dir=None,
        status_path=None,
        overwrite=None,
        resume=None,
        limit_brands=None,
        type=None,
        *args,
        **kwargs,
    ):
        self.mode = (mode or "brands:scrape-all").strip().lower()
        if self.mode not in self.supported_modes:
            raise ValueError(
                f"Unsupported mode '{self.mode}'. Supported values: {', '.join(sorted(self.supported_modes))}"
            )

        self.single_brand_input = self._normalize_brand_value(brand)
        if self.mode == "brands:scrape-one" and not self.single_brand_input:
            raise ValueError("Mode 'brands:scrape-one' requires -a brand='<Brand>'.")

        super().__init__(
            limit_brands=limit_brands,
            brands_only=self.mode == "brands:fetch",
            type=type,
            *args,
            **kwargs,
        )

        base_dir = os.path.join(os.getcwd(), "data")
        self.brands_path = brands_path or os.path.join(base_dir, "brands", self.name, f"{self.scraper_type}_brands.json")
        self.output_dir = output_dir or os.path.join(base_dir, "output", self.name, self.scraper_type)
        self.logs_dir = logs_dir or os.path.join(base_dir, "logs", self.name)
        self.tmp_dir = tmp_dir or os.path.join(base_dir, "tmp", self.name)
        self.status_path = status_path or os.path.join(self.tmp_dir, f"scrape-status-{self.scraper_type}.json")
        self.overwrite_strategy = (overwrite or "").strip().lower() or (
            "skip" if self.mode == "brands:scrape-missing" else "overwrite"
        )
        if self.overwrite_strategy not in {"overwrite", "skip", "update"}:
            raise ValueError("overwrite must be one of: overwrite, skip, update")

        self.resume_enabled = _parse_bool(resume, default=self.mode in {"brands:scrape-all", "brands:scrape-missing"})
        self.discovered_brand_names = []
        self.target_brand_slugs = set()
        self.target_brand_names = {}
        self.processed_brand_slugs = set()
        self.status_data = {}

        for path in (os.path.dirname(self.brands_path), self.output_dir, self.logs_dir, self.tmp_dir):
            os.makedirs(path, exist_ok=True)

        self._configure_file_logger()
        self.status_data = self._load_status_file()

        if self.mode != "brands:fetch":
            self._prepare_target_brand_list()

        self.file_logger.info(
            "Mode=%s type=%s brands_path=%s output_dir=%s status_path=%s overwrite=%s resume=%s",
            self.mode,
            self.scraper_type,
            self.brands_path,
            self.output_dir,
            self.status_path,
            self.overwrite_strategy,
            self.resume_enabled,
        )

    def parse(self, response):
        self.logger.info("START %s status=%s mode=%s", response.url, response.status, self.mode)

        brand_links = response.css(f"a[href*='/de/chiptuning/{self.scraper_type}/']")
        self.logger.info("Found %s total brand links", len(brand_links))

        matched_count = 0
        for link in brand_links:
            if self.limit_brands and matched_count >= self.limit_brands:
                break

            brand_entry = self._extract_brand_entry(response, link)
            if not brand_entry:
                continue

            brand_name = brand_entry["name"]
            brand_slug = brand_entry["slug"]
            file_slug = self._slugify_brand_name(brand_name)

            if self.mode == "brands:fetch":
                self.discovered_brand_names.append(brand_name)
                self._ensure_brand_stub(brand_entry)
                matched_count += 1
                continue

            if file_slug not in self.target_brand_slugs:
                continue

            if self._should_skip_brand(file_slug):
                self.status_data[file_slug] = "skipped"
                self.file_logger.info("Skipping brand %s (%s)", brand_name, file_slug)
                continue

            self._ensure_brand_stub(brand_entry)
            self.status_data[file_slug] = "pending"
            matched_count += 1
            self.file_logger.info("Queueing brand %s (%s)", brand_name, file_slug)

            yield response.follow(
                brand_entry["href"],
                callback=self.parse_brand,
                meta={"brand_slug": brand_slug},
            )

        self._write_status_file()

        if self.mode != "brands:fetch" and not matched_count:
            raise CloseSpider(f"No matching brands found for mode={self.mode}")

    def closed(self, reason):
        try:
            if self.mode == "brands:fetch":
                self._write_brand_list()
                return

            self._write_brand_outputs()

            missing_targets = sorted(self.target_brand_slugs - self.processed_brand_slugs)
            for file_slug in missing_targets:
                self.status_data[file_slug] = "failed"

            self._write_status_file()
            self.file_logger.info(
                "Run finished with reason=%s. done=%s skipped=%s failed=%s",
                reason,
                self._count_status("done"),
                self._count_status("skipped"),
                self._count_status("failed"),
            )
        except OSError as exc:
            self.logger.exception("Brand file export failed: %s", exc)
            self.file_logger.exception("Brand file export failed: %s", exc)

    def _configure_file_logger(self) -> None:
        log_path = os.path.join(self.logs_dir, f"olsx_brand_files_{self.scraper_type}.log")
        self.file_logger = logging.getLogger(f"{self.__class__.__name__}_{self.scraper_type}")
        self.file_logger.setLevel(logging.DEBUG)
        self.file_logger.handlers = []
        self.file_logger.propagate = False

        handler = logging.FileHandler(log_path)
        handler.setLevel(logging.DEBUG)
        formatter = logging.Formatter("%(asctime)s - %(levelname)s - %(message)s")
        handler.setFormatter(formatter)
        self.file_logger.addHandler(handler)

    def _prepare_target_brand_list(self) -> None:
        loaded_names = self._load_brand_list()
        slug_to_name = {self._slugify_brand_name(name): name for name in loaded_names}

        if self.single_brand_input:
            target_slug = self._slugify_brand_name(self.single_brand_input)
            if target_slug not in slug_to_name:
                raise ValueError(
                    f"Brand '{self.single_brand_input}' not found in {self.brands_path}."
                )
            slug_to_name = {target_slug: slug_to_name[target_slug]}

        self.target_brand_slugs = set(slug_to_name)
        self.target_brand_names = slug_to_name
        self.file_logger.info("Loaded %s target brands from %s", len(slug_to_name), self.brands_path)

    def _load_brand_list(self) -> list[str]:
        if not os.path.exists(self.brands_path):
            raise ValueError(
                f"Brand list not found: {self.brands_path}. Run mode=brands:fetch first."
            )

        with open(self.brands_path, "r", encoding="utf-8") as handle:
            payload = json.load(handle)

        if not isinstance(payload, list):
            raise ValueError(f"Brand list must be a JSON array: {self.brands_path}")

        normalized = []
        seen = set()
        for entry in payload:
            name = None
            if isinstance(entry, str):
                name = entry
            elif isinstance(entry, dict):
                name = entry.get("name")

            clean_name = self._normalize_brand_value(name)
            if not clean_name:
                continue

            slug = self._slugify_brand_name(clean_name)
            if not slug or slug in seen:
                continue

            seen.add(slug)
            normalized.append(clean_name)

        if not normalized:
            raise ValueError(f"Brand list is empty or invalid: {self.brands_path}")

        normalized.sort(key=lambda value: self._slugify_brand_name(value))
        return normalized

    def _extract_brand_entry(self, response, link):
        href = link.css("::attr(href)").get()
        if not href:
            return None

        parts = self.path_parts(href)
        if len(parts) != 4 or parts[:3] != ["de", "chiptuning", self.scraper_type]:
            return None

        brand_slug = (parts[3] or "").strip().lower()
        if not brand_slug or brand_slug in self.invalid_brand_slugs:
            return None

        brand_name = (
            self.clean(link.css("p::text").get())
            or self.clean(link.css("img::attr(alt)").get())
            or brand_slug
        )
        brand_name = self._normalize_brand_value(brand_name)
        if not brand_name:
            return None

        logo = link.css("img::attr(src)").get()
        if logo:
            logo = response.urljoin(logo)

        return {
            "slug": brand_slug,
            "name": brand_name,
            "logo": logo,
            "href": href,
            "url": response.urljoin(href),
        }

    def _ensure_brand_stub(self, brand_entry: dict) -> None:
        brand_slug = brand_entry["slug"]
        if brand_slug in self.brand_data:
            return

        self.brand_data[brand_slug] = {
            "slug": brand_slug,
            "name": brand_entry["name"],
            "file_slug": self._slugify_brand_name(brand_entry["name"]),
            "logo": brand_entry["logo"],
            "url": brand_entry["url"],
            "models": {},
            "model_count": 0,
        }

    def _load_status_file(self) -> dict:
        if not os.path.exists(self.status_path):
            return {}

        try:
            with open(self.status_path, "r", encoding="utf-8") as handle:
                payload = json.load(handle)
            if isinstance(payload, dict):
                return {str(key): str(value) for key, value in payload.items()}
        except (OSError, json.JSONDecodeError):
            self.file_logger.warning("Ignoring unreadable status file: %s", self.status_path)
        return {}

    def _write_status_file(self) -> None:
        with open(self.status_path, "w", encoding="utf-8") as handle:
            json.dump(self.status_data, handle, ensure_ascii=False, indent=2)

    def _write_brand_list(self) -> None:
        brand_names = []
        seen = set()

        for brand_name in self.discovered_brand_names:
            normalized = self._normalize_brand_value(brand_name)
            brand_slug = self._slugify_brand_name(normalized)
            if not normalized or not brand_slug or brand_slug in seen:
                continue
            seen.add(brand_slug)
            brand_names.append(normalized)

        brand_names.sort(key=lambda value: self._slugify_brand_name(value))

        with open(self.brands_path, "w", encoding="utf-8") as handle:
            json.dump(brand_names, handle, ensure_ascii=False, indent=2)

        self.file_logger.info("Saved %s brands to %s", len(brand_names), self.brands_path)
        self.logger.info("Saved %s brands to %s", len(brand_names), self.brands_path)

    def _write_brand_outputs(self) -> None:
        serialized_brands = [
            self._serialize_brand(brand_slug)
            for brand_slug in sorted(self.brand_data, key=lambda slug: _sort_key_name_slug(self.brand_data[slug]))
        ]

        for payload in serialized_brands:
            file_slug = payload["file_slug"]
            output_path = os.path.join(self.output_dir, f"{file_slug}.json")

            try:
                self._validate_brand_payload(payload)
                if not payload["models"]:
                    self.file_logger.warning("Brand %s has no models; saving empty payload", file_slug)
                with open(output_path, "w", encoding="utf-8") as handle:
                    json.dump(payload, handle, ensure_ascii=False, indent=2)
                self.status_data[file_slug] = "done"
                self.processed_brand_slugs.add(file_slug)
                self.file_logger.info("Saved brand output %s", output_path)
            except (OSError, ValueError, TypeError) as exc:
                self.status_data[file_slug] = "failed"
                self.file_logger.exception("Failed writing brand output for %s: %s", file_slug, exc)

    def _validate_brand_payload(self, payload: dict) -> None:
        if not isinstance(payload, dict):
            raise ValueError("Brand payload must be an object")
        if not payload.get("brand_name"):
            raise ValueError("brand_name is required")
        if not payload.get("file_slug"):
            raise ValueError("file_slug is required")
        if not isinstance(payload.get("models"), list):
            raise ValueError("models must be a list")

    def _serialize_brand(self, brand_slug: str) -> dict:
        payload = super()._serialize_brand(brand_slug)
        payload["file_slug"] = self.brand_data[brand_slug].get("file_slug") or self._slugify_brand_name(
            payload["brand_name"]
        )
        payload["scraper_type"] = self.scraper_type
        return payload

    def _should_skip_brand(self, file_slug: str) -> bool:
        if self.mode == "brands:scrape-missing":
            return self._is_existing_output_valid(file_slug)

        if self.overwrite_strategy == "skip" and self._is_existing_output_valid(file_slug):
            return True

        if self.resume_enabled and self.status_data.get(file_slug) == "done" and self._is_existing_output_valid(file_slug):
            return True

        return False

    def _is_existing_output_valid(self, file_slug: str) -> bool:
        output_path = os.path.join(self.output_dir, f"{file_slug}.json")
        if not os.path.exists(output_path):
            return False

        try:
            with open(output_path, "r", encoding="utf-8") as handle:
                payload = json.load(handle)
            self._validate_brand_payload(payload)
            return True
        except (OSError, json.JSONDecodeError, ValueError, TypeError):
            return False

    def _count_status(self, value: str) -> int:
        return sum(1 for current in self.status_data.values() if current == value)

    def _normalize_brand_value(self, value):
        return self.clean(value) if value else None

    def _slugify_brand_name(self, value: str | None) -> str:
        if not value:
            return ""

        replacements = {
            "ä": "ae",
            "ö": "oe",
            "ü": "ue",
            "ß": "ss",
            "Ä": "ae",
            "Ö": "oe",
            "Ü": "ue",
        }
        normalized = "".join(replacements.get(char, char) for char in value)
        normalized = unicodedata.normalize("NFKD", normalized)
        normalized = normalized.encode("ascii", "ignore").decode("ascii")
        normalized = normalized.lower().strip()
        normalized = normalized.replace("&", " and ")
        output = []
        last_was_dash = False
        for char in normalized:
            if char.isalnum():
                output.append(char)
                last_was_dash = False
                continue

            if not last_was_dash:
                output.append("-")
                last_was_dash = True

        return "".join(output).strip("-")
