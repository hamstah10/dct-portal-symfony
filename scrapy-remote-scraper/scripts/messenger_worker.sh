#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

: "${MESSENGER_TRANSPORT_DSN:?MESSENGER_TRANSPORT_DSN must be set}"

php bin/console messenger:consume async --time-limit=3600 --memory-limit=512M --sleep=3 --env=prod
