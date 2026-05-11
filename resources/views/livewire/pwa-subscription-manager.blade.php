<div x-data="pwaSubscription({
    vapidPublicKey: '{{ config('webpush.vapid.public_key') }}',
    isSubscribed: @entangle('isSubscribed')
})" @if(!$minimal) class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700" @endif>
    @if(!$minimal)
        <div class="flex items-center gap-3">
            <div class="p-2 bg-primary-50 dark:bg-primary-900/30 rounded-lg text-primary-600">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notificações Push</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Receba alertas de aprovação de férias e licenças.</p>
            </div>
        </div>
    @endif

    <button
        @click="toggleSubscription"
        :class="isSubscribed ? 'bg-danger-600' : 'bg-primary-600'"
        class="px-4 py-2 text-xs font-bold text-white rounded-lg transition-colors shadow-sm hover:opacity-90 flex items-center gap-2 whitespace-nowrap"
    >
        <template x-if="isSubscribed">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </template>
        <template x-if="!isSubscribed">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
        </template>
        <span x-text="isSubscribed ? 'Desactivar' : 'Activar'"></span>
    </button>
</div>

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pwaSubscription', (config) => ({
        vapidPublicKey: config.vapidPublicKey,
        isSubscribed: config.isSubscribed,

        async toggleSubscription() {
            if (this.isSubscribed) {
                await this.unsubscribe();
            } else {
                await this.subscribe();
            }
        },

        async subscribe() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                alert('Notificações não suportadas neste browser.');
                return;
            }

            const registration = await navigator.serviceWorker.ready;

            try {
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
                });

                const subData = JSON.parse(JSON.stringify(subscription));

                await this.$wire.updateSubscription({
                    endpoint: subData.endpoint,
                    publicKey: subData.keys.p256dh,
                    authToken: subData.keys.auth,
                    contentEncoding: 'aes128gcm'
                });

            } catch (error) {
                console.error('Erro ao subscrever:', error);
            }
        },

        async unsubscribe() {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (subscription) {
                await subscription.unsubscribe();
                await this.$wire.deleteSubscription(subscription.endpoint);
            }
        },

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    }));
});
</script>
@endonce
