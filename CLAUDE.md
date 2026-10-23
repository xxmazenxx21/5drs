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

make db-export                                  # ./backups/5drs-<timestamp>.sql.gz
make db-import FILE=backups/5drs-....sql.gz

make theme-dev          # Vite dev server + hot reload
make theme-build        # production build into the theme's dist/
make pot                # regenerate languages/5drs.pot

make lint               # PHPCS (WordPress Coding Standards)
make lint-fix           # phpcbf auto-fixes
make seed               # recreate the 3 test products
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
| **TranslatePress (free)** | English/Arabic. English is the default at `/`; Arabic is served at `/ar/`. The free tier allows exactly one secondary language, which is all this site needs. |
| **Rank Math SEO** | SEO. Deliberately trimmed to `sitemap`, `rich-snippet`, `woocommerce`, `link-counter`; the setup wizard and account connection are skipped. |
| **Query Monitor** | **Development only.** See §7. |

**Do not add** caching, security or payment/shipping plugins here. Caching
(LiteSpeed Cache) is configured on Hostinger; payment and shipping are a
separate task.

---

## 4. The theme

Location: `wp-content/themes/5drs`.

```
style.css          theme header only - no CSS lives here
functions.php      constants + requires from inc/ - no logic
theme.json         colour / typography / spacing placeholders
inc/setup.php      theme supports, menus, image sizes, text domain
inc/assets.php     enqueues the Vite build from dist/
inc/woocommerce.php  WooCommerce support + hook customisations
inc/i18n.php       language + RTL helpers, TranslatePress compat, font slot
header.php footer.php index.php page.php single.php 404.php
template-parts/
woocommerce/       empty by design - read its README before adding anything
languages/         5drs.pot (make pot)
src/               Tailwind CSS + JS sources
dist/              build output, gitignored
```

### 4.1 The `fivedrs_` prefix rule — non-negotiable

PHP identifiers cannot start with a digit, so `5drs` cannot be a code prefix.

- Functions, classes, hooks, globals: **`fivedrs_`**
- Constants: **`FIVEDRS_`** (e.g. `FIVEDRS_VERSION`, `FIVEDRS_DIR`)
- Text domain: **`5drs`** (unchanged — it is a string, not an identifier)

PHPCS enforces both via `phpcs.xml.dist`.

### 4.2 Bilingual and RTL rules — apply from the first line of markup

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
6. The language switcher is `fivedrs_language_switcher()`. It is printed
   unstyled in `header.php` and gets designed with the header.

### 4.3 WooCommerce template overrides

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

### 4.4 Build tooling

Vite + Tailwind v4 (`@tailwindcss/vite`, CSS-first config in
`src/css/main.css`). `make theme-dev` writes `dist/.vite-dev-server`, which
`inc/assets.php` detects to load assets from the dev server; the production
build writes a hashed bundle plus `dist/.vite/manifest.json`, which the same
file reads. Assets are versioned by file mtime.

### 4.5 Coding standards

PHPCS with WordPress Coding Standards + PHPCompatibilityWP, targeting PHP 8.2
and WordPress 6.5+. `make lint` must pass before committing. Suppressions need a
comment saying why (see `inc/assets.php` for the pattern).

---

## 5. Store configuration

- Base: **Egypt (Cairo)**, currency **EGP**, prices entered **excluding tax**,
  tax calculation **off** for now.
- Guest checkout **on**; account creation at checkout **on**.
- WooCommerce usage tracking, marketplace suggestions, marketing nags and the
  onboarding wizard are all disabled via options.
- Three products (`TEST-001/002/003`) exist in the **Test** category, tagged
  **`test-data`**, with generated placeholder images. Delete them before launch:
  `make wp post delete $(make wp post list --post_type=product --tag=test-data --format=ids) --force`

---

## 6. Repository layout

Tracked: the `5drs` theme, `wp-content/mu-plugins/`, any future custom plugin,
Docker/Make/config files.
Ignored: `.env`, WordPress core, third-party plugins and themes, `node_modules`,
`vendor`, `dist`, uploads, `wp-content/languages`, `debug.log`, DB dumps.

To track a new custom plugin, add a `!wp-content/plugins/<name>/` line to
`.gitignore` — the plugins directory is ignore-all by default.

---

## 7. Development-only — must not reach production

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

## 8. Deployment target

The site ships to **Hostinger shared hosting** later:

- **No Docker, no root.** Deployment is files + a database import.
- **PHP 8.2** — keep `composer.json` and the PHPCS `testVersion` aligned with it.
- No WP-CLI guaranteed on the server; assume changes are made through
  wp-admin, SFTP, or Hostinger's tooling.
- Caching will be **LiteSpeed Cache**, configured there, not here.
- Build assets locally (`make theme-build`) and upload `dist/` — there is no
  Node build step on the server.
- Paths and URLs are environment-specific: never hardcode `localhost:8080`.
