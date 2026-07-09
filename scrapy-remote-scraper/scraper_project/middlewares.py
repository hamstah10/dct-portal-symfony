import random
import logging

logger = logging.getLogger(__name__)

class RotateUserAgentMiddleware:
    def __init__(self, user_agents):
        self.user_agents = user_agents or []

    @classmethod
    def from_crawler(cls, crawler):
        enabled = crawler.settings.getbool("UA_ROTATION_ENABLED", False)
        ua_list = crawler.settings.getlist("USER_AGENT_LIST", [])
        if not enabled:
            ua_list = []
        return cls(ua_list)

    def process_request(self, request, spider):
        if self.user_agents:
            request.headers["User-Agent"] = random.choice(self.user_agents)

class RotateProxyMiddleware:
    def __init__(self, proxies):
        self.proxies = proxies or []

    @classmethod
    def from_crawler(cls, crawler):
        enabled = crawler.settings.getbool("PROXY_ROTATION_ENABLED", False)
        proxy_list = crawler.settings.getlist("PROXY_LIST", [])
        if not enabled:
            proxy_list = []
        return cls(proxy_list)

    def process_request(self, request, spider):
        if self.proxies:
            proxy = random.choice(self.proxies)
            request.meta["proxy"] = proxy
            logger.debug("Using proxy %s for %s", proxy, request.url)
