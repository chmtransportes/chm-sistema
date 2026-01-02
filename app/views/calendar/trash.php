<?php
/**
 * CHM Sistema - Lixeira de Eventos
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 31/12/2025
 * @version 2.5.0
 */

$this->setTitle('Lixeira - Agenda');
$this->setData('activeMenu', 'calendar');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-trash3 me-2 text-danger"></i>
                Lixeira da Agenda
            </h4>
            <p class="text-muted mb-0">Eventos excluídos podem ser restaurados ou excluídos permanentemente</p>
        </div>
        <a href="<?= APP_URL ?>calendar" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Voltar para Agenda
        </a>
    </div>

    <!-- Lista de Eventos na Lixeira -->
    <div class="card">
        <div class="card-body">
            <div id="trashList">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
            
            <!-- Paginação -->
            <nav id="trashPagination" class="mt-4"></nav>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão Permanente -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão Permanente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir permanentemente este evento?</p>
                <p class="text-danger"><strong>Esta ação não pode ser desfeita!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmHardDelete">
                    <i class="bi bi-trash me-1"></i>
                    Excluir Permanentemente
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.trash-item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.2s;
}

.trash-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.trash-date {
    color: #666;
    font-size: 14px;
}

.trash-actions {
    display: flex;
    gap: 8px;
}

.empty-trash {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-trash i {
    font-size: 48px;
    margin-bottom: 16px;
}
</style>

<script>
let currentPage = 1;
let deleteId = null;

// Carregar eventos da lixeira
async function loadTrashEvents(page = 1) {
    try {
        const response = await fetch(`${APP_URL}api/calendar/trash?page=${page}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            renderTrashList(result.data.events);
            renderPagination(result.data);
            currentPage = page;
        }
    } catch (error) {
        console.error('Erro ao carregar lixeira:', error);
    }
}

// Renderizar lista de eventos
function renderTrashList(events) {
    const container = document.getElementById('trashList');
    
    if (events.length === 0) {
        container.innerHTML = `
            <div class="empty-trash">
                <i class="bi bi-trash3"></i>
                <h5>Nenhum evento na lixeira</h5>
                <p>Os eventos que você excluir aparecerão aqui</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = events.map(event => `
        <div class="trash-item">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-1">${event.title || 'Sem título'}</h6>
                    <div class="trash-date mb-2">
                        <i class="bi bi-calendar3 me-1"></i>
                        ${formatDateTime(event.start_datetime)}
                        ${event.end_datetime ? ' - ' + formatDateTime(event.end_datetime) : ''}
                    </div>
                    <div class="d-flex gap-3 text-muted small">
                        ${event.client_name ? `<span><i class="bi bi-person me-1"></i>${event.client_name}</span>` : ''}
                        ${event.driver_name ? `<span><i class="bi bi-car-front me-1"></i>${event.driver_name}</span>` : ''}
                        <span><i class="bi bi-trash me-1"></i>Excluído em: ${formatDateTime(event.deleted_at)}</span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="trash-actions justify-content-end">
                        <button class="btn btn-sm btn-outline-success" onclick="restoreEvent(${event.id})">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Restaurar Evento
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmHardDelete(${event.id})">
                            <i class="bi bi-trash me-1"></i>
                            Excluir Definitivamente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Renderizar paginação
function renderPagination(data) {
    const container = document.getElementById('trashPagination');
    
    if (data.pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<ul class="pagination justify-content-center">';
    
    // Anterior
    html += `<li class="page-item ${data.page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadTrashEvents(${data.page - 1})">Anterior</a>
    </li>`;
    
    // Páginas
    for (let i = 1; i <= data.pages; i++) {
        html += `<li class="page-item ${i === data.page ? 'active' : ''}">
            <a class="page-link" href="#" onclick="loadTrashEvents(${i})">${i}</a>
        </li>`;
    }
    
    // Próxima
    html += `<li class="page-item ${data.page === data.pages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadTrashEvents(${data.page + 1})">Próxima</a>
    </li>`;
    
    html += '</ul>';
    container.innerHTML = html;
}

// Restaurar evento
async function restoreEvent(id) {
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('_token', document.querySelector('[name="_token"]')?.value || '');
        
        const response = await fetch(`${APP_URL}api/calendar/events/restore`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Evento restaurado com sucesso! Ele voltou para a agenda.', 'success');
            loadTrashEvents(currentPage);
        } else {
            showToast(result.message || 'Erro ao restaurar evento', 'error');
        }
    } catch (error) {
        showToast('Erro de conexão', 'error');
    }
}

// Confirmar exclusão permanente
function confirmHardDelete(id) {
    deleteId = id;
    new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
}

// Excluir permanentemente
document.getElementById('confirmHardDelete').addEventListener('click', async function() {
    if (!deleteId) return;
    
    try {
        const formData = new FormData();
        formData.append('id', deleteId);
        formData.append('_token', document.querySelector('[name="_token"]')?.value || '');
        
        const response = await fetch(`${APP_URL}api/calendar/events/hard-delete`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
            showToast('Evento excluído permanentemente. Esta ação não pode ser desfeita.', 'success');
            loadTrashEvents(currentPage);
        } else {
            showToast(result.message || 'Erro ao excluir evento', 'error');
        }
    } catch (error) {
        showToast('Erro de conexão', 'error');
    }
});

// Formatar data e hora
function formatDateTime(str) {
    if (!str) return '';
    const date = new Date(str);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Função showToast (se não existir)
function showToast(message, type = 'info') {
    // Verifica se já existe container de toasts
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    // Cria o toast
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header">
                <i class="bi bi-info-circle-fill me-2 text-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'}"></i>
                <strong class="me-auto">Sistema</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    // Mostra o toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove após ocultar
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Carregar ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    loadTrashEvents();
});
</script>
