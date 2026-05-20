# Tasks: Initial Screens — Login & Dashboard (3 Apps)

**Input**: Design documents from `specs/002-initial-screens/`
**Branch**: `002-initial-screens`
**TDD**: Constitution Principle II — write failing test first, confirm red, then implement

## Format: `[ID] [P?] [Story?] Description with file path`

- **[P]**: Can run in parallel with other [P]-marked tasks in the same phase (no shared file deps)
- **[Story]**: User story phase tasks (US1–US4)
- **No story label**: Setup, Foundation, or Polish phase

---

## Phase 1: Setup

**Purpose**: Install new dependencies, scaffold directories, create mock fixtures.

- [X] T001 Install guardian-web dependencies: `zod sonner recharts next-intl` in `frontends/guardian-web/package.json`
- [X] T002 [P] Install school-web dependencies: `zod sonner recharts next-intl swr` in `frontends/school-web/package.json`
- [X] T003 [P] Install guardian-mobile dependencies: `react-native-reanimated react-native-toast-message @tanstack/react-query expo-image i18n-js` in `frontends/guardian-mobile/package.json`
- [X] T004 [P] Create directory structure for design-system additions: `shared/design-system/src/tokens/`, `shared/design-system/src/context/`, `shared/design-system/src/native/`
- [X] T005 [P] Create directory structure for guardian-mobile routes: `frontends/guardian-mobile/src/app/(auth)/`, `frontends/guardian-mobile/src/app/(app)/`, `frontends/guardian-mobile/src/components/`, `frontends/guardian-mobile/src/hooks/`, `frontends/guardian-mobile/src/lib/`, `frontends/guardian-mobile/src/locales/`
- [X] T006 [P] Create directory structure for guardian-web additions: `frontends/guardian-web/src/components/`, `frontends/guardian-web/src/actions/`, `frontends/guardian-web/src/lib/`, `frontends/guardian-web/src/messages/`
- [X] T007 [P] Create directory structure for school-web additions: `frontends/school-web/src/components/`, `frontends/school-web/src/actions/`, `frontends/school-web/src/lib/`, `frontends/school-web/src/messages/`
- [X] T008 [P] Verify mock fixtures exist at `specs/002-initial-screens/fixtures/` (guardian-dashboard.json, school-dashboard.json, auth-responses.json)

**Checkpoint**: All dependencies installable, directories exist, fixtures ready.

---

## Phase 2: Foundation — Design System (US4 prerequisite, blocks US1–US3)

**Purpose**: Design system TS tokens, AppearanceProvider, complete component variants, native entrypoint. All 3 app user stories depend on this phase.

**⚠️ CRITICAL**: US1, US2, US3 cannot start until T009–T034 are complete.

### TS Token Constants

- [X] T009 Write failing unit tests for token constant values in `shared/design-system/src/__tests__/tokens.test.ts` (verify each token matches its CSS variable in `tokens.css`)
- [X] T010 [P] Create `shared/design-system/src/tokens/colors.ts` — mirror `--color-*` variables from `tokens.css` exactly
- [X] T011 [P] Create `shared/design-system/src/tokens/typography.ts` — scale: display-lg → numeric-lg with size/weight/lineHeight
- [X] T012 [P] Create `shared/design-system/src/tokens/spacing.ts` — 0/4/8/12/16/20/24/32/40/48/64px map
- [X] T013 [P] Create `shared/design-system/src/tokens/radii.ts` — sm:6, md:8, lg:12, xl:16, full:9999
- [X] T014 [P] Create `shared/design-system/src/tokens/shadows.ts` — shadowsRN object with shadowColor/opacity/radius/offset/elevation per shadow level
- [X] T015 Run token tests (T009) to confirm green after T010–T014

### AppearanceContext

- [X] T016 Write failing test for AppearanceContext default value and useAppearance() hook in `shared/design-system/src/__tests__/AppearanceContext.test.tsx`
- [X] T017 Create `shared/design-system/src/context/AppearanceContext.tsx` — provides `appearance: 'warm' | 'pro'`, default `'warm'`, maps warm→guardian and pro→admin
- [X] T018 Wire `useAppearance()` into `shared/design-system/src/components/Button/Button.tsx` — read context as default for `context` prop; explicit prop overrides context (backward-compatible)
- [X] T019 Wire `useAppearance()` into `shared/design-system/src/components/Card/Card.tsx` — same backward-compatible pattern as T018

