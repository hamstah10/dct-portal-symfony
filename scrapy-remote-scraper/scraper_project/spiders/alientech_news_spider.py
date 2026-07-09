import html
import json
import re
from datetime import datetime, timezone

import scrapy

from scraper_project.items import ToolNewsItem


class AlientechNewsSpider(scrapy.Spider):
    """News spider for Alientech's KESS3 news listing.

    The listing page (Elementor "posts" widget) is only used to discover
    post IDs and to paginate; article details are fetched from the
    WordPress REST API (custom post type "news"), which returns clean
    structured data instead of fragile Elementor markup."""

    name = "alientech_news"
    allowed_domains = ["alientech-tools.com"]
    start_urls = ["https://www.alientech-tools.com/de/kess3-nachrichten/"]

    POST_ID_RE = re.compile(r"\bpost-(\d+)\b")

    def __init__(self, limit=None, year=None, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.limit = int(limit) if limit else None
        self.year = int(year) if year else None
        self.scraped_count = 0
        self.stop_pagination = False

    def parse(self, response):
        articles = response.css("article.elementor-post")
        for article in articles:
            if self.limit is not None and self.scraped_count >= self.limit:
                return

            class_attr = article.attrib.get("class", "")
            match = self.POST_ID_RE.search(class_attr)
            if not match:
                continue

            post_id = match.group(1)
            api_url = f"https://www.alientech-tools.com/de/wp-json/wp/v2/news/{post_id}?_embed=1"
            self.scraped_count += 1
            yield response.follow(api_url, callback=self.parse_article, errback=self.handle_request_error)

        if self.stop_pagination:
            return

        if self.limit is None or self.scraped_count < self.limit:
            next_page = response.css("a.page-numbers.next::attr(href)").get()
            if not next_page:
                next_page = response.css(".e-load-more-anchor::attr(data-next-page)").get()
            if next_page:
                yield response.follow(next_page, callback=self.parse, errback=self.handle_request_error)

    def parse_article(self, response):
        try:
            data = json.loads(response.text)
        except json.JSONDecodeError:
            self.logger.error("Invalid JSON from REST API: %s", response.url)
            return

        item = ToolNewsItem()
        item["provider"] = "alientech"
        item["title"] = html.unescape(self.strip_tags(data.get("title", {}).get("rendered", ""))).strip()
        item["url"] = data.get("link", response.url)

        content_html = data.get("content", {}).get("rendered", "")
        paragraphs = [
            html.unescape(self.strip_tags(p)).strip()
            for p in re.findall(r"<p[^>]*>(.*?)</p>", content_html, re.DOTALL)
        ]
        paragraphs = [p for p in paragraphs if p]

        excerpt_html = data.get("excerpt", {}).get("rendered", "")
        item["excerpt"] = html.unescape(self.strip_tags(excerpt_html)).strip() or (paragraphs[0] if paragraphs else "")
        item["content"] = " ".join(paragraphs)

        embedded = data.get("_embedded", {})
        media = embedded.get("wp:featuredmedia", [])
        item["image_url"] = media[0].get("source_url") if media else None

        published_at = data.get("date_gmt") or data.get("date")
        item["published_at"] = self.normalize_iso_date(published_at)
        item["key_features"] = self.parse_key_features(content_html)
        item["scraped_at"] = datetime.now(timezone.utc).isoformat()

        if not item["title"]:
            self.logger.warning("Title missing on article: %s", response.url)
            return

        if self.year is not None and item["published_at"]:
            published_year = int(item["published_at"][:4])
            if published_year < self.year:
                # Articles are listed newest-first, so an older-than-target
                # article means everything after it on the site is older too.
                self.stop_pagination = True
                return
            if published_year > self.year:
                return

        yield item

    def parse_key_features(self, content_html: str) -> dict:
        """Extracts the "Technischer Fokus" list, e.g.
        "ECU: <strong>FoMoCo EEC-VI</strong>" -> {"ecu": "FoMoCo EEC-VI"}."""
        match = re.search(
            r"Technischer Fokus.*?<ul[^>]*>(.*?)</ul>",
            content_html,
            re.DOTALL,
        )
        if not match:
            return {}

        features = {}
        for li in re.findall(r"<li[^>]*>(.*?)</li>", match.group(1), re.DOTALL):
            text = html.unescape(self.strip_tags(li)).strip()
            if not text:
                continue

            if ":" in text:
                label, value = text.split(":", 1)
                features[self.slugify(label)] = value.strip()
            else:
                features[self.slugify(text)] = True

        return features

    @staticmethod
    def strip_tags(value: str) -> str:
        return re.sub(r"<[^>]+>", " ", value or "").strip()

    @staticmethod
    def slugify(value: str) -> str:
        value = value.strip().lower()
        value = value.replace("ä", "ae").replace("ö", "oe").replace("ü", "ue").replace("ß", "ss")
        value = re.sub(r"[^a-z0-9]+", "_", value)
        return value.strip("_")

    @staticmethod
    def normalize_iso_date(raw_date: str | None) -> str | None:
        if not raw_date:
            return None

        try:
            parsed = datetime.fromisoformat(raw_date)
        except ValueError:
            return None

        if parsed.tzinfo is None:
            parsed = parsed.replace(tzinfo=timezone.utc)

        return parsed.isoformat()

    def handle_request_error(self, failure):
        self.logger.error("Request failed: %s", failure.request.url)
        self.logger.error("Failure: %r", failure)
