/* YouthConnect service worker: static assets cache-first; pages network-first with an offline fallback.
   Authenticated pages are never stored, so private project data is not kept in the cache. */
var VERSION = 'ycp-v2';
var base = self.location.pathname.replace(/\/sw\.js$/, '');
var SHELL = [base + '/offline'];

self.addEventListener('install', function (e) {
  e.waitUntil(caches.open(VERSION).then(function (c) {
    // store the offline page AND the CSS/JS it uses, so it is styled even with no signal
    return fetch(SHELL[0]).then(function (res) {
      var copy = res.clone();
      return res.text().then(function (html) {
        var files = (html.match(/(?:href|src)="[^"]*\/assets\/[^"]+"/g) || []).map(function (m) { return m.replace(/^(?:href|src)="/, '').replace(/"$/, ''); });
        return Promise.all([c.put(SHELL[0], copy)].concat(files.map(function (u) { return c.add(u).catch(function () {}); })));
      });
    });
  }).then(function () { return self.skipWaiting(); }));
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
      return cache.match(req).then(function (exact) {
        var hit = exact;
        var net = fetch(req).then(function (res) { if (res.ok) cache.put(req, res.clone()); return res; }).catch(function () { return hit || cache.match(req, { ignoreSearch: true }); });   // offline: any saved version
        return hit || net;
      });
    }));
    return;
  }
  if (req.mode === 'navigate') {                                   // pages: network first, offline page if there is no signal
    e.respondWith(fetch(req).catch(function () { return caches.match(base + '/offline'); }));
  }
});
