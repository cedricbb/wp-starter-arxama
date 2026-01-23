up:
	@./scripts/generate-secrets.sh
	docker compose up -d --build
	@echo "🚀 Stack started"

down:
	docker compose down

logs:
	docker compose logs -f

php:
	docker compose exec php sh

db:
	docker compose exec mariadb mysql -uwordpress -pwordpress wordpress

digest-core:
	@rm -rf .aidigest/core
	@mkdir -p .aidigest/core
	@rsync -av \
		config/ \
		docs/ \
		composer.json \
		composer.lock \
		README.md \
		.aidigest/core/
	@cd .aidigest/core && npx ai-digest --output ../../codebase-wp-starter-core.md
	@echo "✅ Digest CORE généré"

digest-theme:
	@rm -rf .aidigest/theme
	@mkdir -p .aidigest/theme/theme
	@rsync -av \
		web/app/themes/arxama-child/ \
		.aidigest/theme/theme/
	@cd .aidigest/theme && npx ai-digest --output ../../codebase-wp-starter-theme.md
	@echo "✅ Digest THEME généré"

digest-clean:
	@rm -rf .aidigest
	@echo "🧹 Dossiers temporaires supprimés"
