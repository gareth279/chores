const CACHE_NAME = 'chores-static-v3';
const ASSET_VERSION = '20261114-1';
const STATIC_ASSETS = [
  '/chores.css?v=' + ASSET_VERSION,
  '/chores.js?v=' + ASSET_VERSION,
  '/manifest.json?v=' + ASSET_VERSION,
  '/favicon.svg?v=' + ASSET_VERSION,
  '/favicon.ico?v=' + ASSET_VERSION,
  '/assets/icons/chores-icon.svg?v=' + ASSET_VERSION,
  '/assets/icons/icon-192.png?v=' + ASSET_VERSION,
  '/assets/icons/icon-512.png?v=' + ASSET_VERSION,
  '/assets/icons/apple-touch-icon.png?v=' + ASSET_VERSION
];
const STATIC_PATHS = new Set(STATIC_ASSETS.map(function (asset) {
  return new URL(asset, self.location.origin).pathname;
}));

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function (cache) {
        // Optional icons or a transient network error must not block activation.
        return Promise.allSettled(STATIC_ASSETS.map(function (asset) {
          return fetch(asset, { cache: 'reload' }).then(function (response) {
            if (response.ok && response.type === 'basic') {
              return cache.put(asset, response);
            }
          });
        }));
      })
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
      return fetch(request, { cache: 'no-cache' }).then(function (response) {
        if (response.ok && response.type === 'basic') {
          cache.put(request, response.clone());
          return response;
        }
        return cache.match(request).then(function (cached) { return cached || response; });
      }).catch(function () {
        return cache.match(request).then(function (cached) { return cached || Response.error(); });
      });
    })
  );
});
