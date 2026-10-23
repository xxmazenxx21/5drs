# ---------------------------------------------------------------------------
# 5drs - local development
# Run `make help` for the list of targets.
# ---------------------------------------------------------------------------

SHELL      := /bin/bash
THEME_DIR  := wp-content/themes/5drs
COMPOSE    := docker compose
WPCLI      := $(COMPOSE) run --rm wpcli
STAMP      := $(shell date +%Y%m%d-%H%M%S)
UID_GID    := $(shell id -u):$(shell id -g)

.DEFAULT_GOAL := help
.PHONY: help up down restart logs ps wp shell db-export db-import \
        theme-install theme-dev theme-build pot mo lint lint-fix seed pages translations crawl trp-tables mail-test verify

help: ## Show this help
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

# --- stack ------------------------------------------------------------------

up: ## Start the stack (WordPress :8080, phpMyAdmin :8081, Mailpit :8025)
	$(COMPOSE) up -d

down: ## Stop the stack (named volumes are kept)
	$(COMPOSE) down

restart: ## Restart the stack
	$(COMPOSE) restart

ps: ## Show container status
	$(COMPOSE) ps

logs: ## Tail logs from all services
	$(COMPOSE) logs -f --tail=100

shell: ## Open a bash shell in the WordPress container
	$(COMPOSE) exec wordpress bash

# --- wp-cli -----------------------------------------------------------------

# Bare words work for simple commands; anything containing a flag must go
# through ARGS, because make would otherwise parse the flag as its own option.
#   make wp plugin list
#   make wp ARGS="plugin list --fields=name,status"
wp: ## Run WP-CLI: `make wp plugin list` or `make wp ARGS="... --flag"`
	@$(WPCLI) $(if $(ARGS),$(ARGS),$(filter-out $@,$(MAKECMDGOALS)))

# --- database ---------------------------------------------------------------

db-export: ## Dump the database to ./backups/5drs-<timestamp>.sql.gz
	@mkdir -p backups
	@$(COMPOSE) exec -T db sh -c 'exec mariadb-dump --single-transaction --quick --default-character-set=utf8mb4 -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"' | gzip > backups/5drs-$(STAMP).sql.gz
	@echo "wrote backups/5drs-$(STAMP).sql.gz"

db-import: ## Import a dump: `make db-import FILE=backups/5drs-....sql.gz`
	@test -n "$(FILE)" || { echo "usage: make db-import FILE=backups/5drs-....sql.gz"; exit 1; }
	@test -f "$(FILE)" || { echo "no such file: $(FILE)"; exit 1; }
	@if [[ "$(FILE)" == *.gz ]]; then gunzip -c "$(FILE)"; else cat "$(FILE)"; fi | $(COMPOSE) exec -T db sh -c 'exec mariadb -u"$$MARIADB_USER" -p"$$MARIADB_PASSWORD" "$$MARIADB_DATABASE"'
	@echo "imported $(FILE)"

# --- theme ------------------------------------------------------------------

theme-install: ## Install the theme's npm dependencies
	cd $(THEME_DIR) && npm install

theme-dev: ## Vite dev server with hot reload (Ctrl-C to stop)
	cd $(THEME_DIR) && npm run dev

theme-build: ## Production build into the theme's dist/
	cd $(THEME_DIR) && npm run build

pot: ## Regenerate languages/5drs.pot from the theme source
	$(WPCLI) i18n make-pot $(THEME_DIR) $(THEME_DIR)/languages/5drs.pot --domain=5drs --exclude=node_modules,dist,vendor

mo: ## Compile languages/*.po into the .mo files WordPress loads
	$(WPCLI) i18n make-mo $(THEME_DIR)/languages $(THEME_DIR)/languages

# --- quality ----------------------------------------------------------------

lint: ## Run PHPCS (WordPress Coding Standards) over the theme
	@docker run --rm -u $(UID_GID) -e HOME=/tmp -v "$(CURDIR)/$(THEME_DIR):/app:z" -w /app composer:2 sh -c 'composer install --no-interaction --quiet && vendor/bin/phpcs'

lint-fix: ## Auto-fix what PHPCS can fix
	@docker run --rm -u $(UID_GID) -e HOME=/tmp -v "$(CURDIR)/$(THEME_DIR):/app:z" -w /app composer:2 sh -c 'composer install --no-interaction --quiet && vendor/bin/phpcbf; [ $$? -le 1 ]'

# --- data -------------------------------------------------------------------

seed: ## (Re)create the 3 test products
	$(WPCLI) eval-file /scripts/seed/seed-products.php

pages: ## Create the page tree, assign templates and build the menus
	$(WPCLI) eval-file /scripts/seed/pages.php

# TranslatePress only stores a gettext string once it has rendered it, and
# rendering a translated page surfaces further strings, so this converges in
# two crawl/seed passes rather than one.
translations: ## Seed the Arabic translations TranslatePress serves
	@echo "pass 1: crawling Arabic pages so TranslatePress captures every string"
	@./scripts/crawl.sh ar >/dev/null || true
	$(WPCLI) eval-file /scripts/seed/translations.php
	@echo "pass 2"
	@./scripts/crawl.sh ar >/dev/null || true
	$(WPCLI) eval-file /scripts/seed/translations.php

crawl: ## Request every page in both languages and report status codes
	@./scripts/crawl.sh

trp-tables: ## Create the TranslatePress tables for the configured languages
	$(WPCLI) eval-file /scripts/seed/trp-tables.php

mail-test: ## Create a test order and send its emails to Mailpit
	$(WPCLI) eval-file /scripts/seed/test-order.php
	@echo "check http://localhost:8025"

verify: ## Smoke-test the running stack
	@./scripts/verify.sh

# Swallow extra words passed to `make wp ...` so make does not treat them as
# targets of their own. Must stay last.
%:
	@:
