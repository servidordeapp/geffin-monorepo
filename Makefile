COMPOSE := docker compose
API     := $(COMPOSE) exec api

.PHONY: up down build shell artisan tinker migrate fresh test logs \
        up-workers down-workers rabbitmq-ui minio-ui

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
shell:
	$(API) bash

artisan:
	$(API) php artisan $(filter-out $@,$(MAKECMDGOALS))

tinker:
	$(API) php artisan tinker

migrate:
	$(API) php artisan migrate

fresh:
	$(API) php artisan migrate:fresh --seed

test:
	$(API) php artisan test

logs:
	$(COMPOSE) logs -f api

# ─── Platform UIs ─────────────────────────────────────────────────────────────
rabbitmq-ui:
	@echo "RabbitMQ Management → http://localhost:15672  (gfn / secret)"

minio-ui:
	@echo "MinIO Console → http://localhost:9001  (gfn / secret123)"

mailpit-ui:
	@echo "Mailpit → http://localhost:8025"

%:
	@:
