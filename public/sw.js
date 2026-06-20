const FSMS_CACHE = "fsms-mobile-v1";
const STATIC_ASSETS = [
  "/",
  "/assets/css/fsms-ui.css",
  "/assets/js/fsms-app.js",
  "/assets/images/tharimpepe-logo.png",
  "/assets/images/tharimpepe-logo.svg"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(FSMS_CACHE).then((cache) => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== FSMS_CACHE).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  const request = event.request;

  if (request.method !== "GET") {
    return;
  }

  if (request.mode === "navigate") {
    event.respondWith(fetch(request).catch(() => caches.match("/")));
    return;
  }

  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) {
        return cached;
      }

      return fetch(request).then((response) => {
        const url = new URL(request.url);
        const canCache =
          response.ok &&
          url.origin === self.location.origin &&
          ["/assets/css/", "/assets/js/", "/assets/images/"].some((path) => url.pathname.startsWith(path));

        if (canCache) {
          const copy = response.clone();
          caches.open(FSMS_CACHE).then((cache) => cache.put(request, copy));
        }

        return response;
      });
    })
  );
});
