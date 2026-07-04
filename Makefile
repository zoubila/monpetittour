DC_DEV=docker compose -f compose.dev.yaml
DC_PROD=docker compose -f compose.yaml

up:
	$(DC_DEV) up -d

down:
	$(DC_DEV) down --remove-orphans

build:
	$(DC_DEV) up -d --build

logs:
	$(DC_DEV) logs -f app

sh:
	$(DC_DEV) exec app sh

composer-install:
	$(DC_DEV) exec app composer install

console:
	$(DC_DEV) exec app php bin/console

test:
	$(DC_DEV) exec app php bin/phpunit

stan:
	$(DC_DEV) exec app vendor/bin/phpstan analyse

cs:
	$(DC_DEV) exec app vendor/bin/php-cs-fixer fix --dry-run --diff
