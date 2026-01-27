SHELL := /bin/sh

# Détection OS
ifeq ($(OS),Windows_NT)
	DB_SCRIPT = powershell -ExecutionPolicy Bypass -File scripts/db-create.ps1
	SECRETS_SCRIPT = powershell -ExecutionPolicy Bypass -File scripts/generate-secrets.ps1
else
	DB_SCRIPT = ./scripts/db-create.sh
	SECRETS_SCRIPT = ./scripts/generate-secrets.sh
endif

# ─────────────────────────────────────────────
# Commands
# ─────────────────────────────────────────────

up:
	@$(SECRETS_SCRIPT)
	docker compose up -d --build
	@echo "🚀 Stack started"

down:
	docker compose down

restart:
	docker compose down
	docker compose up -d --build

install:
	@echo "🔐 Génération des secrets…"
	@$(SECRETS_SCRIPT)

	@echo "🗄️  Création de la base de données…"
	@$(DB_SCRIPT)

	@echo "🐳 Lancement des containers…"
	docker compose up -d --build

	@echo "📦 Installation des dépendances Composer…"
	docker compose run --rm php composer install

	@echo "✅ Projet prêt"

logs:
	docker compose logs -f

php:
	docker compose exec php sh
