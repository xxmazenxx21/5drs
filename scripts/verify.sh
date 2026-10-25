#!/usr/bin/env bash
# Smoke-test the local 5drs stack. Exits non-zero if any check fails.
set -uo pipefail
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
cd "$SCRIPT_DIR/.."

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
count=$(grep -c 'woocommerce-loop-product__title' <<<"$shop")
check "$([ "$count" -ge 4 ] && echo yes || echo no)" "Our Products page lists the catalogue ($count products)"

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
for page in products about contact policies; do
	code=$(curl -sL -o /dev/null -w '%{http_code}' "$WP_URL/$page/")
	check "$([ "$code" = "200" ] && echo yes || echo no)" "/$page/ returns 200 (got $code)"
done

# The home page carries real content now; its sections are checked below.
# The remaining pages are still structural shells.
# The About page has real content now; its sections are checked further down.

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
# Not HOME: that is the user's home directory, and overwriting it breaks any
# command that reads it - Docker looks for its config under $HOME.
HOME_PAGE=$(curl -sL "$WP_URL/")
AR_HOME=$(curl -sL "$WP_URL/ar/")

check "$([ "$(grep -c '<h1' <<<"$HOME_PAGE")" = "1" ] && echo yes || echo no)" "home page has exactly one h1"

for sect in home-hero trust why cats featured kids tips faq cta; do
	check "$(grep -q "fivedrs-$sect" <<<"$HOME_PAGE" && echo yes || echo no)" "home section renders: $sect"
done

# The hero is the LCP element: eager, prioritised, and never lazy.
HERO_IMG=$(grep -oE '<img[^>]*fivedrs-home-hero__image[^>]*>' <<<"$HOME_PAGE" | head -1)
check "$(grep -q 'fetchpriority="high"' <<<"$HERO_IMG" && echo yes || echo no)" "hero image has fetchpriority=high"
check "$(grep -q 'loading="lazy"' <<<"$HERO_IMG" && echo no || echo yes)" "hero image is NOT lazy-loaded"
check "$(grep -q 'srcset=' <<<"$HERO_IMG" && echo yes || echo no)" "hero image has srcset"

# Every other content image below the fold must lazy-load.
NONLAZY=$(grep -oE '<img[^>]*(cat-classic|cat-kids|kids-spotlight|test-product)[^>]*>' <<<"$HOME_PAGE" | grep -cv 'loading="lazy"')
check "$([ "$NONLAZY" = "0" ] && echo yes || echo no)" "all below-the-fold images lazy-load ($NONLAZY eager)"

check "$([ "$(grep -c 'FAQPage' <<<"$HOME_PAGE")" = "1" ] && echo yes || echo no)" "exactly one FAQPage schema (English)"
check "$([ "$(grep -c 'FAQPage' <<<"$AR_HOME")" = "1" ] && echo yes || echo no)" "exactly one FAQPage schema (Arabic)"
check "$(grep -q '"inLanguage":"ar"' <<<"$AR_HOME" && echo yes || echo no)" "Arabic FAQ schema is marked inLanguage=ar"

check "$(grep -q 'product-category/classic' <<<"$HOME_PAGE" && echo yes || echo no)" "category card links to the Classic archive"
# Held out on purpose: the supplied Black Edition photo has "S55" on the handle.
check "$(grep -q 'product-category/black-edition' <<<"$HOME_PAGE" && echo no || echo yes)" "Black Edition card stays hidden until a clean image is set"

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
check "$(grep -q '<title>5Doctors | Toothbrushes' <<<"$HOME_PAGE" && echo yes || echo no)" "English page title unchanged"

# --- our products page ----------------------------------------------------------
PRODUCTS=$(curl -sL "$WP_URL/products/")
AR_PRODUCTS=$(curl -sL "$WP_URL/ar/products/")

check "$(grep -q 'fivedrs-phero' <<<"$PRODUCTS" && echo yes || echo no)" "products hero renders"

PHERO_IMG=$(grep -oE '<img[^>]*fivedrs-phero__image[^>]*>' <<<"$PRODUCTS" | head -1)
check "$(grep -q 'fetchpriority="high"' <<<"$PHERO_IMG" && echo yes || echo no)" "products hero image has fetchpriority=high"
check "$(grep -q 'loading="lazy"' <<<"$PHERO_IMG" && echo no || echo yes)" "products hero image is NOT lazy-loaded"
check "$(grep -q 'srcset=' <<<"$PHERO_IMG" && echo yes || echo no)" "products hero image has srcset"

