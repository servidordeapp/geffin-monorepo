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
        <div>
            <label for="email" class="block text-sm font-medium mb-1" style="color: var(--fg-2);">
                E-mail
            </label>
            <input
                type="email"
                id="email"
                wire:model="email"
                class="form-input w-full rounded-lg px-4 py-2.5 text-sm outline-none @error('email') form-input--error @enderror"
                placeholder="seu@email.com"
                autofocus
                autocomplete="email"
            />
            @error('email')
                <p class="mt-1 text-xs" style="color: var(--color-semantic-danger);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1" style="color: var(--fg-2);">
                Senha
            </label>
            <input
                type="password"
                id="password"
                wire:model="password"
                class="form-input w-full rounded-lg px-4 py-2.5 text-sm outline-none @error('password') form-input--error @enderror"
                placeholder="••••••••"
                autocomplete="current-password"
            />
            @error('password')
                <p class="mt-1 text-xs" style="color: var(--color-semantic-danger);">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <label class="flex items-center gap-2 text-sm cursor-pointer" style="color: var(--fg-2);">
                <input
                    type="checkbox"
                    wire:model="remember"
                    class="rounded"
                    style="accent-color: var(--color-brand-primary-700);"
                />
                Lembrar-me
            </label>
        </div>

        <button
            type="submit"
            class="btn-primary w-full rounded-lg px-4 py-2.5 text-sm font-semibold"
        >
            <span wire:loading.remove>Entrar</span>
            <span wire:loading>Entrando…</span>
        </button>
    </form>
</div>
