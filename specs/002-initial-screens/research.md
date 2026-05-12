# Research — Initial Screens (002)

## Decision 1: Auth token storage on web

**Decision**: Replace `localStorage.setItem('auth_token', ...)` with HttpOnly cookies set via Next.js 15 server actions.

**Rationale**: `localStorage` is accessible to JavaScript and vulnerable to XSS. The existing guardian-web and school-web login pages both use this pattern. Server actions allow setting HttpOnly cookies from `next/headers` so the token is never accessible to client-side JS.

**Alternatives considered**:
- Keep localStorage + token rotation: rejected — still exposes the access token to JS.
- External auth library (NextAuth, Clerk): rejected per YAGNI — BFF contract already defined.

**Impact**: Both web login pages rewritten. `client fetch → localStorage` replaced by server action → `cookies().set()`.

---

## Decision 2: Mobile navigation — expo-router v3.5

**Decision**: Migrate guardian-mobile to expo-router with `(auth)` + `(app)` route groups under `src/app/`.

**Rationale**: `expo-router` is already declared in `package.json` and `"main": "expo-router/entry"` points to it. Current `App.tsx` is an interim stub. expo-router enables tab-bar navigation, deep-linking, and type-safe routes needed for the dashboard.

**Migration path**:
1. Add `"expo": { "router": { "root": "src/app" } }` to `app.json`.
2. Create `src/app/(auth)/login.tsx`, `src/app/(auth)/forgot-password.tsx`.
3. Create `src/app/(app)/_layout.tsx` (tab bar) + `src/app/(app)/index.tsx` (dashboard).
4. Remove `App.tsx` after migration.

**Alternatives considered**: Keep `@react-navigation/native` with DashboardScreen only — rejected; expo-router is the declared entry point; mixing both creates dead code.

---

## Decision 3: CSS import order for Tailwind v4

**Decision**: Keep current `globals.css` (`@import "@gfn/design-system/styles"`) unchanged. Do not add `@import "@gfn/design-system/tokens"`.

**Rationale**: `index.css` (styles) already starts with `@import "tailwindcss"; @import "./tokens.css"`. Adding tokens again creates a duplicate `@theme` block. Transitive import is functionally identical to the handoff doc's three-line pattern.

**Impact**: Both web `globals.css` files are already correct.

---

## Decision 4: AppearanceProvider pattern

**Decision**: Add `AppearanceContext` in `@gfn/design-system`. Provides `appearance: 'warm' | 'pro'`. Components read from context as default; explicit `context` prop overrides it.

**Rationale**: Existing Button/Card have `context: 'guardian' | 'admin'` props. Provider avoids threading `context` through every call site.

**Implementation**: `src/context/AppearanceContext.tsx` + `useAppearance()` hook. Mapping: `warm → guardian`, `pro → admin`.

---

## Decision 5: Mobile data fetching — TanStack Query

**Decision**: Add `@tanstack/react-query` to guardian-mobile.

**Rationale**: 4 parallel dashboard sections need caching, background refetch, loading/error states, and pull-to-refresh. Handoff spec explicitly names React Query.

---

## Decision 6: School-web realtime — SWR

**Decision**: `swr` in school-web for SystemActivity auto-refresh (`refreshInterval: 30_000`) only.

**Rationale**: RSC handles all other data. SWR is minimal for one polling use case vs TanStack Query.

---

## Decision 7: i18n bootstrapping

**Decision**: `next-intl` (web) with static `pt-BR` locale (no URL routing). `i18n-js` (mobile). `messages/pt-BR.json` per web app; `locales/pt-BR.ts` for mobile.

**Rationale**: No locale switching in v1. Static locale avoids middleware and route group changes.

---

## Decision 8: Charts

**Decision**: `recharts` — bar chart (guardian-web), line chart + donut (school-web). Import only used components.

**Rationale**: Explicitly named in handoff doc. Tree-shakeable.

---

## Decision 9: Toast libraries

**Decision**: `sonner` (web), `react-native-toast-message` (mobile). Both wrapped behind `useToast()` hook from `@gfn/design-system`.

**Rationale**: Explicitly named in handoff doc section 3.6.

---

## Decision 10: Design System native entrypoint

**Decision**: Add `"./native"` export to `package.json` pointing to `src/native/index.ts`. Native variants use `StyleSheet.create()` + TS token constants.

**Structure**:
```
shared/design-system/src/
├── tokens/       ← colors.ts, typography.ts, spacing.ts, radii.ts, shadows.ts
├── native/       ← Button.native.tsx, Card.native.tsx, Avatar.native.tsx, ...
│   └── index.ts  ← re-exports all native components
├── context/      ← AppearanceContext.tsx
```
