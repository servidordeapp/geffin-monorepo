<!-- SYNC IMPACT REPORT
Version change: [TEMPLATE] → 1.0.0
Added principles:
  - I. Code Quality (new)
  - II. Testing Standards — TDD (new, NON-NEGOTIABLE)
  - III. UX Consistency (new)
  - IV. Performance Requirements (new)
  - V. Simplicity — YAGNI (new)
Added sections:
  - Quality Gates (new)
  - Development Workflow (new)
Removed sections: none
Templates updated:
  - .specify/templates/plan-template.md ✅ (Constitution Check gates filled)
  - .specify/templates/spec-template.md ✅ (no changes required)
  - .specify/templates/tasks-template.md ✅ (no changes required)
Deferred TODOs: none
-->

# GFN (Geffin) Constitution

## Core Principles

### I. Code Quality

Every line of code introduced to this system MUST meet the following standards:

- Code MUST have a single, clear responsibility. Functions and modules with multiple unrelated
  concerns are prohibited.
- Naming MUST be explicit and self-documenting. Abbreviations and single-letter variables
  (outside tight loops) are disallowed.
- Dead code MUST NOT be committed. Commented-out blocks and unreachable branches must be
  removed before merge.
- Each service MUST enforce formatting and linting via automated tooling (PHP-CS-Fixer for
  Laravel, ESLint/Prettier for JS/TS, gofmt for Go, Black/Ruff for Python). CI gates on
  these checks.
- Opportunistic refactors during feature work are prohibited. Refactors require a dedicated
  task and their own PR.
- Complexity MUST be justified in writing. Any abstraction or indirection beyond the simplest
  working solution requires a documented rationale.

### II. Testing Standards — TDD (NON-NEGOTIABLE)

Test-driven development is mandatory for all feature work in this project. No exceptions.

- Tests MUST be written before implementation. The Red-Green-Refactor cycle is strictly
  enforced: (1) write a failing test, (2) confirm it fails for the right reason, (3) write
  minimal code to pass, (4) refactor under green.
- Financial operations (payments, wallet mutations, billing) MUST have integration tests
  hitting a real database. Mocks are prohibited for these code paths.
- Contract tests MUST cover all inter-service HTTP APIs and domain event schemas.
- Minimum test pyramid per component:
  - **API Core (Laravel)**: unit + feature + integration; Pest test suite.
  - **Workers (Go)**: unit + integration; standard `testing` package.
  - **BFFs (Node)**: unit + integration; Jest or Vitest.
  - **AI Gateway / API Gateway (Python)**: unit + integration; pytest.
  - **Frontends (Next.js / React Native)**: component tests + E2E critical paths;
    Testing Library + Playwright/Detox.
- All tests MUST be deterministic. Flaky tests MUST be fixed or removed — skipping is not
  an option.

### III. UX Consistency

User experience across all client surfaces MUST be coherent and predictable.

- All frontends MUST use components from `shared/design-system/`. Custom one-off UI
  components duplicating design-system patterns are prohibited.
- API responses MUST follow a consistent envelope format: `{ data, meta, errors }`. BFFs
  MUST NOT expose raw core-API response shapes directly to clients.
- Error states MUST be surfaced to users with actionable messages. Generic "something went
  wrong" messages without context are prohibited.
- Accessibility (WCAG 2.1 AA) is non-negotiable for all web frontends. A11y regressions
  block merge.
- Guardian-facing flows MUST support offline data entry (React Native); data syncs on
  reconnect.
- UI responsiveness: interactive elements MUST respond within 100ms; data-fetch views
  MUST show a loading state within 200ms.

### IV. Performance Requirements

System performance is a correctness concern, not a polish concern.

- **API Core synchronous endpoints**: p95 latency MUST be < 200ms under expected load.
  Endpoints exceeding this MUST be optimized or moved to async before merge.
- **Async operations**: all cross-domain side-effects MUST use RabbitMQ events. Synchronous
  chains crossing service boundaries are prohibited.
