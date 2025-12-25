<?php
/**
 * CHM Sistema - WhatsApp
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Session;
$csrfToken = Session::getCsrfToken();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-whatsapp me-2"></i>WhatsApp</h4>
        <div class="btn-group">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#sendModal">
                <i class="bi bi-send me-1"></i>Enviar Mensagem
            </button>
        </div>
    </div>

    <!-- Status da API -->
    <div class="alert <?= $apiConfigured ? 'alert-success' : 'alert-warning' ?> mb-4">
        <i class="bi bi-<?= $apiConfigured ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
        <?php if ($apiConfigured): ?>
            <strong>API Configurada</strong> - WhatsApp Business API está pronta para uso.
        <?php else: ?>
            <strong>API Não Configurada</strong> - Configure WHATSAPP_PHONE_ID e WHATSAPP_TOKEN em <code>config/config.php</code>
        <?php endif; ?>
    </div>

    <div class="row g-3">
        <!-- Templates -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-text me-2"></i>Templates de Mensagem</span>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#templateModal">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($templates)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-file-earmark-text"></i>
                        <p>Nenhum template cadastrado</p>
                    </div>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($templates as $t): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= htmlspecialchars($t['name']) ?></strong>
                                    <span class="badge bg-secondary ms-2"><?= $t['category'] ?></span>
                                    <p class="mb-0 mt-1 text-muted small"><?= htmlspecialchars(substr($t['content'], 0, 100)) ?>...</p>
                                </div>
                                <button class="btn btn-sm btn-outline-success" onclick="useTemplate(<?= htmlspecialchars(json_encode($t['content'])) ?>)">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tags Disponíveis -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-tag me-2"></i>Tags Dinâmicas</span>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tagModal">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tags)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-tags"></i>
                        <p>Nenhuma tag cadastrada</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tag</th>
                                    <th>Descrição</th>
                                    <th>Campo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tags as $tag): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($tag['tag']) ?></code></td>
                                    <td><?= htmlspecialchars($tag['description']) ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($tag['field_reference'] ?? '-') ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Instruções -->
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>Instruções de Configuração
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li>Acesse o <a href="https://developers.facebook.com" target="_blank">Meta for Developers</a></li>
                        <li>Crie um App do tipo "Business"</li>
                        <li>Adicione o produto "WhatsApp"</li>
                        <li>Copie o <strong>Phone Number ID</strong> e o <strong>Access Token</strong></li>
                        <li>Configure no arquivo <code>config/config.php</code></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Enviar Mensagem -->
<div class="modal fade" id="sendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-whatsapp me-2"></i>Enviar Mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Telefone (com DDD)</label>
                    <input type="text" id="sendPhone" class="form-control" placeholder="11999999999">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensagem</label>
                    <textarea id="sendMessage" class="form-control" rows="5" placeholder="Digite a mensagem..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="sendMessage()">
                    <i class="bi bi-send me-1"></i>Enviar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Template -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" id="templateName" class="form-control" placeholder="Ex: boas_vindas">
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoria</label>
                    <select id="templateCategory" class="form-select">
                        <option value="quick_reply">Resposta Rápida</option>
                        <option value="voucher">Voucher</option>
                        <option value="confirmacao">Confirmação</option>
                        <option value="lembrete">Lembrete</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Conteúdo</label>
                    <textarea id="templateContent" class="form-control" rows="5" placeholder="Use tags como #cliente, #data, #hora..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplate()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Tag -->
<div class="modal fade" id="tagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tag (com #)</label>
                    <input type="text" id="tagName" class="form-control" placeholder="#cliente">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <input type="text" id="tagDescription" class="form-control" placeholder="Nome do cliente">
                </div>
                <div class="mb-3">
                    <label class="form-label">Campo de Referência</label>
                    <input type="text" id="tagField" class="form-control" placeholder="client_name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveTag()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<script>
const sendModal = new bootstrap.Modal(document.getElementById('sendModal'));

function useTemplate(content) {
    document.getElementById('sendMessage').value = content;
    sendModal.show();
}

async function sendMessage() {
    const phone = document.getElementById('sendPhone').value;
    const message = document.getElementById('sendMessage').value;

    if (!phone || !message) {
        showToast('Preencha todos os campos', 'error');
        return;
    }

    try {
        const result = await apiRequest('whatsapp/send', 'POST', { phone, message });
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) sendModal.hide();
    } catch (error) {
        showToast('Erro ao enviar', 'error');
    }
}

async function saveTemplate() {
    const name = document.getElementById('templateName').value;
    const category = document.getElementById('templateCategory').value;
    const content = document.getElementById('templateContent').value;

    if (!name || !content) {
        showToast('Preencha todos os campos', 'error');
        return;
    }

    try {
        const result = await apiRequest('whatsapp/template', 'POST', { name, category, content });
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) location.reload();
    } catch (error) {
        showToast('Erro ao salvar', 'error');
    }
}

async function saveTag() {
    const tag = document.getElementById('tagName').value;
    const description = document.getElementById('tagDescription').value;
    const field_reference = document.getElementById('tagField').value;

    if (!tag || !description) {
        showToast('Preencha os campos obrigatórios', 'error');
        return;
    }

    try {
        const result = await apiRequest('whatsapp/tag', 'POST', { tag, description, field_reference });
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) location.reload();
    } catch (error) {
        showToast('Erro ao salvar', 'error');
    }
}
</script>
