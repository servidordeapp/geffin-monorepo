<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.guest', ['eyebrow' => 'Recuperar acesso', 'heading' => 'Redefinir senha', 'subtitle' => 'Escolha uma nova senha para a sua conta.'])] class extends Component {
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function (User $user): void {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        session()->flash('status', __($status));

        $this->redirect(route('login'), navigate: true);
    }
};
?>

<div class="w-full">
    <form wire:submit="resetPassword" class="space-y-4" novalidate>
        <fieldset class="fieldset">
            <legend class="fieldset-legend">E-mail institucional</legend>
            <input type="email" wire:model="email" placeholder="voce@escola.edu.br" autocomplete="email"
                class="input w-full @error('email') input-error @enderror" />
            @error('email')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Nova senha</legend>
            <input type="password" wire:model="password" placeholder="••••••••" autofocus autocomplete="new-password"
                class="input w-full @error('password') input-error @enderror" />
            @error('password')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">Confirmar nova senha</legend>
            <input type="password" wire:model="password_confirmation" placeholder="••••••••"
                autocomplete="new-password" class="input w-full" />
        </fieldset>

        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled"
            wire:target="resetPassword">
            <span wire:loading.remove wire:target="resetPassword" class="inline-flex items-center gap-2">
                Redefinir senha
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </span>
            <span wire:loading wire:target="resetPassword" class="inline-flex items-center gap-2">
                <span class="loading loading-spinner loading-sm"></span>
                Redefinindo…
            </span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-base-content/70">
        Lembrou a senha?
        <a href="{{ route('login') }}" wire:navigate class="link link-primary font-medium no-underline">
            Voltar para o login
        </a>
    </p>
</div>
