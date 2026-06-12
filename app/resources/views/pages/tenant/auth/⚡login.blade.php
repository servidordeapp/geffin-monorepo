<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('components.layouts.tenant.guest', ['eyebrow' => 'Acesso restrito', 'heading' => 'Entre na sua conta', 'subtitle' => 'Use suas credenciais institucionais para acessar o painel.'])] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        if (!Auth::guard('tenant')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', __('auth.failed'));

            return;
        }

        session()->regenerate();

        $this->redirect(route('tenant.dashboard'), navigate: true);
    }
};
?>

<div class="w-full">
    @if (session('status'))
        <div class="alert alert-success mb-4" role="status">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form wire:submit="login" class="space-y-4" novalidate>
        <fieldset class="fieldset">
            <legend class="fieldset-legend">E-mail institucional</legend>
            <input type="email" wire:model="email" placeholder="voce@escola.edu.br" autofocus autocomplete="email"
                class="input w-full @error('email') input-error @enderror" />
            @error('email')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend flex w-full items-center justify-between">
                <span>Senha</span>
                <a href="{{ route('tenant.password.request') }}" wire:navigate class="link link-primary text-xs font-medium no-underline">
                    Esqueci minha senha
                </a>
            </legend>
            <input type="password" wire:model="password" placeholder="••••••••" autocomplete="current-password"
                class="input w-full @error('password') input-error @enderror" />
            @error('password')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <label class="label cursor-pointer justify-start gap-3">
            <input type="checkbox" wire:model="remember" class="checkbox checkbox-sm" />
            <span class="label-text">Manter sessão neste dispositivo</span>
        </label>

        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                Entrar
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </span>
            <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                <span class="loading loading-spinner loading-sm"></span>
                Entrando…
            </span>
        </button>

        <p class="text-center text-xs text-base-content/60">
            Ao continuar você concorda com os
            <a href="#" class="link link-primary no-underline">Termos</a>
            e a
            <a href="#" class="link link-primary no-underline">Política de Privacidade</a>.
        </p>
    </form>
</div>
