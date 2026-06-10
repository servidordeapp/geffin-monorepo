# GFN Constitution

## Core Principles

### I. Code Quality
Code must be readable, typed, and consistent with the conventions of each service's
ecosystem (PSR/Pint for PHP, gofmt for Go, Black/Ruff for Python, ESLint/Prettier for
TypeScript). Thin controllers, domain logic in services. No dead code, no opportunistic
refactors inside feature work.

### II. Testing Standards (TDD, NON-NEGOTIABLE)
TDD mandatory: write the failing test first, then the smallest change to make it pass.
Every behavior change ships with a test. Feature tests are preferred over unit tests for
HTTP/Livewire behavior. Tests must not depend on execution order.

### III. UX Consistency
All user-facing copy is Brazilian Portuguese (pt-BR). Screens are composed from the shared
UI components and design tokens — no per-page bespoke CSS. Equivalent flows (forms,
validation feedback, loading states, success/error alerts) behave identically across screens.

### IV. Performance
No N+1 queries; eager-load what views need. Cache reads that are expensive and frequent
(Redis). Queue work that doesn't need to block the request (RabbitMQ). Pagination by
default on list endpoints.

### V. Simplicity (YAGNI)
Build only what the current spec requires. No speculative abstractions, configuration
flags, or generalizations for imagined future needs. Prefer the boring, framework-native
solution.

## Naming & Language Conventions

- **Route URIs MUST be written in Brazilian Portuguese.** URL paths are user-facing
  surface. Examples: `/entrar`, `/esqueci-senha`, `/redefinir-senha/{token}`, `/painel`.
- **Route *names* keep Laravel's framework conventions in English** (`login`,
  `password.request`, `password.reset`, `dashboard`), because the framework and its
  packages resolve them internally (auth middleware redirects, `ResetPassword`
  notification, etc.). Only the URI is translated.
- Database identifiers, code identifiers (classes, methods, variables), events, and queue
  names remain in English. User-facing strings (views, validation messages, mail) are pt-BR.

## Governance

This constitution supersedes ad-hoc practices. Amendments go through
`/speckit.constitution` and must be committed with a version bump below. Every plan
produced by `/speckit.plan` must be checked against these principles before
implementation starts.

**Version**: 1.1.0 | **Ratified**: 2026-06-10 | **Last Amended**: 2026-06-10
