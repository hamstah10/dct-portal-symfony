import re
from datetime import datetime, timezone
from urllib.parse import urljoin

import scrapy

from scraper_project.items import ToolNewsItem

GERMAN_MONTHS = {
    "Jan": 1, "Feb": 2, "Mär": 3, "Apr": 4, "Mai": 5, "Jun": 6,
    "Jul": 7, "Aug": 8, "Sep": 9, "Okt": 10, "Nov": 11, "Dez": 12,
}


class AutotunerNewsSpider(scrapy.Spider):
    """News spider for Autotuner's Shopify blog ("Neuigkeiten", tagged "tool")."""

    name = "autotuner_news"
    allowed_domains = ["autotuner.com"]
    start_urls = ["https://www.autotuner.com/de/blogs/neuigkeiten/tagged/tool"]

    def __init__(self, limit=None, year=None, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.limit = int(limit) if limit else None
        self.year = int(year) if year else None
        self.scraped_count = 0
        self.stop_pagination = False

    def parse(self, response):
        article_links = response.css("blog-post-card h2 a::attr(href)").getall()
        for href in article_links:
            if self.limit is not None and self.scraped_count >= self.limit:
                return
            detail_url = urljoin(response.url, href)
            self.scraped_count += 1
            yield response.follow(detail_url, callback=self.parse_article, errback=self.handle_request_error)

        if self.stop_pagination:
            return

        if self.limit is None or self.scraped_count < self.limit:
            next_page = response.css("a[rel='next']::attr(href)").get()
            if next_page:
                yield response.follow(next_page, callback=self.parse, errback=self.handle_request_error)

    def parse_article(self, response):
        item = ToolNewsItem()
        item["provider"] = "autotuner"
        item["title"] = response.css("h1::text").get(default="").strip()
        item["url"] = response.url
        paragraphs = [t.strip() for t in response.css(".article-content .prose p::text").getall() if t.strip()]
        item["excerpt"] = next((p for p in paragraphs if p != "Liebe Nutzer,"), paragraphs[0] if paragraphs else "")
        item["content"] = " ".join(
            t.strip() for t in response.css(".article-content .prose *::text").getall() if t.strip()
        )
        image_src = response.css(".article-banner__image img::attr(src)").get()
        item["image_url"] = urljoin(response.url, image_src) if image_src else None
        raw_date = response.css(".article__meta time::text").get(default="").strip()
        item["published_at"] = self.parse_german_date(raw_date)
        item["key_features"] = self.parse_key_features(response)
        item["scraped_at"] = datetime.now(timezone.utc).isoformat()

        if not item["title"]:
            self.logger.warning("Title missing on article page: %s", response.url)
            return

        if self.year is not None:
            published_year = int(item["published_at"][:4]) if item["published_at"] else None
            if published_year is None:
                self.logger.warning("No published date found, skipping due to --year filter: %s", response.url)
                return
            if published_year < self.year:
                # Articles are listed newest-first, so an older-than-target
                # article means everything after it on the site is older too.
                self.stop_pagination = True
                return
            if published_year > self.year:
                return

        yield item

    def parse_key_features(self, response) -> dict:
        """Extracts the "Hauptmerkmale" (key features) list, e.g.
        "MCU: TC1793" -> {"mcu": "TC1793"}. Items without a colon are kept
        as boolean flags, e.g. "Prüfsummen-Korrektur" -> {"pruefsummen_korrektur": True}."""
        items = response.xpath(
            "//h2[contains(text(), 'Hauptmerkmale')]/following-sibling::ul[1]/li"
        )
        features = {}
        for li in items:
            text = " ".join(t.strip() for t in li.css("*::text").getall() if t.strip())
            text = re.sub(r"^[^\w]+", "", text).strip()
            if not text:
                continue

            if ":" in text:
                label, value = text.split(":", 1)
                features[self.slugify(label)] = value.strip()
            else:
                features[self.slugify(text)] = True

        return features

    @staticmethod
    def slugify(value: str) -> str:
        value = value.strip().lower()
        value = (
            value.replace("ä", "ae").replace("ö", "oe").replace("ü", "ue").replace("ß", "ss")
        )
        value = re.sub(r"[^a-z0-9]+", "_", value)
        return value.strip("_")

    def parse_german_date(self, raw_date: str) -> str | None:
        """Parses dates like "3. Jul 2026" into an ISO-8601 string."""
        match = re.match(r"(\d{1,2})\.\s*([A-Za-zäöü]{3,4})\s*(\d{4})", raw_date)
        if not match:
            return None

        day, month_name, year = match.groups()
        month = GERMAN_MONTHS.get(month_name.capitalize())
        if not month:
            return None

        try:
            return datetime(int(year), month, int(day), tzinfo=timezone.utc).isoformat()
        except ValueError:
            return None

    def handle_request_error(self, failure):
        self.logger.error("Request failed: %s", failure.request.url)
        self.logger.error("Failure: %r", failure)
