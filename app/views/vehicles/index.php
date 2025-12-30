<?php
/**
 * CHM Sistema - Lista de Veículos
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-car-front me-2"></i>Veículos</h4>
        <a href="<?= APP_URL ?>vehicles/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Novo Veículo
        </a>
    </div>

    <!-- Lista -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($vehicles)): ?>
            <div class="empty-state">
                <i class="bi bi-car-front"></i>
                <h4>Nenhum veículo cadastrado</h4>
                <p>Cadastre seu primeiro veículo para começar.</p>
                <a href="<?= APP_URL ?>vehicles/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Novo Veículo
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Veículo</th>
                            <th class="d-none d-md-table-cell">Cor</th>
                            <th class="d-none d-md-table-cell">Ano</th>
                            <th class="d-none d-lg-table-cell">Categoria</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th width="100">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><strong><?= Helpers::formatPlate($vehicle['plate']) ?></strong></td>
                            <td><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?></td>
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($vehicle['color']) ?></td>
                            <td class="d-none d-md-table-cell"><?= $vehicle['year'] ?></td>
                            <td class="d-none d-lg-table-cell">
                                <?php 
                                $categories = [
                                    'sedan' => 'Sedan',
                                    'suv' => 'SUV',
                                    'van' => 'Van',
                                    'bus' => 'Ônibus',
                                    'other' => 'Outro'
                                ];
                                echo $categories[$vehicle['category']] ?? $vehicle['category'];
                                ?>
                            </td>
                            <td>
                                <?php if ($vehicle['owner'] === 'proprio'): ?>
                                <span class="badge bg-primary">Próprio</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Terceirizado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $statusLabels = [
                                    'active' => '<span class="badge bg-success">Ativo</span>',
                                    'inactive' => '<span class="badge bg-secondary">Inativo</span>',
                                    'maintenance' => '<span class="badge bg-warning">Manutenção</span>'
                                ];
                                echo $statusLabels[$vehicle['status']] ?? $vehicle['status'];
                                ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" title="Visualizar" 
                                            data-bs-toggle="modal" data-bs-target="#viewVehicleModal"
                                            onclick="loadVehicleData(<?= $vehicle['id'] ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="<?= APP_URL ?>vehicles/<?= $vehicle['id'] ?>/edit" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Excluir" onclick="deleteVehicle(<?= $vehicle['id'] ?>)">
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

<!-- Modal Visualização Veículo -->
<div class="modal fade" id="viewVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-car-front me-2"></i>Dados do Veículo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="vehicleDetails">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2 text-muted">Carregando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" id="btnEditVehicle" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Carrega dados do veículo para o modal
async function loadVehicleData(id) {
    document.getElementById('vehicleDetails').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-info" role="status"></div>
            <p class="mt-2 text-muted">Carregando...</p>
        </div>`;
    document.getElementById('btnEditVehicle').href = APP_URL + 'vehicles/' + id + '/edit';
    
    try {
        const response = await fetch(APP_URL + 'api/vehicles/' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        
        if (result.success) {
            const v = result.data;
            const ownerLabel = v.owner === 'proprio' ? 'Próprio' : 'Terceirizado';
            const ownerClass = v.owner === 'proprio' ? 'primary' : 'secondary';
            const statusLabels = { active: 'Ativo', inactive: 'Inativo', maintenance: 'Manutenção' };
            const statusClasses = { active: 'success', inactive: 'secondary', maintenance: 'warning' };
            const categories = { sedan: 'Sedan', suv: 'SUV', van: 'Van', bus: 'Ônibus', other: 'Outro' };
            
            document.getElementById('vehicleDetails').innerHTML = `
                <div class="text-center mb-3">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:80px;height:80px">
                        <i class="bi bi-car-front-fill text-info" style="font-size:2.5rem"></i>
                    </div>
                    <h5 class="mt-2 mb-0">${v.brand} ${v.model}</h5>
                    <h6 class="text-primary">${v.plate}</h6>
                    <div>
                        <span class="badge bg-${ownerClass} me-1">${ownerLabel}</span>
                        <span class="badge bg-${statusClasses[v.status] || 'secondary'}">${statusLabels[v.status] || v.status}</span>
                    </div>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Placa</small>
                        <strong>${v.plate || '-'}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Marca</small>
                        <strong>${v.brand || '-'}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Modelo</small>
                        <strong>${v.model || '-'}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Ano</small>
                        <strong>${v.year || '-'}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Cor</small>
                        <strong>${v.color || '-'}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Categoria</small>
                        <strong>${categories[v.category] || v.category || '-'}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Lugares</small>
                        <strong>${v.seats ? v.seats + ' passageiros' : '-'}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Combustível</small>
                        <strong>${v.fuel || '-'}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Tipo</small>
                        <strong>${v.ownership === 'proprio' ? 'Próprio' : v.ownership === 'alugado' ? 'Alugado' : '-'}</strong>
                    </div>
                    ${v.chassis ? `
                    <div class="col-12">
                        <small class="text-muted d-block">Chassi</small>
                        <strong>${v.chassis}</strong>
                    </div>` : ''}
                    ${v.notes ? `
                    <div class="col-12">
                        <small class="text-muted d-block">Observações</small>
                        <strong>${v.notes}</strong>
                    </div>` : ''}
                </div>
            `;
        } else {
            document.getElementById('vehicleDetails').innerHTML = '<div class="alert alert-danger">Erro ao carregar dados</div>';
        }
    } catch (error) {
        document.getElementById('vehicleDetails').innerHTML = '<div class="alert alert-danger">Erro de conexão</div>';
    }
}

async function deleteVehicle(id) {
    if (!confirm('Tem certeza que deseja excluir este veículo?')) return;
    
    try {
        const response = await fetch(APP_URL + 'vehicles/' + id, {
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
        showToast('Erro ao excluir veículo', 'error');
    }
}
</script>
