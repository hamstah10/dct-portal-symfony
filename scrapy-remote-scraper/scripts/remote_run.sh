#!/usr/bin/env bash
set -euo pipefail

: "${REMOTE_HOST:?REMOTE_HOST is required}"
: "${REMOTE_USER:?REMOTE_USER is required}"
: "${REMOTE_DIR:?REMOTE_DIR is required}"

ssh "${REMOTE_USER}@${REMOTE_HOST}" "cd ${REMOTE_DIR} && docker compose run --rm scraper"
