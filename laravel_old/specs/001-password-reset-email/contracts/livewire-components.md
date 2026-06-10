# Livewire Components Contract — Password Reset by Email

Two new single-file components, matching the existing `⚡login.blade.php` convention.

---

## 1. `auth.forgot-password` (`resources/views/components/auth/⚡forgot-password.blade.php`)

### Mount

```php
public function mount(): void
{
    // no-op; the form starts empty
}
```

### Public properties

| Property | Type | Default | Validation | Notes |
|---|---|---|---|---|
| `email` | `string` | `''` | `#[Validate('required|email:rfc|max:254')]` | Bound to the email input via `wire:model="email"`. |
| `status` | `?string` | `null` | — | Set after the action runs to show the neutral confirmation. |

### Actions

#### `requestReset(): void`

1. `$this->validate();` (Livewire renders inline errors on failure; FR-002 satisfied).
2. `app(PasswordResetRateLimiter::class)->hitOrFail($this->email, request()->ip())` — returns void if under both limits, otherwise the wrapper sets `$status = __('passwords.throttled')` and **calls `return;` via an early exit token in the component**. (Implementation detail: the wrapper returns a `bool` and the component branches; the wrapper does not throw.)
3. `app(PasswordResetService::class)->request($this->email, request())` — service is the sole side-effect owner.
4. `$this->status = __('passwords.sent');`
5. `$this->reset('email');`

The action returns void. The component re-renders. No `redirect()` is issued, so the form remains on the page (with the status message displayed and the email field cleared).

### Layout & attributes

```php
new #[Layout('layouts.guest')] class extends Component { ... };
```

### Routes register

```php
Route::livewire('/password/forgot', 'auth.forgot-password')->name('password.request');
```

### Template requirements (Blade)

- Form `wire:submit="requestReset"`.
- Email input bound `wire:model="email"`, `type="email"`, `autocomplete="email"`, `autofocus`, `novalidate` on the form so the server validation runs (FR-002 server-side).
- Inline `@error('email')` block beneath the input (matches existing login screen pattern).
- Neutral status message rendered in a `@if($status)` block above the form.
- Submit button shows loading state via `wire:loading.attr="disabled"` (matches existing pattern).
- Back link to `route('login')`.

---

## 2. `auth.reset-password` (`resources/views/components/auth/⚡reset-password.blade.php`)

### Mount

```php
public function mount(string $token): void
{
    $this->token = $token;
    $this->email = (string) request()->query('email', '');
    app(PasswordResetService::class)->recordLinkOpened($this->email, request());
}
```

### Public properties

| Property | Type | Default | Validation | Notes |
|---|---|---|---|---|
| `token` | `string` | `''` | `#[Validate('required|string')]` | Hidden; set in `mount()` from the URL path. |
| `email` | `string` | `''` | `#[Validate('required|email')]` | Hidden; set in `mount()` from `?email=`. |
| `password` | `string` | `''` | composite — see below | Bound to the new-password input. |
| `passwordConfirmation` | `string` | `''` | `#[Validate('required|same:password')]` | Bound to the confirmation input. |
| `tokenError` | `?string` | `null` | — | Set when the broker rejects the token; controls the "request new link" CTA visibility. |

Composite password validation (declared on the class via a `rules()` method because `Password::min(...)` cannot be expressed as an attribute literal):

```php
protected function rules(): array
{
    return [
        'token' => ['required', 'string'],
        'email' => ['required', 'email'],
        'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(12)->letters()->numbers(), 'max:255'],
        'passwordConfirmation' => ['required'],
    ];
}
```

Field mapping for `confirmed`: Livewire allows naming the confirmation field `passwordConfirmation` by also declaring it explicitly; alternatively use `password_confirmation` to match Laravel's default convention. Either works — pick `password_confirmation` to match the broker's `validate(['password' => 'confirmed'])` expectation without renaming.

### Actions

#### `updatePassword(): void`

1. `$this->validate();`
2. `app(PasswordResetRateLimiter::class)->hitOrFail(null, request()->ip())` — IP-only (per-email key not meaningful at this step because the email is supplied by the token-holder).
3. `$status = app(PasswordResetService::class)->reset($this->email, $this->token, $this->password, request());`
4. Branch on `$status`:
   - `Password::PASSWORD_RESET`: `session()->flash('status', __('passwords.reset')); return redirect()->route('login');`
   - Anything else: `$this->tokenError = __('passwords.token');` — re-render with the error and the "Pedir novo link" link.

### Layout & attributes

```php
new #[Layout('layouts.guest')] class extends Component { ... };
```

### Routes register

```php
Route::livewire('/password/reset/{token}', 'auth.reset-password')
    ->middleware('signed')
    ->name('password.reset');
```

### Template requirements

- Form `wire:submit="updatePassword"`.
- Hidden inputs for `token` and `email` (not user-editable; rendered as `<input type="hidden" wire:model="token">`).
- Password and confirmation inputs both `type="password"`, `autocomplete="new-password"`.
- Inline `@error('password')`, `@error('password_confirmation')` blocks.
- `@if($tokenError)` block shows the error and a button-style link to `route('password.request')` with copy "Pedir novo link".

---

## Login screen wiring (existing `⚡login.blade.php`)

The existing template at line 55-57 has a placeholder `<a href="#">Esqueci minha senha</a>`. This becomes:

```blade
<a href="{{ route('password.request') }}" wire:navigate class="t-caption" ...>
    Esqueci minha senha
</a>
```

`wire:navigate` keeps the SPA-like transition Livewire already uses elsewhere (the existing login uses `navigate: true` on its post-login redirect, so the directive is consistent).

---

## Localization keys touched

| File | Key | Default (PT-BR) |
|---|---|---|
| `lang/pt_BR/passwords.php` | `sent` | "Se uma conta existir para este e-mail, você receberá instruções em instantes." |
| `lang/pt_BR/passwords.php` | `reset` | "Senha atualizada. Faça login com a nova senha." |
| `lang/pt_BR/passwords.php` | `throttled` | "Muitas tentativas. Tente novamente em alguns minutos." |
| `lang/pt_BR/passwords.php` | `token` | "Link inválido ou expirado. Solicite um novo e-mail." |
| `lang/pt_BR/passwords.php` | `user` | "Não foi possível encontrar uma conta com este e-mail." (NEVER surfaced — broker uses it internally; reset response is always neutral) |
| `lang/pt_BR/auth.php` | `forgot.title` | "Esqueci minha senha" |
| `lang/pt_BR/auth.php` | `forgot.subtitle` | "Informe seu e-mail e enviaremos um link para você criar uma nova senha." |
| `lang/pt_BR/auth.php` | `forgot.submit` | "Enviar link de redefinição" |
| `lang/pt_BR/auth.php` | `forgot.back_to_login` | "Voltar para o login" |
| `lang/pt_BR/auth.php` | `reset.title` | "Criar nova senha" |
| `lang/pt_BR/auth.php` | `reset.subtitle` | "Escolha uma senha forte com pelo menos 12 caracteres." |
| `lang/pt_BR/auth.php` | `reset.password_label` | "Nova senha" |
| `lang/pt_BR/auth.php` | `reset.password_confirmation_label` | "Confirme a nova senha" |
| `lang/pt_BR/auth.php` | `reset.submit` | "Atualizar senha" |
| `lang/pt_BR/auth.php` | `reset.request_new_link` | "Pedir novo link" |
| `lang/en/passwords.php` | same keys | English equivalents (fallback) |
| `lang/en/auth.php` | same keys | English equivalents |
