import os
from pathlib import Path
from scraper_project.utils.env import get_bool, get_int, get_float, get_list

BOT_NAME = "scraper_project"

SPIDER_MODULES = ["scraper_project.spiders"]
NEWSPIDER_MODULE = "scraper_project.spiders"

ROBOTSTXT_OBEY = get_bool("ROBOTSTXT_OBEY", True)

CONCURRENT_REQUESTS = get_int("CONCURRENT_REQUESTS", 8)
DOWNLOAD_DELAY = get_float("DOWNLOAD_DELAY", 0.5)
DOWNLOAD_TIMEOUT = get_int("DOWNLOAD_TIMEOUT", 20)
RETRY_ENABLED = True
RETRY_TIMES = get_int("RETRY_TIMES", 3)

AUTOTHROTTLE_ENABLED = get_bool("AUTOTHROTTLE_ENABLED", True)
AUTOTHROTTLE_START_DELAY = 1
AUTOTHROTTLE_MAX_DELAY = 10
AUTOTHROTTLE_TARGET_CONCURRENCY = 1.0

LOG_LEVEL = os.getenv("LOG_LEVEL", "INFO")
LOG_FORMAT = "%(asctime)s [%(name)s] %(levelname)s: %(message)s"

COOKIES_ENABLED = False
TELNETCONSOLE_ENABLED = False

ITEM_PIPELINES = {
    "scraper_project.pipelines.ValidationPipeline": 100,
    "scraper_project.pipelines.ApiPostPipeline": 300,
}

DOWNLOADER_MIDDLEWARES = {
    "scraper_project.middlewares.RotateUserAgentMiddleware": 400,
    "scraper_project.middlewares.RotateProxyMiddleware": 410,
}

UA_ROTATION_ENABLED = get_bool("UA_ROTATION_ENABLED", False)
USER_AGENT_LIST = get_list("USER_AGENTS", separator="||", default=[])

PROXY_ROTATION_ENABLED = get_bool("PROXY_ROTATION_ENABLED", False)
PROXY_LIST = get_list("PROXY_LIST", separator="||", default=[])

API_POST_ENABLED = get_bool("API_POST_ENABLED", False)
API_POST_URL = os.getenv("API_POST_URL", "")
API_POST_TOKEN = os.getenv("API_POST_TOKEN", "")

FEED_EXPORT_ENCODING = "utf-8"

output_format = os.getenv("OUTPUT_FORMAT", "json").lower()
output_path = os.getenv("OUTPUT_PATH", "output/data.json")
Path(os.path.dirname(output_path) or ".").mkdir(parents=True, exist_ok=True)

if output_format not in {"json", "csv", "jl", "xml"}:
    output_format = "json"

FEEDS = {
    output_path: {
        "format": output_format,
        "overwrite": True,
    }
}
