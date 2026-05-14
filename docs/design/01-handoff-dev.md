# GFN — Handoff de Desenvolvimento (Telas Iniciais)

> Documento técnico para engenharia. Tradução prática da especificação de design em contratos de componente, tokens e regras de implementação.
> Escopo: **6 telas** (Login + Dashboard) × **3 apps** (`guardian-mobile`, `guardian-web`, `school-web`) compartilhando `@gfn/design-system`.
> Stack-alvo: React Native (Expo ~51, RN 0.74) para mobile; Next.js 15 App Router + React 19 para web; TypeScript em todo lugar; Tailwind v4 para web.

---

## 0. Estrutura do monorepo (real)

```
geffin-monorepo/
├── apps/
│   ├── api-laravel/          # API Core Laravel
│   ├── bff-guardian/         # BFF Guardian (NestJS)
│   ├── bff-school/           # BFF School (NestJS)
│   ├── ai-gateway/           # Python — LLM orchestration
│   ├── gateway/              # Python — API gateway
│   └── workers-go/           # Go — RabbitMQ consumers
├── frontends/
│   ├── guardian-mobile/      # React Native — app do responsável
│   │   └── src/screens/
│   │       ├── LoginScreen.tsx        ← já existe
│   │       └── DashboardScreen.tsx    ← a criar
│   ├── guardian-web/         # Next.js 15 — portal do responsável
│   │   └── src/app/
│   │       ├── login/page.tsx         ← já existe
│   │       └── dashboard/page.tsx     ← a criar
│   └── school-web/           # Next.js 15 — dashboard da escola
│       └── src/app/
│           ├── login/page.tsx         ← já existe
│           └── dashboard/page.tsx     ← a criar
└── shared/
    ├── design-system/        # @gfn/design-system
    │   └── src/
    │       ├── components/   ← Button, Input, Card, Avatar, Badge, Toast (já existem)
    │       ├── styles/       ← tokens.css (Tailwind v4 @theme), index.css
    │       ├── tokens/       ← a criar: constantes TS para RN + uso runtime
    │       └── lib/utils.ts
    ├── utils/                # utilitários compartilhados
    └── contracts/            # schemas de eventos e HTTP
```

**Regras de pacote:**
- `@gfn/design-system` exporta via três entrypoints:
  - `"."` — componentes React (web, DOM + Tailwind classes)
  - `"./styles"` — `src/styles/index.css` (importar no CSS do app junto de `@import "tailwindcss"`)
  - `"./tokens"` — `src/styles/tokens.css` (variáveis Tailwind v4 `@theme`)
  - `"./native"` — **a criar**: re-exports com StyleSheet RN (sem Tailwind classes)
- Tokens (cores, tipografia, radii, sombras) existem como **CSS custom properties** em `tokens.css`
  e serão espelhados como **constantes TS** em `src/tokens/*.ts` para uso no React Native e cálculos runtime.
- Nenhum app web importa Tailwind diretamente — todos consomem via `@import "@gfn/design-system/tokens"`.
- `shared/utils/` e `shared/contracts/` ainda sem conteúdo; `shared/icons/` não existe ainda.

---

## 1. Design Tokens

### 1.1 CSS custom properties — `shared/design-system/src/styles/tokens.css`

Já existe com os seguintes tokens em bloco `@theme` (Tailwind v4):

```css
/* Cores */
--color-brand-primary-{50,100,500,700,900}
--color-accent-green-{100,500,700}
--color-neutral-{0,50,100,200,400,600,900}
--color-semantic-{success,warning,danger,info}

/* Tipografia */
--font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;

/* Border radius */
--radius-sm:   0.375rem   /* 6px  — admin: inputs, botões secundários */
--radius-md:   0.5rem     /* 8px  — admin: cards, botões primários */
--radius-lg:   0.75rem    /* 12px — guardian: inputs, botões */
--radius-xl:   1rem       /* 16px — guardian: cards */
--radius-full: 9999px

/* Sombras */
--shadow-sm: 0 1px 2px rgb(15 23 42 / 0.06)
--shadow-md: 0 4px 12px rgb(15 23 42 / 0.08)
--shadow-lg: 0 12px 32px rgb(15 23 42 / 0.12)
```

> **Tailwind v4:** as classes utilitárias geradas seguem o nome da variável. Exemplos:
> `--color-brand-primary-700` → `bg-brand-primary-700` · `text-brand-primary-700`
> `--color-neutral-900` → `text-neutral-900`
> `--radius-lg` → `rounded-lg`
> `--shadow-md` → `shadow-md`

### 1.2 Constantes TS — `shared/design-system/src/tokens/` (a criar)

Espelham as CSS variables para uso em: (a) StyleSheets React Native, (b) qualquer cálculo runtime.

**`src/tokens/colors.ts`**
```ts
export const colors = {
  brand: {
    primary50:  '#EFF6FF',
    primary100: '#DBEAFE',
    primary500: '#3B82F6',
    primary700: '#1E40AF',
    primary900: '#0B2E5C',
  },
  accent: {
    green100: '#D1FAE5',
    green500: '#10B981',
    green700: '#047857',
  },
  neutral: {
    0:   '#FFFFFF',
    50:  '#F8FAFC',
    100: '#F1F5F9',
    200: '#E2E8F0',
    400: '#94A3B8',
    600: '#475569',
    900: '#0F172A',
  },
  semantic: {
    success: '#10B981',
    warning: '#F59E0B',
    danger:  '#EF4444',
    info:    '#3B82F6',
  },
} as const;
```

**`src/tokens/typography.ts`**
```ts
export const typography = {
  'display-lg':   { size: 32, weight: '700', lineHeight: 40 },
  'heading-xl':   { size: 24, weight: '700', lineHeight: 32 },
  'heading-lg':   { size: 20, weight: '600', lineHeight: 28 },
  'heading-md':   { size: 16, weight: '600', lineHeight: 24 },
  'body-lg':      { size: 16, weight: '400', lineHeight: 24 },
  'body-md':      { size: 14, weight: '400', lineHeight: 20 },
  'body-sm':      { size: 13, weight: '400', lineHeight: 18 },
  'label-md':     { size: 14, weight: '500', lineHeight: 20 },
  'numeric-hero': { size: 36, weight: '700', lineHeight: 44 },
  'numeric-lg':   { size: 20, weight: '600', lineHeight: 28 },
} as const;
```

