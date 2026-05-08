<div x-data="pwaSubscription({
    vapidPublicKey: '{{ config('webpush.vapid.public_key') }}',
    isSubscribed: @entangle('isSubscribed')
})" class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notificações Push</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">Receba alertas de aprovação de férias e licenças.</p>
    </div>

    <button
        @click="toggleSubscription"
        :class="isSubscribed ? 'bg-danger-600' : 'bg-primary-600'"
        class="px-4 py-2 text-xs font-bold text-white rounded-lg transition-colors shadow-sm hover:opacity-90"
    >
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
