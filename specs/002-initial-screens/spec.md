# Feature Specification: Initial Screens — Login & Dashboard (3 Apps)

**Feature Branch**: `002-initial-screens`
**Created**: 2026-05-12
**Status**: Draft
**Input**: User description: "Crie as telas iniciais descritas em docs/design/01-handoff-dev.md"

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Guardian logs in and sees their child's financial summary on mobile (Priority: P1)

A parent (responsável) opens the GFN mobile app for the first time after installing it. They enter their email and password, tap "Entrar", and land on a dashboard that shows their child's name, current wallet balance, pending payments for the month, and recent transaction history. If they have multiple children enrolled, they can swipe between child cards.

**Why this priority**: The mobile dashboard is the primary touchpoint for the guardian persona. Without a working login-to-dashboard flow on mobile, the product has no usable surface. All other screens depend on this flow functioning correctly.

**Independent Test**: Can be fully tested by launching the mobile app, entering valid credentials, and verifying the dashboard renders the child's wallet balance, at least one quick-access shortcut, and at least one section (due payments or recent activity).

**Acceptance Scenarios**:

1. **Given** the guardian is on the mobile login screen, **When** they enter a valid email and password and tap "Entrar", **Then** they are taken to the dashboard showing their child's name, grade, school, and wallet balance.
2. **Given** the guardian is on the dashboard, **When** the wallet balance is positive, **Then** the balance is displayed in green; when zero, in dark blue; when negative, in red.
3. **Given** the guardian has multiple children, **When** they open the dashboard, **Then** a horizontally scrollable carousel shows each child's card with pagination dots.
4. **Given** the guardian has no children linked to their account, **When** they open the dashboard, **Then** an empty state is shown with a message asking them to request the school to link the student.
5. **Given** the mobile app loses network connection, **When** the guardian tries to log in, **Then** a toast message informs them of the connection failure and their credentials are preserved.
6. **Given** the guardian enters invalid credentials, **When** they tap "Entrar", **Then** the form shakes, a red toast shows the error, the password field is cleared, and focus moves to the password field.
7. **Given** the account requires two-factor authentication, **When** the guardian submits valid credentials, **Then** they are redirected to the OTP verification screen.

---

### User Story 2 — Guardian logs in and views their dashboard via web portal (Priority: P2)

A guardian accesses the GFN web portal on a desktop or tablet browser. They see a two-panel layout: an illustrated left panel with a welcome message, and a right panel with a login form. After signing in (email/password or social login), they land on a dashboard with a top navigation bar, a hero section showing their child's balance alongside a monthly financial summary chart, a table of pending payments, a recent activity feed, and a section for upcoming school events.

**Why this priority**: The web portal serves guardians who prefer desktop access and provides richer financial views (charts, tables) than mobile. It is the second most critical guardian touchpoint.

**Independent Test**: Can be fully tested by navigating to the web login URL, authenticating, and verifying the dashboard renders the hero card with child data, the monthly summary chart, and the payments table (or its empty state).

**Acceptance Scenarios**:

1. **Given** the guardian is on the web login page on a desktop, **When** they submit valid credentials, **Then** they are redirected to the dashboard.
2. **Given** the guardian enables "Lembrar de mim", **When** they close and reopen the browser, **Then** they remain authenticated.
3. **Given** the guardian is on a mobile browser (<1024px), **When** they visit the login page, **Then** the illustrated left panel is hidden and the form occupies the full width.
4. **Given** the guardian is on the dashboard with multiple children, **When** they switch between child tabs, **Then** the balance and child details update for the selected child.
5. **Given** the guardian scrolls down on the dashboard, **When** they pass 8px of scroll, **Then** the top navigation gains a shadow indicating it is sticky.
6. **Given** the payments table has no pending charges, **When** the guardian views the dashboard, **Then** a celebratory empty state ("Tudo em dia!") is shown instead of the table.

---

### User Story 3 — School administrator logs in and monitors the school's financial overview via web (Priority: P3)

