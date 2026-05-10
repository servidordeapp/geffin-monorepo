# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository State

This monorepo has a seeded directory structure with the Laravel API bootstrapped. Other services are scaffolded as placeholder directories. Implementation follows the SpecKit workflow (Specify → Plan → Tasks → Implement) per service.

## Monorepo Layout

```
apps/
  api-laravel/     ← API Core: Laravel/PHP (financial logic, domain events)
  bff-school/      ← BFF for school web frontend (Node/Next lightweight server)
  bff-guardian/    ← BFF for guardian web + mobile
  ai-gateway/      ← Python: LLM orchestration (observer only, no business logic)
  gateway/         ← Python: external API gateway (routing, rate limiting, auth)
  workers-go/      ← Go: RabbitMQ consumers (async, high-throughput)

frontends/
  school-web/      ← Next.js (React) – school dashboard
  guardian-web/    ← Next.js (React) – guardian portal
  guardian-mobile/ ← React Native – guardian mobile app

platform/
  database/init/   ← PostgreSQL init SQL scripts
  redis/           ← redis.conf
  rabbitmq/        ← rabbitmq.conf + enabled_plugins
  minio/           ← MinIO config

shared/
  contracts/
    events/        ← Domain event schemas (PaymentCreated, etc.)
    http/          ← OpenAPI specs
  schemas/         ← JSON Schema / Zod validation
  utils/           ← Shared utility libraries
  design-system/   ← Shared React UI components

infra/
  docker/          ← docker-compose.yml (base) + nginx config
  k8s/             ← Kubernetes manifests
  terraform/       ← Cloud infra
  ci/              ← CI/CD pipelines

tools/
  scripts/         ← Automation scripts
```

**Request flow:**
```
[Mobile/Web Guardian] → bff-guardian  → api-laravel
[Web School]          → bff-school    → api-laravel

api-laravel → RabbitMQ → workers-go
api-laravel → Redis (cache/sessions)
api-laravel → MinIO (file storage)

workers-go  → ai-gateway → LLM insights
gateway     → external routing → bff-* / api-laravel
```

A sibling file, `GEMINI.md`, contains the foundational mandate for AI agents in this repo. Treat its directives as authoritative; this file complements it with Claude-Code-specific guidance.

## Authoritative Documents (read before non-trivial work)

- `.specify/memory/constitution.md` — project constitution. Five non-negotiable principles: Code Quality, Testing Standards (TDD), UX Consistency, Performance, Simplicity (YAGNI). Any plan must verify against this before execution.
- `specs/architecture/system-overview.md` — components and their responsibilities.
- `specs/architecture/tech-stack.md` — approved languages/frameworks per component.
- `specs/architecture/communication.md` — REST (sync) vs RabbitMQ (async) usage rules.
- `specs/architecture/principles.md` — eight architectural rules (event-driven first, BFF pattern, idempotency, auditability, etc.).

## High-Level Architecture

GFN is a multi-tenant financial hub for schools. The target runtime is a **modular monolith + event-driven satellites**:

- **API Core (Laravel/PHP)** — authoritative business logic and financial consistency; emits domain events.
- **BFF Guardian / BFF School (per-client backends)** — frontends do **not** call the core API directly; they go through a BFF.
- **Workers (Go)** — consume RabbitMQ events for async/high-throughput work.
- **AI Gateway (Python, FastAPI/Flask)** — LLM orchestration. AI is an *observer*; it must not own business logic.
- **API Gateway (Python)** — external entry point: routing, rate limiting, authentication.
- **Storage** — PostgreSQL (transactional), Redis (cache/sessions), MinIO (objects).
- **Frontends** — Next.js (web, schools + guardians), React Native (mobile, guardians).

Cross-domain side effects MUST flow through RabbitMQ events, not direct service calls. Every financial transaction MUST be auditable and idempotent.

## SpecKit Workflow (how features are built here)

The repo is a SpecKit project (Gemini integration). Each feature follows **Specify → Plan → Tasks → Implement**, with a feature branch and a `specs/<id>-<slug>/` directory created up front. Slash commands live in `.gemini/commands/*.toml`:

