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
    <form wire:submit="login" class="space-y-5">
        <label class="field w-full" for="email">
            <span class="field-label">E-mail</span>
            <input
                type="email"
                id="email"
                wire:model="email"
                class="input w-full @error('email') error @enderror"
                placeholder="seu@email.com"
                autofocus
                autocomplete="email"
            />
            @error('email')
                <span class="t-caption" style="color: var(--color-semantic-danger);">{{ $message }}</span>
            @enderror
        </label>

        <label class="field w-full" for="password">
            <span class="field-label">Senha</span>
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

        <label class="flex items-center gap-2 t-body-sm cursor-pointer">
            <input
                type="checkbox"
                wire:model="remember"
                class="rounded"
                style="accent-color: var(--color-brand-primary-700);"
            />
            Lembrar-me
        </label>

        <button type="submit" class="btn btn-primary w-full justify-center">
            <span wire:loading.remove>Entrar</span>
            <span wire:loading>Entrando…</span>
        </button>
    </form>
</div>
