const CACHE_NAME = 'quickjuan-pos-v5-no-cache';

// Install event - skip caching
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing (no cache mode)...');
  self.skipWaiting();
});

// Activate event - delete all existing caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating (clearing all caches)...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          console.log('[Service Worker] Deleting cache:', cacheName);
          return caches.delete(cacheName);
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event - always fetch from network, no caching
self.addEventListener('fetch', (event) => {
  const { request } = event;

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip chrome extensions
  if (request.url.startsWith('chrome-extension://')) {
    return;
  }

  // Always fetch from network, no cache
  event.respondWith(
    fetch(request).catch(() => {
      return new Response('Network error', {
        status: 408,
        headers: { 'Content-Type': 'text/plain' },
      });
    })
  );
});

// Handle messages from clients
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
