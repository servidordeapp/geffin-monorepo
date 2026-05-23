<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', __('auth.failed'));

            return;
        }

        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>

<div class="w-full">
    <form wire:submit="login" class="space-y-5" novalidate>
        <label class="field w-full" for="email">
            <span class="field-label">E-mail institucional</span>
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

        <label class="field w-full" for="password">
            <span class="flex items-center justify-between">
                <span class="field-label">Senha</span>
                <a href="#" class="t-caption" style="color: var(--fg-link); text-decoration: none; font-weight: 500;">
                    Esqueci minha senha
                </a>
            </span>
            <input
                type="password"
                id="password"
                wire:model="password"
                class="input w-full @error('password') error @enderror"
                placeholder="••••••••"
                autocomplete="current-password"
            />
            @error('password')
                <span class="t-caption" style="color: var(--color-semantic-danger);">{{ $message }}</span>
            @enderror
        </label>

        <label class="flex items-center gap-2 t-body-sm cursor-pointer select-none">
            <input
                type="checkbox"
                wire:model="remember"
                class="rounded"
                style="accent-color: var(--color-brand-primary-700);"
            />
            Manter sessão neste dispositivo
        </label>

        <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                Entrar
                <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
            </span>
            <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                <i data-lucide="loader-circle" class="animate-spin" style="width:16px;height:16px;"></i>
                Entrando…
            </span>
        </button>

        <p class="t-caption" style="text-align:center; color: var(--fg-3);">
            Ao continuar você concorda com os
            <a href="#" style="color: var(--fg-link); text-decoration: none;">Termos</a>
            e a
            <a href="#" style="color: var(--fg-link); text-decoration: none;">Política de Privacidade</a>.
        </p>
    </form>
</div>
