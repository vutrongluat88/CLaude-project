SHELL := /bin/bash
DC := docker compose

.PHONY: help init up down restart build logs ps sh-backend sh-frontend artisan composer npm migrate fresh test api-install

help: ## List targets
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "  %-14s %s\n", $$1, $$2}'

init: ## First-time setup: env files, build, install deps, migrate
	@test -f .env || (sed -e "s/^UID=.*/UID=$$(id -u)/" -e "s/^GID=.*/GID=$$(id -g)/" .env.example > .env && echo "created .env")
	@test -f backend/.env || (cp backend/.env.example backend/.env && echo "created backend/.env")
	$(DC) build
	$(DC) run --rm backend composer install
	$(DC) run --rm backend php artisan key:generate
	$(DC) run --rm --entrypoint npm frontend install
	$(DC) up -d
	$(DC) exec -T backend php artisan migrate

api-install: ## Add routes/api.php + Sanctum (run once, after init)
	$(DC) exec backend php artisan install:api

up: ## Start the stack
	$(DC) up -d

down: ## Stop the stack
	$(DC) down

restart: down up ## Restart the stack

build: ## Rebuild images
	$(DC) build --no-cache

logs: ## Tail logs (make logs s=backend)
	$(DC) logs -f $(s)

ps: ## Show service status
	$(DC) ps

sh-backend: ## Shell into the backend container
	$(DC) exec backend sh

sh-frontend: ## Shell into the frontend container
	$(DC) exec frontend sh

artisan: ## Run artisan (make artisan c="make:model User")
	$(DC) exec backend php artisan $(c)

composer: ## Run composer (make composer c="require laravel/sanctum")
	$(DC) exec backend composer $(c)

npm: ## Run npm in frontend (make npm c="install axios")
	$(DC) exec frontend npm $(c)

migrate: ## Run migrations
	$(DC) exec backend php artisan migrate

fresh: ## Drop, re-migrate and seed
	$(DC) exec backend php artisan migrate:fresh --seed

test: ## Backend test suite
	$(DC) exec backend php artisan test
