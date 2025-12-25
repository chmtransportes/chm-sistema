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

<script>
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
