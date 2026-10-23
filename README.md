# 5drs

Bilingual (English + Arabic) WooCommerce store with a custom classic PHP theme.

- English at `/`, Arabic at `/ar/` (TranslatePress)
- Custom theme: `wp-content/themes/5drs` — Tailwind v4 + Vite, RTL-first
- Brand tokens sampled from the logo and audited for WCAG AA
- Self-hosted fonts: Montserrat (English), Tajawal (Arabic)
- Local stack: Docker (WordPress + MariaDB + phpMyAdmin + Mailpit + WP-CLI)

The pages are currently **structural shells**: a header, footer and empty,
named section containers. Content and section design come in a later task.

Working conventions live in [CLAUDE.md](CLAUDE.md). Read that before changing
the theme.

---

## Requirements

- Docker + Docker Compose v2
- Node.js 20+ and npm
- GNU Make

No PHP, Composer or WP-CLI are needed on the host — they all run in containers.

## Setup on a fresh machine

```bash
git clone <repo-url> 5drs && cd 5drs

cp .env.example .env         # then edit: set the DB and admin passwords
make up                      # start the stack (first run pulls ~1.5 GB)
```

If this is a brand-new database, install WordPress once:

```bash
make wp core install --url=http://localhost:8080 --title=5drs \
  --admin_user=<user> --admin_password=<password> --admin_email=<email> --skip-email
```

Otherwise import the database you were given:

```bash
make db-import FILE=backups/5drs-<timestamp>.sql.gz
```

Then build the theme and seed the site structure:

```bash
make theme-install
make theme-build
make pages             # page tree, templates and menus
make mo                # compile the Arabic translations
make translations      # seed what TranslatePress serves
make verify            # should report 0 failures
```

### SELinux hosts (Fedora, RHEL, etc.)

The bind mounts already carry `:z`. If Apache still returns 403 on
`wp-content`, re-label and fix ownership:

```bash
docker run --rm -v "$PWD/wp-content:/wpc:z" alpine:3 \
  sh -c 'chown -R 1000:33 /wpc && find /wpc -type d -exec chmod 2775 {} +'
```

## URLs

| What | Where |
|---|---|
| Site (English) | http://localhost:8080 |
| Site (Arabic) | http://localhost:8080/ar/ |
| Admin | http://localhost:8080/wp-admin |
| phpMyAdmin | http://localhost:8081 |
| Mailpit (all local email) | http://localhost:8025 |

Admin credentials are in `.env` (never committed).

## Day-to-day

```bash
make theme-dev     # Vite with hot reload while building pages
make lint          # PHPCS - must pass before committing
make pot           # after adding or changing translatable strings
make mo            # compile languages/ar.po after editing it
make translations  # push Arabic strings into TranslatePress
make crawl         # hit every page in both languages
make help          # everything else

# WP-CLI with flags goes through ARGS:
make wp ARGS="user list --field=user_login"
```

Brand colours are derived from the logo, not hand-picked. After changing a
colour token, re-run the contrast audit:

```bash
python3 scripts/brand/tokens.py --check
```
