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

# The home page carries real content now; its sections are checked below.
# The remaining pages are still structural shells.
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

# --- language switching both ways ------------------------------------------
# TranslatePress rewrites every href to the current language; without
# data-no-translation it rewrites the switcher's own links, which made it
# impossible to get back from Arabic to English.
check "$(grep -q 'fivedrs-langs" aria-label="[^"]*" data-no-translation' <<<"$ar" && echo yes || echo no)" "switcher is marked data-no-translation"
AR_ABOUT=$(curl -sL "$WP_URL/ar/about/")
EN_LINK=$(grep -oE 'href="[^"]*"[^>]*lang="en-US"' <<<"$AR_ABOUT" | grep -oE 'https?://[^"]*' | head -1)
check "$([ -n "$EN_LINK" ] && ! grep -q '/ar/' <<<"$EN_LINK" && echo yes || echo no)" "English link on an Arabic page leaves /ar/ ($EN_LINK)"
# Captured first, then matched with a here-string: `grep -q` closes the pipe
# early, and under `set -o pipefail` curl's resulting SIGPIPE would fail the
# whole pipeline even on a match.
EN_PAGE=$(curl -sL "$EN_LINK")
check "$(grep -qE '<html[^>]*lang="en-US"' <<<"$EN_PAGE" && echo yes || echo no)" "following it lands on an English page"

AR_LINK=$(grep -oE 'href="[^"]*"[^>]*lang="ar"' <<<"$home" | grep -oE 'https?://[^"]*' | head -1)
check "$([ -n "$AR_LINK" ] && grep -q '/ar/' <<<"$AR_LINK" && echo yes || echo no)" "Arabic link on an English page points into /ar/"

# --- cascade layers ----------------------------------------------------------
# WooCommerce's CSS is unlayered and beats layered rules whatever the
# specificity, so the override split has to stay the right way round.
if python3 scripts/check-css-layers.py >/dev/null 2>&1; then
	ok "WooCommerce overrides are unlayered, theme components are layered"
else
	bad "cascade layer split is wrong - run python3 scripts/check-css-layers.py"
fi

THEME_CSS=$(cat wp-content/themes/5drs/dist/assets/style-*.css)
check "$(grep -q '\.woocommerce \.fivedrs-brand img' <<<"$THEME_CSS" && echo yes || echo no)" "store pages re-assert the header logo size"

# --- home page ----------------------------------------------------------------
HOME=$(curl -sL "$WP_URL/")
AR_HOME=$(curl -sL "$WP_URL/ar/")

check "$([ "$(grep -c '<h1' <<<"$HOME")" = "1" ] && echo yes || echo no)" "home page has exactly one h1"

for sect in home-hero trust why cats featured kids tips faq cta; do
	check "$(grep -q "fivedrs-$sect" <<<"$HOME" && echo yes || echo no)" "home section renders: $sect"
done

# The hero is the LCP element: eager, prioritised, and never lazy.
HERO_IMG=$(grep -oE '<img[^>]*fivedrs-home-hero__image[^>]*>' <<<"$HOME" | head -1)
check "$(grep -q 'fetchpriority="high"' <<<"$HERO_IMG" && echo yes || echo no)" "hero image has fetchpriority=high"
check "$(grep -q 'loading="lazy"' <<<"$HERO_IMG" && echo no || echo yes)" "hero image is NOT lazy-loaded"
check "$(grep -q 'srcset=' <<<"$HERO_IMG" && echo yes || echo no)" "hero image has srcset"

# Every other content image below the fold must lazy-load.
NONLAZY=$(grep -oE '<img[^>]*(cat-classic|cat-kids|kids-spotlight|test-product)[^>]*>' <<<"$HOME" | grep -cv 'loading="lazy"')
check "$([ "$NONLAZY" = "0" ] && echo yes || echo no)" "all below-the-fold images lazy-load ($NONLAZY eager)"

check "$([ "$(grep -c 'FAQPage' <<<"$HOME")" = "1" ] && echo yes || echo no)" "exactly one FAQPage schema (English)"
check "$([ "$(grep -c 'FAQPage' <<<"$AR_HOME")" = "1" ] && echo yes || echo no)" "exactly one FAQPage schema (Arabic)"
check "$(grep -q '"inLanguage":"ar"' <<<"$AR_HOME" && echo yes || echo no)" "Arabic FAQ schema is marked inLanguage=ar"

check "$(grep -q 'product-category/classic' <<<"$HOME" && echo yes || echo no)" "category card links to the Classic archive"
# Held out on purpose: the supplied Black Edition photo has "S55" on the handle.
check "$(grep -q 'product-category/black-edition' <<<"$HOME" && echo no || echo yes)" "Black Edition card stays hidden until a clean image is set"

# --- home page in Arabic --------------------------------------------------------
check "$(grep -q 'ابتسامة أصح' <<<"$AR_HOME" && echo yes || echo no)" "Arabic hero heading renders"
check "$(grep -q 'تسوّق حسب احتياجك' <<<"$AR_HOME" && echo yes || echo no)" "Arabic section headings render"
check "$(grep -q 'تسوّق فرش الأطفال' <<<"$AR_HOME" && echo yes || echo no)" "Arabic strings containing apostrophes render"

LEFTOVER=0
for phrase in "Shop by Need" "Featured Products" "Gentle on Gums" "Shop Kids" "right for you"; do
	grep -q "$phrase" <<<"$AR_HOME" && LEFTOVER=$((LEFTOVER+1))
done
check "$([ "$LEFTOVER" = "0" ] && echo yes || echo no)" "no English home copy left on /ar/ ($LEFTOVER found)"

# TranslatePress free does not translate <title> or the meta description;
# inc/seo.php swaps in the Arabic pair instead.
check "$(grep -qE '<title>[^<]*فرش أسنان' <<<"$AR_HOME" && echo yes || echo no)" "Arabic page title"
check "$(grep -q 'name="description" content="اكتشف' <<<"$AR_HOME" && echo yes || echo no)" "Arabic meta description"
check "$(grep -q '<title>5Doctors | Toothbrushes' <<<"$HOME" && echo yes || echo no)" "English page title unchanged"

# --- build ------------------------------------------------------------------
check "$([ -f wp-content/themes/5drs/dist/.vite/manifest.json ] && echo yes || echo no)" "theme build output exists"
check "$([ -f wp-content/themes/5drs/languages/5drs.pot ] && echo yes || echo no)" "translation template exists"
check "$([ -f wp-content/themes/5drs/languages/ar.mo ] && echo yes || echo no)" "compiled Arabic translations exist"
check "$([ -f wp-content/themes/5drs/assets/images/logo-lockup.png ] && echo yes || echo no)" "theme logo asset exists"

echo
printf 'passed: %s   failed: %s\n' "$PASS" "$FAIL"
[ "$FAIL" -eq 0 ]
