{% load static %}
const CACHE_NAME = 'agahi-offline-v1';
const PRECACHE_URLS = [
  '/',
  '/offline/',
  '{% static "css/style.css" %}',
  '{% static "fonts/Vazirmatn-Regular.woff2" %}',
];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((names) => Promise.all(names.filter((name) => name.startsWith('agahi-offline-') && name !== CACHE_NAME).map((name) => caches.delete(name))))
      .then(() => self.clients.claim())
  );
});

function cacheSuccessfulResponse(cache, request, response) {
  if (response && response.status === 200 && response.type === 'basic') {
    cache.put(request, response.clone());
  }
  return response;
}

function isPublicNavigation(pathname) {
  const privatePrefixes = ['/admin/', '/accounts/', '/dashboard/', '/billing/', '/support/', '/moderation/', '/ads/create/'];
  if (privatePrefixes.some((prefix) => pathname.startsWith(prefix))) return false;
  if (/^\/ads\/\d+\/(edit|permit|links|delete|report)\/?$/.test(pathname)) return false;
  if (pathname === '/' || pathname === '/ads/' || /^\/ads\/\d+\/$/.test(pathname) || /^\/ad\/\d+\/[^/]+\/$/.test(pathname) || pathname.startsWith('/categories/')) return true;
  // Root-level one/two segment paths are reserved for public city and city/category landings.
  return /^\/[^/]+\/?$/.test(pathname) || /^\/[^/]+\/[^/]+\/?$/.test(pathname);
}

function isCacheableAsset(pathname) {
  return pathname.startsWith('/static/') || (pathname.startsWith('/media/') && !pathname.startsWith('/media/permits/'));
}

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin) return;

  if (event.request.mode === 'navigate') {
    if (!isPublicNavigation(url.pathname)) return;
    event.respondWith(
      fetch(event.request)
        .then((response) => caches.open(CACHE_NAME).then((cache) => cacheSuccessfulResponse(cache, event.request, response)))
        .catch(() => caches.match(event.request).then((cached) => cached || caches.match('/offline/')))
    );
    return;
  }

  if (isCacheableAsset(url.pathname)) {
    event.respondWith(
      caches.match(event.request).then((cached) => cached || fetch(event.request)
        .then((response) => caches.open(CACHE_NAME).then((cache) => cacheSuccessfulResponse(cache, event.request, response))))
    );
  }
});
