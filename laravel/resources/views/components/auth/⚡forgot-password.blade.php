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
        <div class="mb-5 t-body-sm" style="color: var(--color-semantic-success);">
            {{ $status }}
        </div>
    @endif

    <form wire:submit="requestReset" class="space-y-5" novalidate>
        <div class="space-y-1">
            <p class="t-body-sm" style="color: var(--fg-2);">
                {{ __('auth.forgot.subtitle') }}
            </p>
        </div>

        <label class="field w-full" for="email">
            <span class="field-label">E-mail</span>
            <input
                type="email"
                id="email"
                wire:model="email"
                class="input w-full @error('email') error @enderror"
                placeholder="voce@escola.edu.br"
                autofocus
                autocomplete="email"
            />
            @error('email')
                <span class="t-caption" style="color: var(--color-semantic-danger);">{{ $message }}</span>
            @enderror
        </label>

        <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="requestReset" class="inline-flex items-center gap-2">
                {{ __('auth.forgot.submit') }}
                <i data-lucide="send" style="width:16px;height:16px;"></i>
            </span>
            <span wire:loading wire:target="requestReset" class="inline-flex items-center gap-2">
                <i data-lucide="loader-circle" class="animate-spin" style="width:16px;height:16px;"></i>
                {{ __('Enviando…') }}
            </span>
        </button>

        <p class="t-caption" style="text-align:center; color: var(--fg-3);">
            <a href="{{ route('login') }}" wire:navigate style="color: var(--fg-link); text-decoration: none;">
                {{ __('auth.forgot.back_to_login') }}
            </a>
        </p>
    </form>
</div>
