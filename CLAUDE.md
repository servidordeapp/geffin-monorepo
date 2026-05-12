# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository State

This monorepo has a seeded directory structure with the Laravel API bootstrapped. Other services are scaffolded as placeholder directories. Implementation follows the SpecKit workflow (Specify → Plan → Tasks → Implement) per service.

## Monorepo Layout

```
apps/
  api-laravel/     ← API Core: Laravel/PHP — modular monolith, six isolated domain modules
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

## Frontend Skills (mandatory)

### Web Frontends (school-web, guardian-web)

Before any work in `frontends/school-web/` or `frontends/guardian-web/`, activate the relevant skill from `.claude/skills/` (root):

| Skill | Trigger |
|---|---|
| `next-best-practices` | Any Next.js code — file structure, RSC, routing, async params, metadata, error handling, image/font optimization |
| `next-cache-components` | Caching — `use cache`, PPR, `cacheLife`, `cacheTag`, `revalidateTag` |
| `vercel-react-best-practices` | React components, data fetching, bundle size, re-renders, waterfalls |
| `vercel-composition-patterns` | Component architecture — compound components, render props, context, React 19 APIs |
| `tailwind-design-system` | Tailwind v4, design tokens, `@gfn/design-system` components, variants, dark mode |
| `javascript-testing-patterns` | All tests — Jest/Vitest/Testing Library (TDD mandatory per constitution) |

### Mobile Frontend (guardian-mobile)

Before any work in `frontends/guardian-mobile/`, activate the relevant skill from `frontends/guardian-mobile/.claude/skills/`:

| Skill | Trigger |
|---|---|
| `vercel-react-native-skills` | React Native components, Expo, performance, native modules, platform APIs |
| `building-native-ui` | Expo Router, lists, modals, tabs, bottom sheets, animations, navigation |
| `native-data-fetching` | API calls, React Query, auth tokens (`expo-secure-store`), offline support |
| `expo-tailwind-setup` | NativeWind v5, Tailwind v4 in Expo, CSS component wrappers, platform-specific styles |

### Monorepo Build

| Skill | Trigger |
|---|---|
| `turborepo` | `turbo.json`, task pipelines, caching, `--filter`, `--affected`, remote cache, CI optimization |

## API Laravel — Skills (mandatory)

Before any work in `apps/api-laravel/`, activate the relevant skill from `apps/api-laravel/.claude/skills/`:

| Skill | Trigger |
|---|---|
| `laravel-best-practices` | Any Laravel PHP code — controllers, models, migrations, services, queries, routes, queues, events, security, testing, architecture |
| `deploying-laravel-cloud` | Deploy, environment management, provisioning, billing, or any Laravel Cloud operation |

Read the skill's rule files via a sub-agent before making changes. Do not skip this step.

## API Laravel — Module Structure

The API Core is a **modular monolith**. Each domain lives in its own module under `app/Modules/` and is treated as an isolated bounded context.

```
app/
  Modules/
    Financial/    ← wallet, payments, audit trail, ledger entries
    Billing/      ← invoices, charges, subscriptions, payment plans
    Contracts/    ← school contracts, terms, agreements
    Students/     ← enrollment, profiles, guardians
    Canteen/      ← menus, food items, canteen orders
    Commerce/     ← marketplace, products, catalog, orders
    Shared/       ← cross-module value objects, base classes, traits
```

Each module owns its own:

```
<Module>/
  Controllers/    ← HTTP handlers (thin; delegate to Services)
  Models/         ← Eloquent models scoped to this module
  Services/       ← domain logic and orchestration
  Events/         ← domain events emitted by this module
  Listeners/      ← handles events from RabbitMQ (this module only)
  Requests/       ← Form Request validation
  Resources/      ← API Resource transformers
  Policies/       ← Authorization policies
  routes.php      ← route definitions for this module
  Providers/
    <Module>ServiceProvider.php  ← registers routes, bindings, listeners
```

**Module isolation rules (non-negotiable):**

- A module MUST NOT import `Models`, `Services`, or `Controllers` from another module.
- Cross-module reads go through a dedicated Query Service interface published by the owning module.
- Cross-module writes MUST use RabbitMQ domain events, not direct method calls.
- `Modules/Shared/` is the only cross-module import allowed within the API; it MUST contain only value objects, base classes, and reusable traits — never domain logic.
- Each module MUST have its own `ServiceProvider` registered in `config/app.php`.
- Migrations that belong to a module are stored in `database/migrations/<module>/` to make ownership explicit.

## High-Level Architecture

GFN is a multi-tenant financial hub for schools. The target runtime is a **modular monolith + event-driven satellites**:

- **API Core (Laravel/PHP)** — authoritative business logic split into six isolated domain modules; emits domain events.
- **BFF Guardian / BFF School (per-client backends)** — frontends do **not** call the core API directly; they go through a BFF.
- **Workers (Go)** — consume RabbitMQ events for async/high-throughput work.
- **AI Gateway (Python, FastAPI/Flask)** — LLM orchestration. AI is an *observer*; it must not own business logic.
- **API Gateway (Python)** — external entry point: routing, rate limiting, authentication.
- **Storage** — PostgreSQL (transactional), Redis (cache/sessions), MinIO (objects).
- **Frontends** — Next.js (web, schools + guardians), React Native (mobile, guardians).

Cross-domain side effects MUST flow through RabbitMQ events, not direct service calls. Every financial transaction MUST be auditable and idempotent.

## Architectural Principles

Eight non-negotiable rules governing all services in this system:

1. **Separation of Concerns** — Each service has a single clear responsibility. Cross-cutting logic belongs in shared libraries, not spread across services.
2. **Event-Driven First** — All cross-domain actions MUST be triggered via RabbitMQ events, not synchronous service-to-service calls.
3. **Financial Consistency** — Financial data MUST be accurate and auditable. Every mutation produces an audit log entry.
4. **AI as Observer** — The AI Gateway MUST NOT own or mutate business state. It observes events and enriches data only.
5. **BFF Pattern** — Frontends MUST NOT call the API Core directly. All client traffic goes through a BFF (bff-school or bff-guardian).
6. **Stateless Services** — Services MUST NOT rely on in-memory state. All state lives in PostgreSQL, Redis, or RabbitMQ.
7. **Idempotency** — All operations exposed to retries (queue consumers, API endpoints) MUST be idempotent.
8. **Auditability** — Every financial transaction MUST be traceable end-to-end with a full audit trail.
9. **Module Isolation** — Within the API Core, each domain module (Financial, Billing, Contracts, Students, Canteen, Commerce) is a bounded context. Modules MUST NOT directly import one another's internals. Cross-module reads go through published Query Service interfaces; cross-module writes go through RabbitMQ events.

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
at `specs/001-epic-auth/plan.md`.
<!-- SPECKIT END -->
