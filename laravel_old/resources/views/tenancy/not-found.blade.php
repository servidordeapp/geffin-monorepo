<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambiente Não Encontrado</title>
    <style>
        :root { --blue:#1E40AF; --ink:#0F172A; --muted:#64748B; --bg:#F8FAFC; --line:#E2E8F0; }
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: var(--bg); color: var(--ink); padding: 1.5rem; }
        .card { position: relative; background: #fff; padding: 2.25rem 2rem; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 12px 32px rgb(15 23 42 / 0.10); max-width: 480px; text-align: center; overflow: hidden; }
        .card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--blue), #93C5FD 60%, transparent); }
        .badge { display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: var(--blue); margin-bottom: 1.25rem; }
        h1 { font-size: 1.375rem; font-weight: 700; letter-spacing: -0.02em; color: var(--ink); margin: 0 0 0.6rem; }
        p { color: var(--muted); line-height: 1.6; margin: 0; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </div>
        <h1>Ambiente Não Encontrado</h1>
        <p>{{ $message }}</p>
    </div>
</body>
</html>
