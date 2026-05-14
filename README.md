<div align="center">

```text
╔═══════════════════════════════════════════════════╗
║                                                   ║
║   ██████╗ ███████╗███████╗███████╗██╗███╗   ██╗   ║
║  ██╔════╝ ██╔════╝██╔════╝██╔════╝██║████╗  ██║   ║
║  ██║  ███╗█████╗  █████╗  █████╗  ██║██╔██╗ ██║   ║
║  ██║   ██║██╔══╝  ██╔══╝  ██╔══╝  ██║██║╚██╗██║   ║
║  ╚██████╔╝███████╗██║     ██║     ██║██║ ╚████║   ║
║   ╚═════╝ ╚══════╝╚═╝     ╚═╝     ╚═╝╚═╝  ╚═══╝   ║
║                                                   ║
║  Financial Infrastructure for Education Payments  ║
║                                                   ║
╚═══════════════════════════════════════════════════╝
```
</div>

# Geffin Monorepo

Multi-tenant financial hub for schools. Handles payments, billing, and financial operations for educational institutions — connecting schools and guardians through dedicated frontends backed by a Laravel API core.

## Architecture Overview

```
[Guardian Mobile / Guardian Web] → bff-guardian → api-laravel
[School Web]                     → bff-school   → api-laravel

api-laravel → RabbitMQ → workers-go → ai-gateway (LLM insights)
api-laravel → Redis     (cache / sessions / queues)
api-laravel → MinIO     (file storage)
gateway     → external routing → bff-* / api-laravel
```

Cross-domain side effects flow through RabbitMQ events, never direct service calls. Every financial transaction is auditable and idempotent.

## Repository Structure

```
apps/
  api-laravel/     Laravel 13 / PHP 8.3 — API core, financial logic, domain events
  bff-school/      BFF for school web frontend
  bff-guardian/    BFF for guardian web + mobile
  ai-gateway/      Python — LLM orchestration (observer only, no business logic)
  gateway/         Python — external API gateway (routing, rate limiting, auth)
  workers-go/      Go — RabbitMQ consumers (async, high-throughput)

frontends/
  school-web/      Next.js — school dashboard
  guardian-web/    Next.js — guardian portal
  guardian-mobile/ React Native — guardian mobile app

platform/
  database/init/   PostgreSQL init SQL scripts
  redis/           redis.conf
  rabbitmq/        rabbitmq.conf + enabled_plugins
  minio/           MinIO config

shared/
  contracts/
    events/        Domain event schemas (PaymentCreated, etc.)
    http/          OpenAPI specs
  schemas/         JSON Schema / Zod validation
  utils/           Shared utility libraries
  design-system/   Shared React UI components

infra/
  docker/          docker-compose.yml (base) + nginx config
  k8s/             Kubernetes manifests
  terraform/       Cloud infra
  ci/              CI/CD pipelines

tools/
  scripts/         Automation scripts
```

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) and Docker Compose v2
- [Make](https://www.gnu.org/software/make/)

## Local Environment Setup

### 1. Start the platform

```bash
make up
```

This starts:
- **API** (Laravel via nginx) — `http://localhost:8000`
- **PostgreSQL 16** — `localhost:5432`
- **Redis 7** — `localhost:6379`
- **RabbitMQ 3.13** — `localhost:5672` (AMQP) / `localhost:15672` (management UI)
- **MinIO** — `localhost:9000` (S3 API) / `localhost:9001` (console)
- **Mailpit** (dev mail catcher) — `localhost:8025`

To also start the Laravel queue worker:

```bash
make up-workers
```

### 2. Run database migrations

```bash
make migrate
```

To reset and re-seed from scratch:

```bash
make fresh
```

### 3. Verify the API is running

```bash
curl http://localhost:8000/up
```

## Common Commands

### Lifecycle

| Command | Description |
|---|---|
| `make up` | Start all core services |
| `make up-workers` | Start all services including the queue worker |
| `make down` | Stop all services |
| `make down-volumes` | Stop all services and remove volumes |
| `make build` | Rebuild all Docker images (no cache) |

### API (Laravel)

| Command | Description |
|---|---|
| `make shell` | Open a bash shell inside the API container |
| `make migrate` | Run pending migrations |
| `make fresh` | Drop all tables and re-run migrations with seeders |
| `make test` | Run the full test suite |
| `make tinker` | Open Laravel Tinker REPL |
| `make artisan <cmd>` | Run any artisan command, e.g. `make artisan route:list` |
| `make logs` | Tail API container logs |

### Platform UIs

| Command | Output |
|---|---|
| `make rabbitmq-ui` | Print RabbitMQ management URL and credentials |
| `make minio-ui` | Print MinIO console URL and credentials |
| `make mailpit-ui` | Print Mailpit URL |

## Service Credentials (local only)

| Service | Host | Credentials |
|---|---|---|
| PostgreSQL | `localhost:5432` | `gfn` / `secret` — DB: `gfn` |
| Redis | `localhost:6379` | no auth |
| RabbitMQ | `localhost:15672` | `gfn` / `secret` |
| MinIO | `localhost:9001` | `gfn` / `secret123` |
| Mailpit | `localhost:8025` | no auth |

## Documentation

- [Handoff de desenvolvimento — Telas Iniciais](./docs/design/01-handoff-dev.md) — tokens, contratos de componente e specs de tela (Login + Dashboard × 3 apps)

## Development Workflow

Features follow the **SpecKit** workflow: `Specify → Plan → Tasks → Implement`. Each feature lives in a `specs/<id>-<slug>/` directory on a dedicated branch. Implementation is TDD-mandatory — write the failing test first, then the smallest change to make it pass.

See `CLAUDE.md` for the full workflow reference and architectural principles.
