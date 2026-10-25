#!/usr/bin/env bash
set -euo pipefail

# Projektwurzel: scripts/ liegt in scrapy-remote-scraper/ unterhalb des Symfony-Projekts
cd "$(dirname "$0")/../.."

# Der Handler ruft "scrapy" ohne Pfad auf, daher das Venv des Scrapers in den PATH nehmen
export PATH="$(pwd)/scrapy-remote-scraper/.venv/bin:${PATH}"

: "${MESSENGER_TRANSPORT_DSN:?MESSENGER_TRANSPORT_DSN must be set}"

php bin/console messenger:consume async --time-limit=3600 --memory-limit=512M --sleep=3 --env=prod