A school administrator opens the GFN school web app. They see a clean, professional centered login card with fields for institution code, email, and password — no illustrations, no social login. After signing in, they land on a dashboard with a collapsible sidebar navigation, a header showing the current page title, KPI cards (monthly revenue, delinquency rate, active students, canteen tickets), a revenue trend chart, a charge-status donut chart, a sortable charges table with pagination, and a live system activity feed.

**Why this priority**: The school admin dashboard is the operational heart of the product. It is lower priority than guardian flows because it targets internal users rather than the end-customer-facing experience.

**Independent Test**: Can be fully tested by navigating to the school web login URL, entering a valid institution code + email + password, and verifying the four KPI cards and the charges table (or empty state) render on the dashboard.

**Acceptance Scenarios**:

1. **Given** the administrator enters a valid institution code, email, and password, **When** they submit the form, **Then** they are taken to the dashboard with the correct school's data.
2. **Given** the institution code field receives an invalid format (not 4–10 uppercase alphanumeric characters), **When** the admin submits or leaves the field, **Then** an inline validation error is shown.
3. **Given** the admin is on the dashboard, **When** they click the sidebar collapse toggle, **Then** the sidebar shrinks to icon-only mode and the preference is remembered across page reloads.
4. **Given** the admin has a "viewer" role, **When** they view the dashboard, **Then** the "Exportar" button and any staff-only actions are hidden or disabled.
5. **Given** the charges table has more than 10 rows, **When** the admin views the table, **Then** pagination controls appear and the admin can navigate between pages.
6. **Given** a column header in the charges table is clicked, **When** the table is sorted by that column, **Then** a sort indicator appears and rows reorder accordingly.
7. **Given** the system activity section is visible, **When** 30 seconds pass, **Then** the activity list refreshes automatically without a full page reload.
8. **Given** the admin manages multiple schools, **When** they use the school switcher in the top bar, **Then** all dashboard data updates to reflect the selected school.

---

### User Story 4 — All users experience a consistent, accessible visual interface across all apps (Priority: P4)

Regardless of which app (mobile, guardian web, or school web) a user opens, they encounter the same brand palette, typography scale, spacing rhythm, and interaction feedback patterns. Components such as buttons, inputs, badges, cards, and toasts behave and look consistently. Users with assistive technologies can navigate all screens using keyboard (web) or screen readers (mobile and web).

**Why this priority**: Shared design system consistency is a foundation concern, but user-facing screens can be partially delivered before all tokens and component variants are complete.

**Independent Test**: Can be tested by rendering each shared component (Button, Input, Card, Avatar, Badge, Toast) in all its variants and verifying the output matches the design token spec; and by running a keyboard-navigation pass on each web screen.

**Acceptance Scenarios**:

1. **Given** any interactive element on a web screen is focused via keyboard, **When** focus is visible, **Then** a 2px brand-blue outline is displayed around the element.
2. **Given** a form input is focused, **When** the user starts typing, **Then** the floating label animates to the top of the field within 150ms.
3. **Given** a form field has a validation error, **When** the error state is shown, **Then** the border turns red, an error message appears below the field, and screen readers announce the error.
4. **Given** the user's operating system has "reduce motion" enabled, **When** any animation would play, **Then** transforms are disabled and only opacity transitions are used.
5. **Given** a toast notification is triggered, **When** it appears, **Then** success/info toasts carry a status ARIA role and danger toasts carry an alert ARIA role.
6. **Given** a loading skeleton is shown, **When** the data arrives, **Then** the skeleton is replaced by real content without layout shift.

---

### Edge Cases

