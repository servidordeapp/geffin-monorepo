# Implementation Plan: Initial Screens — Login & Dashboard (3 Apps)

**Branch**: `002-initial-screens` | **Date**: 2026-05-12 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/002-initial-screens/spec.md`

## Summary

Implement the 6 initial screens (Login + Dashboard × 3 apps) and the supporting shared design system infrastructure. The three apps are `guardian-mobile` (React Native/Expo), `guardian-web` (Next.js 15), and `school-web` (Next.js 15). All UI components derive from `@gfn/design-system`, which needs TypeScript token constants, a native entrypoint, an AppearanceProvider, and missing components (Checkbox, Skeleton, EmptyState, ErrorState). Both web login pages are rewritten from a bare-bones `localStorage`-based stub to a styled, accessible, server-action-based form. Both web dashboards and the mobile dashboard are created from scratch. The guardian-mobile app migrates from `@react-navigation/native` to expo-router v3.5 to support the tab-bar dashboard layout.

---

## Technical Context

**Language/Version**: TypeScript 5.7 (design-system, web apps) · TypeScript 5.1 (guardian-mobile)
**Primary Dependencies**:
- `@gfn/design-system` (shared, internal) — Button, Card, Input, Avatar, Badge, Toast + to-add: Checkbox, Skeleton, EmptyState, ErrorState, AppearanceProvider, native entrypoint
- `next@^15.0.0`, `react@^19.0.0` (guardian-web + school-web)
- `expo@~51`, `react-native@0.74`, `expo-router@~3.5` (guardian-mobile)
- `tailwindcss@^4.0.0` + `@tailwindcss/postcss` (web apps via design-system)
- `class-variance-authority`, `clsx`, `tailwind-merge`, `lucide-react` (design-system, already installed)
- `zod` — client-side form validation (web apps, to add)
- `sonner` — toast notifications (web apps, to add)
- `react-native-toast-message` — mobile toasts (to add)
- `react-native-reanimated` — shake animation on login error (to add)
- `@tanstack/react-query` — mobile data fetching (to add)
- `swr` — school-web SystemActivity 30s polling (to add)
- `recharts` — web charts (to add)
- `next-intl` — web i18n (to add, static pt-BR locale)
- `i18n-js` — mobile i18n (to add, static pt-BR)
- `expo-image` — optimized images in mobile (to add)

**Storage**: Session tokens stored in HttpOnly cookies (web, set via server actions). Mobile sessions via `expo-secure-store` (already installed).
**Testing**: Jest + Testing Library (web + mobile). Playwright (E2E web). Detox (E2E mobile — future). Visual regression with Chromatic.
**Target Platform**: iOS 15+ / Android 10+ (API 29+) for mobile · browsers (latest Chrome, Safari, Firefox) for web.
**Project Type**: Multi-surface frontend (mobile app + 2 web apps) + shared component library.
**Performance Goals**: Mobile TTI < 2s on 4G · Web LCP < 1.5s p75 · Web CLS < 0.1 · Login bundle < 120 KB gzip · Dashboard bundle < 200 KB gzip.
**Constraints**: WCAG 2.1 AA · `prefers-reduced-motion` respected · tap targets ≥ 44×44 pt · no hardcoded strings (i18n keys) · no hardcoded color/spacing values (tokens only).
**Scale/Scope**: 6 screens × 3 apps + shared design system. 38 functional requirements. Mock data only; real API integration is a subsequent feature.

---

## Constitution Check

| # | Principle | Check | Notes |
|---|-----------|-------|-------|
| I | **Code Quality** — single responsibility, explicit naming, no dead code, linting enforced, no opportunistic refactors. | ✅ | Each component has one concern. Existing bare stubs (login pages) rewritten, not patched. Opportunistic refactor of auth flow (localStorage → HttpOnly cookies) is in scope per spec FR-001 and handoff doc — this is not incidental. |
| II | **Testing Standards (TDD)** — failing test first; no financial paths here; contract tests for BFF interfaces. | ✅ | No financial mutations in this feature. Contract tests cover BFF guardian + BFF school endpoints. Component tests follow red-green-refactor. E2E covers login→dashboard flow. |
| III | **UX Consistency** — design-system components used; `{ data, meta, errors }` envelope; actionable errors; WCAG 2.1 AA; offline guardian mobile. | ✅ | All UI built from `@gfn/design-system`. BFF envelope `{ data, meta, errors }` documented in contracts. Error states have retry actions. WCAG 2.1 AA enforced via axe-core in tests. Mobile offline: dashboard shows cached data with an offline banner. |
| IV | **Performance** — frontend budgets met; no blocking sync chains across services. | ✅ | Perf budgets in Technical Context. Parallel Suspense boundaries for dashboard sections. `next/image` + `expo-image`. Code split per route (Next.js 15 native). No blocking cross-service calls — data flows through BFF as specified. |
| V | **Simplicity (YAGNI)** — no speculative abstractions; patterns justified. | ✅ | AppearanceProvider justified: 3 apps share components, threading `context` prop everywhere is worse. expo-router migration justified: it's the declared `"main"` entry; `App.tsx` is a stub. recharts and SWR are minimal additions for their specific use cases. |
| VI | **Module Isolation** | N/A | This feature does not touch `apps/api-laravel/`. No module boundary concern. |

---

## Project Structure

### Documentation (this feature)

```text
specs/002-initial-screens/
├── plan.md           ← this file
├── research.md       ← 10 architectural decisions
├── data-model.md     ← TypeScript types for all data shapes
├── contracts/
│   ├── bff-guardian.md   ← guardian BFF HTTP contracts + mock response shapes
│   └── bff-school.md     ← school BFF HTTP contracts + mock response shapes
├── fixtures/
│   ├── guardian-dashboard.json
│   ├── school-dashboard.json
│   └── auth-responses.json
├── checklists/
│   └── requirements.md
└── tasks.md          ← generated by /speckit-tasks (NOT this command)
```

### Source Code (repository root)

```text
shared/design-system/
├── src/
│   ├── tokens/                    ← NEW
│   │   ├── colors.ts
│   │   ├── typography.ts
│   │   ├── spacing.ts
│   │   ├── radii.ts
│   │   └── shadows.ts
│   ├── context/                   ← NEW
│   │   └── AppearanceContext.tsx
│   ├── native/                    ← NEW
│   │   ├── Button.native.tsx
│   │   ├── Card.native.tsx
│   │   ├── Avatar.native.tsx
│   │   ├── Input.native.tsx
│   │   ├── Badge.native.tsx
│   │   └── index.ts
│   ├── components/
│   │   ├── Button/      ← complete variants + AppearanceContext integration
│   │   ├── Input/       ← floating label, error state, password toggle
│   │   ├── Card/        ← kpi variant, AppearanceContext integration
│   │   ├── Avatar/      ← already complete
│   │   ├── Badge/       ← already complete
│   │   ├── Toast/       ← sonner wrapper
│   │   ├── Checkbox/    ← NEW
│   │   ├── Skeleton/    ← NEW (shimmer)
│   │   ├── EmptyState/  ← NEW
│   │   └── ErrorState/  ← NEW
│   ├── styles/
│   │   ├── tokens.css   ← no change
│   │   └── index.css    ← no change
│   └── index.ts         ← updated exports
├── package.json         ← add "./native" entrypoint