### Input Component Completion

- [X] T020 Write failing tests for Input floating label, error state, and password toggle in `shared/design-system/src/__tests__/Input.test.tsx`
- [X] T021 Rewrite `shared/design-system/src/components/Input/Input.tsx` — add floating label animation (150ms ease-out), per-app geometry (guardian: h-48/56, school: h-44), error state (danger border + message + AlertCircle icon), password visibility toggle (Eye/EyeOff rightSlot), aria-invalid + aria-describedby, guardian/admin context variants

### Card Completion

- [X] T022 Add `kpi` variant to `shared/design-system/src/components/Card/Card.tsx` — neutral-0 bg, rounded-md, shadow-sm, padding md (p-5)

### Missing Components

- [X] T023 Write failing tests for Checkbox in `shared/design-system/src/__tests__/Checkbox.test.tsx`
- [X] T024 [P] Create `shared/design-system/src/components/Checkbox/Checkbox.tsx` and `index.ts` — label prop, checked/onChange, aria-checked, focus-visible outline
- [X] T025 Write failing tests for Skeleton in `shared/design-system/src/__tests__/Skeleton.test.tsx`
- [X] T026 [P] Create `shared/design-system/src/components/Skeleton/Skeleton.tsx` and `index.ts` — shimmer gradient animation 1500ms loop, neutral-100→neutral-200, respects prefers-reduced-motion
- [X] T027 Write failing tests for EmptyState in `shared/design-system/src/__tests__/EmptyState.test.tsx`
- [X] T028 [P] Create `shared/design-system/src/components/EmptyState/EmptyState.tsx` and `index.ts` — icon + title + description + optional action button props
- [X] T029 Write failing tests for ErrorState in `shared/design-system/src/__tests__/ErrorState.test.tsx`
- [X] T030 [P] Create `shared/design-system/src/components/ErrorState/ErrorState.tsx` and `index.ts` — AlertTriangle icon + title + "Tentar novamente" action
- [X] T031 Wrap sonner in `shared/design-system/src/components/Toast/Toast.tsx` — export `useToast()` hook that calls `sonner.toast.*`; export `<Toaster>` wrapper for app root

### Native Entrypoint

- [X] T032 [P] Create `shared/design-system/src/native/Button.native.tsx` — StyleSheet using tokens from T010–T014; variant/size/context props mirroring web Button; Pressable with opacity 0.85 on press; tap target ≥ 44×44
- [X] T033 [P] Create `shared/design-system/src/native/Card.native.tsx` — StyleSheet with token radii and shadowsRN; hero variant with gradient via expo-linear-gradient
- [X] T034 [P] Create `shared/design-system/src/native/Avatar.native.tsx` — Image with fallback initials; sizes 24/32/40/56
- [X] T035 [P] Create `shared/design-system/src/native/Input.native.tsx` — TextInput with floating label (Animated.Value), error state, password toggle, h-56, radius 12; tap target ≥ 44×44
- [X] T036 [P] Create `shared/design-system/src/native/Badge.native.tsx` — View with StyleSheet tokens; dot indicator; all 5 variants
- [X] T037 Create `shared/design-system/src/native/index.ts` — re-exports T032–T036
- [X] T038 Update `shared/design-system/package.json` — add `"./native": "./src/native/index.ts"` exports entry
- [X] T039 Update `shared/design-system/src/index.ts` — add exports for AppearanceContext, Checkbox, Skeleton, EmptyState, ErrorState, Toast/useToast

**Checkpoint**: `pnpm test` green in shared/design-system. `@gfn/design-system` and `@gfn/design-system/native` fully importable.

---

## Phase 3: User Story 1 — Guardian Mobile Login + Dashboard (Priority: P1) 🎯 MVP

**Goal**: Guardian can launch the mobile app, log in with email/password, and view their child's wallet balance, pending payments, and recent activity on a native dashboard with a tab bar.

**Independent Test**: Launch Expo app → enter `guardian@mock.local` + any password ≥6 chars → land on dashboard showing child card with balance in green → swipe to second child (negative balance in red) → see "A pagar este mês" list → pull to refresh.

### Tests — US1

