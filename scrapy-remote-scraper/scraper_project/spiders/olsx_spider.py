import scrapy
from urllib.parse import urlparse


class OlsxSpider(scrapy.Spider):
    name = "olsx_spider"
    allowed_domains = ["olsx.lu"]
    start_urls = ["https://olsx.lu/de/chiptuning/car"]

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
        "FEEDS": {
            "olsx_chiptuning.json": {
                "format": "json",
                "encoding": "utf-8",
                "indent": 2,
                "overwrite": True,
            }
        },
        "LOG_LEVEL": "INFO",
    }

    def parse(self, response):
        self.logger.info("START %s status=%s", response.url, response.status)

        links = self.extract_internal_links(response)
        self.logger.info("Found %s links on start page", len(links))

        for url in links:
            parts = self.path_parts(url)

            # Erwartet: /de/chiptuning/car/<brand>
            if len(parts) == 4 and parts[:3] == ["de", "chiptuning", "car"]:
                yield response.follow(
                    url,
                    callback=self.parse_brand,
                    meta={"brand_slug": parts[3]},
                )

    def parse_brand(self, response):
        self.logger.info("BRAND %s status=%s", response.url, response.status)

        brand_slug = response.meta["brand_slug"]
        brand_name = self.clean(response.css("h1::text").get()) or brand_slug

        links = self.extract_internal_links(response)
        self.logger.info("Found %s links on brand page %s", len(links), brand_slug)

        for url in links:
            parts = self.path_parts(url)

            # /de/chiptuning/car/<brand>/<model>
            if len(parts) == 5 and parts[:3] == ["de", "chiptuning", "car"] and parts[3] == brand_slug:
                yield response.follow(
                    url,
                    callback=self.parse_model,
                    meta={
                        "brand_slug": brand_slug,
                        "brand_name": brand_name,
                        "model_slug": parts[4],
                    },
                )

    def parse_model(self, response):
        self.logger.info("MODEL %s status=%s", response.url, response.status)

        brand_slug = response.meta["brand_slug"]
        brand_name = response.meta["brand_name"]
        model_slug = response.meta["model_slug"]

        h1 = self.clean(response.css("h1::text").get()) or model_slug

        links = self.extract_internal_links(response)
        self.logger.info("Found %s links on model page %s", len(links), model_slug)

        for url in links:
            parts = self.path_parts(url)

            # /de/chiptuning/car/<brand>/<model>/<version>
            if (
                len(parts) == 6
                and parts[:3] == ["de", "chiptuning", "car"]
                and parts[3] == brand_slug
                and parts[4] == model_slug
            ):
                yield response.follow(
                    url,
                    callback=self.parse_version,
                    meta={
                        "brand_slug": brand_slug,
                        "brand_name": brand_name,
                        "model_slug": model_slug,
                        "model_name": self.extract_model_name(h1, model_slug),
                        "version_slug": parts[5],
                    },
                )

    def parse_version(self, response):
        self.logger.info("VERSION %s status=%s", response.url, response.status)

        brand_name = response.meta["brand_name"]
        model_name = response.meta["model_name"]
        brand_slug = response.meta["brand_slug"]
        model_slug = response.meta["model_slug"]
        version_slug = response.meta["version_slug"]

        version_name = self.extract_visible_title(response, fallback=version_slug)

        links = self.extract_internal_links(response)
        self.logger.info("Found %s links on version page %s", len(links), version_slug)

        for url in links:
            parts = self.path_parts(url)

            # /de/chiptuning/car/<brand>/<model>/<version>/<engine>
            if (
                len(parts) == 7
                and parts[:3] == ["de", "chiptuning", "car"]
                and parts[3] == brand_slug
                and parts[4] == model_slug
                and parts[5] == version_slug
            ):
                yield response.follow(
                    url,
                    callback=self.parse_engine,
                    meta={
                        "brand_name": brand_name,
                        "model_name": model_name,
                        "version_name": version_name,
                    },
                )

    def parse_engine(self, response):
        self.logger.info("ENGINE %s status=%s", response.url, response.status)

        text_nodes = [self.clean(x) for x in response.css("body *::text").getall()]
        text_nodes = [x for x in text_nodes if x]

        item = {
            "url": response.url,
            "brand_name": response.meta.get("brand_name"),
            "model_name": response.meta.get("model_name"),
            "version_name": response.meta.get("version_name"),
            "vehicle_title": self.find_before_marker(text_nodes, "Verfügbare Nachprogrammierungen", 2),
            "engine_title": self.find_before_marker(text_nodes, "Verfügbare Nachprogrammierungen", 1),
            "stages_raw": self.extract_stage_blocks(text_nodes),
            "ecus_raw": self.extract_section(
                text_nodes,
                "Steuergeräte, die von diesem Fahrzeug verwendet werden",
                "Verfügbare Optionen",
            ),
            "options_raw": self.extract_section(
                text_nodes,
                "Verfügbare Optionen",
                "Motoroptimierungsprogramme für Fachleute.",
            ),
        }

        yield item

    # -------------------------
    # Helper
    # -------------------------

    def extract_internal_links(self, response):
        urls = []
        seen = set()

        for href in response.css("a::attr(href)").getall():
            if not href:
                continue

            url = response.urljoin(href)
            parsed = urlparse(url)

            if parsed.netloc != "olsx.lu":
                continue

            if "/de/chiptuning/car" not in parsed.path:
                continue

            clean_url = f"{parsed.scheme}://{parsed.netloc}{parsed.path}".rstrip("/")

            if clean_url not in seen:
                seen.add(clean_url)
                urls.append(clean_url)

        return urls

    def path_parts(self, url):
        path = urlparse(url).path.strip("/")
        return [p for p in path.split("/") if p]

    def clean(self, value):
        if not value:
            return None
        value = " ".join(value.split()).strip()
        return value or None

    def extract_model_name(self, h1, fallback):
        # Beispiel: "Audi Kennfeld-Dateien für Tuning-Profis"
        # Falls kein Modell klar erkennbar ist, fallback
        if not h1:
            return fallback
        return fallback.replace("-", " ").title()

    def extract_visible_title(self, response, fallback=None):
        h1 = self.clean(response.css("h1::text").get())
        if h1:
            return h1
        return fallback

    def find_before_marker(self, text_nodes, marker, steps_back):
        for i, txt in enumerate(text_nodes):
            if txt == marker and i - steps_back >= 0:
                return text_nodes[i - steps_back]
        return None

    def extract_stage_blocks(self, text_nodes):
        stages = []
        joined = " | ".join(text_nodes)

        for stage in ["Stage 1", "Stage 2", "Stage 3"]:
            if stage in joined:
                stages.append(stage)

        return stages

    def extract_section(self, text_nodes, start_marker, end_marker):
        capture = False
        values = []

        for txt in text_nodes:
            if txt == start_marker:
                capture = True
                continue

            if capture and txt == end_marker:
                break

            if capture:
                values.append(txt)

        return values