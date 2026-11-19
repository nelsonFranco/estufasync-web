const CACHE = 'estufa-estaticos-v1';
const ARQUIVOS = ['./offline.html', './style/style.css', './script/painel.js', './script/thingSpeakScript.js', './script/relatorios.js', './icone.ico'];

self.addEventListener('install', function(evento) {
    evento.waitUntil(caches.open(CACHE).then(function(cache) { return cache.addAll(ARQUIVOS); }));
});

self.addEventListener('fetch', function(evento) {
    if (evento.request.method !== 'GET') return;
    let url = new URL(evento.request.url);
    if (url.origin !== location.origin) return;
    if (evento.request.mode === 'navigate') {
        evento.respondWith(fetch(evento.request).catch(function() { return caches.match('./offline.html'); }));
        return;
    }
    evento.respondWith(caches.match(evento.request).then(function(resposta) { return resposta || fetch(evento.request); }));
});