> **Web:** `font-variant-numeric: tabular-nums` em todo valor monetário.
> **RN:** `fontVariant={['tabular-nums']}` no `<Text>`.

**`src/tokens/spacing.ts`**
```ts
export const spacing = {
  0: 0, 1: 4, 2: 8, 3: 12, 4: 16, 5: 20, 6: 24, 8: 32, 10: 40, 12: 48, 16: 64,
} as const;
```

**`src/tokens/radii.ts`**
```ts
// Espelha --radius-* de tokens.css
export const radii = {
  sm:   6,     // admin: inputs, botões secundários
  md:   8,     // admin: cards, botões primários
  lg:   12,    // guardian: inputs, botões
  xl:   16,    // guardian: cards
  full: 9999,
} as const;
```

**`src/tokens/shadows.ts`**
```ts
// shadowsRN: equivalentes iOS + Android para --shadow-*
export const shadowsRN = {
  sm: { shadowColor: '#0F172A', shadowOpacity: 0.06, shadowRadius: 2,  shadowOffset: { width: 0, height: 1  }, elevation: 1  },
  md: { shadowColor: '#0F172A', shadowOpacity: 0.08, shadowRadius: 12, shadowOffset: { width: 0, height: 4  }, elevation: 4  },
  lg: { shadowColor: '#0F172A', shadowOpacity: 0.12, shadowRadius: 32, shadowOffset: { width: 0, height: 12 }, elevation: 12 },
} as const;
```

### 1.3 Tailwind v4 — como consumir nos apps web

Em `frontends/guardian-web/src/app/globals.css` e equivalente em `school-web`:

```css
@import "tailwindcss";
@import "@gfn/design-system/tokens";   /* injeta o @theme com todos os tokens */
@import "@gfn/design-system/styles";   /* estilos base do design system */
```

Não existe `tailwind.config.ts` com `theme.extend` — no v4 toda customização vai em CSS. Ambos os apps web já têm `postcss.config.mjs` com `@tailwindcss/postcss`.

---

## 2. Convenções de tom por app

Paleta idêntica entre os três apps; diferença de "tom" via convenções de uso de classes:

| Classe semântica | App | Implementação (Tailwind v4) |
|---|---|---|
| `.surface-warm` | guardian-* | `bg-gradient-to-br from-brand-primary-50 to-brand-primary-100 rounded-xl` |
| `.surface-pro` | school-web | `bg-neutral-0 rounded-md shadow-sm border border-neutral-200` |
| `.text-positive` | todos | `text-accent-green-700 tabular-nums` |
| `.text-negative` | todos | `text-semantic-danger tabular-nums` |
| `.text-money` | todos | `tabular-nums` |

**Regra de revisão:** PR que usar `bg-brand-primary-700` como fundo decorativo grande em `school-web` deve ser rejeitado — `school-web` usa `brand-primary-700` apenas em estados (link ativo, botão, barra lateral), nunca como background de painel.

---

## 3. Componentes compartilhados — Contratos

> Os componentes abaixo já têm esqueleto em `shared/design-system/src/components/`. Os contratos aqui definem o comportamento e aparência esperados.

### 3.1 `<Button>`

**Props:**
```ts
type ButtonProps = {
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';        // 32 / 40 / 48px de altura
  fullWidth?: boolean;
  loading?: boolean;                 // spinner, mantém largura, disabled implícito
  leftIcon?: LucideIcon;
  rightIcon?: LucideIcon;
  disabled?: boolean;
  onPress?: () => void;              // RN
  onClick?: () => void;              // web
  children: ReactNode;
  'aria-label'?: string;             // obrigatório se children for só ícone
};
```

**Tokens por variante:**

| Variante | Background | Texto | Borda | Hover (web) | Active | Disabled |
|---|---|---|---|---|---|---|
| `primary` | `brand-primary-700` | `neutral-0` | — | `brand-primary-900` | `brand-primary-900` op.90 | `neutral-200` bg / `neutral-400` text |
| `secondary` | `neutral-0` | `brand-primary-700` | 1px `neutral-200` | `neutral-50` | `neutral-100` | bg `neutral-50` / text `neutral-400` |
| `ghost` | transparent | `brand-primary-700` | — | `brand-primary-50` | `brand-primary-100` | text `neutral-400` |
| `danger` | `semantic-danger` | `neutral-0` | — | darken 8% | darken 12% | bg `neutral-200` |

**Geometria:**
- `sm`: 32h × padding 12px × text `body-sm` × gap ícone/texto 6px
- `md`: 40h × padding 16px × text `body-md` × gap 8px
- `lg`: 48h × padding 20px × text `body-lg` × gap 8px
- Border-radius: **guardian** `lg` (`--radius-lg`, 12px) · **admin** `md` (`--radius-md`, 8px) — controlado por prop `appearance: 'warm' | 'pro'` (default via Provider).

**Estados:**
- **Loading**: substitui children por `<Spinner size={size === 'sm' ? 14 : 18} />`, mantém largura via `min-width`. `aria-busy="true"`. Bloqueia clique.
- **Focus visível** (a11y obrigatório): `outline: 2px solid brand-primary-500; outline-offset: 2px;`
- **Pressed (RN)**: opacity 0.85 via `Pressable`.

**Tap target mínimo:** 44×44 (iOS HIG) / 48×48 (Material). Para `size=sm`, área tocável estendida via padding invisível.

---

### 3.2 `<Input>` / `<TextField>`

**Props:**
```ts
type InputProps = {
  label: string;                      // obrigatório (label flutuante)
  value: string;
  onChange: (v: string) => void;      // web
  onChangeText?: (v: string) => void; // RN
  leftIcon?: LucideIcon;
  rightSlot?: ReactNode;              // ex: toggle de visibilidade da senha
  error?: string;
  helperText?: string;
  type?: 'text' | 'email' | 'password' | 'numeric';
  autoComplete?: string;
  disabled?: boolean;
  required?: boolean;
  maxLength?: number;
};
```

**Geometria:**

| App | Altura | Radius token | Padding H | Tipografia | Borda default | Borda focus |
|---|---|---|---|---|---|---|
| guardian-mobile | 56 | `lg` (12px) | 16 | `body-md` | `neutral-200` | `brand-primary-500` 2px |
| guardian-web | 48 | `lg` (12px) | 16 | `body-lg` | `neutral-200` | `brand-primary-500` 2px |
| school-web | 44 | `sm` (6px) | 12 | `body-md` | `neutral-200` | `brand-primary-500` 2px |

