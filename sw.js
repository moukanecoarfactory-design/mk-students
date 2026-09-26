// Service Worker for The MK Students PWA
const CACHE_NAME = 'mk-students-v1';
const ASSETS = [
    '/',
    '/index.php',
    '/css/style.css',
    '/includes/header.php',
    '/manifest.json'
];

// Install — cache essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS).catch(() => {
                // Some files might fail (like PHP), ignore
            });
        })
    );
    self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
            );
        })
    );
    self.clients.claim();
});

// Fetch — network first, fallback to cache
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, clone).catch(() => {});
                });
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});