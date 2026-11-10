/* YouthConnect service worker: static assets cache-first; pages network-first with an offline fallback.
   Authenticated pages are never stored, so private project data is not kept in the cache. */
var VERSION = 'ycp-v1';
var base = self.location.pathname.replace(/\/sw\.js$/, '');
var SHELL = [base + '/offline'];

self.addEventListener('install', function (e) {
  e.waitUntil(caches.open(VERSION).then(function (c) { return c.addAll(SHELL); }).then(function () { return self.skipWaiting(); }));
});
self.addEventListener('activate', function (e) {
  e.waitUntil(caches.keys().then(function (keys) {
    return Promise.all(keys.filter(function (k) { return k !== VERSION; }).map(function (k) { return caches.delete(k); }));
  }).then(function () { return self.clients.claim(); }));
});
self.addEventListener('fetch', function (e) {
  var req = e.request, url = new URL(req.url);
  if (req.method !== 'GET' || url.origin !== location.origin) return;
  if (url.pathname.indexOf(base + '/assets/') === 0) {            // CSS/JS: cache first, refresh in the background
    e.respondWith(caches.open(VERSION).then(function (cache) {
      return cache.match(req).then(function (hit) {
        var net = fetch(req).then(function (res) { if (res.ok) cache.put(req, res.clone()); return res; }).catch(function () { return hit; });
        return hit || net;
      });
    }));
    return;
  }
  if (req.mode === 'navigate') {                                   // pages: network first, offline page if there is no signal
    e.respondWith(fetch(req).catch(function () { return caches.match(base + '/offline'); }));
  }
});
