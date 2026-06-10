<x-mail::message>
@if($recipientName)
{{ __('Olá,') }} {{ $recipientName }}.
@else
{{ __('Olá.') }}
@endif

{{ __('Recebemos um pedido de redefinição de senha para sua conta.') }}

<x-mail::button :url="$url">
{{ __('Criar nova senha') }}
</x-mail::button>

{{ __('Este link expira em') }} {{ $expiresInMinutes }} {{ __('minutos.') }}

{{ __('Se você não solicitou esta redefinição, pode ignorar este e-mail. Sua senha atual continua válida.') }}

{{ config('app.name') }}
</x-mail::message>
