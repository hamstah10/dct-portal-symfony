import logging
import requests

logger = logging.getLogger(__name__)

class ValidationPipeline:
    required_fields = ("title", "detail_url")
    skip_validation_spiders = ("olsx_brands_spider", "autotuner_news", "magicmotorsport_news", "alientech_news")

    def process_item(self, item, spider):
        # Skip validation for brand spider and tool news spiders (different item schema)
        if spider.name in self.skip_validation_spiders:
            return item

        for field in self.required_fields:
            if not item.get(field):
                raise ValueError(f"Missing required field: {field}")
        if item.get("price"):
            item["price"] = str(item["price"]).strip()
        if item.get("title"):
            item["title"] = str(item["title"]).strip()
        return item

class ApiPostPipeline:
    def __init__(self, enabled, url, token, timeout):
        self.enabled = enabled
        self.url = url
        self.token = token
        self.timeout = timeout

    @classmethod
    def from_crawler(cls, crawler):
        return cls(
            enabled=crawler.settings.getbool("API_POST_ENABLED", False),
            url=crawler.settings.get("API_POST_URL", ""),
            token=crawler.settings.get("API_POST_TOKEN", ""),
            timeout=crawler.settings.getint("DOWNLOAD_TIMEOUT", 20),
        )

    def process_item(self, item, spider):
        if not self.enabled:
            return item
        if not self.url:
            logger.error("API_POST_ENABLED=true but API_POST_URL is empty.")
            return item

        headers = {"Content-Type": "application/json"}
        if self.token:
            headers["Authorization"] = f"Bearer {self.token}"

        try:
            response = requests.post(
                self.url,
                json=dict(item),
                headers=headers,
                timeout=self.timeout,
            )
            if response.status_code >= 400:
                logger.error("API POST failed (%s): %s", response.status_code, response.text)
        except requests.RequestException as exc:
            logger.exception("API POST exception: %s", exc)

        return item
