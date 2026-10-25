# CLAUDE.md — 5drs

Bilingual (English + Arabic) WooCommerce store on a fully custom classic PHP
theme. This file is the working agreement for anyone — human or agent — touching
this repo.

---

## 1. Stack and ports

Local development runs entirely in Docker (`docker-compose.yml`).

| Service      | Image                   | URL / port              | Notes |
|--------------|-------------------------|-------------------------|-------|
| `wordpress`  | `wordpress:php8.2-apache` | http://localhost:8080 | Core lives in the `wp_core` **named volume**, not in the repo |
| `db`         | `mariadb:10.11`         | internal only           | Data in the `db_data` named volume |
| `phpmyadmin` | `phpmyadmin:5-apache`   | http://localhost:8081   | Auto-logs in with the `.env` DB credentials |
| `mailpit`    | `axllent/mailpit:v1.21` | http://localhost:8025   | Catches **all** outgoing mail locally |
| `wpcli`      | `wordpress:cli-php8.2`  | — | `cli` profile, runs as uid/gid `33:33` |

Only `./wp-content` is bind-mounted into the container. WordPress core is
deliberately **not** in the repo.

### Environment gotchas (this machine)

- **SELinux is enforcing**, so every bind mount carries a `:z` suffix. Dropping
  it makes Apache return 403 on everything in `wp-content`.
- `wp-content` is owned `1000:33` with setgid directories, so the host user and
  `www-data` can both write. Files WordPress creates are owned by uid 33 — use
  `make wp ...` for plugin/theme installs rather than editing those by hand.
- `WORDPRESS_CONFIG_EXTRA` is `eval()`'d from the **runtime environment** on
  every request, so it is defined once as the `x-wp-environment` YAML anchor and
  shared by both `wordpress` and `wpcli`. If only one service gets it, constants
  like `WP_ENVIRONMENT_TYPE` silently go missing under WP-CLI.
- `WP_DEBUG` must be switched on with `WORDPRESS_DEBUG: 1`, not in the extra
  config — the image defines `WP_DEBUG` before the extra config is evaluated.

---

## 2. Running things

Everything is a Make target; run `make help` for the list.

```bash
make up                 # start the stack
make down               # stop it (named volumes are kept)
make logs               # tail all services
make shell              # bash inside the WordPress container

make wp plugin list     # any WP-CLI command
make wp option get home
# Flags must go through ARGS - make would read them as its own options:
make wp ARGS="plugin list --fields=name,status"

make db-export                                  # ./backups/5drs-<timestamp>.sql.gz
make db-import FILE=backups/5drs-....sql.gz

make theme-dev          # Vite dev server + hot reload
make theme-build        # production build into the theme's dist/
make pot                # regenerate languages/5drs.pot
make mo                 # compile languages/ar.po -> ar.mo
make pages              # rebuild the page tree, templates and menus
make store-setup        # remove placeholder data, categories + Bristle Type attribute
make product-media      # convert assets/products to WebP and import them
make products           # create the four real products
make about-media        # convert the About hero to WebP and import it
make home-media         # convert assets/home to WebP and import to the Media Library
make categories         # category card images
make home-content       # Arabic Home copy + the Home SEO fields
make product-content    # Arabic catalogue + Products page copy
make about-content      # Arabic About copy + the About SEO fields
make contact-content    # Arabic Contact copy + the Contact SEO fields
make policies-content   # the policy text, its Arabic and the Policies SEO fields
make translations       # seed the Arabic translations TranslatePress serves
make crawl              # request every page in both languages

make lint               # PHPCS (WordPress Coding Standards)
make lint-fix           # phpcbf auto-fixes
make seed               # recreate the 3 test products
make trp-tables         # (re)create TranslatePress tables after a CLI language change
make mail-test          # create a test order, check Mailpit
make verify             # smoke-test the whole stack
```

WP-CLI runs in its own container, so paths are **container paths**:
`/var/www/html/...`, not host paths. `./scripts/wp.sh` is the thin wrapper the
Makefile uses.

---

## 3. Installed plugins, and why

| Plugin | Why it is here |
|---|---|
| **WooCommerce** | The store. |
| **TranslatePress (free)** | English/Arabic. English is the default at `/`; Arabic is served at `/ar/`. The free tier allows exactly one secondary language, which is all this site needs. Its settings were written straight to the `trp_settings` option, which skips the routine that creates its database tables — `make trp-tables` does that. Re-run it if you ever add or rename a language from the CLI. |
| **Rank Math SEO** | SEO. Deliberately trimmed to `sitemap`, `rich-snippet`, `woocommerce`, `link-counter`; the setup wizard and account connection are skipped. |
| **Query Monitor** | **Development only.** See §7. |