**Label flutuante:**
- Repouso: label dentro do input, `body-md`, `neutral-400`.
- Foco/preenchido: label sobe ao topo, escala 0.85, cor `neutral-600`. Transição `transform 150ms ease-out`.
- A11y: usar `<label htmlFor>` real — animação é visual, não substitui semântica.

**Estado de erro:**
- Borda 1px `semantic-danger`, mensagem `body-sm` `semantic-danger` abaixo, ícone `AlertCircle` 16px à esquerda da mensagem.
- `aria-invalid="true"`, `aria-describedby` → id da mensagem de erro.

**Password:**
- `rightSlot` = ícone `Eye` / `EyeOff`, `aria-label="Mostrar senha"` / `"Ocultar senha"`.
- `autoComplete="current-password"` no login.

---

### 3.3 `<Card>`

```ts
type CardProps = {
  variant?: 'default' | 'hero' | 'kpi';
  appearance?: 'warm' | 'pro';  // herda do Provider
  padding?: 'md' | 'lg';        // 16 | 24
  elevation?: 'none' | 'sm' | 'md';
  children: ReactNode;
};
```

| Variante | Background | Radius token | Sombra/Borda |
|---|---|---|---|
| `default` warm | `neutral-0` | `xl` (16px) | borda 1px `neutral-200` ou `shadow-sm` |
| `default` pro | `neutral-0` | `md` (8px) | `shadow-sm` |
| `hero` warm | gradient `brand-primary-50→brand-primary-100` 135° | `xl` (16px) | `shadow-md` |
| `kpi` pro | `neutral-0` | `md` (8px) | `shadow-sm` |

---

### 3.4 `<Avatar>`

```ts
type AvatarProps = {
  src?: string;
  name: string;    // iniciais e alt
  size?: 24 | 32 | 40 | 56;
};
```

- Fallback: iniciais (primeiras letras de cada palavra, máx 2), texto `brand-primary-700` peso 600, bg `brand-primary-100`.
- Quando `src` falha, fallback automático.
- `alt` = `name`.

---

### 3.5 `<Badge>` / `<Pill>`

```ts
type BadgeProps = {
  variant: 'neutral' | 'success' | 'warning' | 'danger' | 'info';
  size?: 'sm' | 'md';
  showDot?: boolean;   // bullet 8px à esquerda
  children: ReactNode;
};
```

| Variante | Bg | Text | Dot |
|---|---|---|---|
| `success` | `accent-green-100` | `accent-green-700` | `semantic-success` |
| `warning` | `#FEF3C7` | `#92400E` | `semantic-warning` |
| `danger` | `#FEE2E2` | `#991B1B` | `semantic-danger` |
| `info` | `brand-primary-100` | `brand-primary-700` | `semantic-info` |
| `neutral` | `neutral-100` | `neutral-600` | `neutral-400` |

- `border-radius: full`. Padding: `sm` = 2/8, `md` = 4/12. Texto `body-sm` peso 500.

---

### 3.6 `<Toast>`

```ts
type ToastProps = {
  variant: 'success' | 'warning' | 'danger' | 'info';
  title?: string;
  description: string;
  duration?: number;   // default 4000ms
  action?: { label: string; onPress: () => void };
};
```

- **Posição web**: `position: fixed; top: 24px; right: 24px; z-index: 1000`. Empilhamento vertical com gap 12px.
- **Posição mobile**: topo, abaixo da safe-area. Slide-in de cima (250ms `ease-out`), slide-out (200ms `ease-in`).
- Auto-dismiss em 4s, pausa no hover (web) ou touch (mobile).
- A11y: `role="status"` (success/info) ou `role="alert"` (danger), `aria-live` correspondente.
- Lib: **sonner** (web) e **react-native-toast-message** (RN), ambas wrappadas em `@gfn/design-system`.

---

## 4. Mobile — App do Responsável (`frontends/guardian-mobile`)

**Plataformas:** iOS 15+ / Android 10+ (API 29+). Expo ~51, RN 0.74, React 18.2.
**Frame de referência:** 390×844 (iPhone 13/14). Layout responsivo via `Dimensions` + safe area.

### 4.1 Login (`frontends/guardian-mobile/src/screens/LoginScreen.tsx`)

**Layout (top → bottom, padding lateral 24):**

| # | Elemento | Spacing | Componente | Notas |
|---|---|---|---|---|
| 1 | Logo GFN | `mt: 64 + safeTop` | `<Logo height={40} />` | Centralizado |
| 2 | Título "Bem-vindo de volta 👋" | `mt: 48` | `<Text variant="display-lg" color="neutral-900">` | |
| 3 | Subtítulo | `mt: 8` | `<Text variant="body-md" color="neutral-600">` | Máx 2 linhas |
| 4 | Input e-mail | `mt: 40` | `<Input label="E-mail" leftIcon={Mail} autoComplete="email" type="email" />` | `autoCapitalize="none"` |
| 5 | Input senha | `mt: 16` | `<Input label="Senha" leftIcon={Lock} type="password" rightSlot={<EyeToggle/>} autoComplete="current-password" />` | `secureTextEntry` toggleável |
| 6 | Link "Esqueci minha senha" | `mt: 12`, à direita | `<TextLink>` | → `ForgotPasswordScreen` (já existe) |
| 7 | Botão Entrar | `mt: 24` | `<Button variant="primary" size="lg" fullWidth loading={isSubmitting}>` | |
| 8 | Divisor | `mt: 32` | `<Divider label="ou continue com" />` | Traço `neutral-200`, texto `body-sm neutral-400` |
| 9 | Botões sociais | `mt: 16`, grid 2 col gap 12 | `<Button variant="secondary" size="md" leftIcon={GoogleIcon}>` e `AppleIcon` | iOS: Apple obrigatório se Google presente |
| 10 | Rodapé | `position: absolute; bottom: 24 + safeBottom` | `<Text>Não tem conta? <Link>Solicitar acesso</Link></Text>` | Sempre visível |

**Comportamento de teclado:**
- `KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'}`.
- Ao focar input, scroll garante 16px entre input e topo do teclado.
- `Enter` no e-mail move foco para senha; `Enter` na senha submete.

