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
        <x-ui.input
            type="email"
            name="email"
            label="E-mail institucional"
            wire:model="email"
            placeholder="voce@escola.edu.br"
            autofocus
            autocomplete="email"
        />

        <x-ui.input
            type="password"
            name="password"
            wire:model="password"
            placeholder="••••••••"
            autocomplete="current-password"
        >
            <x-slot:label>
                <span class="flex items-center justify-between">
                    <span>Senha</span>
                    <a href="{{ route('password.request') }}" wire:navigate class="t-caption" style="color: var(--fg-link); text-decoration: none; font-weight: 500;">
                        Esqueci minha senha
                    </a>
                </span>
            </x-slot:label>
        </x-ui.input>

        <x-ui.checkbox wire:model="remember" label="Manter sessão neste dispositivo" />

        <flux:button type="submit" variant="primary" class="w-full justify-center" :loading="false" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                Entrar
                <flux:icon name="arrow-right" class="size-4" />
            </span>
            <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                <flux:icon name="loading" class="size-4 animate-spin" />
                Entrando…
            </span>
        </flux:button>

        <p class="t-caption" style="text-align:center; color: var(--fg-3);">
            Ao continuar você concorda com os
            <a href="#" style="color: var(--fg-link); text-decoration: none;">Termos</a>
            e a
            <a href="#" style="color: var(--fg-link); text-decoration: none;">Política de Privacidade</a>.
        </p>
    </form>
</div>