frontends/guardian-mobile/
├── app.json             ← add expo.router.root = "src/app"
├── src/
│   ├── app/
│   │   ├── _layout.tsx                ← NEW: expo-router root layout
│   │   ├── (auth)/
│   │   │   ├── _layout.tsx            ← NEW: unauthenticated stack layout
│   │   │   ├── login.tsx              ← REWRITE: styled LoginScreen
│   │   │   └── forgot-password.tsx    ← MOVE: ForgotPasswordScreen
│   │   └── (app)/
│   │       ├── _layout.tsx            ← NEW: tab bar layout
│   │       └── index.tsx              ← NEW: DashboardScreen
│   ├── components/
│   │   ├── ChildCarousel.tsx          ← NEW
│   │   ├── QuickAccessGrid.tsx        ← NEW
│   │   ├── DueItemCard.tsx            ← NEW
│   │   ├── ActivityRow.tsx            ← NEW
│   │   └── OfflineBanner.tsx          ← NEW
│   ├── hooks/
│   │   ├── useGuardianDashboard.ts    ← NEW (TanStack Query)
│   │   └── useAuth.ts                 ← NEW
│   ├── lib/
│   │   ├── i18n.ts                    ← NEW
│   │   ├── format.ts                  ← NEW (BRL, dates)
│   │   └── api.ts                     ← NEW (BFF client)
│   ├── locales/
│   │   └── pt-BR.ts                   ← NEW
│   └── screens/                       ← kept for backward compat during migration
│       ├── LoginScreen.tsx            ← existing (referenced by new route)
│       └── ForgotPasswordScreen.tsx   ← existing