- [X] T040 [P] [US1] Write failing unit tests for useAuth hook (login success, invalid credentials, MFA redirect, network error) in `frontends/guardian-mobile/src/__tests__/useAuth.test.ts`
- [X] T041 [P] [US1] Write failing unit tests for format utilities (formatBRL, formatRelative, formatDue) in `frontends/guardian-mobile/src/__tests__/format.test.ts`
- [X] T042 [P] [US1] Write failing integration test for LoginScreen (valid submit, invalid credentials shake, network offline toast) in `frontends/guardian-mobile/src/__tests__/LoginScreen.test.tsx`
- [X] T043 [P] [US1] Write failing integration test for DashboardScreen (renders children, empty state, loading skeletons, pull-to-refresh) in `frontends/guardian-mobile/src/__tests__/DashboardScreen.test.tsx`

### Expo-Router Migration — US1

- [X] T044 [US1] Update `frontends/guardian-mobile/app.json` — add `"expo": { "router": { "root": "src/app" } }` and remove App.tsx entry conflict
- [X] T045 [US1] Create `frontends/guardian-mobile/src/app/_layout.tsx` — root layout wrapping `<QueryClientProvider>` + `<Toaster>` (react-native-toast-message) + `<Slot />`
- [X] T046 [US1] Create `frontends/guardian-mobile/src/app/(auth)/_layout.tsx` — unauthenticated Stack navigator with no header
- [X] T047 [US1] Create `frontends/guardian-mobile/src/app/(auth)/login.tsx` — thin route file that renders the LoginScreen component

### Login Screen — US1

- [X] T048 [US1] Create i18n setup in `frontends/guardian-mobile/src/lib/i18n.ts` and `frontends/guardian-mobile/src/locales/pt-BR.ts` — all login + dashboard string keys
- [X] T049 [US1] Create format utilities in `frontends/guardian-mobile/src/lib/format.ts` — `formatBRL(cents)`, `formatRelative(iso)`, `formatDue(isoDate)` using Intl + pt-BR locale
- [X] T050 [US1] Create BFF API client in `frontends/guardian-mobile/src/lib/api.ts` — `postLogin()`, `getGuardianDashboard()` using mock fixtures in dev
- [X] T051 [US1] Create `useAuth` hook in `frontends/guardian-mobile/src/hooks/useAuth.ts` — calls api.ts, stores token in expo-secure-store, returns `{ login, logout, isLoading }`
- [X] T052 [US1] Rewrite `frontends/guardian-mobile/src/app/(auth)/login.tsx` with full design per handoff 4.1 — Logo, heading, email/password Inputs from `@gfn/design-system/native`, "Esqueci" link, primary Button (lg, fullWidth, loading), divider, Google/Apple secondary buttons, footer link; KeyboardAvoidingView; shake animation (react-native-reanimated) on 401; i18n strings
- [X] T053 [US1] Move `frontends/guardian-mobile/src/app/(auth)/forgot-password.tsx` — adapt ForgotPasswordScreen to expo-router route

### Dashboard Screen — US1

- [X] T054 [US1] Create `useGuardianDashboard` hook in `frontends/guardian-mobile/src/hooks/useGuardianDashboard.ts` — TanStack Query fetching `getGuardianDashboard()`, returns children/payments/activity/isLoading/isError/refetch
- [X] T055 [P] [US1] Create `frontends/guardian-mobile/src/components/ChildCarousel.tsx` — FlatList horizontal, pagingEnabled, child Card (hero warm) with Avatar/name/grade/school/balance (color-coded), "Recarregar"+"Ver extrato" buttons; pagination dots; EmptyState when no children
- [X] T056 [P] [US1] Create `frontends/guardian-mobile/src/components/QuickAccessGrid.tsx` — FlatList numColumns=4, 80×80 Pressable items (Mensalidades/Cantina/Loja/Boletos), radius-lg, border neutral-200, icon + label
- [X] T057 [P] [US1] Create `frontends/guardian-mobile/src/components/DueItemCard.tsx` — payment row with description/amount/dueDate/Badge; maps PaymentStatus to Badge variant per data-model; tap opens bottom sheet stub
- [X] T058 [P] [US1] Create `frontends/guardian-mobile/src/components/ActivityRow.tsx` — 56h row with colored dot, description, relative timestamp, signed amount (color-coded credit/debit)
- [X] T059 [P] [US1] Create `frontends/guardian-mobile/src/components/OfflineBanner.tsx` — fixed top banner "Você está offline. Tentando reconectar...", shown via NetInfo
- [X] T060 [US1] Create `frontends/guardian-mobile/src/app/(app)/_layout.tsx` — Tabs layout with 4 tabs: Início/Pagar/Histórico/Perfil; active: brand-primary-700, inactive: neutral-400; bg neutral-0 with top border; safe-area bottom padding
- [X] T061 [US1] Assemble `frontends/guardian-mobile/src/app/(app)/index.tsx` — DashboardScreen using ChildCarousel + QuickAccessGrid + DueItemCard list + ActivityRow list; skeleton states (Skeleton from design-system) on isLoading; ErrorState + retry on isError; RefreshControl (pull-to-refresh); OfflineBanner
- [X] T062 [US1] Run tests T040–T043 to confirm green