**Do not add** caching, security or payment/shipping plugins here. Caching
(LiteSpeed Cache) is configured on Hostinger; payment and shipping are a
separate task.

---

## 4. The theme

Location: `wp-content/themes/5drs`.

```
style.css            theme header only - no CSS lives here
functions.php        constants + requires from inc/ - no logic
theme.json           editor presets, mirroring src/css/tokens.css
inc/setup.php        theme supports, menus, image sizes, text domain
inc/assets.php       enqueues the Vite build from dist/
inc/i18n.php         language + RTL helpers, per-language fonts, switcher
inc/customizer.php   contact + social fields the client fills in
inc/customizer-content.php  Home and Products page content (see 5.5)
inc/template-tags.php  logo, SVG icons, cart button, contact and social output
inc/seo.php          FAQ schema, Arabic SEO metadata, breadcrumb/schema translation
inc/contact.php      contact form handling and the Messages post type
inc/woocommerce.php  WooCommerce support, wrappers, cart fragment, shop + product hooks
header.php footer.php front-page.php page.php page-about.php
page-contact.php page-policies.php index.php single.php 404.php sidebar.php
template-parts/
  page-hero.php            shared page header
  section-placeholder.php  empty, named section container
  hero-banner.php          full-bleed hero, shared by Products and About
  category-cards.php       the four category cards, shared by Home and About
  cta-band.php             closing call-to-action, shared by Home and About
  nav/offcanvas.php        mobile menu
  home/                    the nine Home page sections
  about/                   story, slideshow, mission, values, promise
  contact/                 form and information card
  products/toolbar.php     pills + search + sorting, one row
  products/filter.php      category pill links
  products/search.php      product search form (post_type=product)
woocommerce/         empty by design - read its README before adding anything
assets/images/       logo-lockup.png, logo-full.png (generated, see below)
languages/           5drs.pot, ar.po, ar.mo
src/css/             tokens.css, base.css, components/, woocommerce.css
                     components/on-dark.css is UNLAYERED - see 4.7
                     components/home-motion.css holds every Home animation
src/js/              main.js + modules/ (offcanvas, header, cart, reveal,
                     shop, product, cart-notice, slideshow, parallax, faq)
dist/                build output, gitignored
```

### 4.1 The `fivedrs_` prefix rule — non-negotiable

PHP identifiers cannot start with a digit, so `5drs` cannot be a code prefix.

- Functions, classes, hooks, globals: **`fivedrs_`**
- Constants: **`FIVEDRS_`** (e.g. `FIVEDRS_VERSION`, `FIVEDRS_DIR`)
- Text domain: **`5drs`** (unchanged — it is a string, not an identifier)

PHPCS enforces both via `phpcs.xml.dist`.

### 4.2 Design tokens

Colours come from the logo, not from guesswork.
`scripts/brand/extract-logo.py` samples `assets/brand/logo5drs.jpeg` and writes
the theme's two PNGs; `scripts/brand/tokens.py` generates the 50–900 scales and
audits every text/background pair for WCAG AA.

| Role | Anchor | Notes |
|---|---|---|
| Navy | `#0e3d4d` | sampled from the "5D" wordmark - a teal-navy, not a blue |
| Green | `#82ae61` | 2.57:1 on white, so **accents, icons and fills only** |
| Green (text) | `#4d6f35` | the green that may carry text, 5.77:1 |
| Gold | `#d0b572` | 1.99:1 - decorative highlights only, never text |

Tokens live once in `src/css/tokens.css` (Tailwind v4 `@theme`) and are mirrored
into `theme.json` for the editor. **After changing any colour run
`python3 scripts/brand/tokens.py --check`** - it fails if a pair drops below AA.
Use the semantic tokens (`--color-ink`, `--color-surface`, `--color-primary`,
`--color-accent-strong`, …) in components rather than a raw scale step.

### 4.3 Fonts

Montserrat (English) and Tajawal (Arabic), **self-hosted** via `@fontsource`
and bundled by Vite - no Google CDN. They are separate Vite entries
(`src/css/fonts-en.css`, `src/css/fonts-ar.css`) and
`fivedrs_enqueue_fonts()` loads only the one the current language needs, so an
Arabic visitor never downloads Montserrat and vice versa. The 400 weight is
preloaded by `fivedrs_preload_font()`.

Font files are emitted to `dist/fonts/` **unhashed** (`vite.config.js`
`assetFileNames`) precisely so that preload path is predictable; everything else
stays content-hashed.