**Microinterações:**
- Erro de credencial: shake horizontal (`translateX -8 → 8 → -4 → 4 → 0`, 350ms). Usar `react-native-reanimated`.
- Toast vermelho topo com mensagem do servidor (mapear códigos HTTP → strings PT-BR).

**Estados:**

| Estado | Comportamento |
|---|---|
| Inicial | Botão Entrar disabled até e-mail válido + senha ≥ 6 chars |
| Loading | Spinner branco no botão, inputs disabled, social oculto |
| Erro 401 | Toast + shake + foco na senha + limpa senha |
| Erro de rede | Toast "Sem conexão. Tente novamente." Não limpa campos |
| 2FA exigido | Navega para `OTPScreen`, passa session token |

**A11y:**
- `accessibilityLabel` em todos os botões.
- Logo: `accessibilityRole="image"` `accessibilityLabel="GFN"`.
- Ordem de foco: email → password → toggle senha → esqueci → entrar → google → apple → solicitar acesso.
- `allowFontScaling` respeita `accessibilityFontSize` do sistema.

---

### 4.2 Dashboard (`frontends/guardian-mobile/src/screens/DashboardScreen.tsx`)

**Estrutura:**

```tsx
<SafeAreaView edges={['top']}>
  <Header />                          {/* 56h */}
  <ScrollView contentInsetAdjustmentBehavior="automatic">
    <ChildCarousel children={kids} /> {/* hero */}
    <QuickAccessGrid />               {/* 4 atalhos */}
    <DueThisMonthList />              {/* cobranças */}
    <RecentActivityList />            {/* últimos eventos */}
  </ScrollView>
  <TabBar />                          {/* 56h + safe bottom */}
</SafeAreaView>
```

#### Header
- Altura 56, padding H 24, padding V 16.
- Esquerda: `<Text variant="heading-lg">Olá, {firstName} 👋</Text>` (truncate 1 linha).
- Direita: `<IconButton icon={Bell} badge={unreadCount} />` + `<IconButton icon={Settings} />`. Gap 16.
- Badge de notificação: círculo 8px `semantic-danger`, oculto quando `unreadCount === 0`.

#### Card do filho (hero)

`<Card variant="hero" appearance="warm" padding="lg">` com radius `xl` (16px).

```
<Row gap={12}>
  <Avatar size={56} src={kid.avatar} name={kid.name} />
  <Column flex={1}>
    <Text variant="heading-lg">{kid.name}</Text>
    <Text variant="body-sm" color="neutral-600">{kid.grade} · {kid.school}</Text>
  </Column>
</Row>
<Text variant="label-md" color="neutral-600" mt={16}>Saldo da carteira</Text>
<Text variant="numeric-hero" color={balanceColor} mt={4}>{formatBRL(kid.balance)}</Text>
<Row gap={8} mt={16}>
  <Button variant="secondary" size="md" leftIcon={Plus}>Recarregar</Button>
  <Button variant="secondary" size="md">Ver extrato</Button>
</Row>
```

Cor do saldo: `balance > 0` → `accent-green-700` · `balance === 0` → `brand-primary-900` · `balance < 0` → `semantic-danger`.

Múltiplos filhos: `<FlatList horizontal pagingEnabled snapToInterval={cardWidth + 16} decelerationRate="fast" />` + dots indicadores (8px, gap 6, ativo `brand-primary-700`, inativo `neutral-200`).

Caso vazio: `<EmptyState illustration="family" title="Nenhum aluno vinculado" description="Solicite à escola que vincule o aluno ao seu CPF." />`.

#### Quick Access Grid
- `<FlatList numColumns={4} columnWrapperStyle={{ gap: 12 }} />`, mt 24.
- Item: `<Pressable>` 80×80, radius 12 (`--radius-lg`), borda 1px `neutral-200`, padding 12. Ícone 24 `brand-primary-700` em cima, label `body-sm` `neutral-900` embaixo (2 linhas, truncate).
- Itens: Mensalidades, Cantina, Loja, Boletos.

#### Lista "A pagar este mês"
- Header: `<SectionTitle icon={ClipboardList}>A pagar este mês</SectionTitle>` (mt 32).
- Item card: padding 16, radius 12, borda 1px `neutral-200`, mb 12.

```
<Row justifyContent="space-between">
  <Text variant="body-md" weight={500}>{item.description}</Text>
  <Text variant="numeric-lg">{formatBRL(item.amount)}</Text>
</Row>
<Row justifyContent="space-between" mt={6}>
  <Text variant="body-sm" color="neutral-600">{formatDue(item.dueDate)}</Text>
  <Badge variant={statusVariant} showDot>{statusLabel}</Badge>
</Row>
```

Mapeamento de status: `paid` → success "Pago" · `dueSoon` (≤7d) → warning "Próximo" · `overdue` → danger "Atrasado" · `onTime` (>7d) → success "Em dia".

Tap → bottom sheet com detalhes + "Pagar".

#### Atividade recente
- mt 32. Lista plana, cada linha 56h.

```
<Row alignItems="center">
  <Dot color={isCredit ? 'accent-green-500' : 'semantic-danger'} size={8} />
  <Column flex={1} ml={12}>
    <Text variant="body-md">{item.description}</Text>
    <Text variant="body-sm" color="neutral-400">{formatRelative(item.timestamp)}</Text>
  </Column>
  <Text variant="numeric-lg" color={isCredit ? 'accent-green-700' : 'neutral-900'}>{signedAmount}</Text>
</Row>
```

Limite 5 itens; `<Button variant="ghost">Ver tudo</Button>` se houver mais.

#### Tab bar
- Customização do `@react-navigation/bottom-tabs`.
- 56h + safe-area-bottom. Bg `neutral-0`, `borderTopWidth: 1`, `borderTopColor: neutral-200`.
- 4 tabs: Início (`Home`), Pagar (`CreditCard`), Histórico (`BarChart3`), Perfil (`User`).
- Ativa: ícone + label `brand-primary-700`. Inativa: `neutral-400`.

**Pull-to-refresh:** `<RefreshControl tintColor={colors.brand.primary700} />`.

**Skeletons (primeira carga):**
- Hero card: bloco radius 16, shimmer gradient 1500ms loop.
- Quick access: 4 blocos 80×80.
- Listas: 2 linhas-skeleton cada.