- Long child name (>30 characters): truncated with ellipsis; full name accessible via tooltip (web) or detail modal (mobile).
- Wallet balance exceeding R$ 10,000: tabular-numeral formatting preserved; on very small screens (<360px wide) the hero numeric text reduces in size.
- Negative balance: prefixed with "−" (minus sign, not hyphen), displayed in danger color; never shown in parentheses.
- No recent activity: the section header and list are hidden entirely — not shown as an empty list.
- No pending charges: celebratory empty state shown instead of the charges table/list.
- Avatar image fails to load: automatically falls back to initials (first letter of each word, max 2 letters).
- More than 5 children linked: horizontal carousel with pagination on mobile; scrollable tabs on web.
- Slow network (load >3s): skeleton shimmer maintained; partial data rendered as it arrives; global 30s timeout triggers error state with retry action.
- School code format mismatch: inline error shown on blur; form submission blocked.
- Session expiry while the user is navigating: user is redirected to login; form state is not lost.

## Requirements *(mandatory)*

### Functional Requirements

**Authentication — Guardian**

- **FR-001**: The system MUST allow guardians to authenticate with email and password on both mobile and web portal.
- **FR-002**: The system MUST offer social login (Google and Apple) on the guardian mobile app and on the guardian web portal.
- **FR-003**: The system MUST disable the login submit button until the email is valid and the password is at least 6 characters.
- **FR-004**: The system MUST support a "Lembrar de mim" option on web that persists the session across browser restarts.
- **FR-005**: The system MUST redirect the guardian to an OTP screen when their account requires two-factor authentication.

**Authentication — School**

- **FR-006**: The system MUST allow school administrators to authenticate with institution code, email, and password.
- **FR-007**: The system MUST validate institution codes as 4–10 uppercase alphanumeric characters and reject non-conforming values with an inline error.
- **FR-008**: The system MUST record every school login attempt (success and failure) with timestamp, IP, and institution identifier.

**Guardian Mobile Dashboard**

- **FR-009**: The dashboard MUST display the guardian's first name in the header greeting.
- **FR-010**: The dashboard MUST display each linked child's name, grade, school, avatar, and wallet balance.
- **FR-011**: Wallet balance color MUST reflect: positive → green, zero → dark brand blue, negative → red.
- **FR-012**: The dashboard MUST include a Quick Access grid with four shortcuts: Mensalidades, Cantina, Loja, Boletos.
- **FR-013**: The dashboard MUST display a "A pagar este mês" list with payment description, amount, due date, and status badge per item.
- **FR-014**: Payment status MUST map to: paid → "Pago" (success), due ≤7 days → "Próximo" (warning), overdue → "Atrasado" (danger), due >7 days → "Em dia" (success).
- **FR-015**: Tapping a payment item MUST open a detail bottom sheet with a pay action.
- **FR-016**: The dashboard MUST display a recent activity feed capped at 5 items with a "Ver tudo" link when more exist.
- **FR-017**: The dashboard MUST support pull-to-refresh.

**Guardian Web Dashboard**

- **FR-018**: The web dashboard MUST display the hero child card with name, grade, school, avatar, and wallet balance.
- **FR-019**: The web dashboard MUST display a monthly financial summary with 3 key stats and a bar chart of the last 7 weeks.
- **FR-020**: The web dashboard MUST display a payments table with columns: description, due date, amount, status; and an empty state when no pending charges exist.
- **FR-021**: The web dashboard MUST display a "Próximos eventos" section alongside the recent activity feed.
- **FR-022**: The top navigation MUST be sticky and gain a shadow after 8px of vertical scroll.
- **FR-023**: The top navigation MUST collapse to a hamburger menu on viewports narrower than 1024px.

**School Web Dashboard**

- **FR-024**: The school dashboard MUST display 4 KPI cards: monthly revenue (BRL), delinquency rate (%), active student count, canteen ticket count — each with a period-over-period change indicator.
- **FR-025**: The school dashboard MUST display a revenue line chart for the last 6 months and a charge-status donut chart.
- **FR-026**: The school dashboard MUST display a charges table with columns: student, plan, due date, amount, status; sortable by student, due date, and amount; paginated at 10 rows per page.
- **FR-027**: The school dashboard MUST display a system activity feed that auto-refreshes every 30 seconds.
- **FR-028**: The school sidebar MUST be collapsible to icon-only mode, with the preference persisted across sessions.
- **FR-029**: Dashboard actions (export, edit, etc.) MUST be hidden or disabled for users without sufficient role permissions.
- **FR-030**: The school top bar MUST include a school switcher for users with access to multiple schools.

