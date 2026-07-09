import json
import os
import re
from datetime import datetime, timezone

import scrapy


class SwiftecModulesSpider(scrapy.Spider):
    name = "swiftec_modules_spider"
    allowed_domains = ["swiftec.pt", "www.swiftec.pt"]
    start_urls = ["https://www.swiftec.pt/modules/"]

    custom_settings = {
        "ROBOTSTXT_OBEY": False,
        "DOWNLOAD_DELAY": 0.25,
        "CONCURRENT_REQUESTS_PER_DOMAIN": 2,
        "USER_AGENT": (
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/122.0.0.0 Safari/537.36"
        ),
        "DEFAULT_REQUEST_HEADERS": {
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language": "en-US,en;q=0.9,de;q=0.8",
            "Referer": "https://www.swiftec.pt/",
        },
        "ITEM_PIPELINES": {},
        "FEEDS": {},
        "LOG_LEVEL": "INFO",
    }

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.modules = []

    def parse(self, response):
        module_items = response.css("ul#list_modules > li")
        self.logger.info("Found %s module <li> elements", len(module_items))

        for idx, li in enumerate(module_items, start=1):
            href = li.css("a::attr(href)").get()
            title = li.css("a::attr(title)").get() or li.css("h2::text, h3::text").get()
            image_urls = [
                response.urljoin(src)
                for src in li.css("img::attr(src)").getall()
                if src
            ]

            detail_url = response.urljoin(href) if href else response.url
            yield response.follow(
                detail_url,
                callback=self.parse_detail,
                meta={
                    "index": idx,
                    "list_page_url": response.url,
                    "list_title": self.clean_text(title) or f"Module {idx}",
                    "list_image_urls": image_urls,
                },
            )

    def parse_detail(self, response):
        detail_container = response.css("#main_module_content")
        title = self.clean_text(
            detail_container.css(".bg_header_module h2::text").get()
            or response.meta["list_title"]
        )
        intro_text = self.clean_text(" ".join(detail_container.css(".bg_header_module p::text").getall()))
        content_title = self.clean_text(" ".join(detail_container.css("#content > .d-flex h3 *::text").getall()))
        module_content = detail_container.css("#module_content")
        module_image_urls = [
            response.urljoin(src)
            for src in detail_container.css(".bg_header_module img::attr(src)").getall()
            if src
        ]

        structured_brands = self.extract_brands(module_content)
        module_slug = self.extract_module_slug(response.url)
        total_controllers = sum(len(brand["controllers"]) for brand in structured_brands)

        self.modules.append(
            {
                "index": response.meta["index"],
                "module_slug": module_slug,
                "module_name": title,
                "module_url": response.url,
                "list_page_url": response.meta["list_page_url"],
                "intro_text": intro_text,
                "content_title": content_title,
                "brand_count": len(structured_brands),
                "controller_count": total_controllers,
                "image_urls": list(dict.fromkeys(response.meta["list_image_urls"] + module_image_urls)),
                "brands": structured_brands,
                "scraped_at": datetime.now(timezone.utc).isoformat(),
            }
        )

    def closed(self, reason):
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        output_path = os.path.join(os.getcwd(), f"swiftec_modules_{timestamp}.json")
        payload = sorted(self.modules, key=lambda item: ((item.get("module_name") or "").lower(), item.get("module_slug") or ""))

        with open(output_path, "w", encoding="utf-8") as handle:
            json.dump(payload, handle, ensure_ascii=False, indent=2)

        self.logger.info("Wrote %s modules to %s", len(payload), output_path)

    def extract_brands(self, module_content):
        brands = []
        current_brand = None

        for child in module_content.xpath("./*"):
            tag = (child.root.tag or "").lower()

            if tag == "h4":
                brand_name = self.clean_text(" ".join(child.css("*::text, ::text").getall()))
                if not brand_name:
                    continue

                current_brand = {
                    "brand_name": brand_name,
                    "brand_slug": self.slugify(brand_name),
                    "controllers": [],
                }
                brands.append(current_brand)
                continue

            if tag == "ul" and current_brand is not None:
                for entry in child.css("li"):
                    controller_text = self.clean_text(" ".join(entry.css("*::text, ::text").getall()))
                    if not controller_text:
                        continue
                    current_brand["controllers"].append(self.parse_controller(controller_text))

        for brand in brands:
            brand["controllers"] = sorted(
                brand["controllers"],
                key=lambda controller: (
                    (controller.get("manufacturer") or "").lower(),
                    (controller.get("model") or "").lower(),
                    (controller.get("notes") or "").lower(),
                ),
            )
            brand["controller_count"] = len(brand["controllers"])

        return sorted(brands, key=lambda brand: (brand.get("brand_name") or "").lower())

    def parse_controller(self, controller_text):
        parts = re.split(r"\s+-\s+", controller_text, maxsplit=1)
        controller_name = parts[0].strip()
        notes = parts[1].strip() if len(parts) > 1 else None
        cleaned_notes = self.clean_notes_marker(notes)

        controller_parts = controller_name.split(None, 1)
        manufacturer = controller_parts[0].strip() if controller_parts else ""
        model = controller_parts[1].strip() if len(controller_parts) > 1 else manufacturer

        return {
            "raw": self.clean_notes_marker(controller_text),
            "manufacturer": manufacturer,
            "model": model,
            "notes": cleaned_notes,
            "notes_de": self.translate_notes(cleaned_notes),
        }

    def extract_module_slug(self, url):
        return url.rstrip("/").split("/")[-1]

    def slugify(self, value):
        slug = re.sub(r"[^a-z0-9]+", "-", (value or "").lower())
        return slug.strip("-")

    def clean_text(self, value):
        if not value:
            return ""
        return " ".join(value.split()).strip()

    def translate_notes(self, notes):
        if not notes:
            return None

        translated = notes.upper()
        replacements = [
            ("*BETA*", "*BETA*"),
            ("ONLY FOR ", "NUR FUER "),
            ("FULL READ REQUIRED!", "FULL READ ERFORDERLICH!"),
            (
                "DASH ECU NEEDS TO BE PROGRAMED AS NON DPF DASH (OPCOM)!",
                "DIE DASH-ECU MUSS ALS NON-DPF-DASH PROGRAMMIERT WERDEN (OPCOM)!",
            ),
            ("ACTUATOR PLUG", "AKTUATOR-STECKER"),
            ("UREA ECU PLUG", "UREA-ECU-STECKER"),
            ("ADBLUE ECU", "ADBLUE-ECU"),
            ("AFTERTREATMENT ECU", "AFTERTREATMENT-ECU"),
            ("DPF PRESSURE AND TEMPERATURE SENSORS", "DPF-DRUCK- UND TEMPERATURSENSOREN"),
            ("DPF PRESSURE SENSOR", "DPF-DRUCKSENSOR"),
            ("NOX SENSORS", "NOX-SENSOREN"),
            ("NOX SENSOR", "NOX-SENSOR"),
            ("SCR AND NOX FUSES AND RELAYS", "SCR- UND NOX-SICHERUNGEN UND RELAIS"),
            ("FUSES AND RELAYS", "SICHERUNGEN UND RELAIS"),
            ("PUMP AND TANK", "PUMPE UND TANK"),
            ("TANK", "TANK"),
            ("ECU", "ECU"),
            ("AND", "UND"),
        ]

        for source, target in replacements:
            translated = translated.replace(source, target)

        translated = re.sub(r"\bPUMP\b", "PUMPE", translated)

        phrase_replacements = [
            (" MUESSEN ABGESTECKT WERDEN!", " muessen abgesteckt werden!"),
            (" MUSS ABGESTECKT WERDEN!", " muss abgesteckt werden!"),
            (" KOENNEN ABGESTECKT WERDEN!", " koennen abgesteckt werden!"),
            (" KANN ABGESTECKT WERDEN!", " kann abgesteckt werden!"),
            (" MUESSEN ANGESCHLOSSEN BLEIBEN!", " muessen angeschlossen bleiben!"),
            (" MUSS ANGESCHLOSSEN BLEIBEN!", " muss angeschlossen bleiben!"),
            (" KOENNEN ANGESCHLOSSEN BLEIBEN!", " koennen angeschlossen bleiben!"),
            (" KANN ANGESCHLOSSEN BLEIBEN!", " kann angeschlossen bleiben!"),
        ]

        # Normalize recurring imperative endings before singular/plural correction.
        translated = translated.replace(" MUST BE DISCONNECTED!", " MUSS ABGESTECKT WERDEN!")
        translated = translated.replace(" CAN BE DISCONNECTED!", " KANN ABGESTECKT WERDEN!")
        translated = translated.replace(" MUST BE CONNECTED!", " MUSS ANGESCHLOSSEN BLEIBEN!")
        translated = translated.replace(" CAN BE CONNECTED!", " KANN ANGESCHLOSSEN BLEIBEN!")

        plural_subjects = [
            "SENSOREN",
            "SICHERUNGEN UND RELAIS",
            "SCR- UND NOX-SICHERUNGEN UND RELAIS",
            "PUMPE UND TANK",
            "DPF-DRUCK- UND TEMPERATURSENSOREN",
        ]
        for subject in plural_subjects:
            translated = translated.replace(f"{subject} MUSS ABGESTECKT WERDEN!", f"{subject} MUESSEN ABGESTECKT WERDEN!")
            translated = translated.replace(f"{subject} KANN ABGESTECKT WERDEN!", f"{subject} KOENNEN ABGESTECKT WERDEN!")
            translated = translated.replace(f"{subject} MUSS ANGESCHLOSSEN BLEIBEN!", f"{subject} MUESSEN ANGESCHLOSSEN BLEIBEN!")
            translated = translated.replace(f"{subject} KANN ANGESCHLOSSEN BLEIBEN!", f"{subject} KOENNEN ANGESCHLOSSEN BLEIBEN!")

        for source, target in phrase_replacements:
            translated = translated.replace(source, target)

        translated = self.clean_text(translated)
        translated = translated.replace("PUMPEE", "PUMPE")
        if translated:
            translated = translated[0].upper() + translated[1:]

        return translated

    def clean_notes_marker(self, value):
        if not value:
            return value

        cleaned = value.replace("*IMPORTANT* ", "").replace("*IMPORTANT*", "")
        cleaned = cleaned.replace(" -  ", " - ").replace("  ", " ")
        return self.clean_text(cleaned)
