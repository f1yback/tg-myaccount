.PHONY: help up down build rebuild shell test lint migrate fixtures clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

up: ## Start all services
	docker-compose up -d

stop: ## Start all services
	docker-compose stop

down: ## Down all services
	docker-compose down

build: ## Build all services
	docker-compose build

rebuild: ## Rebuild all services
	docker-compose down
	docker-compose build --no-cache
	docker-compose up -d

shell: ## Access PHP container shell
	docker-compose exec php sh

test: ## Run tests
	docker-compose exec php bin/console --env=test doctrine:database:drop --force --if-exists
	docker-compose exec php bin/console --env=test doctrine:database:create --if-not-exists
	docker-compose exec php bin/console --env=test doctrine:migrations:migrate --no-interaction
	docker-compose exec php bin/console --env=test doctrine:fixtures:load --no-interaction
	docker-compose exec php vendor/bin/phpunit

lint: ## Run linters
	docker-compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff

phpstan: ## Run linters
	docker-compose exec php vendor/bin/phpstan --memory-limit=2G

lint-fix: ## Fix code style
	docker-compose exec php vendor/bin/php-cs-fixer fix

migrate: ## Run database migrations
	docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction

fixtures: ## Load fixtures
	docker-compose exec php bin/console doctrine:fixtures:load --no-interaction

cache-clear: ## Clear Symfony cache
	docker-compose exec php bin/console cache:clear

clean: ## Clean up containers and volumes
	docker-compose down -v
	docker system prune -f
