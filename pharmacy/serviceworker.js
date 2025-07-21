var staticCacheName = "pharmacy-" + new Date().getTime();
var filesToCache = [
    'public/pwa-icon/icon-72x72.png',
    "public/pwa-icon/icon-96x96.png",
    "public/pwa-icon/icon-128x128.png",
    "public/pwa-icon/icon-144x144.png",
    "public/pwa-icon/icon-152x152.png",
    "public/pwa-icon/icon-192x192.png",
    "public/pwa-icon/icon-384x384.png",
    "public/pwa-icon/icon-512x512.png",
    "public/pos/js/cart.js",
    "public/pos/css/pos.css",
    "public/pos/css/style.css",
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pharmacy-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