### 4.4 Logo

`assets/brand/logo5drs.jpeg` is the supplied original: a JPEG on a near-white
ground, with a tagline band that is only 21px tall and unreadable at header
size. `scripts/brand/extract-logo.py` crops it, knocks the background out to
transparency and writes:

- `assets/images/logo-lockup.png` - "5Drs" + "5Doctors", used in the header and footer
- `assets/images/logo-full.png` - the complete lockup, the Customizer default

The tagline is printed as **real text** (`fivedrs_tagline()`), not baked into
the image, so it translates into Arabic and is readable by screen readers.
A client logo set in Appearance → Customize wins over both files.
**Ask the designer for an SVG** when one is available and re-point
`fivedrs_logo()` at it.

### 4.5 Bilingual and RTL rules — apply from the first line of markup

1. **Every** user-facing string goes through a translation function with the
   `5drs` text domain: `__( 'Text', '5drs' )`, `esc_html_e( 'Text', '5drs' )`.
   No bare strings in templates. Run `make pot` after adding strings.
2. **Never write `left` or `right` in CSS.** Use logical properties:
   `margin-inline-start`, `padding-inline-end`, `inset-inline-start`,
   `border-inline-end`, `text-align: start | end`.
   In markup use Tailwind's logical utilities: `ms-* me-* ps-* pe-*`,
   `start-* end-*`, `text-start` / `text-end`, `border-s-*` / `border-e-*`.
   The `rtl:` / `ltr:` variants are a last resort for things that genuinely
   cannot mirror on their own (arrow glyphs, background images, transforms).
3. `<html>` direction and language come from `language_attributes()`. Arabic
   pages get `dir="rtl" lang="ar"` with no extra work — do not hardcode either.
4. Ask `fivedrs_current_language()` / `fivedrs_is_rtl()` (in `inc/i18n.php`) for
   the current language; TranslatePress swaps the locale mid-request, so never
   cache it.
5. Fonts load per language in `fivedrs_enqueue_fonts()` — currently an empty,
   documented slot. Components must reference the `--font-sans` /
   `--font-arabic` custom properties, never a font family name directly.
6. The language switcher is `fivedrs_language_switcher()`. It builds its own
   markup from TranslatePress's `trp_custom_language_switcher()` data rather
   than the plugin's `[language-switcher]` shortcode, so it is fully styleable
   and carries `aria-current`.
7. **The language switcher must carry `data-no-translation`.** TranslatePress
   rewrites every `href` on the page to the current language, including the
   switcher's own links - without the marker, the "English" link on an Arabic
   page points back at the Arabic URL and there is no way back. The attribute is
   inherited by descendants. It also has to survive escaping, so the switcher is
   printed with `fivedrs_kses_switcher()` rather than `wp_kses_post()`, which
   strips `data-*`.
8. The off-canvas menu slides from the **start** edge. Its transform and the
   button arrow glyph are the only two direction-specific rules in the theme;
   both are commented as such. Do not add a third without the same justification.

### 4.6 WooCommerce template overrides

**Hooks first, overrides last.** Full rule in
`wp-content/themes/5drs/woocommerce/README.md`. In short:

- Change output from `inc/woocommerce.php` using WooCommerce's own hooks.
- Only if that is impossible, copy the **single** template you are changing from
  `wp-content/plugins/woocommerce/templates/<path>` to
  `wp-content/themes/5drs/woocommerce/<path>`, keep its `@version` header, and
  re-check it after every WooCommerce update (`WooCommerce → Status` flags stale
  overrides).
- Cart and Checkout use the classic shortcodes `[woocommerce_cart]` and
  `[woocommerce_checkout]` precisely so these PHP templates render. **Do not
  convert those pages back to blocks.**

### 4.7 Cascade layers — the rule that bites

Tailwind puts everything in cascade layers. **WooCommerce's stylesheets are
unlayered, and an unlayered rule beats a layered one no matter how specific the
layered one is.** A layered `.fivedrs-brand img { height: 2.5rem }` loses to
WooCommerce's unlayered `.woocommerce img { height: auto }`, which is how the
header logo ended up rendering at its intrinsic 534x364 on every store page.

So the split is:

- `src/css/woocommerce.css` and anything else whose job is to outrank a plugin
  is **unlayered**. It competes on specificity and source order instead, and
  the theme stylesheet is enqueued after WooCommerce's.
- The theme's own components stay in `@layer components`, so Tailwind utilities
  can still override them when pages get built.

