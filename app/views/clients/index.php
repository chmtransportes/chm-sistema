<?php
/**
 * CHM Sistema - Lista de Clientes
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Clientes</h4>
        <a href="<?= APP_URL ?>clients/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Novo Cliente
        </a>
    </div>

    <!-- Busca -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nome, CPF/CNPJ, e-mail ou telefone..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-search me-1"></i>Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($clients)): ?>
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h4>Nenhum cliente encontrado</h4>
                <p>Cadastre seu primeiro cliente para começar.</p>
                <a href="<?= APP_URL ?>clients/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Novo Cliente
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th class="d-none d-md-table-cell">CPF/CNPJ</th>
                            <th>Telefone</th>
                            <th class="d-none d-lg-table-cell">E-mail</th>
                            <th>Status</th>
                            <th width="100">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($client['name']) ?></strong>
                                <?php if ($client['type'] === 'pj' && $client['trade_name']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($client['trade_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?= $client['type'] === 'pf' ? Helpers::formatCpf($client['document']) : Helpers::formatCnpj($client['document']) ?>
                            </td>
                            <td>
                                <?php if ($client['phone']): ?>
                                <a href="tel:<?= $client['phone'] ?>" class="text-decoration-none">
                                    <?= Helpers::formatPhone($client['phone']) ?>
                                </a>
                                <?php if ($client['whatsapp']): ?>
                                <a href="https://wa.me/55<?= Helpers::onlyNumbers($client['whatsapp']) ?>" target="_blank" class="text-success ms-2">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <?php endif; ?>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <?= htmlspecialchars($client['email'] ?? '-') ?>
                            </td>
                            <td>
                                <?= Helpers::statusLabel($client['status']) ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" title="Visualizar" 
                                            data-bs-toggle="modal" data-bs-target="#viewClientModal"
                                            onclick="loadClientData(<?= $client['id'] ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="<?= APP_URL ?>clients/<?= $client['id'] ?>/edit" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Excluir" 
                                            onclick="deleteClient(<?= $client['id'] ?>)" data-confirm="Excluir este cliente?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                        <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Visualização Cliente -->
<div class="modal fade" id="viewClientModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-person-circle me-2"></i>Dados do Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="clientDetails">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2 text-muted">Carregando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" id="btnEditClient" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Carrega dados do cliente para o modal
async function loadClientData(id) {
    document.getElementById('clientDetails').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-info" role="status"></div>
            <p class="mt-2 text-muted">Carregando...</p>
        </div>`;
    document.getElementById('btnEditClient').href = APP_URL + 'clients/' + id + '/edit';
    
    try {
        const response = await fetch(APP_URL + 'api/clients/' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        
        if (result.success) {
            const c = result.data;
            const typeLabel = c.type === 'pf' ? 'Pessoa Física' : 'Pessoa Jurídica';
            const statusClass = c.status === 'active' ? 'success' : 'secondary';
            const statusLabel = c.status === 'active' ? 'Ativo' : 'Inativo';
            
            document.getElementById('clientDetails').innerHTML = `
                <div class="text-center mb-3">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:80px;height:80px">
                        <i class="bi bi-person-fill text-info" style="font-size:2.5rem"></i>
                    </div>
                    <h5 class="mt-2 mb-0">${c.name}</h5>
                    ${c.trade_name ? '<small class="text-muted">' + c.trade_name + '</small>' : ''}
                    <div><span class="badge bg-${statusClass}">${statusLabel}</span></div>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Tipo</small>
                        <strong>${typeLabel}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">${c.type === 'pf' ? 'CPF' : 'CNPJ'}</small>
                        <strong>${c.document || '-'}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Celular/WhatsApp</small>
                        <strong>${c.phone || '-'}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">E-mail</small>
                        <strong>${c.email || '-'}</strong>
                    </div>
                    ${c.address ? `
                    <div class="col-12">
                        <small class="text-muted d-block">Endereço</small>
                        <strong>${c.address}${c.number ? ', ' + c.number : ''}${c.complement ? ' - ' + c.complement : ''}</strong>
                        <br><small>${c.neighborhood || ''} - ${c.city || ''}/${c.state || ''} - CEP: ${c.zipcode || ''}</small>
                    </div>` : ''}
                    ${c.notes ? `
                    <div class="col-12">
                        <small class="text-muted d-block">Observações</small>
                        <strong>${c.notes}</strong>
                    </div>` : ''}
                </div>
            `;
        } else {
            document.getElementById('clientDetails').innerHTML = '<div class="alert alert-danger">Erro ao carregar dados</div>';
        }
    } catch (error) {
        document.getElementById('clientDetails').innerHTML = '<div class="alert alert-danger">Erro de conexão</div>';
    }
}

async function deleteClient(id) {
    if (!confirm('Tem certeza que deseja excluir este cliente?')) return;
    
    try {
        const response = await fetch(APP_URL + 'clients/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Erro ao excluir cliente', 'error');
    }
}
</script>
