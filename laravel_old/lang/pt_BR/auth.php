<?php

declare(strict_types=1);

return [
    'failed' => 'Essas credenciais não foram encontradas em nossos registros.',
    'password' => 'A senha informada está incorreta.',
    'throttle' => 'Muitas tentativas de login. Tente novamente em :seconds segundos.',

    'forgot' => [
        'title' => 'Esqueci minha senha',
        'subtitle' => 'Informe seu e-mail e enviaremos um link para você criar uma nova senha.',
        'submit' => 'Enviar link de redefinição',
        'back_to_login' => 'Voltar para o login',
    ],

    'reset' => [
        'title' => 'Criar nova senha',
        'subtitle' => 'Escolha uma senha forte com pelo menos 12 caracteres.',
        'password_label' => 'Nova senha',
        'password_confirmation_label' => 'Confirme a nova senha',
        'submit' => 'Atualizar senha',
        'request_new_link' => 'Pedir novo link',
    ],

    'mail' => [
        'reset' => [
            'subject' => 'Redefinição de senha — Geffin',
        ],
    ],
];