**Erro global:** `<ErrorState icon={AlertTriangle} title="Não conseguimos carregar agora" action={{ label: 'Tentar de novo', onPress: refetch }} />`.

---

## 5. Web — Portal do Responsável (`frontends/guardian-web`)

**Stack:** Next.js 15, React 19, TypeScript, Tailwind v4.

**Breakpoints:**

| Nome | Min-width | Uso |
|---|---|---|
| `sm` | 640px | — |
| `md` | 768px | tablet |
| `lg` | 1024px | desktop padrão |
| `xl` | 1280px | container max 1200px |
| `2xl` | 1536px | conteúdo permanece 1200px |

### 5.1 Login (`frontends/guardian-web/src/app/login/page.tsx`)

**Layout:** grid 2 colunas 50/50, `min-h-screen`. Em `<lg`, painel esquerdo oculto.

| Painel | Largura | Background | Padding | Conteúdo |
|---|---|---|---|---|
| Esquerdo | 50% (`lg:block hidden`) | gradient `from-brand-primary-700 to-brand-primary-900` 135° | 64px | Ilustração SVG + frase (`heading-lg` branca, max-w 360, mt 48) |
| Direito | 50% (full em `<lg`) | `neutral-0` | 64px | Conteúdo centralizado vertical, `max-w-md` |

**Ilustração:** `frontends/guardian-web/public/illustrations/login-family.svg`. Fallback 404 = sem imagem (não quebra layout).

**Form fields:** idêntico ao mobile, exceto:
- Altura input 48 (vs 56 mobile).
- Linha "Lembrar de mim / Esqueci minha senha": `<Row justify-between items-center>` com `<Checkbox>` e `<Link>`. mt 16.
- Botões sociais: `flex-1` cada, gap 12.

**Validação:**
- Client-side: **Zod** — e-mail `.email()`, senha `.min(6)`.
- Server action Next.js 15: `'use server'`. Retorna `{ error?: string }`.
- Em erro: foco volta para o campo problemático via `useFormState`.

**A11y:**
- `<form>` real com `action={loginAction}` (funciona sem JS).
- `<label htmlFor>` em cada input.
- Botões sociais com `aria-label` explícito ("Entrar com Google", "Entrar com Apple ID").

---

### 5.2 Dashboard (`frontends/guardian-web/src/app/dashboard/page.tsx`)

**Layout:**

```tsx
<>
  <TopNav />   {/* sticky, 64h */}
  <main className="max-w-[1200px] mx-auto px-8 py-8 space-y-6">
    <PageHeader />
    <HeroRow />      {/* grid 3/5 + 2/5 */}
    <DueTable />
    <BottomRow />    {/* grid 3/5 + 2/5 */}
  </main>
</>
```

#### TopNav
- `<header className="sticky top-0 z-40 bg-neutral-0 border-b border-neutral-200 h-16">`.
- Sombra `shadow-sm` aparece só após scroll > 8px (IntersectionObserver ou `useScroll`).
- Esquerda: `<Logo height={32} />`.
- Centro: `<nav>` — Início, Pagamentos, Cantina, Loja, Histórico. Ativo: `text-brand-primary-700` peso 600, `border-b-2 border-brand-primary-700`. Inativo: `text-neutral-600` peso 500.
- Direita: `<NotificationBell />` + `<UserMenu>` (avatar 32 + nome + chevron).
- Em `<lg`: links entram em hambúrguer.

#### PageHeader
```tsx
<h1 className="text-display-lg text-neutral-900">Olá, {firstName} 👋</h1>
<p className="text-body-lg text-neutral-600 mt-2">Aqui está um resumo da vida escolar dos seus filhos</p>
```

#### HeroRow
- `<div className="grid grid-cols-1 lg:grid-cols-5 gap-6">`.
- ChildCard `lg:col-span-3`; MonthSummary `lg:col-span-2`.
- **ChildCard**: idêntico ao mobile, padding 24, radius `xl`, gradient warm. Multi-filho → tabs no topo do card (em vez de carousel).
- **MonthSummary** (`Card variant="default" appearance="warm" padding="lg"`):
  - Título `heading-md` "Resumo do mês" + `<DropdownMenu>` de mês.
  - 3 stats (ícone + label + valor `numeric-lg`).
  - Gráfico de barras: `recharts <BarChart>` 7 últimas semanas, barras `accent-green-500`, sem grid/axis labels, tooltip on hover.

#### DueTable
- `<Card variant="default" padding="none">` (overflow hidden).
- Barra topo: "A pagar este mês" `heading-md` + `<Button variant="ghost" size="sm">Ver tudo</Button>` — padding 16/24.
- `<table className="w-full">`:
  - `<thead>`: bg `neutral-50`, 48h, texto `label-md neutral-600`, descrição/vencimento left, valor/status right.
  - `<tbody>`: linhas 64h, `border-t border-neutral-200`, hover `bg-neutral-50`, cursor-pointer.
  - Colunas: Descrição | Vencimento | Valor (tabular-nums, right) | Status (badge, right).
- Empty state: `<EmptyState icon={CheckCircle} title="Tudo em dia!" description="Você não tem cobranças neste mês." />`.

#### BottomRow
- `<div className="grid grid-cols-1 lg:grid-cols-5 gap-6">`.
- "Atividade recente" `lg:col-span-3`: lista com bullets coloridos, máx 5, link "Ver tudo".
- "Próximos eventos" `lg:col-span-2`: ícone `Calendar brand-primary-700` 20px + título + data (`28 de maio · 19h`), divisor entre itens, link "Ver agenda".

**Loading:** Server Component faz fetch inicial; Suspense boundary em cada bloco com skeleton tipado.

**Responsividade:**

| Breakpoint | Mudanças |
|---|---|
| ≥1024px | Layout 3/5–2/5, tabela completa, top nav full |
| 768–1024px | Hero empilha (child cima, resumo baixo), tabela completa, hambúrguer |
| <768px | Layout mobile-first (sem tab bar) |

---

## 6. Web — Dashboard da Escola (`frontends/school-web`)

**Stack:** mesma do guardian-web. Tom `pro` via `<AppearanceProvider appearance="pro">` no layout root.

### 6.1 Login (`frontends/school-web/src/app/login/page.tsx`)

**Layout:** centralizado, sem split.