**Checkpoint**: `npx expo start` → login → dashboard shows child card with mocked data, pull-to-refresh works, offline banner appears when disconnected.

---

## Phase 4: User Story 2 — Guardian Web Login + Dashboard (Priority: P2)

**Goal**: Guardian can log in on the web portal (split-panel layout, social login placeholders, "remember me"), land on a dashboard with sticky top nav, child card, monthly summary chart, payments table, and upcoming events.

**Independent Test**: Open `http://localhost:3000/login` → submit `guardian@mock.local` + any password → redirect to `/dashboard` → top nav visible and sticky on scroll → child card shows balance in green → month summary bar chart renders → payments table shows 2 rows with correct badges.

### Tests — US2

- [X] T063 [P] [US2] Write failing tests for loginAction server action (success → cookie set, invalid → error returned, network error) in `frontends/guardian-web/src/__tests__/actions/loginAction.test.ts`
- [X] T064 [P] [US2] Write failing integration tests for login page (split panel layout, Zod validation, form submission, remember-me checkbox) in `frontends/guardian-web/src/__tests__/login.test.tsx`
- [X] T065 [P] [US2] Write failing integration tests for dashboard page (TopNav sticky, ChildCard tabs, DueTable empty state, RecentActivity "Ver tudo") in `frontends/guardian-web/src/__tests__/dashboard.test.tsx`

### Server Actions + Auth — US2

- [X] T066 [US2] Create BFF client in `frontends/guardian-web/src/lib/api.ts` — `loginGuardian()`, `getGuardianDashboard()`, `getGuardianSummary()`; server-side fetch using BFF_GUARDIAN_URL env var; uses mock fixtures when BFF_GUARDIAN_URL not set
- [X] T067 [US2] Create format utilities in `frontends/guardian-web/src/lib/format.ts` — `formatBRL(cents)`, `formatDate(iso)`, `formatRelative(iso)` using Intl pt-BR
- [X] T068 [US2] Create `frontends/guardian-web/src/actions/loginAction.ts` — `'use server'`; calls api.ts loginGuardian(); on success sets HttpOnly `session` cookie via `cookies().set()` from `next/headers`; returns `{ error? }` per Next.js 15 server action convention
- [X] T069 [US2] Create `frontends/guardian-web/src/actions/logoutAction.ts` — `'use server'`; clears session cookie; redirects to `/login`

### Login Page — US2

- [X] T070 [US2] Update `frontends/guardian-web/src/app/layout.tsx` — add `<AppearanceProvider appearance="warm">` from design-system; add next-intl `NextIntlClientProvider` with pt-BR messages
- [X] T071 [US2] Create `frontends/guardian-web/src/messages/pt-BR.json` — all login + dashboard string keys in pt-BR
- [X] T072 [US2] Rewrite `frontends/guardian-web/src/app/login/page.tsx` per handoff 5.1 — grid 2-col (lg:block hidden left panel with gradient bg + SVG illustration placeholder + tagline; right panel max-w-md centered); Input (email + password, floating label); "Lembrar de mim" Checkbox + "Esqueci minha senha" link row; primary Button (fullWidth, loading); social login buttons (Google + Apple, flex-1); server action form (`action={loginAction}`); Zod client-side validation (email().min(1), password.min(6)); error display via useFormState; responsive (single column <lg); i18n strings; aria-labels on social buttons

### Dashboard Page — US2

