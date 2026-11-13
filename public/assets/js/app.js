/* YouthConnect client script. Everything here is progressive enhancement: the site works without it. */
(function () {
  'use strict';
  var base = (document.querySelector('meta[name="ycp-base"]') || {}).content || '';
  var csrf = (document.querySelector('meta[name="ycp-csrf"]') || {}).content || '';
  var isLite = document.body.classList.contains('lite') ||
    !!(navigator.connection && navigator.connection.saveData);
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var store = {
    get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
    set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} },
    del: function (k) { try { localStorage.removeItem(k); } catch (e) {} },
    keys: function () { try { return Object.keys(localStorage); } catch (e) { return []; } }
  };

  /* ---- Mobile navigation ---- */
  var toggle = $('.nav-toggle'), nav = $('#nav');
  if (toggle && nav) toggle.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  /* ---- Confirmations for destructive actions ---- */
  document.addEventListener('submit', function (e) {
    var f = e.target, msg = f.getAttribute && f.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  }, true);
  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-confirm-btn]');
    if (b && !window.confirm(b.getAttribute('data-confirm-btn'))) e.preventDefault();
    var p = e.target.closest && e.target.closest('[data-print]');
    if (p) window.print();
  });

  /* ---- Type-ahead search (tiny JSON, only after 2+ letters and a pause) ---- */
  var sInput = $('input[data-suggest]');
  if (sInput) {
    var list = $('.suggest', sInput.form), timer, last = '';
    sInput.addEventListener('input', function () {
      clearTimeout(timer);
      var q = sInput.value.trim();
      if (q.length < 2 || isLite) { list.hidden = true; return; }
      timer = setTimeout(function () {
        if (q === last) return; last = q;
        fetch(sInput.getAttribute('data-suggest') + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (d) {
            while (list.firstChild) list.removeChild(list.firstChild);
            (d.results || []).forEach(function (r) {
              var li = document.createElement('li'), a = document.createElement('a');
              a.href = r.u; a.textContent = r.t; li.appendChild(a); list.appendChild(li);
            });
            list.hidden = !(d.results || []).length;
          }).catch(function () { list.hidden = true; });
      }, 300);
    });
    document.addEventListener('click', function (e) { if (!sInput.form.contains(e.target)) list.hidden = true; });
  }

  /* ---- Video loads only on request (saves data) ---- */
  $$('[data-load-video]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var box = btn.closest('[data-video]'), id = box.getAttribute('data-video');
      var f = document.createElement('iframe');
      f.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?rel=0';
      f.allow = 'encrypted-media; picture-in-picture'; f.setAttribute('allowfullscreen', '');
      f.title = 'Lesson video';
      while (box.firstChild) box.removeChild(box.firstChild);
      box.appendChild(f);
    });
  });

  /* ---- Evidence form: show only the relevant field ---- */
  var kindSel = $('[data-evidence-kind]');
  if (kindSel) {
    var syncKind = function () {
      $$('[data-kind]').forEach(function (el) { el.hidden = el.getAttribute('data-kind') !== kindSel.value; });
    };
    kindSel.addEventListener('change', syncKind); syncKind();
  }

  /* ---- Online / offline banner ---- */
  var banner = $('#net-banner');
  var queuedForm = null;
  function netState() {
    if (banner) banner.hidden = navigator.onLine;
    if (navigator.onLine && queuedForm) { var f = queuedForm; queuedForm = null; f.submit(); }
  }
  window.addEventListener('online', netState); window.addEventListener('offline', netState); netState();

  /* ---- Drafts: what you type is kept on this device until it is safely sent ---- */
  var DKEY = 'ycp:draft:';
  function fieldsOf(form) {
    return $$('input,textarea,select', form).filter(function (el) {
      return el.name && el.name !== '_csrf' && el.type !== 'file' && el.type !== 'hidden' && el.type !== 'password' && el.type !== 'submit';
    });
  }
  function saveDraft(form, key, sent) {
    var data = {};
    fieldsOf(form).forEach(function (el) {
      if (el.type === 'radio') { if (el.checked) data[el.name] = el.value; }
      else if (el.type === 'checkbox') data[el.name + '|' + el.value] = el.checked;
      else data[el.name] = el.value;
    });
    store.set(DKEY + key, JSON.stringify({ d: data, sent: !!sent, t: Date.now() }));
  }
  $$('form[data-draft]').forEach(function (form) {
    var key = form.getAttribute('data-draft'), raw = store.get(DKEY + key), draft = null;
    try { draft = raw ? JSON.parse(raw) : null; } catch (e) {}
    if (draft && draft.d) {
      fieldsOf(form).forEach(function (el) {
        var v = draft.d[el.type === 'checkbox' ? el.name + '|' + el.value : el.name];
        if (v === undefined) return;
        if (el.type === 'radio') el.checked = (el.value === v);
        else if (el.type === 'checkbox') el.checked = !!v;
        else if (!el.value) el.value = v;
      });
    }
    var debounce;
    form.addEventListener('input', function () { clearTimeout(debounce); debounce = setTimeout(function () { saveDraft(form, key, false); }, 400); });
    form.addEventListener('change', function () { saveDraft(form, key, false); });
    form.addEventListener('submit', function (e) {
      saveDraft(form, key, true);
      if (!navigator.onLine) {                      // offline: keep the work, send automatically when back online
        e.preventDefault(); queuedForm = form;
        if (banner) { banner.hidden = false; banner.textContent = 'You are offline. Your work is saved and will be sent automatically when you reconnect.'; }
      }
    });
  });
  // After a successful save (the server shows a success message), forget drafts that were sent.
  if ($('.alert-success')) store.keys().forEach(function (k) {
    if (k.indexOf(DKEY) !== 0) return;
    try { if (JSON.parse(store.get(k)).sent) store.del(k); } catch (e) {}
  });

  /* ---- Project page: low-data "pulse" instead of reloading the page ---- */
  var proj = $('[data-pulse]');
  var chat = $('#chat');
  var lastMsg = proj ? parseInt(proj.getAttribute('data-lastmsg') || '0', 10) : 0;
  if (chat) {
    var ids = $$('.msg', chat).map(function (m) { return parseInt(m.getAttribute('data-id') || '0', 10); });
    if (ids.length) lastMsg = Math.max.apply(null, ids.concat([0]));
    chat.scrollTop = chat.scrollHeight;
  }
  function addMessage(m) {
    if (!chat || $('.msg[data-id="' + m.id + '"]', chat)) return;
    var empty = $('#chat-empty'); if (empty) empty.remove();
    var d = document.createElement('div'); d.className = 'msg' + (m.mine ? ' mine' : ''); d.setAttribute('data-id', m.id);
    var meta = document.createElement('p'); meta.className = 'meta';
    var b = document.createElement('strong'); b.textContent = m.name;
    var s = document.createElement('span'); s.className = 'muted small'; s.textContent = ' ' + m.when;
    meta.appendChild(b); meta.appendChild(s);
    var p = document.createElement('p'); p.textContent = m.body;
    d.appendChild(meta); d.appendChild(p); chat.appendChild(d);
    chat.scrollTop = chat.scrollHeight;
    lastMsg = Math.max(lastMsg, m.id);
  }
  function fetchNewMessages() {
    if (!chat) return Promise.resolve();
    return fetch(chat.getAttribute('data-url') + '?after=' + lastMsg, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) { (d.messages || []).forEach(addMessage); });
  }

  if (proj && !isLite) {
    var delay = 30000, MAX = 300000, version = parseInt(proj.getAttribute('data-version'), 10),
        fbCount = parseInt(proj.getAttribute('data-feedback'), 10), tmr;
    var tick = function () {
      if (document.hidden || !navigator.onLine) { schedule(); return; }
      fetch(proj.getAttribute('data-pulse'), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(function (d) {
          var changed = false;
          if (chat && d.m > lastMsg) { fetchNewMessages(); changed = true; }
          if (d.v !== version) {
            var ban = $('#update-banner'), txt = $('#update-text');
            if (ban) {
              txt.textContent = d.f > fbCount ? 'New feedback has been shared with your team.' : 'This project was updated.';
              ban.hidden = false;
            }
            version = d.v; fbCount = d.f; changed = true;
          }
          var bell = $('#bell-count'); if (bell && d.n === 0) bell.remove();
          delay = changed ? 30000 : Math.min(delay * 2, MAX);      // back off while nothing changes
          schedule();
        }).catch(function () { delay = Math.min(delay * 2, MAX); schedule(); });
    };
    var schedule = function () { clearTimeout(tmr); tmr = setTimeout(tick, delay); };
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { delay = 30000; schedule(); } });
    schedule();
  }

  /* ---- Chat: send without reloading; offline messages are queued and retried safely ---- */
  var cform = $('#chat-form');
  if (cform && chat) {
    var QKEY = 'ycp:chatq:' + cform.getAttribute('action');
    var status = $('#chat-status');
    var newKey = function () { var a = new Uint8Array(16); (window.crypto || window.msCrypto).getRandomValues(a); return Array.prototype.map.call(a, function (x) { return ('0' + x.toString(16)).slice(-2); }).join(''); };
    var readQ = function () { try { return JSON.parse(store.get(QKEY) || '[]'); } catch (e) { return []; } };
    var say = function (t) { if (status) { status.hidden = !t; status.textContent = t || ''; } };
    var send = function (item) {
      var fd = new FormData(); fd.append('_csrf', csrf); fd.append('body', item.body); fd.append('client_key', item.key);
      return fetch(cform.getAttribute('action'), { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
        .then(function (r) { if (r.status === 422 || r.status === 429) return r.json().then(function (j) { throw { fatal: true, msg: j.error }; }); if (!r.ok) throw new Error('x'); return r.json(); });
    };
    var flush = function () {
      var q = readQ(); if (!q.length || !navigator.onLine) return;
      var item = q[0];
      send(item).then(function () { var cur = readQ(); cur.shift(); store.set(QKEY, JSON.stringify(cur)); say(cur.length ? cur.length + ' message(s) waiting to send…' : ''); return fetchNewMessages().then(flush); })
        .catch(function (err) { if (err && err.fatal) { var cur = readQ(); cur.shift(); store.set(QKEY, JSON.stringify(cur)); say(err.msg || ''); } else say('Will send when your connection returns.'); });
    };
    cform.addEventListener('submit', function (e) {
      e.preventDefault();
      var ta = $('textarea', cform), body = ta.value.trim(); if (!body) return;
      var q = readQ(); q.push({ body: body, key: newKey() }); store.set(QKEY, JSON.stringify(q));
      ta.value = ''; store.del(DKEY + cform.getAttribute('data-draft'));
      say(navigator.onLine ? 'Sending…' : 'Offline: your message is saved and will send when you reconnect.');
      flush();
    });
    window.addEventListener('online', flush);
    flush();
  }

  /* ---- Cards drop in as the page is scrolled (skipped for reduced-motion and Lite mode) ---- */
  (function () {
    var calm = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!('IntersectionObserver' in window) || calm || document.body.classList.contains('lite')) return;
    var els = document.querySelectorAll('.course-card, .enrol-card, .badge-card, .cert-mini, .week, .sector');
    if (!els.length) return;
    function drop(list) {
      list.forEach(function (el, n) {
        if (el.classList.contains('rv-in')) return;
        io.unobserve(el);
        el.style.setProperty('--d', (n % 4) * 0.09 + 's');   // stagger cards that appear together
        el.classList.add('rv-in');
        // once the drop-in has finished, hand control back to normal hover/press styles
        setTimeout(function () { el.classList.remove('rv', 'rv-in'); el.style.removeProperty('--d'); }, 1300);
      });
    }
    var io = new IntersectionObserver(function (entries) {
      drop(entries.filter(function (en) { return en.isIntersecting; }).map(function (en) { return en.target; }));
    }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });
    els.forEach(function (el) { el.classList.add('rv'); io.observe(el); });
    // first pass: cards already on screen drop in right after the first paint (does not rely on the observer's first callback)
    function onScreen() {
      drop([].filter.call(document.querySelectorAll('.rv:not(.rv-in)'), function (el) {
        var r = el.getBoundingClientRect(); return r.top < innerHeight - 30 && r.bottom > 0 && r.left < innerWidth && r.right > 0;
      }));
    }
    setTimeout(onScreen, 80);
    // safety net: never leave visible content hidden if something goes wrong
    setTimeout(onScreen, 2500);
  })();

  /* ---- Idle float: visible cards drift gently when not being used; off-screen cards stand still (saves battery) ---- */
  (function () {
    var calm = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!('IntersectionObserver' in window) || calm || document.body.classList.contains('lite')) return;
    var timers = new WeakMap();
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        var el = en.target;
        clearTimeout(timers.get(el));
        if (en.isIntersecting) {
          timers.set(el, setTimeout(function () {          // wait until the drop-in has finished
            el.style.setProperty('--fd', (5.5 + Math.random() * 3).toFixed(1) + 's');
            el.style.setProperty('--fl', (-Math.random() * 6).toFixed(1) + 's');   // each card drifts out of step
            el.classList.add('is-float');
          }, 1500));
        } else {
          el.classList.remove('is-float');
        }
      });
    }, { threshold: 0.2 });
    document.querySelectorAll('.course-card, .sector, .badge-card, .cert-mini').forEach(function (el) { io.observe(el); });
  })();

  /* ---- Confetti burst on a certificate ---- */
  (function () {
    var host = document.querySelector('[data-confetti]');
    var calm = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!host || calm || document.body.classList.contains('lite')) return;
    var colors = ['#0b5cd5', '#f2a900', '#1f8a4c', '#c2571a', '#7a3fc4', '#e11d48'];
    for (var i = 0; i < 50; i++) {
      var b = document.createElement('span');
      b.className = 'confetti-bit';
      b.style.left = (Math.random() * 100) + 'vw';
      b.style.background = colors[i % colors.length];
      b.style.setProperty('--x', ((Math.random() - 0.5) * 160) + 'px');
      b.style.setProperty('--r', (360 + Math.random() * 720) + 'deg');
      b.style.setProperty('--t', (2.2 + Math.random() * 1.6) + 's');
      b.style.setProperty('--dl', (Math.random() * 0.6) + 's');
      document.body.appendChild(b);
      setTimeout(function (el) { return function () { if (el.parentNode) el.parentNode.removeChild(el); }; }(b), 5000);
    }
  })();

  /* ---- Service worker: caches the app shell so the site opens fast and works offline ---- */
  if ('serviceWorker' in navigator && location.protocol !== 'file:') {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register(base + '/sw.js', { scope: (base || '') + '/' }).catch(function () {});
    });
  }
})();
