<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-base-200 font-sans antialiased">
        <main class="grid min-h-screen grid-cols-1 lg:grid-cols-[1.1fr_1fr]">
            {{-- LEFT: brand storytelling --}}
            <aside
                aria-hidden="true"
                class="relative hidden flex-col justify-between overflow-hidden p-14 text-white lg:flex
                       bg-[linear-gradient(160deg,#051C3D_0%,#0a2a52_55%,#07224A_100%)]"
            >
                {{-- subtle grid overlay --}}
                <div class="pointer-events-none absolute inset-0 opacity-90
                            [background-image:linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)]
                            [background-size:56px_56px]
                            [mask-image:radial-gradient(80%_60%_at_30%_40%,#000_30%,transparent_75%)]"></div>

                <div class="relative mb-2 z-10 max-w-[520px]">
                    <div class="inline-flex items-center gap-2.5 font-mono text-xs uppercase tracking-[0.14em] text-white/70">
                        <span class="inline-flex size-7 items-center justify-center rounded-lg text-white shadow-lg shadow-primary/40
                                     bg-gradient-to-br from-primary to-success">
                            <x-icon-academic-cap class="size-4" />
                        </span>
                        {{ config('app.name', 'Geffin') }}
                    </div>

                    <div class="mt-[88px] font-mono text-[11px] uppercase tracking-[0.22em] text-info">
                        01 — Hub financeiro escolar
                    </div>

                    <h2 class="mt-[18px] font-display text-[clamp(2.4rem,3.4vw,3.4rem)] font-bold leading-[1.04] tracking-tight text-white">
                        Gestão financeira
                        <em class="bg-gradient-to-r from-[#93C5FD] to-[#6EE7B7] bg-clip-text not-italic text-transparent">auditável</em>
                        para escolas que não podem errar.
                    </h2>

                    <p class="mt-[22px] max-w-[44ch] text-[1.0625rem] leading-relaxed text-slate-200/80">
                        Cobrança, conciliação, mensalidades e cantina em um único painel — com trilha de auditoria de ponta a ponta para cada centavo movimentado.
                    </p>

                    <div class="mt-11 grid gap-[18px]">
                        <div class="flex items-start gap-3.5 rounded-box border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                            <span class="inline-flex size-9 flex-none items-center justify-center rounded-field bg-[#93C5FD]/15 text-[#BFD7FE]">
                                <x-icon-shield-check class="size-[18px]" />
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-white">Conformidade por padrão</div>
                                <div class="mt-0.5 text-[13px] leading-snug text-slate-300/80">LGPD, controles de acesso por perfil e logs imutáveis de cada operação.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5 rounded-box border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                            <span class="inline-flex size-9 flex-none items-center justify-center rounded-field bg-[#93C5FD]/15 text-[#BFD7FE]">
                                <x-icon-receipt-percent class="size-[18px]" />
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-white">Idempotência financeira</div>
                                <div class="mt-0.5 text-[13px] leading-snug text-slate-300/80">Retries seguros em pagamentos, charges e estornos — sem duplicidade contábil.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5 rounded-box border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                            <span class="inline-flex size-9 flex-none items-center justify-center rounded-field bg-[#93C5FD]/15 text-[#BFD7FE]">
                                <x-icon-chart-line class="size-[18px]" />
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-white">Inteligência observadora</div>
                                <div class="mt-0.5 text-[13px] leading-snug text-slate-300/80">IA enriquece dados e antecipa inadimplência — sem nunca alterar o estado do negócio.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 flex items-center justify-between font-mono text-[11px] uppercase tracking-[0.1em] text-slate-400/70">
                    <span>© {{ date('Y') }} {{ config('app.name', 'Geffin') }}</span>
                    <span>v{{ config('app.version', '0.1.0') }} · São Paulo</span>
                </div>
            </aside>

            {{-- RIGHT: form panel --}}
            <section class="relative flex min-h-screen flex-col px-[clamp(20px,6vw,64px)] py-10">
                <div class="flex items-center justify-between text-[13px] text-base-content/70">
                    <span class="inline-flex items-center gap-2 font-semibold text-base-content lg:hidden">
                        <span class="inline-flex size-6 items-center justify-center rounded-md text-white bg-gradient-to-br from-primary to-success">
                            <x-icon-academic-cap class="size-3.5" />
                        </span>
                        {{ config('app.name', 'Geffin') }}
                    </span>
                    <span>
                        Novo por aqui? <a href="#" class="link link-primary font-medium no-underline hover:underline">Solicite uma demonstração</a>
                    </span>
                </div>

                <div class="flex flex-1 items-center">
                    <div class="mx-auto w-full max-w-[420px]">
                        <div class="font-mono text-[11px] uppercase tracking-[0.22em] text-primary">{{ $eyebrow ?? 'Acesso restrito' }}</div>
                        <h1 class="mt-2.5 font-display text-[2rem] font-bold leading-tight tracking-tight text-base-content">{{ $heading ?? 'Entre na sua conta' }}</h1>
                        <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-base-content/70">
                            {{ $subtitle ?? 'Use suas credenciais institucionais para acessar o painel.' }}
                        </p>

                        <div class="mt-9">
                            {{ $slot }}
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between font-mono text-[10.5px] uppercase tracking-[0.14em] text-base-content/50">
                    <span>Seguro · TLS 1.3</span>
                    <span><a href="#" class="hover:text-base-content">Suporte</a> · <a href="#" class="hover:text-base-content">Status</a> · <a href="#" class="hover:text-base-content">Privacidade</a></span>
                </div>
            </section>
        </main>

        @livewireScripts
    </body>
</html>
