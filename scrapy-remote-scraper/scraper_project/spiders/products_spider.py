import os
from datetime import datetime, timezone
from urllib.parse import urljoin

import scrapy

from scraper_project.items import ProductItem
from scraper_project.utils.env import get_list, get_int

class ProductsSpider(scrapy.Spider):
    name = "products_spider"

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.start_urls = get_list("START_URLS", separator=",", default=["https://example.com/products"])
        self.allowed_domains = get_list("ALLOWED_DOMAINS", separator=",", default=["example.com"])
        self.max_pages = get_int("MAX_PAGES", 100)
        self.page_count = 0

    def parse(self, response):
        self.page_count += 1
        if self.page_count > self.max_pages:
            self.logger.info("Reached MAX_PAGES=%s. Stopping pagination.", self.max_pages)
            return

        product_links = response.css("a.product-link::attr(href)").getall()
        for href in product_links:
            detail_url = urljoin(response.url, href)
            yield response.follow(detail_url, callback=self.parse_detail, errback=self.handle_request_error)

        next_page = response.css("a.next::attr(href), a[rel='next']::attr(href)").get()
        if next_page:
            yield response.follow(next_page, callback=self.parse, errback=self.handle_request_error)

    def parse_detail(self, response):
        item = ProductItem()
        item["title"] = response.css("h1.product-title::text").get(default="").strip()
        item["price"] = response.css(".price::text").re_first(r"[\d\.,]+")
        item["currency"] = response.css(".price::text").re_first(r"[€$£]|EUR|USD|GBP")
        item["detail_url"] = response.url
        item["description"] = " ".join(
            t.strip() for t in response.css(".product-description *::text").getall() if t.strip()
        )
        item["image_urls"] = [
            urljoin(response.url, src)
            for src in response.css(".product-gallery img::attr(src)").getall()
            if src
        ]
        item["source_site"] = os.getenv("ALLOWED_DOMAINS", "example.com")
        item["scraped_at"] = datetime.now(timezone.utc).isoformat()

        if not item["title"]:
            self.logger.warning("Title missing on detail page: %s", response.url)

        yield item

    def handle_request_error(self, failure):
        self.logger.error("Request failed: %s", failure.request.url)
        self.logger.error("Failure: %r", failure)
