self.addEventListener('install', (event) => {
  self.skipWaiting();
  console.log('Service Worker instalado (datosinelogin)');
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
  console.log('Service Worker activo (datosinelogin)');
});

// IMPORTANTE: sin fetch handler