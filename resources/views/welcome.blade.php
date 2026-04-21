<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }} - Gestão de Recursos Humanos</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /* Placeholder for styles */
            </style>
        @endif
    </head>
    <body class="bg-white dark:bg-slate-950 text-gray-900 dark:text-gray-100">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 backdrop-blur bg-white/95 dark:bg-slate-950/95 border-b border-gray-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/Teamcorelogo.svg') }}" alt="TeamCore" class="h-8 w-auto">
                    <span class="font-semibold text-lg"></span>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a href="#funcionalidades" class="text-sm hover:text-amber-700 dark:hover:text-amber-500 transition">Funcionalidades</a>
                    <a href="#paineis" class="text-sm hover:text-amber-700 dark:hover:text-amber-500 transition">Painéis</a>
                    <a href="#sobre" class="text-sm hover:text-amber-700 dark:hover:text-amber-500 transition">Sobre</a>
                </div>

                @if (Route::has('login'))
                    <div class="flex gap-3">
                        @auth
                            <a href="{{ route('filament.admin.pages.dashboard') }}" class="px-4 py-2 bg-amber-700 text-white rounded-lg hover:bg-amber-800 dark:bg-amber-600 dark:hover:bg-amber-700 transition text-sm font-medium">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('filament.admin.auth.login') }}" class="px-4 py-2 text-amber-700 dark:text-amber-500 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg transition text-sm font-medium">
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
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <!-- Conteúdo -->
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <h1 class="text-5xl md:text-6xl font-bold bg-gradient-to-r from-amber-900 via-amber-700 to-orange-600 dark:from-amber-400 dark:via-amber-300 dark:to-orange-300 bg-clip-text text-transparent">
                                Gestão de RH Simplificada
                            </h1>
                            <p class="text-xl text-gray-600 dark:text-gray-300">
                                TeamCore é uma plataforma moderna para gestão de recursos humanos. Centralize dados, automatize processos e otimize a gestão do seu tempo.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            @auth
                                <a href="{{ route('filament.admin.pages.dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 bg-amber-700 text-white rounded-lg hover:bg-amber-800 dark:bg-amber-600 dark:hover:bg-amber-700 transition font-medium shadow-lg">
                                    Ir para Dashboard
                                </a>
                            @else
                                <a href="{{ route('filament.admin.auth.login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-amber-700 text-white rounded-lg hover:bg-amber-800 dark:bg-amber-600 dark:hover:bg-amber-700 transition font-medium shadow-lg">
                                    Fazer Login
                                </a>
                            @endif
                            <a href="#funcionalidades" class="inline-flex items-center justify-center px-8 py-4 border-2 border-amber-700 text-amber-700 dark:border-amber-500 dark:text-amber-500 rounded-lg hover:bg-amber-50 dark:hover:bg-slate-800 transition font-medium">
                                Saber Mais
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-6 pt-8 border-t border-gray-200 dark:border-slate-700">
                            <div>
                                <div class="text-3xl font-bold text-amber-700 dark:text-amber-500">14</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Modelos</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-amber-700 dark:text-amber-500">15</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Recursos</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-amber-700 dark:text-amber-500">16</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Políticas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Ilustração -->
                    <div class="relative hidden md:block">
                        <div class="absolute inset-0 bg-gradient-to-br from-amber-400/20 to-orange-400/20 rounded-3xl blur-3xl"></div>
                        <div class="relative bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl border border-amber-100 dark:border-slate-700">
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full bg-amber-700 dark:bg-amber-500"></div>
                                    <div class="h-2 flex-1 bg-gradient-to-r from-amber-700/30 to-transparent rounded"></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="h-2 bg-gray-200 dark:bg-slate-700 rounded w-4/5"></div>
                                    <div class="h-2 bg-gray-200 dark:bg-slate-700 rounded w-3/4"></div>
                                    <div class="h-2 bg-gray-200 dark:bg-slate-700 rounded w-5/6"></div>
                                </div>
                                <div class="pt-6 space-y-3">
                                    <div class="flex gap-2">
                                        <div class="flex-1 h-12 bg-amber-100 dark:bg-slate-700 rounded"></div>
                                        <div class="flex-1 h-12 bg-amber-100 dark:bg-slate-700 rounded"></div>
                                    </div>
                                    <div class="h-8 bg-gray-100 dark:bg-slate-700 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Funcionalidades -->
        <section id="funcionalidades" class="py-20 md:py-32 bg-gray-50 dark:bg-slate-900/50 border-y border-gray-200 dark:border-slate-700">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold mb-4">Funcionalidades Principais</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Tudo o que você precisa para gerir recursos humanos de forma eficiente</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-gray-200 dark:border-slate-700 hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-amber-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 19H9a6 6 0 0112 0v1H3v-1a6 6 0 0112 0h6z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Gestão de Funcionários</h3>
                        <p class="text-gray-600 dark:text-gray-400">Centralize dados pessoais, profissionais e de contatos de todos os seus colaboradores</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-gray-200 dark:border-slate-700 hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-amber-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Gestão de Contratos</h3>
                        <p class="text-gray-600 dark:text-gray-400">Crie e gerencie contratos com diferentes tipos de vínculo e acompanhamento de status</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-gray-200 dark:border-slate-700 hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-amber-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Banco de Horas</h3>
                        <p class="text-gray-600 dark:text-gray-400">Controle e acompanhamento automático de horas trabalhadas e acumuladas</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-gray-200 dark:border-slate-700 hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-amber-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Gestão de Férias</h3>
                        <p class="text-gray-600 dark:text-gray-400">Solicite, aprove e acompanhe férias com workflows de aprovação</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-gray-200 dark:border-slate-700 hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-amber-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Segurança e Controlo</h3>
                        <p class="text-gray-600 dark:text-gray-400">Controlo de acesso baseado em funções (RBAC) com 16 políticas granulares</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-gray-200 dark:border-slate-700 hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-amber-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-700 dark:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Auditoria Completa</h3>
                        <p class="text-gray-600 dark:text-gray-400">Rastreamento completo de todas as operações com Activity Log</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Painel Admin -->
        <section id="paineis" class="py-20 md:py-32">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold mb-4">Painel de Administração</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Interface moderna para gestão completa de recursos humanos</p>
                </div>

                <div class="max-w-3xl mx-auto">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-700/20 to-orange-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition opacity-0 group-hover:opacity-100"></div>
                        <div class="relative bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-gray-200 dark:border-slate-700">
                            <div class="bg-gradient-to-br from-amber-700 to-orange-600 px-8 py-8">
                                <h3 class="text-3xl font-bold text-white mb-2">Painel Admin</h3>
                                <p class="text-amber-100">Acesso em /filament/admin</p>
                            </div>
                            <div class="p-8">
                                <p class="text-gray-600 dark:text-gray-300 mb-8">Gestão completa do sistema com funcionalidades administrativas e relatórios estratégicos</p>
                                <div class="grid md:grid-cols-2 gap-8">
                                    <div>
                                        <h4 class="font-semibold text-lg mb-4 text-amber-700 dark:text-amber-500">Funcionalidades</h4>
                                        <ul class="space-y-3 text-sm">
                                            <li class="flex items-center gap-3"><svg class="w-5 h-5 text-amber-700 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Gestão de funcionários</li>
                                            <li class="flex items-center gap-3"><svg class="w-5 h-5 text-amber-700 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Gestão de contratos</li>
                                            <li class="flex items-center gap-3"><svg class="w-5 h-5 text-amber-700 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Banco de horas</li>
                                            <li class="flex items-center gap-3"><svg class="w-5 h-5 text-amber-700 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Gestão de férias e licenças</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-lg mb-4 text-amber-700 dark:text-amber-500">Segurança</h4>
                                        <ul class="space-y-3 text-sm">
                                            <li class="flex items-center gap-3"><svg class="w-5 h-5 text-amber-700 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Controlo de acesso (RBAC)</li>
                                            <li class="flex items-center gap-3"><svg class="w-5 h-5 text-amber-700 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> 16 políticas granulares</li>
                                            <li class="flex items-center gap-3"><svg class="w-5 h-5 text-amber-700 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Auditoria completa</li>
                                            <li class="flex items-center gap-3"><svg class="w-5 h-5 text-amber-700 dark:text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Activity Log</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 dark:border-slate-700 py-12 bg-gray-50 dark:bg-slate-900/50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid md:grid-cols-4 gap-8 mb-8">
                    <div>
                        <h4 class="font-semibold mb-4">TeamCore</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Uma solução moderna para gestão de recursos humanos</p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Produto</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li><a href="#funcionalidades" class="hover:text-amber-700 dark:hover:text-amber-500 transition">Funcionalidades</a></li>
                            <li><a href="#paineis" class="hover:text-amber-700 dark:hover:text-amber-500 transition">Painéis</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Recursos</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li><a href="#" class="hover:text-amber-700 dark:hover:text-amber-500 transition">Documentação</a></li>
                            <li><a href="#" class="hover:text-amber-700 dark:hover:text-amber-500 transition">Suporte</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Legal</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li><a href="#" class="hover:text-amber-700 dark:hover:text-amber-500 transition">Privacidade</a></li>
                            <li><a href="#" class="hover:text-amber-700 dark:hover:text-amber-500 transition">Termos</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-slate-700 pt-8 text-center text-sm text-gray-600 dark:text-gray-400">
                    <p>&copy; 2026 TeamCore. Todos os direitos reservados.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
