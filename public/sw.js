const CACHE_NAME = 'pizza-pos-v1';
const ASSETS_TO_CACHE = [
    '/',
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
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});