Text on dark surfaces is the second case. `src/css/components/on-dark.css` is
unlayered too, because button labels and footer links have to beat the base
layer's `a { color: ... }` as well as any plugin rule - a layered
`.fivedrs-btn { color: white }` lost to it, and the Home page's "Shop Now"
label rendered in link blue on its navy pill. The one thing that file must not
do is paint the transparent variants: `--outline` and `--ghost` sit on whatever
is behind them, so they are excluded from the blanket rule and coloured per
context.

`python3 scripts/check-css-layers.py` asserts all of this and runs inside
`make verify`. The failure mode is silent - valid CSS that simply loses - so
check it rather than eyeballing the page.

### 4.8 Build tooling

Vite + Tailwind v4 (`@tailwindcss/vite`, CSS-first config in
`src/css/main.css`). `make theme-dev` writes `dist/.vite-dev-server`, which
`inc/assets.php` detects to load assets from the dev server; the production
build writes a hashed bundle plus `dist/.vite/manifest.json`, which the same
file reads. Assets are versioned by file mtime.

### 4.9 Coding standards

PHPCS with WordPress Coding Standards + PHPCompatibilityWP, targeting PHP 8.2
and WordPress 6.5+. `make lint` must pass before committing. Suppressions need a
comment saying why (see `inc/assets.php` for the pattern).

`sidebar.php` exists even though the design has no sidebar: WordPress and
WooCommerce both call `get_sidebar()`, and a theme without the file logs a
deprecation notice on every request. It is an empty stub - the theme registers
no widget areas at all. See 5.6 for why that matters.

---

## 5. Pages, menus and translations

### 5.1 The page tree

`make pages` builds it and is idempotent (pages are matched by slug):

| Page | Slug | Template |
|---|---|---|
| Home | `home` | `front-page.php`, set as the static front page |
| Our Products | `products` | the WooCommerce shop page, renamed from "Shop" |
| About Us | `about` | `page-about.php` |
| Contact Us | `contact` | `page-contact.php` |
| Policies | `policies` | `page-policies.php` |

Policies is a **single page** - see 5.9. It is titled "Return & Exchange
Policy", while both menus label the link "Policies"; `make pages` sets that
label explicitly rather than letting it inherit the page title.

`woocommerce_terms_page_id` points at Policies, so the checkout "I have read and
agree to the terms" checkbox opens the return and exchange policy.
`wp_page_for_privacy_policy` is **deliberately unset** - there is no separate
privacy page, and leaving it pointing at a deleted post would render a link to
nothing. **`woocommerce_shop_page_id` still points at the renamed shop page** -
do not create a second shop page.

Each template has both a `Template Name:` header and the `page-{slug}.php`
filename, and `_wp_page_template` is set explicitly, so renaming a slug cannot
silently detach a template.

### 5.2 Page shells

Every page now carries real content: Home (5.5), Our Products (5.6),
About (5.7), Contact (5.8) and Policies (5.9).

`template-parts/section-placeholder.php` is still available for building the
next page: it emits an `<!-- Section: ... -->` comment and a dashed box that is
**visible to signed-in editors only**, so visitors see nothing. Replace a
`get_template_part( 'template-parts/section-placeholder', ... )` call with the
real markup when that section is built.

### 5.3 Menus

Two locations, both built by `make pages`: `primary` and `footer-quick`. The
client edits them in Appearance → Menus.

A third location, `footer-policies`, still exists in the theme but has no menu
assigned: it used to list the four policy child pages, and with one Policies
page it would be a heading above a link repeating it. The footer skips that
column when nothing is assigned, and its grid sizes itself to however many
columns are present.

### 5.4 Contact details and social links

Appearance → Customize → **Contact & Social**: phone, email, address, WhatsApp,
Facebook, Instagram, TikTok. One definition in `fivedrs_option_fields()` drives
the controls, the sanitisers and the footer output. Every field is optional and
anything empty is simply not rendered.

### 5.5 The Home page

The Home page is the one page with real content. Everything on it - copy,
buttons, links and images - is editable in **Appearance → Customize → 5drs -
Home Page**, one Customizer section per page section.

A single array, `fivedrs_content_config()` in `inc/customizer-content.php`,
defines the fields, their English defaults, their sanitisers and their
controls. Each section declares which panel it belongs to - `home` or
`products` - so the Home page and the Our Products page share one mechanism
rather than duplicating it. Read values with `fivedrs_content()`, links with
`fivedrs_content_page_url()` / `fivedrs_content_category_url()` and images with
`fivedrs_content_image()`. Add a
field there and it appears in the Customizer automatically; nothing else needs
touching. Read values with `fivedrs_home( 'section', 'field' )`, links with
`fivedrs_home_page_url()` / `fivedrs_home_category_url()`, images with
`fivedrs_home_image()`.

