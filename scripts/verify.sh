#!/usr/bin/env bash
# Smoke-test the local 5drs stack. Exits non-zero if any check fails.
set -uo pipefail
cd "$(dirname "$0")/.."

PASS=0
FAIL=0
ok()   { printf '  \033[32mPASS\033[0m  %s\n' "$1"; PASS=$((PASS+1)); }
bad()  { printf '  \033[31mFAIL\033[0m  %s\n' "$1"; FAIL=$((FAIL+1)); }
check(){ if [ "$1" = "yes" ]; then ok "$2"; else bad "$2"; fi; }

WP_URL="${WP_URL:-http://localhost:8080}"
PMA_URL="${PMA_URL:-http://localhost:8081}"
MAIL_URL="${MAIL_URL:-http://localhost:8025}"

echo "5drs stack verification"
echo

# --- reachability -----------------------------------------------------------
code=$(curl -s -o /dev/null -w '%{http_code}' "$WP_URL/")
check "$([ "$code" = "200" ] && echo yes || echo no)" "homepage returns 200 (got $code)"

code=$(curl -s -o /dev/null -w '%{http_code}' "$PMA_URL/")
check "$([ "$code" = "200" ] && echo yes || echo no)" "phpMyAdmin returns 200 (got $code)"

code=$(curl -s -o /dev/null -w '%{http_code}' "$MAIL_URL/")
check "$([ "$code" = "200" ] && echo yes || echo no)" "Mailpit UI returns 200 (got $code)"

code=$(curl -s -o /dev/null -w '%{http_code}' "$WP_URL/wp-login.php")
check "$([ "$code" = "200" ] && echo yes || echo no)" "wp-login.php returns 200 (got $code)"

# --- theme and language -----------------------------------------------------
home=$(curl -s "$WP_URL/")
check "$(grep -q 'themes/5drs/dist/' <<<"$home" && echo yes || echo no)" "5drs theme assets are loaded"
check "$(grep -qE '<html[^>]*lang="en-US"' <<<"$home" && echo yes || echo no)" "English page has lang=\"en-US\""

ar=$(curl -s "$WP_URL/ar/")
check "$(grep -qE '<html[^>]*dir="rtl"' <<<"$ar" && echo yes || echo no)" "Arabic page has dir=\"rtl\""
check "$(grep -qE '<html[^>]*lang="ar"' <<<"$ar" && echo yes || echo no)" "Arabic page has lang=\"ar\""

# --- shop -------------------------------------------------------------------
shop=$(curl -s "$WP_URL/shop/")
count=$(grep -o 'Test Product' <<<"$shop" | wc -l)
check "$([ "$count" -ge 3 ] && echo yes || echo no)" "shop lists the test products ($count matches)"

cart=$(curl -sL "$WP_URL/cart/")
check "$(grep -q 'woocommerce-cart-form\|cart-empty\|wc-empty-cart-message' <<<"$cart" && echo yes || echo no)" "cart page renders WooCommerce markup"
check "$(grep -q 'wp-block-woocommerce-cart' <<<"$cart" && echo no || echo yes)" "cart is NOT using the block template"

# A real add-to-cart, so the checkout form actually renders.
JAR=$(mktemp)
PID=$(curl -s "$WP_URL/shop/" | grep -oE 'add-to-cart=[0-9]+' | head -1 | cut -d= -f2)
curl -s -c "$JAR" -b "$JAR" -o /dev/null "$WP_URL/?add-to-cart=${PID:-0}"
checkout=$(curl -sL -c "$JAR" -b "$JAR" "$WP_URL/checkout/")
check "$(grep -q 'name="woocommerce-process-checkout-nonce"' <<<"$checkout" && echo yes || echo no)" "checkout renders the classic shortcode form"
check "$(grep -q 'wp-block-woocommerce-checkout' <<<"$checkout" && echo no || echo yes)" "checkout is NOT using the block template"
check "$(grep -q 'createaccount\|create account\|Create an account' <<<"$checkout" && echo yes || echo no)" "checkout offers account creation"
rm -f "$JAR"

# --- build ------------------------------------------------------------------
check "$([ -f wp-content/themes/5drs/dist/.vite/manifest.json ] && echo yes || echo no)" "theme build output exists"
check "$([ -f wp-content/themes/5drs/languages/5drs.pot ] && echo yes || echo no)" "translation template exists"

echo
printf 'passed: %s   failed: %s\n' "$PASS" "$FAIL"
[ "$FAIL" -eq 0 ]
