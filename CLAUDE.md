# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository State

This monorepo is **specification-only** at this stage — there is no `src/`, `tests/`, package manifest, or build tooling yet. All current artifacts are Markdown specs and SpecKit scaffolding. Implementation will land under `src/` and `tests/` mirroring the `specs/services/` layout once features move past planning.

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

There is no language toolchain yet, so build/lint/test commands will be added per service when implementation begins (Laravel, Go, Python, Next.js, React Native each bring their own). Until then, the only repo-level commands are SpecKit slash commands (above) and the bash helpers below.

```bash
# Create a feature branch + spec directory + populated spec.md
.specify/scripts/bash/create-new-feature.sh "Short feature description"
.specify/scripts/bash/create-new-feature.sh --short-name auth-oauth "Add OAuth2 login"
.specify/scripts/bash/create-new-feature.sh --dry-run "..."     # compute names without side effects

# Set up plan.md / tasks.md scaffolding for the current feature
.specify/scripts/bash/setup-plan.sh
.specify/scripts/bash/setup-tasks.sh

# Verify environment is ready for the next stage
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