**Clearing a section's text hides that section.** Each part in
`template-parts/home/` returns early when it has nothing to show, so the page
never renders an empty box. The FAQ's JSON-LD disappears with its section, so
markup and schema cannot disagree.

Images live in `assets/home/` as the supplied originals.
`scripts/brand/prepare-images.py home` converts them to WebP (8 MB → 207 KB)
and `make home-media` imports them with stable slugs (`5drs-hero`, …), which is
what the Customizer defaults resolve against - never a hardcoded attachment ID.
The hero is the LCP element: `fetchpriority="high"`, no lazy-loading.
Everything else lazy-loads. All of it goes through `wp_get_attachment_image()`,
so srcset and sizes come from WordPress.

#### Motion

Every Home animation lives in `src/css/components/home-motion.css`, one block
per section, with the reduced-motion opt-out collected at the bottom of the
file rather than scattered through it. Three rules hold:

- **Only `opacity`, `transform` and `clip-path` are animated**, so nothing can
  move the layout. The FAQ is the single exception - there the height change
  *is* the effect, and `src/js/modules/faq.js` owns it.
- **Everything is gated behind a class JavaScript adds**: `js` for the hero,
  which plays on load because it is above the fold, and `fivedrs-reveal-ready`
  for anything waiting on the scroll observer. With JavaScript off the page is
  simply there, fully visible.
- **Two ambient loops only** - the hero image's drift and the CTA band's sheen.
  Both are slow, both are decorative, and both stop under reduced motion.

The FAQ keeps its native `<details>`/`<summary>`: the module animates the
panel's height and bottom padding with the Web Animations API and holds `open`
on until a close finishes. Without JavaScript the accordion still works.

Featured products are *not* in that file. That section carries WooCommerce's
own `woocommerce` class, so `src/js/modules/shop.js` already staggers the cards
through the unlayered rules in `woocommerce.css` - and a layered rule would
lose to them anyway (4.7).

The tips list fixes its column count at the breakpoints rather than leaving it
to `auto-fit`, because the connector between the steps has to know which way to
point: vertical when stacked, horizontal when three across. `transform-origin`
takes no logical keyword, so the edge it grows from is carried in a custom
property and flipped once for RTL - one of the handful of documented direction
exceptions.

#### TODO: placeholder prices

The four products carry **placeholder prices** agreed as a stand-in:
Classic 45, Premium 75, Charcoal 60, Kids 40 EGP. They live in
`scripts/seed/products.php`. Replace them with the client's real prices - in
wp-admin, or in that file followed by `make products` - before launch.

Black Edition is resolved: the category is now **Charcoal** (`charcoal`), and
its photography is in use. The "555" moulded into the charcoal handles is part
of the real product, not generated text.

### 5.6 The Our Products page and the catalogue

**Catalogue.** Four products, all featured, stock management off:

| Product | Type | SKU | Category |
|---|---|---|---|
| Classic Toothbrush | variable (Soft/Medium/Hard) | `5DRS-CLS` + `-S/-M/-H` | Classic |
| Premium Toothbrush | variable (Soft/Medium/Hard) | `5DRS-PRM` + `-S/-M/-H` | Premium |
| Charcoal Soft Toothbrush | simple | `5DRS-CHR-S` | Charcoal |
| Kids Fun Toothbrush | simple | `5DRS-KID` | Kids |

Bristle Type is a **global attribute** (`pa_bristle`). On the variable products
it drives the variations; on the simple ones it is display-only and renders as
a badge instead of a selector. Its terms are ordered Soft → Medium → Hard via
the plain `order` term meta - **not** `order_pa_bristle`, which is the key older
guides suggest and which WooCommerce ignores.

**The shop toolbar** is one row: the category pills on the start side, a
product search and WooCommerce's sort control on the end
(`template-parts/products/toolbar.php`, which prints
`woocommerce_catalog_ordering()` itself - the hook is removed in
`inc/woocommerce.php`). The search is a plain GET form carrying a hidden
`post_type=product`, so it works with JavaScript off and WooCommerce renders
the result with the shop layout.

> **WordPress treats `?s=…&post_type=product` as a post type archive as well as
> a search, so `is_shop()` is true on it.** `fivedrs_is_product_search()` exists
> for that reason, and the hero branch tests it *first* - otherwise the
> catalogue hero prints over the search results.

The empty state is ours (`fivedrs_no_products_found()`), replacing WooCommerce's
bare info notice for both a search that found nothing and an empty category.

