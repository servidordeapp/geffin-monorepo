# Design System — Geffin

Sistema visual para o produto Geffin. Cobre tokens, tipografia, componentes e regras de uso. Todas as decisões são documentadas aqui — siga antes de criar qualquer nova tela ou componente.

---

## Índice

1. [Fontes](#1-fontes)
2. [Tokens CSS](#2-tokens-css)
3. [Classes de tipografia](#3-classes-de-tipografia)
4. [Tailwind v4 — @theme](#4-tailwind-v4--theme)
5. [Iconografia](#5-iconografia)
6. [Componentes](#6-componentes)
7. [Regras não negociáveis](#7-regras-não-negociáveis)
8. [Checklist de implementação](#8-checklist-de-implementação)

---

## 1. Fontes

Carregadas via `@import` no topo de `resources/css/app.css`, antes do `@import 'tailwindcss'`.

| Papel | Família | Pesos |
|---|---|---|
| Interface (sans) | Switzer | 400 500 600 700 800 |
| Código / valores | IBM Plex Mono | 400 500 600 |
    
```css
@import url("https://api.fontshare.com/v2/css?f[]=switzer@400,500,600,700,800&display=swap");
@import url("https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap");
```

---

## 2. Tokens CSS

Definidos em `:root` em `resources/css/app.css`. Dois níveis: primitivos (valores brutos) e semânticos (aliases usados nos componentes).

**Use sempre os tokens semânticos nos componentes. Os primitivos existem só para compor os aliases.**

### Superfícies

| Token | Valor | Uso |
|---|---|---|
| `--bg-app` | `#F8FAFC` | Fundo da página — nunca branco puro |
| `--bg-surface` | `#FFFFFF` | Cards, modais, formulários |
| `--bg-raised` | `#FFFFFF` | Elementos elevados |
| `--bg-sunken` | `#F1F5F9` | Header de tabela, input disabled |
| `--bg-overlay` | `rgba(15,23,42,.48)` | Backdrop de modal |
| `--bg-brand` | `#0B2E5C` | Sidebar, header principal |
| `--bg-brand-soft` | `#EFF6FF` | Fundo de hover ghost button |

### Texto

| Token | Uso |
|---|---|
| `--fg-1` | Texto primário |
| `--fg-2` | Labels, texto secundário |
| `--fg-3` | Placeholders, texto desabilitado |
| `--fg-on-brand` | Texto sobre fundo brand |
| `--fg-brand` | Links, ações em destaque |
| `--fg-link` | Hiperlinks |

### Bordas

| Token | Uso |
|---|---|
| `--border-subtle` | Bordas de card no estado normal |
| `--border-default` | Bordas de input no estado normal |
| `--border-strong` | Bordas de input em hover |
| `--border-focus` | Borda de foco (1px brand-500) |

### Status — sempre fg + bg pareados

| Estado | fg | bg |
|---|---|---|
| Pago | `--status-paid-fg` | `--status-paid-bg` |
| Pendente | `--status-pending-fg` | `--status-pending-bg` |
| Atrasado | `--status-overdue-fg` | `--status-overdue-bg` |
| Rascunho | `--status-draft-fg` | `--status-draft-bg` |

### Radius

| Token | Valor | Uso |
|---|---|---|
| `--radius-xs` | 4px | Chips pequenos |
| `--radius-sm` | 6px | Inputs, botão secundário |
| `--radius-md` | 8px | Cards, botão primário |
| `--radius-lg` | 12px | Inputs/botões guardian |
| `--radius-xl` | 16px | Cards guardian |
| `--radius-full` | 9999px | Pills, avatares |

### Shadows

| Token | Uso |
|---|---|
| `--shadow-xs` | Estado normal de elemento elevado |
| `--shadow-sm` | Card normal |
| `--shadow-md` | Menus, dropdowns |
| `--shadow-lg` | Modais |
| `--shadow-xl` | Dialogs grandes |
| `--shadow-focus` | Anel de foco (brand 20% opacidade) |
| `--shadow-focus-danger` | Anel de foco em campo com erro |

### Motion

| Token | Valor | Uso |
|---|---|---|
| `--duration-fast` | 120ms | Hover |
| `--duration-base` | 180ms | Mudança de estado |
| `--duration-slow` | 280ms | Troca de página |
| `--ease-out` | `cubic-bezier(.2,.8,.2,1)` | Entradas |
| `--ease-in-out` | `cubic-bezier(.4,0,.2,1)` | Swaps |

---

## 3. Classes de tipografia

Disponíveis globalmente via `resources/css/app.css`. Aplique diretamente nos elementos HTML.

```html
<h1 class="t-h1">Título da página</h1>
<p class="t-body">Texto corrido normal.</p>
<span class="t-caption">Nota de rodapé</span>
<span class="t-mono t-numeric">R$ 1.240,00</span>
```

| Classe | Tamanho | Peso | Uso |
|---|---|---|---|
| `.t-display` | 48px | 700 | Hero, splash screens |
| `.t-h1` | 36px | 700 | Título de página |
| `.t-h2` | 30px | 700 | Seção principal |
| `.t-h3` | 24px | 600 | Subseção |
| `.t-h4` | 20px | 600 | Card title |
| `.t-h5` | 18px | 600 | Label de grupo |
| `.t-body-lg` | 18px | 400 | Corpo com destaque |
| `.t-body` | 16px | 400 | Corpo padrão |
| `.t-body-sm` | 14px | 400 | Texto auxiliar, `--fg-2` |
| `.t-label` | 14px | 500 | Labels de formulário |
| `.t-caption` | 12px | 500 | Metadados, `--fg-2` |
| `.t-overline` | 12px | 600 | RÓTULOS EM CAPS |
| `.t-mono` | 14px | 500 | Valores monetários, código |
| `.t-numeric` | — | — | Modificador: `tabular-nums` |

---

## 4. Tailwind v4 — @theme

O bloco `@theme` em `app.css` expõe os tokens como utilitários Tailwind. Isso significa que as classes abaixo funcionam nativamente:

```html
<!-- Cores -->
<div class="bg-brand-primary-900 text-neutral-600">...</div>
<span class="text-semantic-danger">Erro</span>

<!-- Radius -->
<div class="rounded-md">Card admin</div>
<input class="rounded-sm">Input admin</input>

<!-- Shadows -->
<div class="shadow-md">Dropdown</div>

<!-- Fontes -->
<p class="font-mono">R$ 1.240,00</p>
```

Tokens mapeados no `@theme`: todas as cores brand, accent, neutral e semantic; fonts sans/mono; radius sm→full; shadows sm/md/lg.

---

## 5. Iconografia

**Biblioteca:** Lucide Icons. Carregada via CDN com `defer` em ambos os layouts.

**Inicialização automática** em `DOMContentLoaded` e `livewire:navigated`. Para ícones dentro de componentes Livewire que re-renderizam in-place, chame `lucide.createIcons()` manualmente no hook necessário.

```html
<!-- Tamanho 16px — inline em texto -->
<i data-lucide="check-circle-2" width="16" height="16"></i>

<!-- Tamanho 20px — dentro de botões -->
<button class="btn btn-primary">
    <i data-lucide="download" width="20" height="20"></i>
    Baixar recibo
</button>

<!-- Tamanho 24px — navegação, default -->
<i data-lucide="bell" width="24" height="24"></i>

<!-- Tamanho 32px — cards grandes, empty states -->
<i data-lucide="file-text" width="32" height="32"></i>
```

### Ícones do domínio Geffin

| Ícone | Uso |
|---|---|
| `file-text` | Fatura |
| `credit-card` | Pagamento |
| `file-signature` | Contrato |
| `users` | Responsáveis |
| `graduation-cap` | Alunos |
| `bar-chart-3` | Relatórios |
| `check-circle-2` | Pago |
| `alert-circle` | Atrasado |
| `bell` | Alertas |
| `settings` | Configurações |
| `download` | Baixar recibo |

---

## 6. Componentes

Todos os componentes abaixo existem como classes CSS em `resources/css/app.css`.

### Botões

```html
<!-- Ação primária -->
<button class="btn btn-primary">Enviar fatura</button>

<!-- Ação secundária -->
<button class="btn btn-secondary">Cancelar</button>

<!-- Ação fantasma / inline -->
<button class="btn btn-ghost">Ver detalhes</button>

<!-- Ação destrutiva -->
<button class="btn btn-danger">Excluir</button>

<!-- Com ícone -->
<button class="btn btn-primary">
    <i data-lucide="download" width="16" height="16"></i>
    Baixar recibo
</button>

<!-- Largura total -->
<button class="btn btn-primary w-full justify-center">Entrar</button>
```

| Variante | Fundo | Hover |
|---|---|---|
| `btn-primary` | `brand-primary-700` | `brand-primary-900` |
| `btn-secondary` | `#fff` + borda | `bg-sunken` |
| `btn-ghost` | transparente | `bg-brand-soft` |
| `btn-danger` | `semantic-danger` | `#DC2626` |

### Status pills

```html
<span class="pill pill-paid"><span class="dot"></span>Pago</span>
<span class="pill pill-pending"><span class="dot"></span>Pendente</span>
<span class="pill pill-overdue"><span class="dot"></span>Atrasado · 3d</span>
<span class="pill pill-draft"><span class="dot"></span>Rascunho</span>
```

### Inputs

```html
<!-- Padrão -->
<label class="field" for="valor">
    <span class="field-label">Valor da fatura</span>
    <input class="input" type="text" id="valor" placeholder="0,00">
</label>

<!-- Com erro -->
<label class="field" for="email">
    <span class="field-label">E-mail</span>
    <input class="input error" type="email" id="email">
    <span class="t-caption" style="color: var(--color-semantic-danger);">E-mail inválido</span>
</label>
```

No Livewire, use `@error` para adicionar a classe `error` e exibir a mensagem:

```blade
<input class="input w-full @error('email') error @enderror" wire:model="email">
@error('email')
    <span class="t-caption" style="color: var(--color-semantic-danger);">{{ $message }}</span>
@enderror
```

### Cards (admin)

```html
<div class="card">
    <div class="card-overline">Coletado · Mar</div>
    <div class="card-value">R$ 248.520,00</div>
    <div class="card-sub">
        <span class="delta-up">↑ 12,4%</span> vs. fev
    </div>
</div>
```

---

## 7. Regras não negociáveis

### Cores

- **Verde** = pago/sucesso. **Amarelo** = pendente. **Vermelho** = atrasado/erro. Nunca decorativos.
- App background é sempre `--bg-app`. Nunca branco puro — branco é reservado para superfícies elevadas (`--bg-surface`).
- Sem gradientes em produto. Gradientes só em peças de marketing.

### Dimensões

| Elemento | Valor |
|---|---|
| Radius de inputs | `--radius-sm` (6px) |
| Radius de cards | `--radius-md` (8px) |
| Padding de card | 16–20px |
| Altura de botão CTA | 36–40px |

### Valores monetários

- Sempre com símbolo + duas casas decimais: `R$ 1.240,00` — nunca `R$ 1240` ou `1.2k`.
- Sempre com `font-variant-numeric: tabular-nums`. Use `.t-mono` ou `.t-numeric`.
- Em tabelas: alinhar à direita, usar `--font-mono`.

### Foco

**Nunca** use `outline: none` sozinho. Todo elemento interativo deve ter:

```css
/* correto */
:focus-visible {
    outline: none;
    box-shadow: var(--shadow-focus);
    border-color: var(--border-focus);
}
```

Os componentes `.btn` e `.input` já implementam isso corretamente.

### Motion

- 120ms para hover · 180ms para mudança de estado · 280ms para troca de página.
- `--ease-out` para entradas. `--ease-in-out` para swaps.
- Sem bouncing, sem springs. É um produto financeiro.

### Voz e texto

- Imperativo, direto, sem emoji, sem `!`.
- **Sentence case** em tudo: botões, títulos, labels. Ex.: "Enviar lembrete", "Marcar como pago".
- Não: "ENVIAR", "Enviar Lembrete!", "✅ Marcado!".

### Empty states

Exatamente três elementos: 1 ilustração + 1 linha de texto + 1 ação. Nada além.

---

## 8. Checklist de implementação

Use para auditar qualquer tela nova ou existente.

### Setup

- [ ] Fontes carregadas via `@import` no topo de `app.css` (Switzer + IBM Plex Mono)
- [ ] Tokens `:root` disponíveis globalmente (`app.css`)
- [ ] Classes `.t-*` disponíveis (`app.css`)
- [ ] Bloco `@theme` do Tailwind v4 atualizado
- [ ] Lucide CDN com `defer` em ambos os layouts (`app.blade.php`, `guest.blade.php`)
- [ ] `lucide.createIcons()` chamado em `DOMContentLoaded` e `livewire:navigated`

### Por tela

- [ ] Background da página usa `var(--bg-app)`, não branco
- [ ] Cards usam `var(--bg-surface)` com `var(--border-subtle)` e `var(--shadow-sm)`
- [ ] Todo valor monetário usa `tabular-nums` + `var(--font-mono)` (`.t-mono`)
- [ ] Valores monetários alinhados à direita em tabelas
- [ ] Sem `outline: none` sem fallback de foco
- [ ] Status de fatura usa pill com a variante correta (`paid`/`pending`/`overdue`/`draft`)
- [ ] Botões usam `.btn` + variante em vez de classes utilitárias ad-hoc
- [ ] Inputs usam `.field` + `.field-label` + `.input`
- [ ] Textos de erro usam `.t-caption` + `color: var(--color-semantic-danger)`
- [ ] Ícones com tamanhos corretos: 16 (inline) / 20 (botão) / 24 (nav) / 32 (empty state)
- [ ] Voz imperativa, sentence case, sem emoji