PILLS=$(grep -c 'fivedrs-pills__item' <<<"$PRODUCTS")
check "$([ "$PILLS" = "5" ] && echo yes || echo no)" "category pills: All + 4 categories ($PILLS)"

CARDS=$(grep -c 'woocommerce-loop-product__title' <<<"$PRODUCTS")
check "$([ "$CARDS" = "4" ] && echo yes || echo no)" "product grid shows 4 products ($CARDS)"
check "$([ "$(grep -c 'Choose options' <<<"$PRODUCTS")" = "2" ] && echo yes || echo no)" "the two variable products offer Choose options"
check "$(grep -q 'woocommerce-result-count' <<<"$PRODUCTS" && echo no || echo yes)" "result count is hidden"

for cat in classic premium charcoal kids; do
	code=$(curl -sL -o /dev/null -w '%{http_code}' "$WP_URL/product-category/$cat/")
	check "$([ "$code" = "200" ] && echo yes || echo no)" "category archive /$cat/ returns 200 (got $code)"
done

# --- single product ---------------------------------------------------------------
VARIABLE=$(curl -sL "$WP_URL/product/classic-toothbrush/")
SIMPLE=$(curl -sL "$WP_URL/product/charcoal-soft-toothbrush/")

check "$(grep -q 'name="attribute_pa_bristle"' <<<"$VARIABLE" && echo yes || echo no)" "variable product keeps the native variation select"
check "$(grep -q 'fivedrs-variation-hint' <<<"$VARIABLE" && echo yes || echo no)" "variable product shows the choose-a-bristle hint"
check "$(grep -q 'fivedrs-qty__btn' <<<"$VARIABLE" && echo yes || echo no)" "quantity stepper controls render"
check "$(grep -q 'fivedrs-bristle-badge' <<<"$SIMPLE" && echo yes || echo no)" "simple product shows a bristle badge"
check "$(grep -q 'fivedrs-bristle-badge' <<<"$VARIABLE" && echo no || echo yes)" "variable product shows no bristle badge"

# Soft, then Medium, then Hard - not alphabetical.
ORDER=$(grep -oE '<option value="(soft|medium|hard)"' <<<"$VARIABLE" | sed 's/.*value="//;s/"//' | tr '\n' ' ')
check "$([ "$ORDER" = "soft medium hard " ] && echo yes || echo no)" "bristle options are ordered soft/medium/hard ($ORDER)"

check "$([ "$(grep -c '"@type":"Product"' <<<"$VARIABLE")" = "1" ] && echo yes || echo no)" "exactly one Product schema on a variable product"

# --- add to cart ---------------------------------------------------------------------
JAR3=$(mktemp)
MEDIUM=$(grep -oE '"sku":"5DRS-CLS-M","variation_id":[0-9]+' <<<"$VARIABLE" | grep -oE '[0-9]+$' | head -1)
if [ -z "$MEDIUM" ]; then
	MEDIUM=$(grep -oE 'variation_id&quot;:[0-9]+' <<<"$VARIABLE" | grep -oE '[0-9]+' | sed -n 2p)
fi

ADD=$(curl -s -c "$JAR3" -b "$JAR3" -X POST "$WP_URL/?wc-ajax=add_to_cart" -d "product_id=${MEDIUM:-0}&quantity=3")
check "$(grep -qE '>3<' <<<"$ADD" && echo yes || echo no)" "adding 3 of a variation puts 3 in the cart"

CART=$(curl -sL -c "$JAR3" -b "$JAR3" "$WP_URL/cart/")
check "$(grep -q 'Bristle Type' <<<"$CART" && echo yes || echo no)" "cart shows the attribute as Bristle Type: ..."

AR_CART=$(curl -sL -c "$JAR3" -b "$JAR3" "$WP_URL/ar/cart/")
check "$(grep -q 'نوع الشعيرات' <<<"$AR_CART" && echo yes || echo no)" "Arabic cart shows نوع الشعيرات"

SIMPLE_ID=$(grep -oE 'name="add-to-cart" value="[0-9]+"' <<<"$SIMPLE" | grep -oE '[0-9]+' | head -1)
ADD2=$(curl -s -c "$JAR3" -b "$JAR3" -X POST "$WP_URL/?wc-ajax=add_to_cart" -d "product_id=${SIMPLE_ID:-0}&quantity=1")
check "$(grep -q 'fivedrs-cart__count' <<<"$ADD2" && echo yes || echo no)" "simple product adds via AJAX and returns the badge"
rm -f "$JAR3"