**Shared Design System**

- **FR-031**: All colors, typography sizes, spacing values, border radii, and shadows MUST derive from the shared token set.
- **FR-032**: All interactive web elements MUST have a visible focus indicator meeting WCAG 2.1 AA requirements.
- **FR-033**: All mobile tap targets MUST be at least 44×44 points.
- **FR-034**: All monetary values MUST use tabular numerals and pt-BR currency format (R$ 1.234,56).
- **FR-035**: All date values MUST use pt-BR locale and the America/Sao_Paulo timezone.
- **FR-036**: All user-visible strings MUST be externalized to i18n keys; no hardcoded text in component files.
- **FR-037**: Loading states MUST show skeleton shimmer placeholders; error states MUST show an error component with a retry action.
- **FR-038**: All animations MUST be disabled (transforms) or reduced to opacity-only when the user's system "reduce motion" preference is active.

### Key Entities

- **Guardian**: Authenticated parent/responsible; owns one or more linked children; has sessions on both mobile and web.
- **Child (Aluno)**: Enrolled student linked to a guardian; has a BRL wallet balance, grade, and school.
- **Payment / Charge (Cobrança)**: A financial obligation with amount, due date, status, and description.
- **Transaction (Atividade)**: A credit or debit event on a child's wallet with timestamp, description, and signed amount.
- **School (Instituição)**: Multi-tenant entity identified by a unique institution code.
- **School Staff**: Authenticated school employee with a role (viewer / staff / admin) that gates specific actions.
- **KPI Snapshot**: Monthly aggregated school metrics: revenue, delinquency rate, active students, canteen tickets.
- **System Event**: An auditable school activity (enrollment, contract, payment, error) with timestamp and type.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A guardian can complete the full mobile login → dashboard flow in under 30 seconds on a 4G connection.
- **SC-002**: The mobile dashboard reaches an interactive state in under 2 seconds on a 4G connection.
- **SC-003**: The guardian web login page achieves Largest Contentful Paint under 1.5 seconds at the 75th percentile.
- **SC-004**: All web dashboards achieve a Cumulative Layout Shift score below 0.1.
- **SC-005**: The compressed JavaScript bundle for each login screen does not exceed 120 KB; for each dashboard, 200 KB.
- **SC-006**: All 6 screens pass WCAG 2.1 AA automated contrast checks with zero failures.
- **SC-007**: All interactive elements on all web screens are reachable and operable with keyboard-only navigation.
- **SC-008**: All 6 screens render without horizontal scrollbar on viewports from 320px to 1536px wide.
- **SC-009**: 100% of shared design-system primitives (Button, Input, Card, Avatar, Badge, Toast) pass visual regression tests across all documented variants and states.
- **SC-010**: A school administrator can locate any specific charge using sort and pagination without reading more than 2 pages.

## Assumptions

- Users have existing accounts; registration and onboarding flows are out of scope.
- Authentication API contracts (`/auth/login`, `/auth/school-login`) will be provided by the BFF layer; mock data is used during initial development.
- The `@gfn/design-system` package already has working skeletons of Button, Input, Card, Avatar, Badge, and Toast; this feature completes their variants and adds Checkbox, Skeleton, EmptyState, and ErrorState.
- Push notifications on the guardian mobile app are out of scope for this release.
- Portuguese (pt-BR) is the only supported locale for v1; the i18n infrastructure will be bootstrapped as part of this feature.
- Dashboard data will use mock fixtures while backend API contracts are finalized; real API integration is a subsequent feature.
- Apple social login is required on iOS whenever Google login is present (App Store guidelines); both appear on guardian web regardless of device.
- The design tokens in the shared token file are final and will not change during this feature.
- The school web login does not offer social login; institutional credentials only.
- Visual regression tooling baseline will be established as part of this feature.
