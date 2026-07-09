import json
from datetime import datetime, timezone
from urllib.parse import urljoin

import scrapy

from scraper_project.items import ToolNewsItem


class MagicmotorsportNewsSpider(scrapy.Spider):
    """News spider for Magic Motorsport's "Flex Update" news listing
    (Elementor/WordPress site with Yoast JSON-LD metadata on detail pages)."""

    name = "magicmotorsport_news"
    allowed_domains = ["magicmotorsport.com"]
    start_urls = [
        "https://www.magicmotorsport.com/en/news-updates/?e-filter-33ffcdb-category=flex-update"
    ]

    def __init__(self, limit=None, year=None, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.limit = int(limit) if limit else None
        self.year = int(year) if year else None
        self.scraped_count = 0
        self.stop_pagination = False

    def parse(self, response):
        article_links = response.css(".post-title-carousel h4 a::attr(href)").getall()
        for href in article_links:
            if self.limit is not None and self.scraped_count >= self.limit:
                return
            detail_url = urljoin(response.url, href)
            self.scraped_count += 1
            yield response.follow(detail_url, callback=self.parse_article, errback=self.handle_request_error)

        if self.stop_pagination:
            return

        if self.limit is None or self.scraped_count < self.limit:
            next_page = response.css("a.page-numbers:not(.current)::attr(href)").get()
            if next_page:
                yield response.follow(next_page, callback=self.parse, errback=self.handle_request_error)

    def parse_article(self, response):
        article_meta = self.extract_article_schema(response)

        item = ToolNewsItem()
        item["provider"] = "magicmotorsport"
        item["title"] = (article_meta.get("headline") or response.css("title::text").get(default="")).strip()
        item["url"] = response.url
        paragraphs = [
            " ".join(t.strip() for t in p.css("*::text").getall() if t.strip())
            for p in response.css(".elementor-widget-theme-post-content .elementor-widget-text-editor p")
        ]
        paragraphs = [p for p in paragraphs if p]
        item["excerpt"] = paragraphs[0] if paragraphs else ""
        item["content"] = " ".join(paragraphs)
        item["key_features"] = {}
        image_src = response.css(".elementor-widget-theme-post-featured-image img::attr(src)").get()
        item["image_url"] = urljoin(response.url, image_src) if image_src else None
        item["published_at"] = article_meta.get("datePublished")
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

    def extract_article_schema(self, response) -> dict:
        """Reads the Yoast JSON-LD graph and returns the Article node's fields."""
        raw_scripts = response.css('script.yoast-schema-graph::text').getall()
        for raw in raw_scripts:
            try:
                data = json.loads(raw)
            except (json.JSONDecodeError, TypeError):
                continue

            for node in data.get("@graph", []):
                if node.get("@type") == "Article":
                    return node

        return {}

    def handle_request_error(self, failure):
        self.logger.error("Request failed: %s", failure.request.url)
        self.logger.error("Failure: %r", failure)
