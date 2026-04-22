<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="
        $watch('dark', v => { localStorage.setItem('theme', v ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', v) });
        document.documentElement.classList.toggle('dark', dark)
      "
      :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'TeamCore') }} - Gestão de RH</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
        @endif

        <style>
            /* ── Paleta principal ── */
            :root {
                --primary:   #582f0e;
                --secondary: #7f4f24;
                --success:   #2d5016;
                --warning:   #b45309;
                --danger:    #7f1d1d;
                --info:      #936639;
                --gray:      #4b5563;
                --muted:     #6b7280;
                --accent:    #414833;

                /* Light mode surfaces */
                --bg-page:    #fdf8f3;
                --bg-surface: #ffffff;
                --bg-subtle:  #f5ece0;
                --border:     #e5d5c0;
                --border-md:  #c9a87c;

                /* Light mode text */
                --text-heading: #2c1502;
                --text-body:    #3d2008;
                --text-muted:   #7f4f24;

                /* Glow accent */
                --glow: rgba(88, 47, 14, 0.15);
            }

            .dark {
                --bg-page:    #09090b;
                --bg-surface: #1e1510;
                --bg-subtle:  #2a1d10;
                --border:     #3d2910;
                --border-md:  #5c3d1a;

                --text-heading: #f5e6d0;
                --text-body:    #d4b896;
                --text-muted:   #936639;

                --glow: rgba(180, 83, 9, 0.12);
            }

            /* ── Animations ── */
            @keyframes fadeUp {
                from { opacity: 0; transform: translateY(28px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to   { opacity: 1; }
            }
            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50%       { transform: translateY(-8px); }
            }
            @keyframes pulseRing {
                0%   { transform: scale(1); opacity: 0.7; }
                100% { transform: scale(1.8); opacity: 0; }
            }
            @keyframes shimmer {
                0%   { background-position: -200% center; }
                100% { background-position:  200% center; }
            }
            @keyframes countUp {
                from { opacity: 0; transform: scale(0.75); }
                to   { opacity: 1; transform: scale(1); }
            }
            @keyframes borderGlow {
                0%, 100% { box-shadow: 0 0 0 0 var(--glow); }
                50%       { box-shadow: 0 0 18px 4px var(--glow); }
            }
            @keyframes slideRight {
                from { opacity: 0; transform: translateX(-20px); }
                to   { opacity: 1; transform: translateX(0); }
            }

            .anim-fade-up  { animation: fadeUp 0.65s ease both; }
            .anim-fade-in  { animation: fadeIn 0.55s ease both; }
            .anim-slide-r  { animation: slideRight 0.55s ease both; }
            .anim-float    { animation: float 5s ease-in-out infinite; }
            .anim-count-up { animation: countUp 0.55s cubic-bezier(0.175,0.885,0.32,1.275) both; }

            .delay-100 { animation-delay: 0.10s; }
            .delay-200 { animation-delay: 0.20s; }
            .delay-300 { animation-delay: 0.30s; }
            .delay-400 { animation-delay: 0.40s; }
            .delay-500 { animation-delay: 0.50s; }
            .delay-600 { animation-delay: 0.60s; }
            .delay-700 { animation-delay: 0.70s; }
            .delay-800 { animation-delay: 0.80s; }

            /* ── Base ── */
            body {
                background: var(--bg-page);
                color: var(--text-body);
                font-family: 'Instrument Sans', sans-serif;
                transition: background 0.35s ease, color 0.35s ease;
            }

            /* ── Shimmer text ── */
            .shimmer-text {
                background: linear-gradient(90deg,
                    var(--primary) 0%,
                    var(--warning) 35%,
                    var(--info)    60%,
                    var(--primary) 100%);
                background-size: 200% auto;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .shimmer-text:hover {
                animation: shimmer 1.8s linear infinite;
            }

            /* ── Pulse dot ── */
            .pulse-dot::after {
                content: '';
                position: absolute;
                inset: -4px;
                border-radius: 50%;
                background: var(--warning);
                animation: pulseRing 1.6s ease-out infinite;
            }

            /* ── Scroll reveal ── */
            .reveal {
                opacity: 0;
                transform: translateY(24px);
                transition: opacity 0.6s ease, transform 0.6s ease;
            }
            .reveal.visible {
                opacity: 1;
                transform: translateY(0);
            }

            /* ── Card hover ── */
            .card-hover {
                transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            }
            .card-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 18px 38px -10px var(--glow);
                border-color: var(--border-md) !important;
            }

            /* ── Button ── */
            .btn-press { transition: transform 0.15s ease, box-shadow 0.15s ease; }
            .btn-press:hover  { transform: translateY(-2px); box-shadow: 0 8px 20px -4px rgba(88,47,14,0.35); }
            .btn-press:active { transform: translateY(0); box-shadow: none; }

            /* ── Nav link ── */
            .nav-link { position: relative; text-decoration: none; }
            .nav-link::after {
                content: '';
                position: absolute;
                bottom: -2px; left: 0;
                width: 0; height: 2px;
                background: var(--primary);
                transition: width 0.25s ease;
                border-radius: 2px;
            }
            .nav-link:hover::after { width: 100%; }

            /* ── Icon rotate ── */
            .icon-wrap { transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1); }
            .card-hover:hover .icon-wrap { transform: rotate(-8deg) scale(1.12); }

            /* ── Theme toggle ── */
            .theme-toggle {
                position: relative;
                width: 40px; height: 22px;
                background: var(--warning);
                border-radius: 11px;
                cursor: pointer;
                transition: background 0.3s;
                border: none; outline: none;
                flex-shrink: 0;
            }
            .theme-toggle::after {
                content: '';
                position: absolute;
                top: 3px; left: 3px;
                width: 16px; height: 16px;
                background: white;
                border-radius: 50%;
                transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
            }
            .dark .theme-toggle { background: #3d2910; }
            .dark .theme-toggle::after { transform: translateX(18px); }

            /* ── Stack badge ── */
            .stack-badge { transition: transform 0.2s ease, background 0.2s ease; }
            .stack-badge:hover {
                transform: scale(1.05);
                background: var(--bg-subtle) !important;
                border-color: var(--border-md) !important;
            }

            /* ── Animated border on hero card ── */
            .hero-card {
                animation: borderGlow 4s ease-in-out infinite;
            }

            /* ── Navbar ── */
            nav {
                background: color-mix(in srgb, var(--bg-page) 95%, transparent);
                border-bottom: 1px solid var(--border);
                backdrop-filter: blur(12px);
                transition: background 0.35s ease, border-color 0.35s ease;
            }

            /* ── Surface helpers ── */
            .surface { background: var(--bg-surface); border: 1px solid var(--border); }
            .surface-subtle { background: var(--bg-subtle); border: 1px solid var(--border); }
        </style>
    </head>

    <body>

        <!-- ── Navigation ── -->
        <nav class="sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="anim-fade-in flex items-center gap-3">
                    <img src="{{ asset('images/Teamcorelogo.svg') }}" alt="TeamCore" class="h-8 w-auto">
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a href="#funcionalidades" class="nav-link text-sm font-medium transition-colors" style="color:var(--text-muted)">Funcionalidades</a>
                    <a href="#paineis"         class="nav-link text-sm font-medium transition-colors" style="color:var(--text-muted)">Painéis</a>
                    <a href="#sobre"           class="nav-link text-sm font-medium transition-colors" style="color:var(--text-muted)">Sobre</a>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color:var(--warning)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/></svg>
                        <button class="theme-toggle" @click="dark = !dark" :aria-label="dark ? 'Mudar para modo claro' : 'Mudar para modo escuro'"></button>
                        <svg class="w-4 h-4" style="color:var(--text-muted)" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                    </div>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/admin') }}" class="btn-press px-5 py-2 rounded-full text-sm font-semibold text-white shadow-sm transition-colors" style="background:var(--primary)">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ url('/admin/login') }}" class="btn-press px-5 py-2 rounded-full transition-colors text-sm font-semibold" style="color:var(--primary);border:1.5px solid var(--border-md)">
                                Login
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </nav>

        <!-- ── Hero ── -->
        <section class="relative overflow-hidden pt-20 pb-32 md:pt-32 md:pb-48">
            <div class="absolute inset-0 transition-colors duration-500" style="background: var(--bg-page)"></div>

            <!-- Decorative blobs -->
            <div class="absolute top-16 right-8 w-72 h-72 rounded-full pointer-events-none" style="background:color-mix(in srgb, var(--warning) 10%, transparent); filter:blur(60px)"></div>
            <div class="absolute bottom-16 left-8 w-56 h-56 rounded-full pointer-events-none" style="background:color-mix(in srgb, var(--primary) 10%, transparent); filter:blur(50px)"></div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="space-y-10">
                        <div class="space-y-6">
                            <div class="anim-fade-up inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold" style="background:color-mix(in srgb, var(--warning) 12%, transparent); color:var(--warning); border:1px solid color-mix(in srgb, var(--warning) 25%, transparent)">
                                <span class="relative w-2 h-2 flex-shrink-0">
                                    <span class="pulse-dot absolute inset-0 rounded-full" style="background:var(--warning)"></span>
                                    <span class="absolute inset-0 rounded-full" style="background:var(--warning)"></span>
                                </span>
                                PAP 2025–2026 · Victor Gomes
                            </div>

                            <h1 class="anim-fade-up delay-100 text-6xl md:text-7xl font-extrabold tracking-tight" style="color:var(--text-heading)">
                                <span class="shimmer-text">Gestão de RH</span><br>
                                Inteligente.
                            </h1>
                            <p class="anim-fade-up delay-200 text-xl leading-relaxed max-w-xl" style="color:var(--text-muted)">
                                O TeamCore é a solução definitiva para modernizar os processos de RH da sua organização. Centralize operações, automatize cálculos e foque no que realmente importa: as pessoas.
                            </p>
                        </div>

                        <div class="anim-fade-up delay-300 flex flex-col sm:flex-row gap-4">
                            <a href="{{ url('/admin/login') }}" class="btn-press inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full font-bold text-white" style="background:var(--primary)">
                                Começar Agora
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                            <a href="#funcionalidades" class="btn-press inline-flex items-center justify-center px-8 py-4 rounded-full font-bold transition-colors" style="border:2px solid var(--border-md); color:var(--secondary)">
                                Explorar Funcionalidades
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="anim-fade-up delay-400 grid grid-cols-3 gap-8 pt-10" style="border-top:1px solid var(--border)">
                            <div class="anim-count-up delay-500">
                                <div class="text-4xl font-black" style="color:var(--primary)">15</div>
                                <div class="text-sm font-semibold uppercase tracking-wider" style="color:var(--muted)">Modelos</div>
                            </div>
                            <div class="anim-count-up delay-600">
                                <div class="text-4xl font-black" style="color:var(--primary)">16</div>
                                <div class="text-sm font-semibold uppercase tracking-wider" style="color:var(--muted)">Recursos</div>
                            </div>
                            <div class="anim-count-up delay-700">
                                <div class="text-4xl font-black" style="color:var(--primary)">17</div>
                                <div class="text-sm font-semibold uppercase tracking-wider" style="color:var(--muted)">Políticas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Mock UI card -->
                    <div class="anim-float relative hidden md:block anim-fade-in delay-400">
                        <div class="absolute -inset-4 rounded-[2rem] pointer-events-none" style="background:color-mix(in srgb, var(--warning) 15%, transparent); filter:blur(40px)"></div>
                        <div class="hero-card relative surface rounded-[2rem] p-10 shadow-2xl transition-colors duration-300">
                            <div class="space-y-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-3 rounded-full" style="background:var(--primary)"></div>
                                    <div class="w-24 h-3 rounded-full" style="background:var(--bg-subtle)"></div>
                                    <div class="ml-auto flex gap-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full" style="background:var(--danger)"></div>
                                        <div class="w-2.5 h-2.5 rounded-full" style="background:var(--warning)"></div>
                                        <div class="w-2.5 h-2.5 rounded-full" style="background:var(--success)"></div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="h-4 rounded-xl w-full" style="background:var(--bg-subtle)"></div>
                                    <div class="h-4 rounded-xl w-5/6" style="background:var(--bg-subtle)"></div>
                                    <div class="h-4 rounded-xl w-4/6" style="background:var(--bg-subtle)"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 pt-4">
                                    <div class="h-24 rounded-2xl flex items-center justify-center" style="background:color-mix(in srgb, var(--secondary) 10%, transparent); border:1px solid var(--border)">
                                        <div class="w-8 h-8 rounded-xl" style="background:color-mix(in srgb, var(--secondary) 30%, transparent)"></div>
                                    </div>
                                    <div class="h-24 rounded-2xl flex items-center justify-center" style="background:color-mix(in srgb, var(--info) 10%, transparent); border:1px solid var(--border)">
                                        <div class="w-8 h-8 rounded-xl" style="background:color-mix(in srgb, var(--info) 30%, transparent)"></div>
                                    </div>
                                </div>
                                <div class="h-12 rounded-xl w-full flex items-center justify-center" style="background:var(--primary)">
                                    <div class="w-24 h-3 rounded-full" style="background:color-mix(in srgb, white 30%, transparent)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Funcionalidades ── -->
        <section id="funcionalidades" class="py-24 transition-colors duration-300" style="background:var(--bg-subtle)">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="reveal max-w-2xl mb-20">
                    <h2 class="text-4xl font-bold mb-6" style="color:var(--text-heading)">Funcionalidades de Elite</h2>
                    <p class="text-xl leading-relaxed" style="color:var(--text-muted)">
                        Desenvolvido com as tecnologias mais recentes do ecossistema Laravel para garantir performance e segurança.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Card 1 -->
                    <div class="reveal card-hover surface p-8 rounded-3xl transition-colors">
                        <div class="icon-wrap w-14 h-14 rounded-2xl flex items-center justify-center mb-6" style="background:color-mix(in srgb, var(--primary) 10%, transparent)">
                            <svg class="w-7 h-7" style="color:var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3 1.343 3 3-1.343 3-3 3m0-13a9 9 0 110 18 9 9 0 010-18zm0 0V3m0 18v-3"/></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color:var(--text-heading)">Processamento Salarial</h3>
                        <p style="color:var(--text-muted)">Cálculo automático de vencimentos, subsídios e descontos com base em contratos e banco de horas.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="reveal delay-200 card-hover surface p-8 rounded-3xl transition-colors">
                        <div class="icon-wrap w-14 h-14 rounded-2xl flex items-center justify-center mb-6" style="background:color-mix(in srgb, var(--warning) 10%, transparent)">
                            <svg class="w-7 h-7" style="color:var(--warning)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color:var(--text-heading)">Automação Inteligente</h3>
                        <p style="color:var(--text-muted)">Geração automática de contas de utilizador, contratos e bancos de horas no momento da contratação.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="reveal delay-400 card-hover surface p-8 rounded-3xl transition-colors">
                        <div class="icon-wrap w-14 h-14 rounded-2xl flex items-center justify-center mb-6" style="background:color-mix(in srgb, var(--accent) 12%, transparent)">
                            <svg class="w-7 h-7" style="color:var(--accent)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color:var(--text-heading)">Isolamento de Dados</h3>
                        <p style="color:var(--text-muted)">Segurança multi-camada com 17 políticas Shield que garantem que cada um aceda apenas ao que deve.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Painéis ── -->
        <section id="paineis" class="py-24 transition-colors duration-300" style="background:var(--bg-page)">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="reveal text-center max-w-3xl mx-auto mb-20">
                    <h2 class="text-4xl font-bold mb-6" style="color:var(--text-heading)">Ecossistema TeamCore</h2>
                    <p class="text-xl" style="color:var(--text-muted)">Duas interfaces otimizadas para diferentes necessidades.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-12">
                    <!-- Admin panel -->
                    <div class="reveal card-hover relative overflow-hidden rounded-[2.5rem] p-12 text-white shadow-2xl" style="background:var(--primary)">
                        <div class="relative z-10 space-y-8">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-widest mb-2" style="color:color-mix(in srgb, white 60%, transparent)">Admin</p>
                                <h3 class="text-3xl font-bold mb-2">Painel Administrativo</h3>
                                <p class="font-medium" style="color:color-mix(in srgb, white 70%, transparent)">Gestão Estratégica e RH</p>
                            </div>
                            <ul class="space-y-4">
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" style="color:color-mix(in srgb, white 70%, transparent)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Administração de Utilizadores e Roles</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" style="color:color-mix(in srgb, white 70%, transparent)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Configuração de Unidades e Cargos</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" style="color:color-mix(in srgb, white 70%, transparent)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Auditoria Spatie Activity Log</span>
                                </li>
                            </ul>
                            <a href="{{ url('/admin') }}" class="btn-press inline-block w-full py-4 text-center rounded-2xl font-bold transition-colors" style="background:white; color:var(--primary)">
                                Aceder ao Admin →
                            </a>
                        </div>
                        <div class="absolute -right-20 -bottom-20 w-64 h-64 rounded-full pointer-events-none" style="background:color-mix(in srgb, var(--secondary) 50%, transparent); filter:blur(40px)"></div>
                    </div>

                    <!-- Collaborator portal -->
                    <div class="reveal delay-200 card-hover relative overflow-hidden surface rounded-[2.5rem] p-12 shadow-2xl transition-colors duration-300">
                        <div class="relative z-10 space-y-8">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-widest mb-2" style="color:var(--info)">Colaborador</p>
                                <h3 class="text-3xl font-bold mb-2" style="color:var(--text-heading)">Portal do Colaborador</h3>
                                <p class="font-medium" style="color:var(--text-muted)">Self-Service e Produtividade</p>
                            </div>
                            <ul class="space-y-4">
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" style="color:var(--secondary)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span style="color:var(--text-muted)">Registo de Presença (Check-in/out)</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" style="color:var(--secondary)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span style="color:var(--text-muted)">Consulta de Férias e Saldo de Horas</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" style="color:var(--secondary)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span style="color:var(--text-muted)">Dashboard de Estatísticas Pessoais</span>
                                </li>
                            </ul>
                            <a href="{{ url('/app') }}" class="btn-press inline-block w-full py-4 text-center rounded-2xl font-bold transition-colors" style="border:2px solid var(--secondary); color:var(--secondary)">
                                Aceder ao Portal →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Sobre ── -->
        <section id="sobre" class="py-24 transition-colors duration-300" style="background:var(--bg-subtle); border-top:1px solid var(--border); border-bottom:1px solid var(--border)">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="reveal space-y-6">
                        <h2 class="text-4xl font-bold" style="color:var(--text-heading)">Sobre o Projeto</h2>
                        <p class="text-lg leading-relaxed" style="color:var(--text-muted)">
                            O <strong style="color:var(--text-heading)">TeamCore</strong> foi desenvolvido como o projeto de Prova de Aptidão Profissional (PAP), representando a culminação de um ciclo de formação técnica em informática.
                        </p>
                        <p class="text-lg leading-relaxed" style="color:var(--text-muted)">
                            A aplicação foca-se na resolução de problemas reais de gestão de pessoal, utilizando as melhores práticas de desenvolvimento modernas, como a <strong style="color:var(--text-heading)">TALL Stack</strong> (Tailwind, Alpine.js, Laravel e Livewire).
                        </p>
                        <div class="flex items-center gap-6 pt-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--muted)">Autor</p>
                                <p class="text-xl font-bold" style="color:var(--primary)">Victor Gomes</p>
                            </div>
                            <div class="w-px h-10" style="background:var(--border-md)"></div>
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wider" style="color:var(--muted)">Orientadora</p>
                                <p class="text-xl font-bold" style="color:var(--primary)">Zélia Capitão</p>
                            </div>
                        </div>
                    </div>

                    <div class="reveal delay-300 surface p-1 rounded-3xl shadow-xl transition-colors duration-300">
                        <div class="rounded-[1.4rem] p-8 transition-colors duration-300" style="background:var(--bg-page)">
                            <h4 class="text-sm font-bold uppercase tracking-widest mb-6" style="color:var(--secondary)">Stack Tecnológica</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="stack-badge p-4 surface rounded-xl font-semibold text-center text-sm cursor-default" style="color:var(--text-heading)">Laravel 13</div>
                                <div class="stack-badge p-4 surface rounded-xl font-semibold text-center text-sm cursor-default" style="color:var(--text-heading)">Filament v5</div>
                                <div class="stack-badge p-4 surface rounded-xl font-semibold text-center text-sm cursor-default" style="color:var(--text-heading)">Livewire v4</div>
                                <div class="stack-badge p-4 surface rounded-xl font-semibold text-center text-sm cursor-default" style="color:var(--text-heading)">Tailwind v4</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Footer ── -->
        <footer class="py-12 transition-colors duration-300" style="background:var(--bg-surface); border-top:1px solid var(--border)">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/Teamcorelogo.svg') }}" alt="TeamCore" class="h-6 w-auto">
                    </div>
                    <p class="text-sm" style="color:var(--muted)">
                        &copy; 2026 TeamCore · PAP · Victor Gomes · Todos os direitos reservados.
                    </p>
                    <div class="flex gap-6">
                        <a href="#funcionalidades" class="nav-link text-sm transition-colors" style="color:var(--muted)">Funcionalidades</a>
                        <a href="#sobre"           class="nav-link text-sm transition-colors" style="color:var(--muted)">Sobre</a>
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <script>
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('visible');
                        observer.unobserve(e.target);
                    }
                });
            }, { threshold: 0.15 });

            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        </script>
    </body>
</html>