- [X] T073 [P] [US2] Create `frontends/guardian-web/src/components/TopNav.tsx` — sticky h-16 header; Logo left; nav links center (Início/Pagamentos/Cantina/Loja/Histórico) with active state (brand-primary-700, border-b-2); NotificationBell + UserMenu right; shadow-sm appears after 8px scroll via IntersectionObserver; hamburger <lg; aria-label="Navegação principal"
- [X] T074 [P] [US2] Create `frontends/guardian-web/src/components/ChildCard.tsx` — Card variant="hero" appearance="warm" lg:col-span-3; Avatar + name/grade/school; balance (numeric-hero, color-coded); "Recarregar" + "Ver extrato" buttons; multi-child tab selector at top; i18n
- [X] T075 [P] [US2] Create `frontends/guardian-web/src/components/MonthSummary.tsx` — Card lg:col-span-2; heading + month DropdownMenu; 3 stat rows (icon + label + numeric-lg value); recharts BarChart (7 weeks, accent-green-500 bars, no grid/axes, tooltip); Skeleton on loading
- [X] T076 [P] [US2] Create `frontends/guardian-web/src/components/DueTable.tsx` — Card padding=none; table with thead (neutral-50 bg, label-md) + tbody (64h rows, hover neutral-50, cursor-pointer); columns: description/dueDate/amount(tabular-nums)/status(Badge); EmptyState (CheckCircle icon, "Tudo em dia!") when empty; "Ver tudo" ghost button
- [X] T077 [P] [US2] Create `frontends/guardian-web/src/components/RecentActivity.tsx` — list with colored bullet dots; 5 items max; "Ver tudo" link; hidden when no activity
- [X] T078 [P] [US2] Create `frontends/guardian-web/src/components/UpcomingEvents.tsx` — Calendar icon + event title + formatted date; divider between items; "Ver agenda" link
- [X] T079 [US2] Create `frontends/guardian-web/src/app/dashboard/page.tsx` — RSC; reads session cookie (redirect to /login if absent); parallel fetch (getGuardianDashboard + getGuardianSummary) with Promise.all; Suspense boundary per section with typed Skeleton fallback; PageHeader; grid HeroRow (ChildCard + MonthSummary); DueTable; BottomRow (RecentActivity + UpcomingEvents); max-w-[1200px] mx-auto px-8 py-8
- [X] T080 [US2] Run tests T063–T065 to confirm green

**Checkpoint**: `pnpm dev` in guardian-web → login → dashboard renders with mock data; sticky nav shadows on scroll; <1024px: single column layout, hamburger nav.

---

## Phase 5: User Story 3 — School Web Login + Dashboard (Priority: P3)

**Goal**: School admin can log in with institution code + email + password, land on a dashboard with collapsible sidebar, 4 KPI cards, revenue trend chart, charges donut, sortable/paginated charges table, and auto-refreshing system activity feed.

**Independent Test**: Open `http://localhost:3003/login` → enter code `ESCOLA01` + `admin@mock.local` + any password → redirect to `/dashboard` → 4 KPI cards visible with change indicators → revenue line chart renders → charges table shows 3 rows → collapse sidebar → sidebar collapses to icons → refresh persists after page reload.

### Tests — US3

- [X] T081 [P] [US3] Write failing tests for schoolLoginAction (institution code validation, success, invalid credentials) in `frontends/school-web/src/__tests__/actions/schoolLoginAction.test.ts`
- [X] T082 [P] [US3] Write failing integration tests for school login page (institution code field, pro card styling, Zod validation, security message) in `frontends/school-web/src/__tests__/login.test.tsx`
- [X] T083 [P] [US3] Write failing integration tests for school dashboard (KpiGrid, ChargesTable sort, sidebar collapse, SystemActivity) in `frontends/school-web/src/__tests__/dashboard.test.tsx`

### Server Actions + Auth — US3

- [X] T084 [US3] Create BFF client in `frontends/school-web/src/lib/api.ts` — `loginSchool()`, `getSchoolDashboard(period)`, `getSchoolActivity()`; server-side; mock fixtures fallback
- [X] T085 [US3] Create format utilities in `frontends/school-web/src/lib/format.ts` — `formatBRL(cents)`, `formatDate(iso)`, `formatPct(value)` using Intl pt-BR
- [X] T086 [US3] Create `frontends/school-web/src/actions/schoolLoginAction.ts` — `'use server'`; Zod schema validates institutionCode `/^[A-Z0-9]{4,10}$/`; calls api.ts; on success sets HttpOnly session cookie; sends `X-Client-Version` header; returns `{ error?, fieldErrors? }`
- [X] T087 [US3] Create `frontends/school-web/src/actions/logoutAction.ts` — `'use server'`; clears cookie; redirects to `/login`

### Login Page — US3

