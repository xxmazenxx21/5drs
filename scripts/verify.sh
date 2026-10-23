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
shop=$(curl -sL "$WP_URL/products/")
count=$(grep -o 'Test Product' <<<"$shop" | wc -l)
check "$([ "$count" -ge 3 ] && echo yes || echo no)" "Our Products page lists the test products ($count matches)"

cart=$(curl -sL "$WP_URL/cart/")
check "$(grep -q 'woocommerce-cart-form\|cart-empty\|wc-empty-cart-message' <<<"$cart" && echo yes || echo no)" "cart page renders WooCommerce markup"
check "$(grep -q 'wp-block-woocommerce-cart' <<<"$cart" && echo no || echo yes)" "cart is NOT using the block template"

# A real add-to-cart, so the checkout form actually renders.
JAR=$(mktemp)
PID=$(curl -s "$WP_URL/products/" | grep -oE 'add-to-cart=[0-9]+' | head -1 | cut -d= -f2)
curl -s -c "$JAR" -b "$JAR" -o /dev/null "$WP_URL/?add-to-cart=${PID:-0}"
checkout=$(curl -sL -c "$JAR" -b "$JAR" "$WP_URL/checkout/")
check "$(grep -q 'name="woocommerce-process-checkout-nonce"' <<<"$checkout" && echo yes || echo no)" "checkout renders the classic shortcode form"
check "$(grep -q 'wp-block-woocommerce-checkout' <<<"$checkout" && echo no || echo yes)" "checkout is NOT using the block template"
check "$(grep -q 'createaccount\|create account\|Create an account' <<<"$checkout" && echo yes || echo no)" "checkout offers account creation"
rm -f "$JAR"

# --- brand, fonts and layout -------------------------------------------------
check "$(grep -q 'montserrat-latin-400-normal.woff2' <<<"$home" && echo yes || echo no)" "English page preloads Montserrat"
check "$(grep -q 'tajawal' <<<"$home" && echo no || echo yes)" "English page does NOT load Tajawal"
check "$(grep -q 'tajawal-arabic-400-normal.woff2' <<<"$ar" && echo yes || echo no)" "Arabic page preloads Tajawal"
check "$(grep -q 'montserrat' <<<"$ar" && echo no || echo yes)" "Arabic page does NOT load Montserrat"
check "$(grep -q 'fivedrs-header' <<<"$home" && echo yes || echo no)" "header renders"
check "$(grep -q 'fivedrs-footer' <<<"$home" && echo yes || echo no)" "footer renders"
check "$(grep -q 'fivedrs-offcanvas' <<<"$home" && echo yes || echo no)" "off-canvas menu renders"
check "$(grep -q 'fivedrs-langs__item' <<<"$home" && echo yes || echo no)" "language switcher renders"
check "$(grep -q 'logo-lockup.png\|custom-logo' <<<"$home" && echo yes || echo no)" "logo renders"

# --- pages --------------------------------------------------------------------
for page in products about contact policies policies/privacy-policy; do
	code=$(curl -sL -o /dev/null -w '%{http_code}' "$WP_URL/$page/")
	check "$([ "$code" = "200" ] && echo yes || echo no)" "/$page/ returns 200 (got $code)"
done

front=$(curl -sL "$WP_URL/")
check "$(grep -q '<!-- Section: ' <<<"$front" && echo yes || echo no)" "home page renders its section shells"

about=$(curl -sL "$WP_URL/about/")
check "$(grep -q 'Section: Our story' <<<"$about" && echo yes || echo no)" "about page uses page-about.php"

# --- translations ---------------------------------------------------------------
check "$(grep -q 'منتجاتنا' <<<"$ar" && echo yes || echo no)" "Arabic menu labels are translated"
check "$(grep -q 'هدفنا راحتك الشخصية' <<<"$ar" && echo yes || echo no)" "Arabic theme strings are translated"

# --- cart fragments ---------------------------------------------------------------
JAR2=$(mktemp)
PID2=$(grep -oE 'add-to-cart=[0-9]+' <<<"$shop" | head -1 | cut -d= -f2)
FRAG=$(curl -s -c "$JAR2" -b "$JAR2" -X POST "$WP_URL/?wc-ajax=add_to_cart" -d "product_id=${PID2:-0}&quantity=2")
check "$(grep -q '.fivedrs-cart__count' <<<"$FRAG" && echo yes || echo no)" "AJAX add-to-cart returns the cart badge fragment"
check "$(grep -qE '>2<' <<<"$FRAG" && echo yes || echo no)" "cart badge fragment carries the new count"
rm -f "$JAR2"

# --- build ------------------------------------------------------------------
check "$([ -f wp-content/themes/5drs/dist/.vite/manifest.json ] && echo yes || echo no)" "theme build output exists"
check "$([ -f wp-content/themes/5drs/languages/5drs.pot ] && echo yes || echo no)" "translation template exists"
check "$([ -f wp-content/themes/5drs/languages/ar.mo ] && echo yes || echo no)" "compiled Arabic translations exist"
check "$([ -f wp-content/themes/5drs/assets/images/logo-lockup.png ] && echo yes || echo no)" "theme logo asset exists"

echo
printf 'passed: %s   failed: %s\n' "$PASS" "$FAIL"
[ "$FAIL" -eq 0 ]
