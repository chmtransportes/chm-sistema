<?php
/**
 * CHM Sistema - Lista de Motoristas
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Motoristas</h4>
        <a href="<?= APP_URL ?>drivers/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Novo Motorista
        </a>
    </div>

    <!-- Lista -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($drivers)): ?>
            <div class="empty-state">
                <i class="bi bi-person-badge"></i>
                <h4>Nenhum motorista cadastrado</h4>
                <p>Cadastre seu primeiro motorista para começar.</p>
                <a href="<?= APP_URL ?>drivers/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Novo Motorista
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th class="d-none d-md-table-cell">CPF</th>
                            <th>Telefone</th>
                            <th class="d-none d-lg-table-cell">CNH</th>
                            <th class="d-none d-lg-table-cell">Validade CNH</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th width="130">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drivers as $driver): ?>
                        <?php 
                            $cnhExpiring = strtotime($driver['cnh_expiry']) <= strtotime('+30 days');
                            $cnhExpired = strtotime($driver['cnh_expiry']) < time();
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($driver['name']) ?></strong></td>
                            <td class="d-none d-md-table-cell"><?= Helpers::formatCpf($driver['document']) ?></td>
                            <td>
                                <?php if ($driver['phone']): ?>
                                <a href="tel:<?= $driver['phone'] ?>" class="text-decoration-none">
                                    <?= Helpers::formatPhone($driver['phone']) ?>
                                </a>
                                <?php if ($driver['whatsapp']): ?>
                                <a href="https://wa.me/55<?= Helpers::onlyNumbers($driver['whatsapp']) ?>" target="_blank" class="text-success ms-2">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <?php endif; ?>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars($driver['cnh']) ?> (<?= $driver['cnh_category'] ?>)</td>
                            <td class="d-none d-lg-table-cell">
                                <span class="<?= $cnhExpired ? 'text-danger fw-bold' : ($cnhExpiring ? 'text-warning' : '') ?>">
                                    <?= Helpers::dataBr($driver['cnh_expiry']) ?>
                                    <?php if ($cnhExpired): ?>
                                    <i class="bi bi-exclamation-triangle-fill ms-1" title="CNH vencida"></i>
                                    <?php elseif ($cnhExpiring): ?>
                                    <i class="bi bi-exclamation-circle ms-1" title="CNH próxima de vencer"></i>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($driver['type'] === 'proprio'): ?>
                                <span class="badge bg-primary">Próprio</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Terceirizado</span>
                                <?php endif; ?>
                            </td>
                            <td><?= Helpers::statusLabel($driver['status']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= APP_URL ?>drivers/<?= $driver['id'] ?>/edit" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= APP_URL ?>drivers/<?= $driver['id'] ?>/closing" class="btn btn-outline-success" title="Fechamento">
                                        <i class="bi bi-cash-stack"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Excluir" onclick="deleteDriver(<?= $driver['id'] ?>)">
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
async function deleteDriver(id) {
    if (!confirm('Tem certeza que deseja excluir este motorista?')) return;
    
    try {
        const response = await fetch(APP_URL + 'drivers/' + id, {
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
        showToast('Erro ao excluir motorista', 'error');
    }
}
</script>
