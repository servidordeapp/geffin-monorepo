# Quickstart — Initial Screens (002)

## Prerequisites

- Docker running (`make up`)
- Node 20 + pnpm 9 installed
- Xcode or Android Studio for mobile (optional — Expo Go works for most dev)

## Running the apps

```bash
# guardian-web (port 3000)
cd frontends/guardian-web && pnpm dev

# school-web (port 3003)
cd frontends/school-web && pnpm dev

# guardian-mobile
cd frontends/guardian-mobile && npx expo start
```

## Running tests

```bash
# Design system unit tests
cd shared/design-system && pnpm test

# guardian-web tests
cd frontends/guardian-web && pnpm test

# school-web tests
cd frontends/school-web && pnpm test

# guardian-mobile tests
cd frontends/guardian-mobile && pnpm test

# E2E (web) — requires both apps running
cd frontends/guardian-web && pnpm test:e2e
cd frontends/school-web && pnpm test:e2e
```

## Mock credentials (dev only)

All BFF calls use mock fixtures in `specs/002-initial-screens/fixtures/` during development.

| App | Credential |
|---|---|
| guardian-web | email: `guardian@mock.local` · password: any ≥6 chars |
| school-web | code: `ESCOLA01` · email: `admin@mock.local` · password: any ≥6 chars |
| guardian-mobile | same as guardian-web |

## Key environment variables

```bash
# guardian-mobile
EXPO_PUBLIC_BFF_URL=http://localhost:3001

# guardian-web
BFF_GUARDIAN_URL=http://localhost:3001

# school-web
BFF_SCHOOL_URL=http://localhost:3002
```

## Design system development

```bash
cd shared/design-system

# Watch mode for component development
pnpm dev

# Storybook (add after Phase A)
pnpm storybook
```

## Implementing a new screen (TDD flow)

1. Write a failing test in `__tests__/` using Testing Library.
2. Run `pnpm test --watch` to confirm the test fails for the right reason.
3. Implement the minimum code to pass.
4. Refactor under green (no new test needed for mechanical cleanup).
5. Run `pnpm test:a11y` to confirm zero axe violations.

## Feature flags

None. This feature uses mock data only — no feature flags, no environment gating.