```tsx
<main className="min-h-screen bg-neutral-50 flex flex-col items-center justify-center px-4">
  <BrandHeader />   {/* "GFN | Administrativo" */}
  <Card variant="default" appearance="pro" elevation="md" className="w-[440px] p-10">
    <h1 className="text-heading-xl">Acesso administrativo</h1>
    <p className="text-body-md text-neutral-600 mt-2 mb-8">Entre com as credenciais da escola</p>
    <form className="flex flex-col gap-4" action={schoolLoginAction}>
      <Input label="Código da instituição" autoComplete="organization" required />
      <Input label="E-mail" type="email" autoComplete="email" required />
      <Input label="Senha" type="password" autoComplete="current-password" required />
      <Checkbox label="Manter conectado" />
      <Button variant="primary" size="md" fullWidth type="submit" loading={pending}>Entrar</Button>
    </form>
    <div className="mt-8 pt-4 border-t border-neutral-100 flex items-center gap-2 text-body-sm text-neutral-400">
      <Lock size={16} /> Sua sessão é protegida e auditada
    </div>
  </Card>
  <FooterLinks />   {/* Esqueci · Suporte */}
  <Version />       {/* v1.0.0 · GFN © 2026 */}
</main>
```

**Diferenças intencionais vs guardian-web:**
- `rounded-sm` (6px) em inputs e botões — radius `sm` em vez de `lg`.
- Sem ilustração, sem gradient, sem emoji, sem login social.
- Campo "Código da instituição" obrigatório (multi-tenancy explícita). Regex: `/^[A-Z0-9]{4,10}$/`.
- Mensagem de segurança ao final do card.
- Header: logo + `" | Administrativo"` em `heading-md neutral-600`, separados por barra vertical 1px `neutral-200`.
- Auditoria: backend registra tentativas (sucesso e falha) com timestamp, IP e instituição. Frontend envia header `X-Client-Version`.

---

### 6.2 Dashboard (`frontends/school-web/src/app/dashboard/page.tsx`)

**Layout root:**

```tsx
<div className="flex min-h-screen bg-neutral-50">
  <Sidebar />   {/* 240px expandido, 64px colapsado */}
  <div className="flex-1 flex flex-col">
    <TopBar />  {/* 64h */}
    <main className="p-8 space-y-6">
      <PageHeader />
      <KpiGrid />
      <ChartsRow />
      <ChargesTable />
      <SystemActivity />
    </main>
  </div>
</div>
```

#### Sidebar
- `<aside className="bg-neutral-0 border-r border-neutral-200 w-60 flex flex-col">`.
- Logo: container 64h, alinhado com TopBar.
- `<SidebarItem>`: 44h, padding 12/16, `body-md text-neutral-600`, ícone 20px `mr-3`.
  - Ativo: `bg-brand-primary-50 text-brand-primary-700 font-semibold border-l-[3px] border-brand-primary-700`.
  - Hover: `bg-neutral-50`.
- Grupos: principal (Visão geral, Alunos, Contratos, Cobranças, Cantina, Lojinha, Relatórios) + operacional (Configurações, Equipe), separados por `<hr className="border-neutral-100 my-2" />`.
- Toggle collapse: `<Button variant="ghost" size="sm" />` no rodapé; persistir em `localStorage('sidebar-collapsed')`.

#### TopBar
- `<header className="sticky top-0 z-30 h-16 bg-neutral-0 border-b border-neutral-200 px-8 flex items-center justify-between">`.
- Esquerda: `<h1 className="text-heading-lg">{pageTitle}</h1>` (via contexto de rota).
- Direita: `<Bell />`, `<Settings />`, `<Avatar size={32} />`, `<SchoolSwitcher />` (dropdown multi-escola).

#### PageHeader
```tsx
<div className="flex items-center justify-between">
  <h1 className="text-heading-xl">{title}</h1>
  <div className="flex items-center gap-3">
    <MonthPicker />
    <Button variant="secondary" size="md" leftIcon={Download}>Exportar</Button>
  </div>
</div>
```

Exportar disponível apenas para role `staff+`.

#### KpiGrid (4 cards)
- `<div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">`.
- Cada `<Card variant="kpi" padding="md">` (padding 20):

```tsx
<Text variant="label-md" color="neutral-600">{label}</Text>
<Text variant="numeric-hero" color="neutral-900" className="mt-2">{value}</Text>
<Row className="mt-3 gap-1.5 items-center">
  <Icon size={12} color={positive ? 'accent-green-700' : 'semantic-danger'} />
  <Text variant="body-sm" color={positive ? 'accent-green-700' : 'semantic-danger'} weight={500}>
    {percentage}
  </Text>
  <Text variant="body-sm" color="neutral-400">{comparison}</Text>
</Row>
```

KPIs: Receita do mês (BRL), Inadimplência (%), Alunos ativos (count), Tickets cantina (count).
API: `GET /admin/dashboard/kpis?period={YYYY-MM}`.

#### ChartsRow
- `<div className="grid grid-cols-1 lg:grid-cols-5 gap-4">`.
- "Receita últimos 6 meses" `lg:col-span-3`: `<Card padding="lg">` + `<LineChart>` recharts.
  - Linha: stroke `brand-primary-700`, strokeWidth 2, dot ativo.
  - Área: fill `brand-primary-100` opacity 0.4.
  - Eixo Y: BRL com sufixo (`R$ 100k`). Eixo X: meses abreviados.
  - Tooltip custom com valor cheio.
- "Status das cobranças" `lg:col-span-2`: `<Card padding="lg">` + `<DonutChart>`.
  - Slices: Pagas (`accent-green-500`), Em aberto (`semantic-warning`), Atrasadas (`semantic-danger`).
  - Legenda à direita: bullet + label + %.
  - Centro do donut: total absoluto `numeric-lg`.

#### ChargesTable
- `<Card padding="none">`.
- Header: "Cobranças" `heading-md` + `<Link className="text-brand-primary-700 ...">Ver todas ↗</Link>`, padding 20/16.
- Tabela:
  - `<thead>`: bg `neutral-50`, 44h, padding-x 20, `label-md neutral-600`.
  - `<tbody>`: 56h, `border-t border-neutral-100`, hover `bg-neutral-50`, cursor-pointer → detalhe da cobrança.
  - Colunas: Aluno | Plano | Vencimento | Valor (tabular-nums, right) | Status (badge com dot, right).
  - Limite 10 linhas; `<Pagination />` no rodapé se >10.
  - Sort por colunas (Aluno, Vencimento, Valor): `ChevronUp/Down` no header.

