import os
from dotenv import load_dotenv
from scrapy.crawler import CrawlerProcess
from scrapy.utils.project import get_project_settings

def main():
    load_dotenv()
    settings = get_project_settings()
    spider_name = os.getenv("SCRAPER_NAME", "products_spider")
    process = CrawlerProcess(settings)
    process.crawl(spider_name)
    process.start()

if __name__ == "__main__":
    main()
