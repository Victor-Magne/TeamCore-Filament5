<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'TeamCore') }} - Gestão de RH</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
            <style type="text/tailwindcss">
                @theme {
                    --color-amber-50: #fffbeb;
                    --color-amber-100: #fef3c7;
                    --color-amber-400: #fbbf24;
                    --color-amber-500: #f59e0b;
                    --color-amber-600: #d97706;
                    --color-amber-700: #b45309;
                    --color-amber-800: #92400e;
                    --color-amber-900: #78350f;
                }
            </style>
        @endif
    </head>
    <body class="bg-white dark:bg-slate-950 text-gray-900 dark:text-gray-100 font-['Instrument_Sans']">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 backdrop-blur bg-white/95 dark:bg-slate-950/95 border-b border-gray-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/Teamcorelogo.svg') }}" alt="TeamCore" class="h-8 w-auto">
                    <span class="font-bold text-xl tracking-tight text-amber-900 dark:text-amber-500 uppercase">TEAMCORE</span>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a href="#funcionalidades" class="text-sm font-medium hover:text-amber-700 dark:hover:text-amber-500 transition">Funcionalidades</a>
                    <a href="#paineis" class="text-sm font-medium hover:text-amber-700 dark:hover:text-amber-500 transition">Painéis</a>
                    <a href="#sobre" class="text-sm font-medium hover:text-amber-700 dark:hover:text-amber-500 transition">Sobre</a>
                </div>

                @if (Route::has('login'))
                    <div class="flex gap-3">
                        @auth
                            <a href="{{ url('/admin') }}" class="px-5 py-2 bg-amber-700 text-white rounded-full hover:bg-amber-800 transition text-sm font-semibold shadow-sm">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ url('/admin/login') }}" class="px-5 py-2 text-amber-700 dark:text-amber-500 hover:bg-amber-50 dark:hover:bg-slate-800 rounded-full transition text-sm font-semibold">
                                Login
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-20 pb-32 md:pt-32 md:pb-48">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-50 via-white to-orange-50 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900"></div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="space-y-10">
                        <div class="space-y-6">
                            <h1 class="text-6xl md:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                                <span class="bg-gradient-to-r from-amber-700 to-orange-600 bg-clip-text text-transparent">Gestão de RH</span><br>
                                Inteligente.
                            </h1>
                            <p class="text-xl text-slate-600 dark:text-slate-300 leading-relaxed max-w-xl">
                                O TeamCore é a solução definitiva para modernizar os processos de RH da sua organização. Centralize operações, automatize cálculos e foque no que realmente importa: as pessoas.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ url('/admin/login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-amber-700 text-white rounded-full hover:bg-amber-800 transition font-bold shadow-xl shadow-amber-900/10">
                                Começar Agora
                            </a>
                            <a href="#funcionalidades" class="inline-flex items-center justify-center px-8 py-4 border-2 border-amber-200 dark:border-slate-700 text-amber-900 dark:text-amber-500 rounded-full hover:border-amber-700 transition font-bold">
                                Explorar Funcionalidades
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-8 pt-10 border-t border-amber-100 dark:border-slate-800">
                            <div>
                                <div class="text-4xl font-black text-amber-700 dark:text-amber-500">15</div>
                                <div class="text-sm font-semibold uppercase tracking-wider text-slate-500">Modelos</div>
                            </div>
                            <div>
                                <div class="text-4xl font-black text-amber-700 dark:text-amber-500">16</div>
                                <div class="text-sm font-semibold uppercase tracking-wider text-slate-500">Recursos</div>
                            </div>
                            <div>
                                <div class="text-4xl font-black text-amber-700 dark:text-amber-500">17</div>
                                <div class="text-sm font-semibold uppercase tracking-wider text-slate-500">Políticas</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative hidden md:block">
                        <div class="absolute -inset-4 bg-gradient-to-tr from-amber-500/20 to-orange-500/20 rounded-[2rem] blur-3xl"></div>
                        <div class="relative bg-white dark:bg-slate-800 rounded-[2rem] p-10 shadow-2xl border border-amber-50 dark:border-slate-700">
                            <div class="space-y-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-3 rounded-full bg-amber-600"></div>
                                    <div class="w-24 h-3 rounded-full bg-slate-100 dark:bg-slate-700"></div>
                                </div>
                                <div class="space-y-3">
                                    <div class="h-4 bg-slate-50 dark:bg-slate-900 rounded-xl w-full"></div>
                                    <div class="h-4 bg-slate-50 dark:bg-slate-900 rounded-xl w-5/6"></div>
                                    <div class="h-4 bg-slate-50 dark:bg-slate-900 rounded-xl w-4/6"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 pt-4">
                                    <div class="h-24 bg-amber-50 dark:bg-slate-700/50 rounded-2xl border border-amber-100 dark:border-slate-600"></div>
                                    <div class="h-24 bg-orange-50 dark:bg-slate-700/50 rounded-2xl border border-orange-100 dark:border-slate-600"></div>
                                </div>
                                <div class="h-12 bg-amber-700 rounded-xl w-full shadow-lg shadow-amber-900/20"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Funcionalidades -->
        <section id="funcionalidades" class="py-24 bg-slate-50 dark:bg-slate-900/40">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="max-w-2xl mb-20">
                    <h2 class="text-4xl font-bold text-slate-900 dark:text-white mb-6">Funcionalidades de Elite</h2>
                    <p class="text-xl text-slate-600 dark:text-slate-400 leading-relaxed">
                        Desenvolvido com as tecnologias mais recentes do ecossistema Laravel para garantir performance e segurança.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature: Payroll -->
                    <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-200 dark:border-slate-700 hover:border-amber-500 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3 1.343 3 3-1.343 3-3 3m0-13a9 9 0 110 18 9 9 0 010-18zm0 0V3m0 18v-3"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Processamento Salarial</h3>
                        <p class="text-slate-600 dark:text-slate-400">Cálculo automático de vencimentos, subsídios e descontos com base em contratos e banco de horas.</p>
                    </div>

                    <!-- Feature: Automation -->
                    <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-200 dark:border-slate-700 hover:border-amber-500 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Automação Inteligente</h3>
                        <p class="text-slate-600 dark:text-slate-400">Geração automática de contas de utilizador, contratos e bancos de horas no momento da contratação.</p>
                    </div>

                    <!-- Feature: Security -->
                    <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-200 dark:border-slate-700 hover:border-amber-500 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Isolamento de Dados</h3>
                        <p class="text-slate-600 dark:text-slate-400">Segurança multi-camada com 17 políticas Shield que garantem que cada um aceda apenas ao que deve.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Paineis -->
        <section id="paineis" class="py-24">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-20">
                    <h2 class="text-4xl font-bold mb-6">Ecossistema TeamCore</h2>
                    <p class="text-xl text-slate-600 dark:text-slate-400">Duas interfaces otimizadas para diferentes necessidades.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-12">
                    <!-- Admin Panel -->
                    <div class="relative overflow-hidden bg-amber-700 rounded-[2.5rem] p-12 text-white shadow-2xl">
                        <div class="relative z-10 space-y-8">
                            <div>
                                <h3 class="text-3xl font-bold mb-2">Painel Administrativo</h3>
                                <p class="text-amber-100/80 font-medium">Gestão Estratégica e RH</p>
                            </div>
                            <ul class="space-y-4">
                                <li class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span>Administração de Utilizadores e Roles</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span>Configuração de Unidades e Cargos</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span>Auditoria Spatie Activity Log</span>
                                </li>
                            </ul>
                            <a href="{{ url('/admin') }}" class="inline-block w-full py-4 bg-white text-amber-700 text-center rounded-2xl font-bold hover:bg-amber-50 transition">
                                Aceder ao Admin
                            </a>
                        </div>
                        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-amber-600 rounded-full blur-3xl opacity-50"></div>
                    </div>

                    <!-- App Panel -->
                    <div class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-[2.5rem] p-12 border-2 border-amber-100 dark:border-slate-700 shadow-2xl">
                        <div class="relative z-10 space-y-8">
                            <div>
                                <h3 class="text-3xl font-bold mb-2">Portal do Colaborador</h3>
                                <p class="text-slate-500 font-medium">Self-Service e Produtividade</p>
                            </div>
                            <ul class="space-y-4">
                                <li class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span class="text-slate-600 dark:text-slate-300">Registo de Presença (Check-in/out)</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span class="text-slate-600 dark:text-slate-300">Consulta de Férias e Saldo de Horas</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span class="text-slate-600 dark:text-slate-300">Dashboard de Estatísticas Pessoais</span>
                                </li>
                            </ul>
                            <a href="{{ url('/app') }}" class="inline-block w-full py-4 border-2 border-amber-600 text-amber-700 dark:text-amber-500 text-center rounded-2xl font-bold hover:bg-amber-50 dark:hover:bg-slate-700 transition">
                                Aceder ao Portal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sobre (PAP) -->
        <section id="sobre" class="py-24 bg-amber-50/50 dark:bg-slate-900/60 border-y border-amber-100 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="space-y-6">
                        <h2 class="text-4xl font-bold text-slate-900 dark:text-white">Sobre o Projeto</h2>
                        <div class="prose prose-slate dark:prose-invert">
                            <p class="text-lg text-slate-600 dark:text-slate-400">
                                O <strong>TeamCore</strong> foi desenvolvido como o projeto de Prova de Aptidão Profissional (PAP), representando a culminação de um ciclo de formação técnica em informática.
                            </p>
                            <p class="text-lg text-slate-600 dark:text-slate-400">
                                A aplicação foca-se na resolução de problemas reais de gestão de pessoal, utilizando as melhores práticas de desenvolvimento modernas, como a <strong>TALL Stack</strong> (Tailwind, Alpine.js, Laravel e Livewire).
                            </p>
                        </div>
                        <div class="flex items-center gap-6 pt-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-500 uppercase">Autor</p>
                                <p class="text-xl font-bold text-amber-900 dark:text-amber-500">Victor Gomes</p>
                            </div>
                            <div class="w-px h-10 bg-amber-200 dark:bg-slate-700"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-500 uppercase">Orientadora</p>
                                <p class="text-xl font-bold text-amber-900 dark:text-amber-500">Zélia Capitão</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-1 rounded-3xl shadow-xl border border-amber-100 dark:border-slate-700">
                        <div class="bg-slate-50 dark:bg-slate-900 rounded-[1.4rem] p-8">
                            <h4 class="text-sm font-bold text-amber-700 dark:text-amber-500 uppercase tracking-widest mb-6">Stack Tecnológica</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 font-semibold text-center text-sm">Laravel 13</div>
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 font-semibold text-center text-sm">Filament v5</div>
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 font-semibold text-center text-sm">Livewire v4</div>
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 font-semibold text-center text-sm">Tailwind v4</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 bg-white dark:bg-slate-950">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/Teamcorelogo.svg') }}" alt="TeamCore" class="h-6 w-auto">
                        <span class="font-bold text-lg tracking-tight text-amber-900 dark:text-amber-500 uppercase">TEAMCORE</span>
                    </div>

                    <p class="text-slate-500 text-sm">
                        &copy; 2026 TeamCore. PAP - Victor Gomes. Todos os direitos reservados.
                    </p>

                    <div class="flex gap-6">
                        <a href="#funcionalidades" class="text-slate-400 hover:text-amber-700 transition underline decoration-amber-200 decoration-2 underline-offset-4">Funcionalidades</a>
                        <a href="#sobre" class="text-slate-400 hover:text-amber-700 transition underline decoration-amber-200 decoration-2 underline-offset-4">Sobre</a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