#### SystemActivity
- `<Card padding="md">` full-width.
- Título `heading-md` + `<Button variant="ghost" size="sm">Ver log completo</Button>`.
- Lista (máx 8):

```tsx
<Row className="items-center gap-2 py-2">
  <Dot color={eventColor[event.type]} size={6} />
  <Text variant="body-md" className="flex-1">{event.description}</Text>
  <Text variant="body-sm" color="neutral-400">{formatTime(event.timestamp)}</Text>
</Row>
```

Cores por tipo: `enrollment` → `brand-primary-500` · `contract` → `accent-green-500` · `payment` → `semantic-success` · `error` → `semantic-danger`.

Auto-refresh: SWR `refreshInterval: 30_000`.

**Responsividade:**

| Breakpoint | Mudanças |
|---|---|
| ≥1280px | KPIs 4 colunas, charts 3/5–2/5 |
| 1024–1280px | KPIs 2×2, charts 3/5–2/5 |
| 768–1024px | KPIs 2×2, charts empilhados |
| <768px | Sidebar vira drawer off-canvas (`<Sheet>`), KPIs 1 coluna |

**Permissões:** sidebar e ações respeitam role `viewer` / `staff` / `admin`. HOC `<RequireRole role="staff">` ou hook `useCan('staff')`.

---

## 7. Catálogo de estados (todas as telas)

| Estado | Quando | Padrão visual | Componente |
|---|---|---|---|
| Loading inicial | Primeira carga | Skeleton shimmer 1500ms (`neutral-100→neutral-200`) | `<Skeleton>` |
| Loading inline | Refetch, ação | Spinner pequeno, mantém layout | `<Spinner size={16}>` |
| Empty | Lista sem itens | Ilustração + título + descrição + ação opcional | `<EmptyState>` |
| Erro de carregamento | Fetch falhou | Ícone alerta + título + "Tentar novamente" | `<ErrorState>` |
| Erro de validação | Form inválido | Borda vermelha + mensagem abaixo | `<Input error>` |
| Erro de servidor | Mutation falhou | Toast `danger` 6s, ação "Tentar de novo" | `<Toast>` |
| Sucesso | Mutation OK | Toast `success` 4s | `<Toast>` |
| Offline | Sem conexão | Banner fixo topo "Você está offline. Tentando reconectar..." | `<OfflineBanner>` |
| Permissão negada | 403 | Cadeado + "Você não tem acesso a esta área" | `<ForbiddenState>` |
| 404 | Rota inexistente | "Página não encontrada" + link "Voltar ao início" | `<NotFoundPage>` |

---

## 8. Edge cases catalogados

### Conteúdo
- **Nome longo** (>30 chars): truncar com `...`, tooltip completo (web) ou modal de detalhe (mobile).
- **Saldo grande** (>R$ 10.000): garantir `tabular-nums`. Em telas <360px, reduzir `numeric-hero` de 36 para 32px.
- **Valor negativo**: prefixo "−" (não hífen `-`), cor `semantic-danger`. Nunca parênteses.
- **Sem filhos**: ver empty state na seção 4.2.
- **Múltiplos filhos (>5)**: carousel com paginação (mobile), tabs scrolláveis (web).

### Internacionalização
- Strings PT-BR (default). Estrutura pronta para i18n: `next-intl` (web), `i18n-js` (RN). Não hardcode strings — usar `t('login.title')`.
- Datas: `Intl.DateTimeFormat('pt-BR')`. Valores: `Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })`.
- Plurais ICU: `{count, plural, one {1 cobrança} other {# cobranças}}`.

### Conexões lentas
- Inputs e botões nunca dependem de network sync inicial.
- Dashboards: skeleton até 3s; estado parcial conforme dados chegam (requests paralelos com Suspense).
- Timeout global 30s; toast de erro com retry.

### Dados ausentes
- Avatar sem `src` → iniciais (automático).
- Sem atividade recente → seção não renderizada (nem o header).
- Sem cobranças pendentes → empty state celebratório.

---

## 9. Animações e motion

| Elemento | Trigger | Animação | Duration | Easing |
|---|---|---|---|---|
| Botão hover (web) | Hover | Background darken 8% | 150ms | `ease-out` |
| Botão press (mobile) | onPressIn | Opacity 1 → 0.85 | 100ms | `linear` |
| Input focus | Focus | Borda 1px → 2px `brand-primary-500` + label flutua | 150ms | `ease-out` |
| Toast in | Show | Slide do topo (web: direita) + fade | 250ms | `cubic-bezier(0.16, 1, 0.3, 1)` |
| Toast out | Auto-dismiss | Slide reverso + fade | 200ms | `ease-in` |
| Skeleton shimmer | Loading | Gradient X de -100% a 100% | 1500ms | `linear`, loop |
| Sidebar collapse | Toggle | Width 240 → 64 | 200ms | `ease-in-out` |
| Modal/Sheet in | Open | Slide-up (mobile) / fade-scale 0.96→1 (web) | 250ms | `ease-out` |
| Erro shake (login) | Erro 401 | TranslateX -8/8/-4/4/0 | 350ms | `ease-out` |
| Dot indicator (carousel) | Slide | Width 8 → 24 do ativo | 200ms | `ease-out` |

**Regra:** respeitar `prefers-reduced-motion`. RN: `AccessibilityInfo.isReduceMotionEnabled()`. Web: `@media (prefers-reduced-motion: reduce)` → desabilitar transforms, manter opacity.

---

## 10. Acessibilidade (WCAG 2.1 AA — checklist obrigatório)

### Cores e contraste

| Combinação | Ratio mínimo | Status |
|---|---|---|
| `neutral-900` sobre `neutral-0` | 4.5 | ✅ ~16:1 |
| `neutral-600` sobre `neutral-0` | 4.5 | ✅ ~7:1 |
| `brand-primary-700` sobre `neutral-0` | 4.5 | ✅ ~7.5:1 |
| `neutral-0` sobre `brand-primary-700` | 4.5 | ✅ ~7.5:1 |
| `accent-green-700` sobre `neutral-0` | 4.5 | ✅ ~6.3:1 |
| `neutral-400` sobre `neutral-0` | 3 (texto auxiliar ≥18px) | ⚠️ ~3.1:1 — **só em texto ≥18px ou bold ≥14px** |
| Badge danger `#991B1B` sobre `#FEE2E2` | 4.5 | ✅ ~7:1 |

