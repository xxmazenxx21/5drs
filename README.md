# 5drs

Bilingual (English + Arabic) WooCommerce store with a custom classic PHP theme.

- English at `/`, Arabic at `/ar/` (TranslatePress)
- Custom theme: `wp-content/themes/5drs` — Tailwind v4 + Vite, RTL-first
- Local stack: Docker (WordPress + MariaDB + phpMyAdmin + Mailpit + WP-CLI)

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

Then build the theme:

```bash
make theme-install
make theme-build
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
make help          # everything else
```
