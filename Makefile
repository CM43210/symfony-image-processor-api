DC ?= docker compose
PHPSVC ?= php

.PHONY: tests

composer-install: 
	$(DC) up -d --build $(PHPSVC)
	$(DC) exec -e COMPOSER_ALLOW_SUPERUSER=1 -e COMPOSER_MEMORY_LIMIT=-1 $(PHPSVC) composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
	$(DC) exec -e COMPOSER_ALLOW_SUPERUSER=1 -e COMPOSER_MEMORY_LIMIT=-1 $(PHPSVC) composer install --no-interaction --prefer-dist --optimize-autoloader

composer:
	$(DC) up -d --build $(PHPSVC)
	$(DC) exec -e COMPOSER_ALLOW_SUPERUSER=1 $(PHPSVC) composer $(CMD)

console:
	$(DC) exec $(PHPSVC) php bin/console $(CMD)

migrations-diff:
	$(DC) exec $(PHPSVC) php bin/console doctrine:migrations:diff

migrations-migrate:
	$(DC) exec $(PHPSVC) php bin/console doctrine:migrations:migrate

ecs-fix:
	$(DC) exec $(PHPSVC) ./vendor/bin/ecs check --fix

tests:
	$(DC) exec $(PHPSVC) php bin/phpunit

up:       
	$(DC) up -d --build

down:   
	$(DC) down

logs:
	$(DC) logs -f

bash:
	$(DC) run --rm php bash

messenger:
	$(DC) run --rm worker