- **Caching**: read-heavy data (school configs, product catalogs, session data) MUST be
  cached in Redis. Cache invalidation strategy MUST be documented for each cached entity.
- **Bulk operations**: batch processing MUST use background workers (Go consumers). Bulk
  HTTP endpoints blocking for more than 500ms are prohibited.
- **Frontend performance budgets**:
  - LCP (Largest Contentful Paint) < 2.5s on a 4G connection.
  - TTI (Time to Interactive) < 3.5s.
  - Bundle size regressions > 10% MUST be justified before merge.
- Performance benchmarks MUST be reported in the PR for any change to a hot path (payment
  processing, event emission, queue consumption).

### V. Simplicity — YAGNI

Build what is needed now, not what might be needed later.

- Every abstraction MUST solve a current, concrete problem. Abstractions introduced "for
  future flexibility" are prohibited.
- Three or fewer similar cases do not justify a shared abstraction — duplication is preferred
  to premature generalization.
- Feature flags, backwards-compatibility shims, and conditional logic for hypothetical callers
  MUST NOT be introduced unless a concrete consumer currently exists.
- Architectural patterns (Repository, Saga, CQRS, etc.) MUST be adopted only when the simpler
  alternative has been explicitly rejected with documented justification.
- When in doubt, choose the solution with fewer moving parts.

## Quality Gates

All pull requests MUST pass the following gates before merge:

- **CI Green**: linting, formatting, type-checking, and full test suite pass without
  suppression.
- **Constitution Check**: the plan and implementation have been verified against all five Core
  Principles. Performed at plan creation (before Phase 0 research) and re-validated after
  design (after Phase 1).
- **Financial Review**: any change touching payment, wallet, billing, or audit logic requires
  peer review from a second engineer.
- **Performance Gate**: changes to synchronous API endpoints or hot-path consumers must
  include a p95 benchmark result confirming compliance with Principle IV.
- **A11y Gate**: frontend PRs must pass automated accessibility checks (axe-core or
  equivalent) with zero new violations.

## Development Workflow

Feature development follows the SpecKit workflow. Deviations require explicit justification.

- **Order**: Specify → Plan → Tasks → Implement. Jumping directly to implementation is
  prohibited.
- **Specs are the source of truth**: when implementation diverges from `spec.md` or
  `plan.md`, the spec MUST be updated in the same PR. Drifted specs are a quality defect.
- **Surgical edits**: implementation tasks MUST change only what the task requires.
  Opportunistic refactors and "while I'm here" changes are prohibited.
- **Feature branches**: all feature work MUST be on a branch matching `^[0-9]{3,}-` or
  `^[0-9]{8}-[0-9]{6}-`. Direct commits to `main` are prohibited.
- **Auditability**: every financial mutation MUST produce an audit log entry. Domain events
  MUST be emitted for all cross-domain state changes.
- **Idempotency**: all operations exposed to retry scenarios (queue consumers, API retries)
  MUST be idempotent.

## Governance

This constitution supersedes all other practices, guidelines, and conventions in this
repository. Conflicts are resolved in favor of the constitution.

**Amendment procedure**:
1. Propose the amendment with rationale in a PR that updates this file.
2. The PR description MUST include: the principle or section being changed, the motivation,
   and a migration plan if existing code is affected.
3. Version MUST be bumped according to the semantic rules below.
4. All dependent templates (`.specify/templates/`) MUST be reviewed and updated in the same
   PR.

**Versioning policy**:
- MAJOR: backward-incompatible governance/principle removals or redefinitions.
- MINOR: new principle/section added or materially expanded guidance.
- PATCH: clarifications, wording, typo fixes, non-semantic refinements.

**Compliance review**: the Constitution Check gate in every plan enforces ongoing compliance.
Deviations found during implementation MUST be resolved before merge — not suppressed, not
deferred.

For runtime development guidance and toolchain commands, refer to `CLAUDE.md`.

**Version**: 1.0.0 | **Ratified**: 2026-05-10 | **Last Amended**: 2026-05-10
