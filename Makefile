up:
	@./scripts/generate-secrets.sh
	docker compose up -d --build
	@echo "🚀 Stack started"

down:
	docker compose down

restart:
	docker compose down
	docker compose up -d --build

install:
	docker compose run --rm php composer install

logs:
	docker compose logs -f

php:
	docker compose exec php sh

db:
	docker compose exec mariadb mysql -uwordpress -pwordpress wordpress

