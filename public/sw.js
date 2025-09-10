/*
  Simple service worker to cache icon and image assets so they don't reload on each page navigation.
  Strategy:
  - Cache-first for images and SVGs under /assets/.
  - Clean up old caches on activate.
*/

const SW_VERSION = 'v1';
const ICON_CACHE = `icon-cache-${SW_VERSION}`;

// Patterns of assets to cache (adjust if your paths differ)
const ICON_URL_PATTERNS = [
    /\/assets\/img\//,
    /\/assets\/icons\//,
    /\.svg(\?.*)?$/,
    /\.png(\?.*)?$/,
    /\.jpe?g(\?.*)?$/,
    /\.gif(\?.*)?$/,
    /\.webp(\?.*)?$/
];

self.addEventListener('install', (event) => {
    // Activate immediately on first load
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const cacheNames = await caches.keys();
            await Promise.all(
                cacheNames
                    .filter((name) => name.startsWith('icon-cache-') && name !== ICON_CACHE)
                    .map((name) => caches.delete(name))
            );
            await self.clients.claim();
        })()
    );
});

function shouldHandleAsIcon(requestUrl) {
    try {
        const url = new URL(requestUrl);
        // Only same-origin runtime cache to avoid opaque responses
        if (url.origin !== self.location.origin) return false;
        return ICON_URL_PATTERNS.some((pattern) => pattern.test(url.pathname) || pattern.test(url.href));
    } catch (_) {
        return false;
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    // Cache-first for icons/images
    if (shouldHandleAsIcon(request.url)) {
        event.respondWith(
            (async () => {
                const cache = await caches.open(ICON_CACHE);
                const cached = await cache.match(request, { ignoreSearch: false });
                if (cached) return cached;
                try {
                    const response = await fetch(request);
                    // Only cache successful, basic/cors responses
                    if (response && response.status === 200 && (response.type === 'basic' || response.type === 'cors')) {
                        cache.put(request, response.clone());
                    }
                    return response;
                } catch (error) {
                    // If offline and not in cache, just fail normally
                    return caches.match(request);
                }
            })()
        );
    }
});