**There are no widget areas.** The theme registers none, on purpose: the moment
a theme registers its first sidebar, WordPress moves the widgets a fresh
install created - Search, Recent Posts, Recent Comments - into it, and they
render on every shop and category page. With none registered the orphans have
nowhere to go; `make store-setup` clears the rows an older install wrote, and
WooCommerce's `get_sidebar()` call is unhooked. `sidebar.php` survives as an
empty stub only because WordPress logs a deprecation notice without it.

**The shop hero** is printed before the theme container so it can run edge to
edge, and is the LCP element. Its veil reaches 0.72 opacity at the centre
because the photograph has pure white highlights behind the text; that measures
5.8:1 for white body text. If the image is ever swapped, re-check that number
before trusting it.

**No default variation is set**, so WooCommerce keeps Add to Cart disabled
until a bristle type is chosen. The hint next to it is shown by CSS alone, off
WooCommerce's own `woocommerce-variation-add-to-cart-disabled` class, so it can
never drift out of step with the button.

**The bristle pills are progressive enhancement.** WooCommerce's native
`<select>` is what the form posts and what its variation engine listens to;
`src/js/modules/product.js` builds the pills, writes to the select and
dispatches a bubbling `change`. Only once the pills exist is the select hidden.
With JavaScript off the native dropdown is still there and still works.

**AJAX add-to-cart on the single product page** is ours - WooCommerce only
wires that up on archives. For a variable product the *variation* id is posted
as `product_id`; `WC_AJAX::add_to_cart()` resolves the parent and the chosen
attributes from it.

Two filters worth knowing about, both in `inc/woocommerce.php`:

- `woocommerce_product_variation_title_include_attributes` is turned **off**, so
  a cart line reads "Classic Toothbrush" with "Bristle Type: Medium" beneath it
  rather than "Classic Toothbrush - Medium". The labelled form is clearer and,
  unlike the appended title, both halves translate.
- `woocommerce_attribute_label` is translated at source, because WooCommerce
  prints it with a trailing colon ("Bristle Type:") and that text node never
  matches a plain dictionary entry.

### 5.7 The About page

Seven sections, all driven by the same content config under the **"5drs — About
Page"** panel: hero, Our Story (with the slideshow), Mission & Vision, Our
Values, Our Range, Our Promise and the closing CTA. Clearing a section's text
hides it, as everywhere else.

**Three components are shared rather than copied.** `hero-banner.php`,
`category-cards.php` and `cta-band.php` live at the root of `template-parts/`
and take a `section` argument; their defaults preserve the Home and Products
behaviour, so those pages did not change. The category cards always read the
`categories` config section, so Home and About show the same four cards from
one source and only the heading above them differs. The CTA renders a second
button only when the section defines one.

#### The slideshow

`template-parts/about/slideshow.php` + `src/js/modules/slideshow.js`. It reuses
images already in the Media Library from the Home and Products work - nothing
is imported for it.

- Every slide ships in the DOM, stacked in a **fixed-aspect frame**, so
  switching can never shift the layout. The first is eager, the rest lazy, and
  the next one is primed before it appears.
- **A visible pause control is not optional** - WCAG 2.2.2 requires a way to
  stop anything that moves automatically. It also pauses itself on hover, on
  focus within, when scrolled off screen and when the tab is hidden.
- Under `prefers-reduced-motion` it starts paused with no zoom; the dots still
  work, so the content is all still reachable.
- Without JavaScript the CSS shows the first slide and the controls stay
  hidden, so the section degrades to a single image rather than breaking.

#### The promise reveal, and a rule it illustrates

The statement animates a word at a time. `fivedrs_the_words()` splits on
**whitespace only** - never inside a word - because splitting mid-word would
break Arabic letter joining.

> **Translate before you fragment.** Once the statement is a run of one-word
> spans, each span is its own text node, and TranslatePress would try to look
> up "Our", "Goal" and "Is" separately. So the statement is translated with
> `fivedrs_translate()` *first*, split *second*, and the heading carries
> `data-no-translation` so the plugin leaves the pieces alone. Any future
> effect that chops text into fragments needs the same order.

### 5.8 The Contact page

A compact gradient hero, then the form on the start side and a contact card on
the end. Page wording lives in the **"5drs — Contact Page"** Customizer panel;
field labels and validation messages are theme strings in `languages/ar.po`,
because they are interface text rather than copy a client re-words.

**The address, email and phone are defaults in `fivedrs_option_fields()`**, not
saved theme mods - so they are live on a fresh install, still editable in
Appearance → Customize → Contact & Social, and the footer and the contact page
read the same values. The address is deliberately one line: a line break would
split it into several text nodes, each needing its own translation entry.