# --- arabic catalogue -----------------------------------------------------------------
check "$(grep -q 'عناية تناسب كل ابتسامة' <<<"$AR_PRODUCTS" && echo yes || echo no)" "Arabic products hero heading"
check "$(grep -q 'اختر النوع' <<<"$AR_PRODUCTS" && echo yes || echo no)" "Arabic Choose options label"
AR_VARIABLE=$(curl -sL "$WP_URL/ar/product/classic-toothbrush/")
check "$(grep -q 'نوع الشعيرات' <<<"$AR_VARIABLE" && echo yes || echo no)" "Arabic bristle attribute label"
check "$(grep -q 'فرشاة كلاسيك' <<<"$AR_VARIABLE" && echo yes || echo no)" "Arabic product name"
check "$(grep -qE '<title>[^<]*فرشاة كلاسيك' <<<"$AR_VARIABLE" && echo yes || echo no)" "Arabic product page title"

# --- home page categories -----------------------------------------------------------------
CAT_CARDS=$(grep -oE 'class="fivedrs-cat-card" href=' <<<"$HOME_PAGE" | wc -l)
check "$([ "$CAT_CARDS" = "4" ] && echo yes || echo no)" "home page shows four category cards ($CAT_CARDS)"

# --- about page -------------------------------------------------------------------
ABOUT=$(curl -sL "$WP_URL/about/")
AR_ABOUT_PAGE=$(curl -sL "$WP_URL/ar/about/")

check "$([ "$(grep -c '<h1' <<<"$ABOUT")" = "1" ] && echo yes || echo no)" "about page has exactly one h1"

for sect in phero story slideshow mission values cats promise cta; do
	check "$(grep -q "fivedrs-$sect" <<<"$ABOUT" && echo yes || echo no)" "about section renders: $sect"
done

ABOUT_HERO=$(grep -oE '<img[^>]*fivedrs-phero__image[^>]*>' <<<"$ABOUT" | head -1)
check "$(grep -q 'fetchpriority="high"' <<<"$ABOUT_HERO" && echo yes || echo no)" "about hero image has fetchpriority=high"
check "$(grep -q 'loading="lazy"' <<<"$ABOUT_HERO" && echo no || echo yes)" "about hero image is NOT lazy-loaded"
check "$(grep -q 'srcset=' <<<"$ABOUT_HERO" && echo yes || echo no)" "about hero image has srcset"

# --- slideshow -----------------------------------------------------------------------
SLIDES=$(grep -oE 'fivedrs-slideshow__image' <<<"$ABOUT" | wc -l)
check "$([ "$SLIDES" = "6" ] && echo yes || echo no)" "slideshow has six slides ($SLIDES)"

DOTS=$(grep -oE 'data-slideshow-dot=' <<<"$ABOUT" | wc -l)
check "$([ "$DOTS" = "6" ] && echo yes || echo no)" "slideshow has six dots ($DOTS)"

check "$(grep -q 'data-slideshow-toggle' <<<"$ABOUT" && echo yes || echo no)" "slideshow has a pause control (WCAG 2.2.2)"
check "$(grep -q 'aria-current="true"' <<<"$ABOUT" && echo yes || echo no)" "the active dot carries aria-current"
check "$(grep -q 'data-slideshow-interval="5000"' <<<"$ABOUT" && echo yes || echo no)" "slideshow advances every 5s"

# First slide eager so it paints immediately; the other five wait.
EAGER=$(grep -oE '<img[^>]*fivedrs-slideshow__image[^>]*loading="eager"' <<<"$ABOUT" | wc -l)
LAZY=$(grep -oE '<img[^>]*fivedrs-slideshow__image[^>]*loading="lazy"' <<<"$ABOUT" | wc -l)
check "$([ "$EAGER" = "1" ] && [ "$LAZY" = "5" ] && echo yes || echo no)" "first slide eager, the rest lazy ($EAGER/$LAZY)"

# --- promise word reveal ---------------------------------------------------------------
# Splitting must never happen inside a word, or Arabic letters stop joining.
EN_WORDS=$(grep -oE '<span class="fivedrs-word"[^>]*>[^<]*</span>' <<<"$ABOUT" | sed 's/<[^>]*>//g' | tr '\n' ' ' | sed 's/ *$//')
check "$([ "$EN_WORDS" = "Our Goal Is Your Personal Comfort" ] && echo yes || echo no)" "English promise splits into whole words"

