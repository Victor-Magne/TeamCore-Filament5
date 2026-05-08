import { precacheAndRoute, cleanupOutdatedCaches } from 'workbox-precaching';
import { registerRoute } from 'workbox-routing';
import { CacheFirst, NetworkFirst, StaleWhileRevalidate } from 'workbox-strategies';
import { ExpirationPlugin } from 'workbox-expiration';
import { CacheableResponsePlugin } from 'workbox-cacheable-response';

cleanupOutdatedCaches();
precacheAndRoute(self.__WB_MANIFEST);

// Cache fonts
registerRoute(
    ({ url }) => url.origin === 'https://fonts.bunny.net',
    new CacheFirst({
        cacheName: 'google-fonts',
        plugins: [
            new ExpirationPlugin({ maxEntries: 20 }),
            new CacheableResponsePlugin({ statuses: [0, 200] })
        ],
    })
);

// Cache static assets
registerRoute(
    ({ request }) => request.destination === 'style' || request.destination === 'script' || request.destination === 'image',
    new StaleWhileRevalidate({
        cacheName: 'static-resources',
    })
);

// Fallback for pages
registerRoute(
    ({ request }) => request.mode === 'navigate',
    new NetworkFirst({
        cacheName: 'pages',
        plugins: [
            new ExpirationPlugin({ maxEntries: 50 }),
        ],
    })
);

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    let data = {};
    if (event.data) {
        data = event.data.json();
    }

    const title = data.title || 'TeamCore';
    const options = {
        body: data.body || 'Nova notificação',
        icon: '/images/Document.svg',
        badge: '/images/Document.svg',
        data: {
            url: data.url || '/'
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const url = event.notification.data.url;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
