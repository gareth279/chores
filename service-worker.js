const CACHE_NAME = 'chores-static-v2';
const STATIC_ASSETS = [
  '/chores.css',
  '/chores.js',
  '/manifest.json',
  '/favicon.svg',
  '/favicon.ico',
  '/assets/icons/chores-icon.svg',
  '/assets/icons/icon-192.png',
  '/assets/icons/icon-512.png',
  '/assets/icons/apple-touch-icon.png'
];
const STATIC_PATHS = new Set(STATIC_ASSETS);

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function (cache) { return cache.addAll(STATIC_ASSETS); })
      .then(function () { return self.skipWaiting(); })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys()
      .then(function (names) {
        return Promise.all(names
          .filter(function (name) {
            return name === 'chores-cache-v1' || (name.indexOf('chores-static-') === 0 && name !== CACHE_NAME);
          })
          .map(function (name) { return caches.delete(name); }));
      })
      .then(function () { return self.clients.claim(); })
  );
});

self.addEventListener('fetch', function (event) {
  const request = event.request;
  const url = new URL(request.url);

  // Only cache known, same-origin static files. Never cache authenticated HTML or form responses.
  if (request.method !== 'GET' || url.origin !== self.location.origin || !STATIC_PATHS.has(url.pathname)) {
    return;
  }

  event.respondWith(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.match(url.pathname).then(function (cached) {
        if (cached) return cached;
        return fetch(request).then(function (response) {
          if (response.ok && response.type === 'basic') {
            cache.put(url.pathname, response.clone());
          }
          return response;
        });
      });
    })
  );
});
