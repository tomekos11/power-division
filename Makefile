.PHONY: up down build restart logs shell composer artisan test migrate fresh analyse format health

up:
	chmod 600 docker/pgadmin/pgpass 2>/dev/null || true
	docker compose up --build -d

down:
	docker compose down

build:
	docker compose build

restart:
	docker compose restart

logs:
	docker compose logs -f app web

logs-all:
	docker compose logs -f

shell:
	docker compose exec app bash

composer:
	docker compose exec app composer $(filter-out composer,$(MAKECMDGOALS))

artisan:
	docker compose exec app php artisan $(filter-out artisan,$(MAKECMDGOALS))

test:
	docker compose exec app php artisan test

migrate:
	docker compose exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh --seed

analyse:
	docker compose exec app composer analyse

format:
	docker compose exec app composer format

health:
	curl -s http://localhost:$${APP_PORT:-8080}/api/health | python3 -m json.tool 2>/dev/null || curl -s http://localhost:$${APP_PORT:-8080}/api/health

setup:
	cp -n .env.example .env 2>/dev/null || true
	$(MAKE) up
	@echo ""
	@echo "API:           http://localhost:$${APP_PORT:-8080}"
	@echo "Health:        http://localhost:$${APP_PORT:-8080}/api/health"
	@echo "pgAdmin:       http://localhost:$${PGADMIN_PORT:-5050}  (admin@local.dev / admin)"
	@echo "RedisInsight:  http://localhost:$${REDISINSIGHT_PORT:-5540}  (add host: redis:6379)"

%:
	@:
