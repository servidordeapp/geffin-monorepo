COMPOSE := docker compose
API     := $(COMPOSE) exec api
ASSETS  := $(COMPOSE) run --rm assets

.PHONY: up down build shell artisan tinker migrate seed fresh test coverage logs \
        up-workers down-workers rabbitmq-ui minio-ui composer pint stan \
        assets-install assets-build

# Coverage threshold (percent). Pipeline fails when total coverage drops below.
COVERAGE_MIN ?= 90

# ─── Lifecycle ────────────────────────────────────────────────────────────────
up:
	$(COMPOSE) up -d

up-workers:
	$(COMPOSE) --profile worker up -d

down:
	$(COMPOSE) down

down-volumes:
	$(COMPOSE) down -v

build:
	$(COMPOSE) build --no-cache

# ─── API Laravel ──────────────────────────────────────────────────────────────
composer:
	$(API) composer $(filter-out $@,$(MAKECMDGOALS))

shell:
	$(API) bash

artisan:
	$(API) php artisan $(filter-out $@,$(MAKECMDGOALS))

tinker:
	$(API) php artisan tinker

migrate:
	$(API) php artisan migrate

seed:
	$(API) php artisan migrate --seed

fresh:
	$(API) php artisan migrate:fresh --seed

# The container injects runtime env vars (APP_ENV=local, DB_CONNECTION=mysql, ...)
# into $_SERVER, which Laravel's env() resolves *before* phpunit.xml's <env> values.
# So tests must receive the testing env as real process env vars. We also clear the
# entrypoint's cached config (Dockerfile runs config:cache on start) so it takes effect.
TEST_ENV := -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: \
            -e CACHE_STORE=array -e SESSION_DRIVER=array -e QUEUE_CONNECTION=sync

test:
	$(COMPOSE) exec $(TEST_ENV) api sh -c "php artisan optimize:clear >/dev/null 2>&1 && php artisan test $(filter-out $@,$(MAKECMDGOALS))"

# Coverage: enables pcov on-demand (extension shipped but not auto-loaded).
# Fails when total line coverage is below $(COVERAGE_MIN).
coverage:
	$(COMPOSE) exec $(TEST_ENV) -e XDEBUG_MODE=off api sh -c "\
	    php artisan optimize:clear >/dev/null 2>&1 && \
	    php -d pcov.enabled=1 -d memory_limit=512M \
	         $(COVERAGE_MIN) $(filter-out $@,$(MAKECMDGOALS))"

pint:
	$(API) vendor/bin/pint

stan:
	$(API) vendor/bin/phpstan analyse --memory-limit=2G

logs:
	$(COMPOSE) logs -f api

# ─── Frontend assets (node:24-alpine via the "assets" service) ─────────────────
assets-install:
	$(ASSETS) npm install

assets-build:
	$(ASSETS) npm run build

# ─── Platform UIs ─────────────────────────────────────────────────────────────
rabbitmq-ui:
	@echo "RabbitMQ Management → http://localhost:15672  (gfn / secret)"

minio-ui:
	@echo "MinIO Console → http://localhost:9001  (gfn / secret123)"

mailpit-ui:
	@echo "Mailpit → http://localhost:8025"

%:
	@:
