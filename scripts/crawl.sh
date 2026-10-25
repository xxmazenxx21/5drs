#!/usr/bin/env bash
# Request every key page, optionally in one language only.
#
#   ./scripts/crawl.sh          both languages, prints status codes
#   ./scripts/crawl.sh ar       Arabic only
#   ./scripts/crawl.sh en       English only
#
# Crawling Arabic has a side effect we rely on: TranslatePress only stores a
# gettext string once it has rendered it, so the pages must be visited before
# scripts/seed/translations.php can fill in those rows.
set -uo pipefail
cd "$(dirname "$0")/.."

BASE="${WP_URL:-http://localhost:8080}"
WHICH="${1:-both}"

PATHS=(
	""
	"products/"
	"about/"
	"contact/"
	"policies/"
	"cart/"
	"checkout/"
	"my-account/"
	"product-category/classic/"
	"product-category/premium/"
	"product-category/charcoal/"
	"product-category/kids/"
	"product/classic-toothbrush/"
	"product/premium-toothbrush/"
	"product/charcoal-soft-toothbrush/"
	"product/kids-fun-toothbrush/"
	"?s=toothbrush"
	"no-such-page-404/"
)

prefixes=()
case "$WHICH" in
	en) prefixes=( "/" ) ;;
	ar) prefixes=( "/ar/" ) ;;
	*)  prefixes=( "/" "/ar/" ) ;;
esac

fail=0
for prefix in "${prefixes[@]}"; do
	for path in "${PATHS[@]}"; do
		url="${BASE}${prefix}${path}"
		code=$(curl -sL -o /dev/null -w '%{http_code}' "$url")
		expected=200
		[[ "$path" == "no-such-page-404/" ]] && expected=404
		if [[ "$code" != "$expected" ]]; then
			printf '  %-58s %s (expected %s)\n' "$url" "$code" "$expected"
			fail=1
		else
			printf '  %-58s %s\n' "$url" "$code"
		fi
	done
done

exit "$fail"
