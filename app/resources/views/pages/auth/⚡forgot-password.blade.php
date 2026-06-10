<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('components.layouts.guest', ['eyebrow' => 'Recuperar acesso', 'heading' => 'Esqueci minha senha', 'subtitle' => 'Informe seu e-mail institucional e enviaremos um link para redefinir sua senha.'])] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    public bool $sent = false;

    public function sendResetLink(): void
    {
        $this->validate();

        Password::sendResetLink(['email' => $this->email]);

        $this->sent = true;
    }
};
?>

<div class="w-full">
    @if ($sent)
        <div class="alert alert-success" role="status">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
            <span>Se o e-mail informado estiver cadastrado, você receberá um link para redefinir sua senha em instantes.</span>
        </div>
    @else
        <form wire:submit="sendResetLink" class="space-y-4" novalidate>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">E-mail institucional</legend>
                <input type="email" wire:model="email" placeholder="voce@escola.edu.br" autofocus autocomplete="email"
                    class="input w-full @error('email') input-error @enderror" />
                @error('email')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled"
                wire:target="sendResetLink">
                <span wire:loading.remove wire:target="sendResetLink" class="inline-flex items-center gap-2">
                    Enviar link de redefinição
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </span>
                <span wire:loading wire:target="sendResetLink" class="inline-flex items-center gap-2">
                    <span class="loading loading-spinner loading-sm"></span>
                    Enviando…
                </span>
            </button>
        </form>
    @endif

    <p class="mt-6 text-center text-sm text-base-content/70">
        Lembrou a senha?
        <a href="{{ route('login') }}" wire:navigate class="link link-primary font-medium no-underline">
            Voltar para o login
        </a>
    </p>
</div>
