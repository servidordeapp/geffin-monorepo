<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

        @livewireStyles

        <style>
            .auth-shell {
                min-height: 100vh;
                display: grid;
                grid-template-columns: 1fr;
                background: var(--bg-app);
            }

            @media (min-width: 1024px) {
                .auth-shell { grid-template-columns: 1.1fr 1fr; }
            }

            /* ── LEFT: brand panel ── */
            .auth-brand {
                display: none;
                position: relative;
                overflow: hidden;
                background:
                    radial-gradient(120% 80% at 10% 0%,  rgba(59,130,246,0.30) 0%, transparent 55%),
                    radial-gradient(90%  70% at 90% 100%, rgba(16,185,129,0.18) 0%, transparent 60%),
                    linear-gradient(160deg, #051C3D 0%, var(--color-brand-primary-900) 55%, #07224A 100%);
                color: var(--fg-on-brand);
                padding: 56px 56px 40px;
                flex-direction: column;
                justify-content: space-between;
            }

            @media (min-width: 1024px) {
                .auth-brand { display: flex; }
            }

            .auth-brand::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
                background-size: 56px 56px;
                mask-image: radial-gradient(80% 60% at 30% 40%, #000 30%, transparent 75%);
                pointer-events: none;
            }

            .auth-brand::after {
                content: "";
                position: absolute;
                inset: 0;
                background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='220' height='220'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  0 0 0 0.06 0'/></filter><rect width='100%' height='100%' filter='url(%23n)'/></svg>");
                opacity: 0.6;
                mix-blend-mode: overlay;
                pointer-events: none;
            }

            .brand-content { position: relative; z-index: 1; max-width: 520px; }

            .brand-mark {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                font-family: var(--font-mono);
                font-size: 12px;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: rgba(255,255,255,0.72);
            }

            .brand-mark-dot {
                width: 28px;
                height: 28px;
                border-radius: 8px;
                background: linear-gradient(135deg, var(--color-brand-primary-500) 0%, var(--color-accent-green-500) 100%);
                box-shadow: 0 8px 24px rgba(59,130,246,0.45);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #fff;
            }

            .brand-eyebrow {
                margin-top: 88px;
                font-family: var(--font-mono);
                font-size: 11px;
                letter-spacing: 0.22em;
                text-transform: uppercase;
                color: var(--color-brand-primary-300);
            }

            .brand-headline {
                font-family: var(--font-display);
                font-size: clamp(2.4rem, 3.4vw, 3.4rem);
                font-weight: 700;
                line-height: 1.04;
                letter-spacing: -0.025em;
                margin-top: 18px;
                color: #fff;
            }

            .brand-headline em {
                font-style: normal;
                background: linear-gradient(90deg, #93C5FD 0%, #6EE7B7 100%);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }

            .brand-lede {
                margin-top: 22px;
                font-size: 1.0625rem;
                line-height: 1.6;
                color: rgba(226,232,240,0.82);
                max-width: 44ch;
            }

            .brand-pillars {
                margin-top: 44px;
                display: grid;
                gap: 18px;
            }

            .pillar {
                display: flex;
                align-items: flex-start;
                gap: 14px;
                padding: 14px 16px;
                border-radius: var(--radius-lg);
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.08);
                backdrop-filter: blur(6px);
            }

            .pillar-icon {
                flex: none;
                width: 36px;
                height: 36px;
                border-radius: var(--radius-md);
                background: rgba(147,197,253,0.14);
                color: #BFD7FE;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .pillar-title {
                font-size: 14px;
                font-weight: 600;
                color: #fff;
                letter-spacing: -0.005em;
            }

            .pillar-body {
                font-size: 13px;
                color: rgba(203,213,225,0.78);
                line-height: 1.5;
                margin-top: 2px;
            }

            .brand-footer {
                position: relative;
                z-index: 1;
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-family: var(--font-mono);
                font-size: 11px;
                letter-spacing: 0.1em;
                color: rgba(148,163,184,0.7);
                text-transform: uppercase;
            }

            .brand-footer a { color: rgba(203,213,225,0.9); text-decoration: none; }
            .brand-footer a:hover { color: #fff; }

            /* ── RIGHT: form panel ── */
            .auth-form-side {
                position: relative;
                display: flex;
                flex-direction: column;
                padding: 40px clamp(20px, 6vw, 64px);
                min-height: 100vh;
            }

            .form-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 13px;
                color: var(--fg-2);
            }

            .form-topbar .mobile-mark {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
                color: var(--fg-1);
            }

            @media (min-width: 1024px) {
                .form-topbar .mobile-mark { display: none; }
            }

            .form-topbar a {
                color: var(--fg-link);
                text-decoration: none;
                font-weight: 500;
            }

            .form-topbar a:hover { text-decoration: underline; }

            .auth-form-wrap {
                flex: 1;
                display: flex;
                align-items: center;
            }

            .auth-form-inner {
                width: 100%;
                max-width: 420px;
                margin: 0 auto;
            }

            .form-eyebrow {
                font-family: var(--font-mono);
                font-size: 11px;
                letter-spacing: 0.22em;
                text-transform: uppercase;
                color: var(--fg-brand);
            }

            .form-title {
                font-family: var(--font-display);
                font-size: 2rem;
                font-weight: 700;
                letter-spacing: -0.025em;
                line-height: 1.1;
                margin-top: 10px;
                color: var(--fg-1);
            }

            .form-subtitle {
                margin-top: 10px;
                font-size: 0.9375rem;
                color: var(--fg-2);
                line-height: 1.55;
            }

            .form-slot { margin-top: 36px; }

            .form-footnote {
                margin-top: 28px;
                font-size: 13px;
                color: var(--fg-2);
                text-align: center;
            }

            .form-footnote a {
                color: var(--fg-link);
                text-decoration: none;
                font-weight: 500;
            }

            .form-footnote a:hover { text-decoration: underline; }

            .auth-bottombar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-family: var(--font-mono);
                font-size: 10.5px;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: var(--fg-3);
                margin-top: 32px;
            }

            .auth-bottombar a { color: var(--fg-2); text-decoration: none; }
            .auth-bottombar a:hover { color: var(--fg-1); }
        </style>
    </head>
    <body style="background: var(--bg-app);">
        <main class="auth-shell">
            {{-- LEFT: brand storytelling --}}
            <aside class="auth-brand" aria-hidden="true">
                <div class="brand-content">
                    <div class="brand-mark">
                        <span class="brand-mark-dot">
                            <i data-lucide="graduation-cap" style="width:16px;height:16px;"></i>
                        </span>
                        {{ config('app.name', 'Geffin') }}
                    </div>

                    <div class="brand-eyebrow">01 — Hub financeiro escolar</div>

                    <h2 class="brand-headline">
                        Gestão financeira <em>auditável</em> para escolas que não podem errar.
                    </h2>

                    <p class="brand-lede">
                        Cobrança, conciliação, mensalidades e cantina em um único painel — com trilha de auditoria de ponta a ponta para cada centavo movimentado.
                    </p>

                    <div class="brand-pillars">
                        <div class="pillar">
                            <span class="pillar-icon"><i data-lucide="shield-check" style="width:18px;height:18px;"></i></span>
                            <div>
                                <div class="pillar-title">Conformidade por padrão</div>
                                <div class="pillar-body">LGPD, controles de acesso por perfil e logs imutáveis de cada operação.</div>
                            </div>
                        </div>
                        <div class="pillar">
                            <span class="pillar-icon"><i data-lucide="receipt" style="width:18px;height:18px;"></i></span>
                            <div>
                                <div class="pillar-title">Idempotência financeira</div>
                                <div class="pillar-body">Retries seguros em pagamentos, charges e estornos — sem duplicidade contábil.</div>
                            </div>
                        </div>
                        <div class="pillar">
                            <span class="pillar-icon"><i data-lucide="line-chart" style="width:18px;height:18px;"></i></span>
                            <div>
                                <div class="pillar-title">Inteligência observadora</div>
                                <div class="pillar-body">IA enriquece dados e antecipa inadimplência — sem nunca alterar o estado do negócio.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="brand-footer">
                    <span>© {{ date('Y') }} {{ config('app.name', 'Geffin') }}</span>
                    <span>v{{ config('app.version', '0.1.0') }} · São Paulo</span>
                </div>
            </aside>

            {{-- RIGHT: form panel --}}
            <section class="auth-form-side">
                <div class="form-topbar">
                    <span class="mobile-mark">
                        <span class="brand-mark-dot" style="width:24px;height:24px;border-radius:6px;">
                            <i data-lucide="graduation-cap" style="width:14px;height:14px;"></i>
                        </span>
                        {{ config('app.name', 'Geffin') }}
                    </span>
                    <span>
                        Novo por aqui? <a href="#">Solicite uma demonstração</a>
                    </span>
                </div>

                <div class="auth-form-wrap">
                    <div class="auth-form-inner">
                        <div class="form-eyebrow">{{ $eyebrow ?? 'Acesso restrito' }}</div>
                        <h1 class="form-title">{{ $heading ?? 'Entre na sua conta' }}</h1>
                        <p class="form-subtitle">
                            {{ $subtitle ?? 'Use suas credenciais institucionais para acessar o painel.' }}
                        </p>

                        <div class="form-slot">
                            {{ $slot }}
                        </div>
                    </div>
                </div>

                <div class="auth-bottombar">
                    <span>Seguro · TLS 1.3</span>
                    <span><a href="#">Suporte</a> · <a href="#">Status</a> · <a href="#">Privacidade</a></span>
                </div>
            </section>
        </main>

        @livewireScripts
        <script>
            document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
            document.addEventListener('livewire:navigated', () => lucide.createIcons());
        </script>
    </body>
</html>