#### How submission works

The form posts to `admin-post.php`, so it works with JavaScript switched off:
validate, then Post/Redirect/Get back to the page with a status. The script
intercepts the same form and posts it with `fetch` to the **same handler**,
which answers JSON when asked - so there is no second validation path that
could drift from the first.

**No submitted data ever travels in a URL.** On a failed no-JavaScript
submission the values and errors go into a sixty-second transient keyed by a
hash of IP and user agent; the redirect carries only `?fivedrs_contact=error`.

Spam defences, all server-side: nonce, a honeypot field, and a **signed**
timestamp (`wp_hash`, so the minimum-time check cannot be back-dated), plus a
per-IP rate limit of five submissions per ten minutes.

`fivedrs_contact_validate()` is deliberately pure - it sends nothing, stores
nothing and reads no request state - so the rules can be exercised directly.
`make verify` does exactly that rather than submitting the form.

**Every accepted message is stored before the email is attempted**, as a
private `fivedrs_message` post under the admin menu **Messages**. That post
type is not public, not queryable, not in REST and cannot be created by hand;
it exists so a mail failure can never lose a customer's message.

#### TODO: SMTP on Hostinger

`wp_mail()` sends through PHP mail by default, and the form's recipient is
currently a Gmail address. **Mail sent "from" the server on behalf of a Gmail
address will not pass SPF and will be filed as spam or rejected outright.**

Before launch, set up real SMTP on Hostinger: create a mailbox on the site's
own domain, then either install WP Mail SMTP and point it at that account, or
reuse the `phpmailer_init` approach already in
`wp-content/mu-plugins/local-mail.php` with production credentials. Send the
notification **from** that domain mailbox and keep the Gmail address as the
recipient. Until that is done, treat the Messages screen as the reliable
record, not the inbox.

### 5.9 The Policies page

One page at `/policies/`, titled **Return & Exchange Policy**, carrying the
whole policy. It replaced a hub that listed four child pages - Privacy Policy,
Shipping & Delivery, Returns & Refunds and Terms & Conditions - which were
deleted once the client supplied a single combined policy. `make pages` deletes
any that reappear, so the removal survives a re-run.

The text lives in the **page's own post content as core blocks**, so the client
edits it in the normal WordPress editor rather than in a template.
`make policies-content` writes it and seeds the Arabic; re-running it overwrites
the page content, so edit in wp-admin unless you mean to reset it.

`page-policies.php` renders the content at a ~70ch measure with styled lists,
numbered steps for the request process, and the hygiene rule in a highlighted
notice - the one rule customers most need to see before buying.

> **Bold lead-ins are separate text nodes.** Each bullet is
> `<strong>Lead:</strong> sentence`, which TranslatePress sees as two strings,
> not one. They are seeded separately in `scripts/seed/policies-content.php`;
> seeding the combined sentence would never match. This is the same rule as
> Trap 5 below.

#### TODO: WhatsApp number

Step 1 of the request process says "email or WhatsApp" and links to the Contact
page. **The client has not supplied a WhatsApp number**, so none is shown
anywhere and none was invented. Once there is one, put it in Appearance →
Customize → Contact & Social, where the footer and contact page already read
it, and the link here can point at it directly.

### 5.10 Translations — two stores, and the traps in both

**Theme strings** (`5drs` text domain) live in `languages/ar.po`, compiled with
`make mo`.

> **Trap 1 — the filename.** `load_theme_textdomain()` loads
> `languages/{locale}.mo` for a theme, i.e. **`ar.mo`**. The
> `{domain}-{locale}.mo` form only works under `WP_LANG_DIR/themes/`. Naming it
> `5drs-ar.mo` silently loads nothing.

**Page titles and menu labels** are content, not gettext, so they live in
TranslatePress's dictionary table. `make translations` writes them through
TranslatePress's own `TRP_Query` API.

> **Trap 2 — TranslatePress caches gettext.** It stores every gettext string it
> renders in `wp_trp_gettext_ar` and then serves that row. A string captured
> *before* its translation existed is cached as untranslated and keeps rendering
> in English even after you add it to `ar.po`. `make translations` repairs those
> rows from the `.po`, and it crawls the Arabic pages first because
> TranslatePress only stores a string once it has rendered it - which is why it
> runs two passes.

