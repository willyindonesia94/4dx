const CACHE_NAME = '4dx-uid-jabar-cache-v2';
const urlsToCache = [
    '/manifest.json',
    '/pwa/icon-192x192.png',
    '/pwa/icon-512x512.png',
    '/pwa/apple-touch-icon.png'
];

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;

    // For non-GET requests (like POST for login/logout), always go to network only
    if (request.method !== 'GET') {
        event.respondWith(fetch(request));
        return;
    }

    // For HTML documents, use Network First strategy
    if (request.headers.get('Accept') && request.headers.get('Accept').includes('text/html')) {
        event.respondWith(
            fetch(request).then(response => {
                // If successful, we don't necessarily need to cache HTML to avoid CSRF issues
                // But we can cache it for offline fallback if desired.
                // For Laravel, it's safer to just return the network response.
                return response;
            }).catch(() => {
                // Offline fallback - optionally serve a cached offline page if we had one
                return caches.match(request);
            })
        );
        return;
    }

    // For static assets (CSS, JS, Images), use Cache First strategy
    event.respondWith(
        caches.match(request)
            .then(cachedResponse => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                
                // If not in cache, fetch from network
                return fetch(request).then(response => {
                    // Cache the new response if it's a good response
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }
                    
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME)
                        .then(cache => {
                            cache.put(request, responseToCache);
                        });
                        
                    return response;
                });
            })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});