- [X] T088 [US3] Update `frontends/school-web/src/app/layout.tsx` — add `<AppearanceProvider appearance="pro">`; add next-intl provider with pt-BR messages
- [X] T089 [US3] Create `frontends/school-web/src/messages/pt-BR.json` — all login + dashboard string keys
- [X] T090 [US3] Rewrite `frontends/school-web/src/app/login/page.tsx` per handoff 6.1 — min-h-screen bg-neutral-50 centered; BrandHeader ("GFN | Administrativo", brand-primary-700 logo + separator + heading-md neutral-600); Card (default, pro, elevation=md, w-[440px] p-10); heading-xl + subtitle; form with institution code Input (autoComplete="organization", Zod regex), email Input, password Input, Checkbox "Manter conectado", primary Button (fullWidth, loading); security message (Lock icon + "Sua sessão é protegida e auditada", body-sm neutral-400, border-t); FooterLinks (Esqueci + Suporte); Version tag; NO social login; NO illustration; rounded-sm (6px) on inputs/button (admin context)

### Dashboard Components — US3

- [X] T091 [P] [US3] Create `frontends/school-web/src/components/Sidebar.tsx` — aside bg-neutral-0 border-r; w-60 expanded / w-16 collapsed; SidebarItem (44h, padding, active: bg-brand-primary-50 border-l-[3px] brand-primary-700); two nav groups (principal + operacional) with `<hr>` divider; collapse toggle button at footer; persist to `localStorage('sidebar-collapsed')`; transition width 200ms ease-in-out; `<nav aria-label="Navegação principal">`
- [X] T092 [P] [US3] Create `frontends/school-web/src/components/TopBar.tsx` — sticky h-16 header; page title (heading-lg) from route context; Bell + Settings icons + Avatar(size=32) + SchoolSwitcher right
- [X] T093 [P] [US3] Create `frontends/school-web/src/components/KpiCard.tsx` — Card variant="kpi" padding="md"; label-md label + numeric-hero value + change row (trend icon + percentage + comparison text, color from changePositive); formatBRL or raw count depending on unit; Skeleton on loading
- [X] T094 [P] [US3] Create `frontends/school-web/src/components/RevenueLineChart.tsx` — recharts LineChart; stroke brand-primary-700 sw-2; area fill brand-primary-100 opacity-0.4; Y axis BRL suffix (R$ 100k); custom tooltip; 6 data points from RevenueChart type
- [X] T095 [P] [US3] Create `frontends/school-web/src/components/ChargesDonut.tsx` — recharts PieChart (donut); 3 slices (paid=accent-green-500, open=semantic-warning, overdue=semantic-danger); legend right (bullet + label + %); center label (total, numeric-lg)
- [X] T096 [P] [US3] Create `frontends/school-web/src/components/ChargesTable.tsx` — Card padding=none; thead (neutral-50 44h, label-md, sortable columns with ChevronUp/Down); tbody (56h rows, hover neutral-50, cursor-pointer); columns: Aluno/Plano/Vencimento/Valor(tabular-nums right)/Status(Badge with dot); Pagination component at footer if total>10; sort state managed locally; `<th scope="col">` for a11y
- [X] T097 [P] [US3] Create `frontends/school-web/src/components/SystemActivity.tsx` — client component; SWR `useSWR('/school/dashboard/activity', { refreshInterval: 30_000 })`; list max 8 events; Row with colored Dot (eventColor map) + description + formatted time; "Ver log completo" ghost button; role="status" on container
- [X] T098 [P] [US3] Create `frontends/school-web/src/components/SchoolSwitcher.tsx` — dropdown showing active school name; onClick updates session activeSchoolId + triggers data reload; hidden if only 1 school
- [X] T099 [P] [US3] Create `frontends/school-web/src/components/useCan.ts` hook — `useCan(role: 'staff' | 'admin'): boolean`; reads role from session; used to hide/disable role-gated actions
- [X] T100 [US3] Create `frontends/school-web/src/app/dashboard/page.tsx` — RSC; session guard (redirect to /login if absent); fetch getSchoolDashboard(period) server-side; Suspense per section; `<div className="flex min-h-screen bg-neutral-50">` with Sidebar + flex-1 column (TopBar + main); PageHeader (heading-xl + MonthPicker + Export button gated by useCan); KpiGrid (grid 4-col xl); ChartsRow (3/5–2/5 grid); ChargesTable; SystemActivity; responsiveness per handoff section 6.2 breakpoint table
- [X] T101 [US3] Run tests T081–T083 to confirm green

