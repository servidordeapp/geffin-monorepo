<?php

use App\Services\Auth\PasswordResetRateLimiter;
use App\Services\Auth\PasswordResetService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component {
    #[Validate('required|email:rfc|max:254')]
    public string $email = '';

    public ?string $status = null;

    public function requestReset(): void
    {
        $this->validate();

        $passed = app(PasswordResetRateLimiter::class)->hitOrFail($this->email, request()->ip());

        if (! $passed) {
            $this->status = __('passwords.throttled');
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

        app(PasswordResetService::class)->request($this->email, request());

        $this->status = __('passwords.sent');
        $this->reset('email');
    }
};
?>

<div class="w-full">
    @if($status)
        <x-ui.alert variant="success" class="mb-5">{{ $status }}</x-ui.alert>
    @endif

    <form wire:submit="requestReset" class="space-y-5" novalidate>
        <p class="t-body-sm" style="color: var(--fg-2);">
            {{ __('auth.forgot.subtitle') }}
        </p>

        <x-ui.input
            type="email"
            name="email"
            label="E-mail"
            wire:model="email"
            placeholder="voce@escola.edu.br"
            autofocus
            autocomplete="email"
        />

        <flux:button type="submit" variant="primary" class="w-full justify-center" :loading="false" wire:loading.attr="disabled" wire:target="requestReset">
            <span wire:loading.remove wire:target="requestReset" class="inline-flex items-center gap-2">
                {{ __('auth.forgot.submit') }}
                <flux:icon name="paper-airplane" class="size-4" />
            </span>
            <span wire:loading wire:target="requestReset" class="inline-flex items-center gap-2">
                <flux:icon name="loading" class="size-4 animate-spin" />
                {{ __('Enviando…') }}
            </span>
        </flux:button>

        <p class="t-caption" style="text-align:center; color: var(--fg-3);">
            <a href="{{ route('login') }}" wire:navigate style="color: var(--fg-link); text-decoration: none;">
                {{ __('auth.forgot.back_to_login') }}
            </a>
        </p>
    </form>
</div>
