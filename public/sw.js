const CACHE_NAME = 'pizza-pos-v1';
const ASSETS_TO_CACHE = [
    '/manifest.json',
    '/pwa-icon-192.png',
    '/pwa-icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    // For navigation requests, we might want to let them pass through without SW interference 
    // if they are likely to redirect (like the login flow)
    if (event.request.mode === 'navigate') {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (event.data) {
        const msg = event.data.json();
        event.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon || '/icons/pizza-icon.png',
            data: msg.data,
            vibrate: msg.vibrate || [200, 100, 200]
        }));
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    if (event.action === 'kitchen_action') {
        event.waitUntil(clients.openWindow('/pizzaiolo'));
    } else if (event.action === 'pos_action') {
        event.waitUntil(clients.openWindow('/pos'));
    } else {
        event.waitUntil(clients.openWindow('/'));
    }
});
