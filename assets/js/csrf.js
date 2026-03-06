(function () {
  function csrfToken() {
    return (
      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
      document.querySelector('meta[name="csrf"]')?.getAttribute('content') ||
      ''
    );
  }

  window.csrfToken = csrfToken;

  const _fetch = window.fetch.bind(window);

  function isSameOrigin(input) {
    try {
      const u =
        typeof input === 'string'
          ? new URL(input, window.location.href)
          : new URL(input.url, window.location.href);
      return u.origin === window.location.origin;
    } catch {
      return true; // relativo
    }
  }

  window.fetch = function (input, init = {}) {
    init = init || {};
    init.credentials = init.credentials || 'same-origin';

    const method = (init.method || 'GET').toUpperCase();

    if (isSameOrigin(input) && !['GET', 'HEAD', 'OPTIONS'].includes(method)) {
      const t = csrfToken();

      const headers = new Headers(init.headers || {});
      if (t && !headers.has('X-CSRF-Token')) headers.set('X-CSRF-Token', t);
      init.headers = headers;

      // Si es FormData, agrega campo "csrf" (NO "_csrf")
      if (init.body instanceof FormData) {
        if (t && !init.body.has('csrf')) init.body.append('csrf', t);
      }

      // Si usan URLSearchParams
      if (init.body instanceof URLSearchParams) {
        if (t && !init.body.has('csrf')) init.body.append('csrf', t);
      }

      // Si es x-www-form-urlencoded como string
      if (typeof init.body === 'string') {
        const ct = headers.get('Content-Type') || '';
        if (t && ct.includes('application/x-www-form-urlencoded') && !init.body.includes('csrf=')) {
          init.body += (init.body ? '&' : '') + 'csrf=' + encodeURIComponent(t);
        }
      }
    }

    return _fetch(input, init);
  };

  // Wrapper opcional
  window.fetchCSRF = function (input, init = {}) {
    init = init || {};
    init.credentials = init.credentials || 'same-origin';
    // si mandas body y no defines method, fuerza POST
    if (!init.method && init.body) init.method = 'POST';
    return fetch(input, init);
  };

  window.postForm = function (url, formData, init = {}) {
    return fetchCSRF(url, { ...init, method: 'POST', body: formData });
  };
})();
