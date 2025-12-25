/**
 * CHM Sistema - Registro do Service Worker
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/chm-sistema/app/pwa/sw.js', {
                scope: '/chm-sistema/app/'
            });
            
            console.log('Service Worker registrado:', registration.scope);

            // Verifica atualizações
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                console.log('Novo Service Worker encontrado');
                
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        // Nova versão disponível
                        showUpdateNotification();
                    }
                });
            });

        } catch (error) {
            console.error('Erro ao registrar Service Worker:', error);
        }
    });

    // Atualização do SW quando a página é recarregada
    let refreshing = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (!refreshing) {
            refreshing = true;
            window.location.reload();
        }
    });
}

// Notificação de atualização
function showUpdateNotification() {
    if (typeof showToast === 'function') {
        showToast('Nova versão disponível! Recarregue a página.', 'info');
    }
}

// Prompt de instalação PWA
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showInstallButton();
});

function showInstallButton() {
    const existingBtn = document.getElementById('pwa-install-btn');
    if (existingBtn) return;

    const btn = document.createElement('button');
    btn.id = 'pwa-install-btn';
    btn.className = 'btn btn-success position-fixed';
    btn.style.cssText = 'bottom: 80px; right: 20px; z-index: 1000; border-radius: 50px; padding: 10px 20px;';
    btn.innerHTML = '<i class="bi bi-download me-2"></i>Instalar App';
    
    btn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        
        console.log('Instalação:', outcome);
        deferredPrompt = null;
        btn.remove();
    });

    document.body.appendChild(btn);
}

// Detecta se está instalado
window.addEventListener('appinstalled', () => {
    console.log('PWA instalado');
    deferredPrompt = null;
    const btn = document.getElementById('pwa-install-btn');
    if (btn) btn.remove();
});

// Status de conexão
function updateOnlineStatus() {
    const status = navigator.onLine ? 'online' : 'offline';
    document.body.classList.toggle('offline', !navigator.onLine);
    
    if (!navigator.onLine && typeof showToast === 'function') {
        showToast('Você está offline. Algumas funcionalidades podem não estar disponíveis.', 'warning');
    }
}

window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);