AR_WORDS=$(grep -oE '<span class="fivedrs-word"[^>]*>[^<]*</span>' <<<"$AR_ABOUT_PAGE" | sed 's/<[^>]*>//g' | tr '\n' ' ' | sed 's/ *$//')
check "$([ "$AR_WORDS" = "هدفنا راحتك الشخصية" ] && echo yes || echo no)" "Arabic promise splits into whole words, letters intact"

# --- about in arabic ----------------------------------------------------------------------
check "$(grep -q 'الابتسامة المشرقة' <<<"$AR_ABOUT_PAGE" && echo yes || echo no)" "Arabic about hero heading"
check "$(grep -q 'عناية بالفم مصممة' <<<"$AR_ABOUT_PAGE" && echo yes || echo no)" "Arabic story heading"
check "$(grep -q 'ما نؤمن به' <<<"$AR_ABOUT_PAGE" && echo yes || echo no)" "Arabic values heading"
check "$(grep -qE '<title>[^<]*عناية بالفم' <<<"$AR_ABOUT_PAGE" && echo yes || echo no)" "Arabic about page title"

ABOUT_LEFT=0
for phrase in "Who We Are" "Our Mission" "What We Stand For" "Find Your Perfect"; do
	grep -q "$phrase" <<<"$AR_ABOUT_PAGE" && ABOUT_LEFT=$((ABOUT_LEFT+1))
done
check "$([ "$ABOUT_LEFT" = "0" ] && echo yes || echo no)" "no English About copy left on /ar/ ($ABOUT_LEFT found)"

# The closing band carries two buttons here and one on the Home page.
ABOUT_CTA_BTNS=$(grep -oE 'fivedrs-cta__actions' <<<"$ABOUT" | wc -l)
check "$([ "$ABOUT_CTA_BTNS" = "1" ] && grep -q 'fivedrs-cta__secondary' <<<"$ABOUT" && echo yes || echo no)" "about CTA has a secondary button"
check "$(grep -q 'fivedrs-cta__secondary' <<<"$HOME_PAGE" && echo no || echo yes)" "home CTA still has only one button"

# --- contact page ---------------------------------------------------------------
CONTACT=$(curl -sL "$WP_URL/contact/")
AR_CONTACT=$(curl -sL "$WP_URL/ar/contact/")

check "$([ "$(grep -c '<h1' <<<"$CONTACT")" = "1" ] && echo yes || echo no)" "contact page has exactly one h1"
check "$(grep -q 'fivedrs-hero--gradient' <<<"$CONTACT" && echo yes || echo no)" "contact hero uses the brand gradient"
check "$(grep -q 'fivedrs-info-card' <<<"$CONTACT" && echo yes || echo no)" "contact information card renders"

# Every field needs a real label, not a placeholder standing in for one.
for field in name phone email message; do
	check "$(grep -q "for=\"fivedrs-$field\"" <<<"$CONTACT" && echo yes || echo no)" "field has a visible label: $field"
done

check "$(grep -qE 'id="fivedrs-phone"[^>]*' <<<"$CONTACT" && grep -q 'type="tel"' <<<"$CONTACT" && echo yes || echo no)" "phone field is type=tel"
check "$(grep -q 'inputmode="tel"' <<<"$CONTACT" && echo yes || echo no)" "phone field sets inputmode=tel"
check "$(grep -q 'autocomplete="tel"' <<<"$CONTACT" && echo yes || echo no)" "phone field sets autocomplete=tel"
check "$(grep -q 'autocomplete="name"' <<<"$CONTACT" && echo yes || echo no)" "name field sets autocomplete=name"
check "$(grep -q 'type="email"' <<<"$CONTACT" && echo yes || echo no)" "email field is type=email"
check "$(grep -q 'maxlength="2000"' <<<"$CONTACT" && echo yes || echo no)" "message field is capped at 2000 characters"
check "$([ "$(grep -o 'required' <<<"$CONTACT" | wc -l)" -ge 3 ] && echo yes || echo no)" "name, phone and message are required"
check "$(grep -q 'aria-describedby="fivedrs-name-error"' <<<"$CONTACT" && echo yes || echo no)" "field errors are wired with aria-describedby"
check "$(grep -q 'aria-live="polite"' <<<"$CONTACT" && echo yes || echo no)" "status region is a polite live region"

