<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; padding: 40px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 12px; color: #888; }
        .warning { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 4px; padding: 12px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset your password</h1>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <p><a class="btn" href="{{ $url }}">Reset Password</a></p>
        <div class="warning">
            <strong>This link expires in 60 minutes.</strong>
        </div>
        <p>If you did not request a password reset, no further action is required.</p>
        <div class="footer">
            <p>If the button doesn't work, copy and paste this URL into your browser:<br>
            <a href="{{ $url }}">{{ $url }}</a></p>
        </div>
    </div>
</body>
</html>
