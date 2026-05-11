@auth
    <div x-data="{
        show: !localStorage.getItem('pwa_prompt_dismissed') && Notification.permission === 'default'
    }"
    x-show="show"
    x-transition
    class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 z-9999 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-primary-100 dark:border-primary-900"
    style="display: none;"
    >
        <div class="flex items-start gap-4">
            <div class="p-3 bg-primary-50 dark:bg-primary-900/30 rounded-xl">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Ativar Notificações?</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fique a par das aprovações de férias e alertas importantes em tempo real.</p>
                <div class="flex gap-3 mt-4">
                    <livewire:pwa-subscription-manager :minimal="true" />
                    <button @click="show = false; localStorage.setItem('pwa_prompt_dismissed', 'true')" class="px-4 py-2 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Agora não
                    </button>
                </div>
            </div>
        </div>
    </div>
@endauth