# Spam defences must be in the markup, not just in the handler.
check "$(grep -q 'fivedrs_contact_nonce' <<<"$CONTACT" && echo yes || echo no)" "form carries a nonce"
check "$(grep -q 'name="fivedrs_website"' <<<"$CONTACT" && echo yes || echo no)" "form carries a honeypot field"
check "$(grep -q 'name="fivedrs_tsh"' <<<"$CONTACT" && echo yes || echo no)" "form carries a signed timestamp"
check "$(grep -q 'admin-post.php' <<<"$CONTACT" && echo yes || echo no)" "form posts to admin-post.php so it works without JS"

# Social marks show here even with no URLs, but must not be dead links.
check "$(grep -q 'fivedrs-social__placeholder' <<<"$CONTACT" && echo yes || echo no)" "social icons render while URLs are empty"
check "$(grep -q 'href="#"' <<<"$CONTACT" && echo no || echo yes)" "no placeholder link goes nowhere"

# --- contact details are shared with the footer ---------------------------------------
check "$(grep -q 'Fifth Settlement' <<<"$CONTACT" && echo yes || echo no)" "contact page shows the address"
check "$(grep -q 'Fifth Settlement' <<<"$HOME_PAGE" && echo yes || echo no)" "footer shows the address"
check "$(grep -q 'mbakr.gop@gmail.com' <<<"$HOME_PAGE" && echo yes || echo no)" "footer shows the email"
check "$(grep -q '+20 111 156 1011' <<<"$HOME_PAGE" && echo yes || echo no)" "footer shows the phone"
check "$(grep -q 'التجمع الخامس' <<<"$AR_HOME" && echo yes || echo no)" "Arabic footer shows the address"

# --- contact page in arabic --------------------------------------------------------------
check "$(grep -q 'أرسل لنا رسالة' <<<"$AR_CONTACT" && echo yes || echo no)" "Arabic form heading"
check "$(grep -q 'معلومات التواصل' <<<"$AR_CONTACT" && echo yes || echo no)" "Arabic information card heading"
check "$(grep -q 'رقم الهاتف' <<<"$AR_CONTACT" && echo yes || echo no)" "Arabic field labels"
check "$(grep -q '<bdi dir="ltr">' <<<"$AR_CONTACT" && echo yes || echo no)" "phone is wrapped in bdi so it reads LTR"
check "$(grep -qE '<title>[^<]*تواصل معنا' <<<"$AR_CONTACT" && echo yes || echo no)" "Arabic contact page title"

# The script must carry no English of its own on /ar/.
AR_JS=$(grep -oE 'fivedrsContact = \{.*?\};' <<<"$AR_CONTACT" | head -1)
check "$(grep -q 'Thank you' <<<"$AR_JS" && echo no || echo yes)" "JS status strings carry no English on /ar/"

# --- message storage ------------------------------------------------------------------------
# Asserted by inspecting the registration, not by submitting anything.
WPOUT=$(mktemp)
"$SCRIPT_DIR/wp.sh" eval >"$WPOUT" 2>/dev/null '
$o = get_post_type_object("fivedrs_message");
echo $o ? sprintf("%s|%s|%s|%s", var_export($o->public,true), var_export($o->publicly_queryable,true), var_export($o->show_in_rest,true), $o->cap->create_posts) : "missing";
'
CPT=$(tr -d "[:space:]" < "$WPOUT")
check "$([ "$CPT" = "false|false|false|do_not_allow" ] && echo yes || echo no)" "messages CPT is private, non-REST and read-only ($CPT)"

# --- validation rules -------------------------------------------------------------------------
# Exercises the pure validator directly: nothing is submitted, stored or emailed.
"$SCRIPT_DIR/wp.sh" eval >"$WPOUT" 2>/dev/null '
$cases = array(
  array(array("name"=>"Mazen","phone"=>"+20 111 156 1011","email"=>"a@b.com","message"=>"Hello"), ""),
  array(array("name"=>"Mazen","phone"=>"01111561011","email"=>"","message"=>"Hi"), ""),
  array(array("name"=>"","phone"=>"01111561011","email"=>"","message"=>"Hi"), "name"),
  array(array("name"=>"Mazen","phone"=>"12345","email"=>"","message"=>"Hi"), "phone"),
  array(array("name"=>"Mazen","phone"=>"0111ABC1011","email"=>"","message"=>"Hi"), "phone"),
  array(array("name"=>"Mazen","phone"=>"01111561011","email"=>"nope","message"=>"Hi"), "email"),
  array(array("name"=>"Mazen","phone"=>"01111561011","email"=>"","message"=>""), "message"),
);
$bad = 0;
foreach ($cases as $c) {
  $got = implode(",", array_keys(fivedrs_contact_validate($c[0])["errors"]));
  if ($got !== $c[1]) { $bad++; }
}
echo $bad;
'
RULES=$(tr -d "[:space:]" < "$WPOUT")
rm -f "$WPOUT"
check "$([ "$RULES" = "0" ] && echo yes || echo no)" "validator accepts and rejects the right inputs ($RULES mismatches)"