**Checkpoint**: `pnpm dev` in school-web → login with ESCOLA01 → dashboard renders KPIs + charts + charges; sidebar collapses/expands with localStorage persistence; SystemActivity refreshes every 30s.

---

## Phase 6: Polish & Cross-Cutting (US4 Completion)

**Purpose**: E2E flows, accessibility audit, visual regression baseline, bundle size verification, reduced-motion compliance.

- [X] T102 [P] Write E2E Playwright test for guardian-web: login → dashboard renders → TopNav sticky shadow → switch child tab → click payment row → logout; save in `frontends/guardian-web/e2e/guardian-flow.spec.ts`
- [X] T103 [P] Write E2E Playwright test for school-web: login with institution code → dashboard renders → click KPI chart tooltip → sort charges table by Vencimento → paginate to page 2 → collapse sidebar → logout; save in `frontends/school-web/e2e/school-flow.spec.ts`
- [X] T104 [P] Run axe-core accessibility scan on all 6 screens (guardian-web login + dashboard, school-web login + dashboard, guardian-mobile login + dashboard via jest-axe or Maestro); fix any violations; zero new violations required
- [X] T105 [P] Add `@media (prefers-reduced-motion: reduce)` rules to all CSS animations in web apps (skeleton shimmer → remove gradient animation; toast slide → opacity-only; input label float → instant); add `AccessibilityInfo.isReduceMotionEnabled()` guard to Reanimated shake in guardian-mobile login
- [X] T106 [P] Establish Chromatic visual regression baseline — run Storybook stories for all design-system components (Button all variants, Input all states, Card all variants, Avatar fallback, Badge all variants, Skeleton, EmptyState, ErrorState, Checkbox); commit baseline screenshots
- [X] T107 Verify bundle sizes with `next build` analyzer in guardian-web and school-web — confirm login page < 120 KB gzip, dashboard < 200 KB gzip; document results in `specs/002-initial-screens/perf-report.md`
- [X] T108 [P] Verify mobile dashboard TTI < 2s on 4G using Flipper or Reactotron profiling — document result in `specs/002-initial-screens/perf-report.md`

**Checkpoint**: All E2E tests green. Zero a11y violations. Bundle budgets met. Visual baseline committed.

---

## Dependency Graph

```
T001–T008 (Setup)
    ↓
T009–T039 (Design System Foundation — Phase 2)
    ↓ ↓ ↓
    ↓ ↓ └── T081–T101 (US3 School Web — Phase 5)
    ↓ └──── T063–T080 (US2 Guardian Web — Phase 4)
    └────── T040–T062 (US1 Guardian Mobile — Phase 3)
                ↘     ↙
              T102–T108 (Polish — Phase 6)
```

**Parallel Opportunities within each Phase**:
- Phase 2: T010–T014 all parallel (independent token files); T024/T026/T028/T030 all parallel (independent components); T032–T036 all parallel (independent native files)
- Phase 3 (US1): T040–T043 all parallel (test files); T055–T059 all parallel (independent components)
- Phase 4 (US2): T063–T065 parallel; T073–T078 all parallel (independent components)
- Phase 5 (US3): T081–T083 parallel; T091–T099 all parallel (independent components)
- Phase 6: T102–T105 all parallel; T106–T108 parallel

---

## Implementation Strategy

**MVP (Phase 1 + 2 + Phase 3 US1 only)**:
Complete T001–T062. Delivers:
- Design system with TS tokens, AppearanceProvider, all missing components, native entrypoint
- Guardian mobile app: login → dashboard full flow with mock data

**Increment 2 (add Phase 4 US2)**:
T063–T080. Delivers guardian-web login + dashboard.

**Increment 3 (add Phase 5 US3)**:
T081–T101. Delivers school-web login + dashboard.

**Hardening (Phase 6)**:
T102–T108. E2E, a11y, perf.

---

## Summary

| Phase | Tasks | User Story |
|---|---|---|
| Setup | T001–T008 | — |
| Foundation (Design System) | T009–T039 | US4 prerequisite |
| Guardian Mobile | T040–T062 | US1 (P1) |
| Guardian Web | T063–T080 | US2 (P2) |
| School Web | T081–T101 | US3 (P3) |
| Polish | T102–T108 | US4 completion |
| **Total** | **108 tasks** | |
