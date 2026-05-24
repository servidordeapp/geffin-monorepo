<?php

use App\Services\Auth\PasswordResetRateLimiter;
use App\Services\Auth\PasswordResetService;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $tokenError = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
        app(PasswordResetService::class)->recordLinkOpened($this->email, request());
    }

    protected function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->numbers(), 'max:255'],
            'password_confirmation' => ['required'],
        ];
    }

    public function updatePassword(): void
    {
        $this->validate();

        $passed = app(PasswordResetRateLimiter::class)->hitOrFail(null, request()->ip());

        if (! $passed) {
            $this->tokenError = __('passwords.throttled');
            \App\Models\PasswordResetAuditEvent::create([
                'event_type' => 'request_throttled',
                'user_id' => null,
                'email_hash' => \App\Models\PasswordResetAuditEvent::emailHash($this->email),
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
                'outcome' => 'throttled',
                'created_at' => now(),
            ]);

            return;
        }

        $status = app(PasswordResetService::class)->reset(
            $this->email,
            $this->token,
            $this->password,
            request()
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', __('passwords.reset'));

            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->tokenError = __('passwords.token');
    }
};
?>

<div class="w-full">
    <form wire:submit="updatePassword" class="space-y-5" novalidate>
        <div class="space-y-1">
            <p class="t-body-sm" style="color: var(--fg-2);">
                {{ __('auth.reset.subtitle') }}
            </p>
        </div>

        <input type="hidden" wire:model="token" />
        <input type="hidden" wire:model="email" />

        @if($tokenError)
            <div class="t-body-sm" style="color: var(--color-semantic-danger);">
                <p>{{ $tokenError }}</p>
                <a href="{{ route('password.request') }}" wire:navigate style="color: var(--fg-link); text-decoration: none;">
                    {{ __('auth.reset.request_new_link') }}
                </a>
            </div>
        @endif

        <label class="field w-full" for="password">
            <span class="field-label">{{ __('auth.reset.password_label') }}</span>
            <input
                type="password"
                id="password"
                wire:model="password"
                class="input w-full @error('password') error @enderror"
                autocomplete="new-password"
            />
            @error('password')
                <span class="t-caption" style="color: var(--color-semantic-danger);">{{ $message }}</span>
            @enderror
        </label>

        <label class="field w-full" for="password_confirmation">
            <span class="field-label">{{ __('auth.reset.password_confirmation_label') }}</span>
            <input
                type="password"
                id="password_confirmation"
                wire:model="password_confirmation"
                class="input w-full @error('password_confirmation') error @enderror"
                autocomplete="new-password"
            />
            @error('password_confirmation')
                <span class="t-caption" style="color: var(--color-semantic-danger);">{{ $message }}</span>
            @enderror
        </label>

        <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="updatePassword" class="inline-flex items-center gap-2">
                {{ __('auth.reset.submit') }}
                <i data-lucide="check" style="width:16px;height:16px;"></i>
            </span>
            <span wire:loading wire:target="updatePassword" class="inline-flex items-center gap-2">
                <i data-lucide="loader-circle" class="animate-spin" style="width:16px;height:16px;"></i>
                {{ __('Atualizando…') }}
            </span>
        </button>
    </form>
</div>
