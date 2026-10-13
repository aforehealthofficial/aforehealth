// AFOREHEALTH Production Service Worker - Safe Caching Policy
const CACHE_NAME = 'afh-static-v2';
const OFFLINE_URL = '/offline.html';

// Static assets safe for pre-caching
const PRECACHE_ASSETS = [
  OFFLINE_URL,
  '/assets/css/main.css',
  '/assets/images/logo.svg'
];

// Sensitive/dynamic paths that must NEVER be served from cache
const SENSITIVE_PATHS = [
  '/admin',
  '/checkout',
  '/cart',
  '/account',
  '/order',
  '/track-order',
  '/login',
  '/register',
  '/otp',
  '/api',
  '/webhook'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // 1. Non-GET requests (POST, PUT, DELETE) must always go directly to network
  if (event.request.method !== 'GET') {
    return;
  }

  // 2. Sensitive/dynamic routes must ALWAYS bypass cache
  const isSensitive = SENSITIVE_PATHS.some((path) => url.pathname.startsWith(path));
  if (isSensitive) {
    if (event.request.mode === 'navigate') {
      event.respondWith(
        fetch(event.request).catch(() => caches.match(OFFLINE_URL))
      );
    }
    return;
  }

  // 3. Static assets: Cache-first with network fallback
  if (url.pathname.match(/\.(css|js|svg|png|jpg|jpeg|webp|woff2|ico)$/)) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        return cached || fetch(event.request).then((response) => {
          if (response && response.status === 200) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // 4. Public navigation pages: Network-first with offline fallback
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => caches.match(OFFLINE_URL))
    );
  }
});