frontends/guardian-web/
├── src/
│   ├── app/
│   │   ├── globals.css              ← no change
│   │   ├── layout.tsx               ← add AppearanceProvider + next-intl
│   │   ├── login/
│   │   │   └── page.tsx             ← REWRITE: split panel, server action, Zod
│   │   └── dashboard/
│   │       └── page.tsx             ← NEW: RSC dashboard
│   ├── components/
│   │   ├── TopNav.tsx               ← NEW
│   │   ├── ChildCard.tsx            ← NEW
│   │   ├── MonthSummary.tsx         ← NEW (recharts bar)
│   │   ├── DueTable.tsx             ← NEW
│   │   ├── RecentActivity.tsx       ← NEW
│   │   └── UpcomingEvents.tsx       ← NEW
│   ├── actions/
│   │   ├── loginAction.ts           ← NEW (server action, HttpOnly cookie)
│   │   └── logoutAction.ts          ← NEW
│   ├── lib/
│   │   ├── api.ts                   ← NEW (BFF client, server-side)
│   │   └── format.ts                ← NEW (BRL, dates)
│   └── messages/
│       └── pt-BR.json               ← NEW

frontends/school-web/
├── src/
│   ├── app/
│   │   ├── globals.css              ← no change
│   │   ├── layout.tsx               ← add AppearanceProvider + next-intl
│   │   ├── login/
│   │   │   └── page.tsx             ← REWRITE: institution code, pro styling, server action
│   │   └── dashboard/
│   │       └── page.tsx             ← NEW: RSC dashboard
│   ├── components/
│   │   ├── Sidebar.tsx              ← NEW (collapsible, localStorage persistence)
│   │   ├── TopBar.tsx               ← NEW
│   │   ├── KpiCard.tsx              ← NEW
│   │   ├── RevenueLineChart.tsx     ← NEW (recharts)
│   │   ├── ChargesDonut.tsx         ← NEW (recharts)
│   │   ├── ChargesTable.tsx         ← NEW (sortable, paginated)
│   │   ├── SystemActivity.tsx       ← NEW (SWR 30s polling)
│   │   └── SchoolSwitcher.tsx       ← NEW
│   ├── actions/
│   │   ├── schoolLoginAction.ts     ← NEW
│   │   └── logoutAction.ts          ← NEW
│   ├── lib/
│   │   ├── api.ts                   ← NEW
│   │   └── format.ts                ← NEW
│   └── messages/
│       └── pt-BR.json               ← NEW
```

**Structure Decision**: Multi-surface feature touching 4 packages (design-system + 3 app frontends). Design-system changes are foundational and must land first. Web apps share layout/component patterns but are kept separate per BFF pattern. No shared component is extracted from web apps to avoid premature abstraction (constitution Principle V).

---

## Implementation Sequence

Work proceeds in this dependency order. Each phase is independently deployable / testable.

### Phase A — Design System Foundation
**Packages**: `shared/design-system`

1. **A1**: Add TS token constants (`src/tokens/*.ts`) — mirror `tokens.css` values exactly.
2. **A2**: Add `AppearanceContext` + `useAppearance()` hook.
3. **A3**: Wire `AppearanceContext` defaults into existing Button and Card components (backward-compatible: explicit `context` prop still overrides).
4. **A4**: Complete Input component — floating label animation, error state, password toggle, guardian/admin geometry variants.
5. **A5**: Add `Checkbox`, `Skeleton` (shimmer), `EmptyState`, `ErrorState` components.
6. **A6**: Add `./native` entrypoint — Button, Card, Avatar, Input, Badge native variants using TS tokens + StyleSheet.
7. **A7**: Export all new items from `index.ts`; add `"./native"` to `package.json`.

### Phase B — Guardian Mobile
**Packages**: `frontends/guardian-mobile`
**Prerequisite**: Phase A complete

1. **B1**: Migrate to expo-router — update `app.json`, create route group structure, move auth screens.
2. **B2**: Rewrite `LoginScreen` using `@gfn/design-system/native` components — full design per handoff section 4.1.
3. **B3**: Create `DashboardScreen` components (ChildCarousel, QuickAccessGrid, DueItemCard, ActivityRow, TabBar).
4. **B4**: Create dashboard page route with TanStack Query data fetching + skeleton/error/empty states.
5. **B5**: Add i18n (i18n-js + pt-BR locale), format utilities (BRL, dates).
6. **B6**: Add offline banner + pull-to-refresh.

### Phase C — Guardian Web
**Packages**: `frontends/guardian-web`
**Prerequisite**: Phase A complete

1. **C1**: Add server actions (`loginAction`, `logoutAction`) with HttpOnly cookie session.
2. **C2**: Rewrite login page — split panel, social login placeholders, Zod validation, "remember me", i18n.
3. **C3**: Add `AppearanceProvider appearance="warm"` to root layout.
4. **C4**: Create dashboard components (TopNav, ChildCard, MonthSummary w/ recharts, DueTable, RecentActivity, UpcomingEvents).
5. **C5**: Create dashboard page (`/dashboard/page.tsx`) as RSC with Suspense boundaries per section.
6. **C6**: Add i18n (next-intl static pt-BR).

### Phase D — School Web
**Packages**: `frontends/school-web`
**Prerequisite**: Phase A complete

1. **D1**: Add server actions (`schoolLoginAction`, `logoutAction`) with HttpOnly cookie session.
2. **D2**: Rewrite login page — institution code field, pro card, security message, Zod validation, audit header.
3. **D3**: Add `AppearanceProvider appearance="pro"` to root layout.
4. **D4**: Create dashboard components (Sidebar w/ collapse+localStorage, TopBar, KpiCard, RevenueLineChart, ChargesDonut, ChargesTable w/ sort+pagination, SystemActivity w/ SWR, SchoolSwitcher).
5. **D5**: Create dashboard page (`/dashboard/page.tsx`) as RSC.
6. **D6**: Add i18n (next-intl static pt-BR).

### Phase E — Testing & Quality
**All packages**

1. **E1**: Unit tests for all new design-system components (100% variant coverage).
2. **E2**: Contract tests for BFF guardian + BFF school endpoint shapes.
3. **E3**: Component integration tests for login flows (valid, invalid, network error, 2FA branch).
4. **E4**: Component integration tests for dashboards (0/1/multiple children, empty states, loading).
5. **E5**: E2E Playwright — guardian-web: login → dashboard → view charge → logout.
6. **E6**: E2E Playwright — school-web: login → dashboard → sort charges table → logout.
7. **E7**: Chromatic baseline — all design-system components in all variants/states.
8. **E8**: axe-core a11y audit — zero new violations on all 6 screens.

---

## Complexity Tracking

No constitution violations. No unusual abstractions beyond what the design system + multi-surface architecture requires.

---

## Security Notes

⚠️ **Auth token storage**: The existing `guardian-web` and `school-web` login pages use `localStorage` to store auth tokens. This is XSS-vulnerable. This feature replaces both with Next.js 15 server actions that set HttpOnly cookies. No external ticket required — the change is in scope per FR-001 / handoff spec section 5.1 ("Cookies HttpOnly para sessão").

⚠️ **Institution code audit**: `school-web` login must send `X-Client-Version` header per handoff doc. Backend records all attempts (success + failure) with IP and institution. Client-side Zod validates `/^[A-Z0-9]{4,10}$/` before submission.