**Nunca depender só de cor** para status — badges sempre com ícone ou texto.

### Foco e teclado
- Tab order natural (top→bottom, left→right).
- `:focus-visible`: outline 2px `brand-primary-500`, offset 2px. Em todos os interativos.
- Escape fecha modais, dropdowns, sheets.
- Enter submete forms; Space ativa botões.

### ARIA
- Botões só com ícone: `aria-label` obrigatório.
- Inputs: `<label>` real ou `aria-labelledby`.
- Toasts: `role="status"` (sucesso/info) ou `role="alert"` (erro).
- Tabelas: `<th scope="col">`.
- Sidebar: `<nav aria-label="Navegação principal">`.

### Mobile
- Tap targets ≥ 44×44 (iOS HIG) / 48dp (Material).
- `accessible={true}` + `accessibilityLabel` em containers tocáveis compostos.
- Testar com VoiceOver (iOS) e TalkBack (Android).
- `accessibilityRole` correto: "button", "link", "header", "image".

---

## 11. Performance (alvos)

| Métrica | Alvo | Como medir |
|---|---|---|
| Mobile: TTI dashboard | <2s em 4G | Flipper / Reactotron + RUM |
| Web: LCP login | <1.5s p75 | Vercel Analytics |
| Web: CLS | <0.1 | Vercel Analytics |
| Web: bundle size login | <120KB gz | `next build` analyzer |
| Web: bundle size dashboard | <200KB gz | `next build` analyzer |

**Otimizações obrigatórias:**
- Imagens: `next/image` (web) ou `expo-image` (RN), `priority` apenas no LCP.
- Code split por rota (Next.js 15 faz nativo).
- Recharts: importar só os componentes usados.
- Inter: self-hosted via `next/font/google` com `display: swap`, subset `latin`.

---

## 12. Testes (mínimo por tela)

### Unit (Jest + Testing Library)
- Cada primitive do design system: 100% de variantes e estados.
- Lógica de formatação (BRL, datas, status badges).

### Integração
- Login: submissão válida, inválida (erro de credencial), erro de rede, branch 2FA.
- Dashboard: render com 0 / 1 / múltiplos filhos, KPIs vazios, cobranças vazias.

### E2E
- Playwright (web) e Detox (mobile).
- Fluxo completo: login → dashboard → ver cobrança → voltar.
- Logout limpa sessão + redireciona.

### Visual regression
- Chromatic ou Percy nos primitives e nas 6 telas em estados: default, loading, error, empty.

---

## 13. Implementation checklist por app/pacote

### `shared/design-system` (`@gfn/design-system`)
- [ ] `src/tokens/*.ts` — constantes TS para RN (colors, typography, spacing, radii, shadows)
- [ ] Entrypoint `"./native"` no `package.json` com re-exports StyleSheet RN
- [ ] Componentes web (já existem): Button, Input, Card, Avatar, Badge, Toast — completar variantes e estados conforme seção 3
- [ ] `<Checkbox>`, `<Skeleton>`, `<EmptyState>`, `<ErrorState>` — a criar
- [ ] Storybook com todas as variantes
- [ ] Testes unit nos primitives

### `frontends/guardian-mobile`
- [ ] Setup expo-router (stack: auth, app)
- [ ] `DashboardScreen.tsx`
- [ ] Componentes: `ChildCarousel`, `QuickAccessGrid`, `DueItemCard`, `ActivityRow`, `TabBar`
- [ ] Integração API com React Query
- [ ] Persistência de sessão via `expo-secure-store` (já instalado)
- [ ] Push notifications (fora de escopo desta release)

### `frontends/guardian-web`
- [ ] Rotas `(auth)/` e `(app)/` como route groups em `src/app/` (a criar)
- [ ] `src/app/dashboard/page.tsx`
- [ ] Server actions: `loginAction`, `logoutAction`
- [ ] Cookies HttpOnly para sessão
- [ ] Componentes: `TopNav`, `PageHeader`, `ChildCard`, `MonthSummary`, `DueTable`, `RecentActivity`, `UpcomingEvents`
- [ ] `<AppearanceProvider appearance="warm">` no layout

### `frontends/school-web`
- [ ] `src/app/dashboard/page.tsx`
- [ ] Componentes: `Sidebar`, `TopBar`, `KpiCard`, `RevenueLineChart`, `ChargesDonut`, `ChargesTable`, `SystemActivity`
- [ ] Middleware de auth + role-based guards
- [ ] `<AppearanceProvider appearance="pro">` no layout
- [ ] School switcher (multi-tenant)
- [ ] Server actions: `schoolLoginAction`, `logoutAction`

---

## 14. Mapeamento Figma ↔ Código

Quando a biblioteca Figma for publicada, cada componente deve ter campo `Documentation` apontando para o código. Convenção de naming:

| Figma | Código |
|---|---|
| `Button / Primary / md` | `<Button variant="primary" size="md">` |
| `Input / Default` | `<Input>` |
| `Card / Hero` | `<Card variant="hero">` |
| `Badge / Success` | `<Badge variant="success">` |

Ativar **Figma Dev Mode** + extensão **Code Connect** para auto-sugerir snippets no inspect.

---

## 15. Próximos passos pré-implementação

1. **Design**: nomes finais dos eventos no SystemActivity, copy do empty state da tabela de cobranças, formato da ilustração de login (guardian-web).
2. **Produto**: roles e permissões granulares no school-web; quais KPIs são derivados no front vs API.
3. **Backend**: contratos de API (`/auth/login`, `/auth/school-login`, `/guardian/dashboard`, `/school/dashboard/*`), schema dos eventos (system activity), timezone canônico (`America/Sao_Paulo`).
4. **CI**: lint, type-check, testes, build dos 3 apps, deploy preview por PR (Vercel para web, EAS Preview para mobile).
5. **Foundations**: subir tokens e primitives do design system primeiro; validar com rota `/test` em cada app antes de iniciar as 6 telas em paralelo.

---

**Documento vivo.** Mudanças entram via PR neste arquivo com referência ao ticket que motivou.
