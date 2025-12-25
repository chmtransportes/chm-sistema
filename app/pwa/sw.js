/**
 * CHM Sistema - Service Worker
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

const CACHE_NAME = 'chm-sistema-v1.0.0';
const OFFLINE_URL = '/chm-sistema/app/offline.html';

const STATIC_ASSETS = [
    '/chm-sistema/app/',
    '/chm-sistema/app/assets/css/app.css',
    '/chm-sistema/app/assets/js/app.js',
    '/chm-sistema/app/assets/icons/icon-192.png',
    '/chm-sistema/app/assets/icons/icon-512.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'
];

// Instalação do Service Worker
self.addEventListener('install', (event) => {
    console.log('[SW] Instalando Service Worker...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Cache aberto');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('[SW] Arquivos em cache');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[SW] Erro ao cachear:', error);
            })
    );
});

// Ativação do Service Worker
self.addEventListener('activate', (event) => {
    console.log('[SW] Ativando Service Worker...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name !== CACHE_NAME)
                        .map((name) => {
                            console.log('[SW] Removendo cache antigo:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => {
                console.log('[SW] Service Worker ativado');
                return self.clients.claim();
            })
    );
});

// Interceptação de requisições
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Ignora requisições não-GET
    if (request.method !== 'GET') {
        return;
    }

    // Ignora requisições de API (sempre busca do servidor)
    if (url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(request)
                .catch(() => {
                    return new Response(
                        JSON.stringify({ success: false, message: 'Sem conexão' }),
                        { headers: { 'Content-Type': 'application/json' } }
                    );
                })
        );
        return;
    }

    // Estratégia: Network First, Cache Fallback
    event.respondWith(
        fetch(request)
            .then((response) => {
                // Se a resposta for válida, armazena no cache
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME)
                        .then((cache) => {
                            cache.put(request, responseClone);
                        });
                }
                return response;
            })
            .catch(() => {
                // Se falhar, tenta buscar do cache
                return caches.match(request)
                    .then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        
                        // Se for uma navegação, retorna página offline
                        if (request.mode === 'navigate') {
                            return caches.match(OFFLINE_URL);
                        }
                        
                        return new Response('Offline', { status: 503 });
                    });
            })
    );
});

// Sincronização em background
self.addEventListener('sync', (event) => {
    console.log('[SW] Sincronização em background:', event.tag);
    
    if (event.tag === 'sync-bookings') {
        event.waitUntil(syncBookings());
    }
});

// Push notifications
self.addEventListener('push', (event) => {
    console.log('[SW] Push recebido');
    
    let data = { title: 'CHM Sistema', body: 'Nova notificação' };
    
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: '/chm-sistema/app/assets/icons/icon-192.png',
        badge: '/chm-sistema/app/assets/icons/icon-72.png',
        vibrate: [100, 50, 100],
        data: data.url || '/chm-sistema/app/dashboard',
        actions: [
            { action: 'open', title: 'Abrir' },
            { action: 'close', title: 'Fechar' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Clique em notificação
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notificação clicada');
    
    event.notification.close();

    if (event.action === 'close') {
        return;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window' })
            .then((clientList) => {
                // Se já existe uma janela aberta, foca nela
                for (const client of clientList) {
                    if (client.url.includes('/chm-sistema/') && 'focus' in client) {
                        return client.focus();
                    }
                }
                // Senão, abre uma nova
                if (clients.openWindow) {
                    return clients.openWindow(event.notification.data);
                }
            })
    );
});

// Função de sincronização de agendamentos
async function syncBookings() {
    try {
        const db = await openDB();
        const pendingBookings = await db.getAll('pending-bookings');
        
        for (const booking of pendingBookings) {
            try {
                const response = await fetch('/chm-sistema/app/api/bookings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(booking)
                });
                
                if (response.ok) {
                    await db.delete('pending-bookings', booking.id);
                }
            } catch (error) {
                console.error('[SW] Erro ao sincronizar:', error);
            }
        }
    } catch (error) {
        console.error('[SW] Erro na sincronização:', error);
    }
}

console.log('[SW] Service Worker carregado');
