<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; padding: 40px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify your email address</h1>
        <p>Please click the button below to verify your email address.</p>
        <p><a class="btn" href="{{ $url }}">Verify Email</a></p>
        <p>If you did not create an account, no further action is required.</p>
        <div class="footer">
            <p>This link expires in 144 hours.</p>
            <p>If the button doesn't work, copy and paste this URL into your browser:<br>
            <a href="{{ $url }}">{{ $url }}</a></p>
        </div>
    </div>
</body>
</html>