| Stage | Command | Output |
|---|---|---|
| Specify what & success criteria | `/speckit.specify` | `specs/<id>-<slug>/spec.md` |
| Clarify ambiguities | `/speckit.clarify` | updates `spec.md` |
| Plan how (stack, structure) | `/speckit.plan` | `specs/<id>-<slug>/plan.md` |
| Break into atomic tasks | `/speckit.tasks` | `specs/<id>-<slug>/tasks.md` |
| Cross-artifact consistency check | `/speckit.analyze` | analysis report |
| Implement (TDD, surgical) | `/speckit.implement` | code changes |
| Sync tasks to GitHub Issues | `/speckit.taskstoissues` | issues |
| Amend the constitution | `/speckit.constitution` | updates `.specify/memory/constitution.md` |

Implementation is **TDD-mandatory**: write the failing test first, then the smallest change to pass it. Surgical edits only — no opportunistic refactors during a feature.

## Feature Branches & Git Automation

The git extension (`.specify/extensions/git/`) wires hooks around SpecKit commands:

- `before_specify` runs `speckit.git.feature` to create a feature branch like `001-short-slug` (sequential numbering by default; configurable to timestamp `YYYYMMDD-HHMMSS-slug` in `.specify/extensions/git/git-config.yml`).
- `before_*`/`after_*` for clarify/plan/tasks/implement/etc. offer optional auto-commits.

`check_feature_branch` in `.specify/scripts/bash/common.sh` rejects branches that don't match `^[0-9]{3,}-` or `^[0-9]{8}-[0-9]{6}-` — keep the prefix when working on a feature.

The active feature directory is resolved (in priority order) from `SPECIFY_FEATURE_DIRECTORY`, then `.specify/feature.json`, then by matching the branch's numeric prefix to a directory under `specs/`.

## Common Commands

Each service brings its own toolchain (Laravel/Composer, Go modules, Python/pip, Node/pnpm). Root-level commands are via `make` and the SpecKit bash helpers.

```bash
# Docker (from repo root)
make up               # start all services
make up-workers       # start including queue worker
make down             # stop all
make down-volumes     # stop and remove volumes
make build            # rebuild images

# API Laravel (from repo root)
make shell            # bash into API container
make migrate          # run migrations
make fresh            # migrate:fresh --seed
make test             # run test suite
make artisan <cmd>    # any artisan command

# Platform UIs
make rabbitmq-ui      # print RabbitMQ management URL
make minio-ui         # print MinIO console URL

# SpecKit feature scaffolding
.specify/scripts/bash/create-new-feature.sh "Short feature description"
.specify/scripts/bash/create-new-feature.sh --short-name auth-oauth "Add OAuth2 login"
.specify/scripts/bash/create-new-feature.sh --dry-run "..."     # compute names without side effects

.specify/scripts/bash/setup-plan.sh
.specify/scripts/bash/setup-tasks.sh
.specify/scripts/bash/check-prerequisites.sh
```

To work on an existing feature without checking out the branch, set `SPECIFY_FEATURE=<branch-name>` (e.g. `001-auth-oauth`) — the scripts honour it as an override.

## Working Conventions

- **Don't bypass the workflow.** New features go through `/speckit.specify` → `/speckit.plan` → `/speckit.tasks` → `/speckit.implement`. Skipping straight to implementation breaks the audit chain the constitution requires.
- **Specs are the source of truth.** When implementation diverges from the spec, update the spec in the same PR — don't let `specs/` drift.
- **Frontends never call the API Core directly** — always through a BFF.
- **Cross-domain effects ride events**, not synchronous calls. Side-effecting handlers must be idempotent.
- **AI features observe, never mutate** — no business decisions inside the AI Gateway.
- **Templates live in `.specify/templates/`** with an override stack (`overrides/` → presets → extensions → core); prefer adding an override file over editing core templates.

<!-- SPECKIT START -->
For additional context about technologies to be used, project structure,
shell commands, and other important information, read the current plan
<!-- SPECKIT END -->
