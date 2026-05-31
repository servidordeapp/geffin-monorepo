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
        <p class="t-body-sm" style="color: var(--fg-2);">
            {{ __('auth.reset.subtitle') }}
        </p>

        <input type="hidden" wire:model="token" />
        <input type="hidden" wire:model="email" />

        @if($tokenError)
            <x-ui.alert variant="danger">
                <p>{{ $tokenError }}</p>
                <a href="{{ route('password.request') }}" wire:navigate style="color: inherit; text-decoration: underline; font-weight: 600;">
                    {{ __('auth.reset.request_new_link') }}
                </a>
            </x-ui.alert>
        @endif

        <x-ui.input
            type="password"
            name="password"
            :label="__('auth.reset.password_label')"
            wire:model="password"
            autocomplete="new-password"
        />

        <x-ui.input
            type="password"
            name="password_confirmation"
            :label="__('auth.reset.password_confirmation_label')"
            wire:model="password_confirmation"
            autocomplete="new-password"
        />

        <flux:button type="submit" variant="primary" class="w-full justify-center" :loading="false" wire:loading.attr="disabled" wire:target="updatePassword">
            <span wire:loading.remove wire:target="updatePassword" class="inline-flex items-center gap-2">
                {{ __('auth.reset.submit') }}
                <flux:icon name="check" class="size-4" />
            </span>
            <span wire:loading wire:target="updatePassword" class="inline-flex items-center gap-2">
                <flux:icon name="loading" class="size-4 animate-spin" />
                {{ __('Atualizando…') }}
            </span>
        </flux:button>
    </form>
</div>
