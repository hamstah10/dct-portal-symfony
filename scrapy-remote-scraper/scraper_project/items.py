import scrapy

class ProductItem(scrapy.Item):
    title = scrapy.Field()
    price = scrapy.Field()
    currency = scrapy.Field()
    detail_url = scrapy.Field()
    image_urls = scrapy.Field()
    description = scrapy.Field()
    source_site = scrapy.Field()
    scraped_at = scrapy.Field()

class ToolNewsItem(scrapy.Item):
    provider = scrapy.Field()
    title = scrapy.Field()
    url = scrapy.Field()
    excerpt = scrapy.Field()
    content = scrapy.Field()
    key_features = scrapy.Field()
    image_url = scrapy.Field()
    published_at = scrapy.Field()
    scraped_at = scrapy.Field()