# --- policies page ----------------------------------------------------------------
POLICY=$(curl -sL "$WP_URL/policies/")
AR_POLICY=$(curl -sL "$WP_URL/ar/policies/")

check "$([ "$(grep -c '<h1' <<<"$POLICY")" = "1" ] && echo yes || echo no)" "policies page has exactly one h1"
check "$(grep -q 'Return &#038; Exchange Policy' <<<"$POLICY" && echo yes || echo no)" "policies h1 is the policy name"
check "$([ "$(grep -c '<h2' <<<"$POLICY")" -ge 4 ] && echo yes || echo no)" "policy has its four sections"
check "$(grep -q 'fivedrs-policy-notice' <<<"$POLICY" && echo yes || echo no)" "the hygiene rule is highlighted"
check "$(grep -q 'سياسة الاسترجاع والاستبدال' <<<"$AR_POLICY" && echo yes || echo no)" "Arabic policy heading"
check "$(grep -q 'أولًا: شروط' <<<"$AR_POLICY" && echo yes || echo no)" "Arabic section headings"

POLICY_LEFT=0
for phrase in "General Return" "Unopened products" "Refund Process" "How to Request"; do
	grep -q "$phrase" <<<"$AR_POLICY" && POLICY_LEFT=$((POLICY_LEFT+1))
done
check "$([ "$POLICY_LEFT" = "0" ] && echo yes || echo no)" "no English policy copy left on /ar/ ($POLICY_LEFT found)"

# The four child pages were deleted; nothing may still link to them.
DEAD=0
for page in "$HOME_PAGE" "$POLICY" "$CONTACT" "$AR_HOME" "$AR_POLICY"; do
	grep -qE 'href="[^"]*(privacy-policy|shipping-delivery|returns-refunds|terms-conditions)' <<<"$page" && DEAD=$((DEAD+1))
done
check "$([ "$DEAD" = "0" ] && echo yes || echo no)" "nothing links to the deleted policy pages ($DEAD)"
check "$([ "$(curl -s -o /dev/null -w '%{http_code}' "$WP_URL/policies/privacy-policy/")" = "404" ] && echo yes || echo no)" "old policy URLs are gone"

# Checkout's terms checkbox must open the policy, not a deleted page.
TERMS=$(./scripts/wp.sh option get woocommerce_terms_page_id 2>/dev/null | tr -d "[:space:]")
POLICY_ID=$(./scripts/wp.sh eval 'echo ( get_page_by_path("policies") ? get_page_by_path("policies")->ID : 0 );' 2>/dev/null | tr -d "[:space:]")
check "$([ -n "$TERMS" ] && [ "$TERMS" = "$POLICY_ID" ] && echo yes || echo no)" "checkout terms page points at Policies ($TERMS)"
check "$([ "$(./scripts/wp.sh option get wp_page_for_privacy_policy 2>/dev/null | tr -d '[:space:]')" = "0" ] && echo yes || echo no)" "privacy page option is unset"

# --- build ------------------------------------------------------------------
check "$([ -f wp-content/themes/5drs/dist/.vite/manifest.json ] && echo yes || echo no)" "theme build output exists"
check "$([ -f wp-content/themes/5drs/languages/5drs.pot ] && echo yes || echo no)" "translation template exists"
check "$([ -f wp-content/themes/5drs/languages/ar.mo ] && echo yes || echo no)" "compiled Arabic translations exist"
check "$([ -f wp-content/themes/5drs/assets/images/logo-lockup.png ] && echo yes || echo no)" "theme logo asset exists"

echo
printf 'passed: %s   failed: %s\n' "$PASS" "$FAIL"
[ "$FAIL" -eq 0 ]
