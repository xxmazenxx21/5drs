#!/usr/bin/env bash
# Thin wrapper around the dockerised WP-CLI. Usage: ./scripts/wp.sh plugin list
set -euo pipefail
cd "$(dirname "$0")/.."
exec docker compose run --rm -T wpcli "$@"