> **Trap 3 - seed the string as it *renders*, not as you typed it.**
> TranslatePress keys on the exact text in the HTML, and WordPress escapes the
> same character differently depending on the path:
> `esc_html()` turns `'` into `&#039;` and `&` into `&amp;`, while a post title
> also passes through `convert_chars()`, which turns `&` into `&#038;`. Seeding
> only the raw string leaves "Shop Kids' Brushes" and "Shipping & Delivery"
> untranslated forever. `fivedrs_seed_dictionary()` in
> `scripts/seed/lib-trp.php` writes every spelling, so pass it the plain text
> and let it expand.

> **Trap 4 - TranslatePress free does not translate SEO metadata.**
> `<title>` and `<meta name="description">` are its premium SEO Pack. Its
> "title" node accessor matches the HTML `title=""` attribute, not the document
> title. The theme handles this instead: the Arabic pair is stored as
> `fivedrs_seo_title_ar` / `fivedrs_seo_description_ar` post meta next to Rank
> Math's own fields, and `inc/seo.php` swaps it in on Arabic requests. It works
> for any post or page carrying that meta, not just the Home page.

> **Trap 5 - TranslatePress only sees rendered *text nodes*.**
> Anything else needs translating at the source with `fivedrs_translate()`:
> JSON inside an attribute (WooCommerce ships variation descriptions in
> `data-product_variations`), anything inside a `<script>` (the FAQ and Product
> JSON-LD), values Rank Math writes into meta attributes (`og:title`), and text
> that shares a node with punctuation - the last breadcrumb crumb sits next to
> its separator, and an attribute label is printed as "Bristle Type:" with the
> colon attached. All of these are handled in `inc/seo.php` and
> `inc/woocommerce.php`; copy that pattern rather than trying to seed the
> punctuation into the dictionary.

**Editing English breaks the Arabic.** The dictionary is keyed by the English
string, so changing a word in the Customizer leaves that string untranslated on
`/ar/` - it does not fall back to the old Arabic, it shows the new English.
After editing English copy, re-translate it in **TranslatePress → Translate
Site**, or update `scripts/seed/home-content.php` and re-run `make home-content`.

Workflow after adding a translatable string:

```bash
make pot          # pick up the new string
# add the msgstr to wp-content/themes/5drs/languages/ar.po
make mo           # compile
make translations # repair any row TranslatePress already cached
```

## 6. Store configuration

- Base: **Egypt (Cairo)**, currency **EGP**, prices entered **excluding tax**,
  tax calculation **off** for now.
- Guest checkout **on**; account creation at checkout **on**.
- WooCommerce usage tracking, marketplace suggestions, marketing nags and the
  onboarding wizard are all disabled via options.
- The catalogue is four real products in four categories - see 5.6. The
  placeholder `TEST-00x` products, the test order and the Test category were
  removed by `make store-setup`.
- **Prices are placeholders.** See the TODO under 5.5.

---

## 7. Repository layout

Tracked: the `5drs` theme, `wp-content/mu-plugins/`, any future custom plugin,
Docker/Make/config files.
Ignored: `.env`, WordPress core, third-party plugins and themes, `node_modules`,
`vendor`, `dist`, uploads, `wp-content/languages`, `debug.log`, DB dumps.

To track a new custom plugin, add a `!wp-content/plugins/<name>/` line to
`.gitignore` — the plugins directory is ignore-all by default.

---

## 8. Development-only — must not reach production

- **Query Monitor** — deactivate and remove before deploying. It also installs
  the `wp-content/db.php` drop-in; that file is gitignored and must not be
  copied to the server.
- **`wp-content/mu-plugins/local-mail.php`** — routes all mail to Mailpit. It is
  safe to commit and deploy because it returns immediately unless
  `WP_ENVIRONMENT_TYPE === 'local'`, which is only set by `docker-compose.yml`.
  Do not weaken that guard.
- `WP_DEBUG`, `WP_DEBUG_LOG`, `SCRIPT_DEBUG` are on locally only. Production
  must have `WP_DEBUG_DISPLAY` false and ideally `WP_DEBUG` off.
- `backups/`, `node_modules/`, `vendor/`, `src/` and the build config do not
  belong on the server — deploy the built `dist/` only.

---

## 9. Deployment target

The site ships to **Hostinger shared hosting** later:

- **No Docker, no root.** Deployment is files + a database import.
- **PHP 8.2** — keep `composer.json` and the PHPCS `testVersion` aligned with it.
- No WP-CLI guaranteed on the server; assume changes are made through
  wp-admin, SFTP, or Hostinger's tooling.
- Caching will be **LiteSpeed Cache**, configured there, not here.
- Build assets locally (`make theme-build`) and upload `dist/` — there is no
  Node build step on the server.
- Paths and URLs are environment-specific: never hardcode `localhost:8080`.
