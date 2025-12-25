<?php
/**
 * CHM Sistema - Lista de Agendamentos
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-journal-bookmark me-2"></i>Agendamentos</h4>
        <a href="<?= APP_URL ?>bookings/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Novo Agendamento
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Data</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
                        <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmado</option>
                        <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>Em Andamento</option>
                        <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Concluído</option>
                        <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                    <a href="<?= APP_URL ?>bookings" class="btn btn-outline-secondary">Limpar</a>
                </div>
                <div class="col-md-2 text-end">
                    <a href="<?= APP_URL ?>calendar" class="btn btn-outline-primary">
                        <i class="bi bi-calendar3 me-1"></i>Agenda
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <i class="bi bi-journal-x"></i>
                <h4>Nenhum agendamento encontrado</h4>
                <p>Crie seu primeiro agendamento para começar.</p>
                <a href="<?= APP_URL ?>bookings/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Novo Agendamento
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Data/Hora</th>
                            <th>Cliente</th>
                            <th class="d-none d-md-table-cell">Origem</th>
                            <th class="d-none d-lg-table-cell">Motorista</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><strong><?= $b['code'] ?></strong></td>
                            <td>
                                <?= Helpers::dataBr($b['date']) ?><br>
                                <small class="text-muted"><?= substr($b['time'], 0, 5) ?></small>
                            </td>
                            <td><?= htmlspecialchars($b['client_name'] ?? '-') ?></td>
                            <td class="d-none d-md-table-cell">
                                <?= htmlspecialchars(Helpers::truncate($b['origin'] ?? '', 30)) ?>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <?= htmlspecialchars($b['driver_name'] ?? '-') ?>
                            </td>
                            <td><?= Helpers::moeda($b['total']) ?></td>
                            <td><?= Helpers::statusLabel($b['status']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= APP_URL ?>bookings/<?= $b['id'] ?>" class="btn btn-outline-secondary" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= APP_URL ?>bookings/<?= $b['id'] ?>/edit" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="<?= APP_URL ?>voucher/<?= $b['id'] ?>" target="_blank"><i class="bi bi-file-text me-2"></i>Voucher</a></li>
                                            <?php if ($b['status'] === 'completed'): ?>
                                            <li><a class="dropdown-item" href="<?= APP_URL ?>receipt/<?= $b['id'] ?>" target="_blank"><i class="bi bi-receipt me-2"></i>Recibo</a></li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="sendVoucher(<?= $b['id'] ?>)"><i class="bi bi-whatsapp me-2"></i>Enviar WhatsApp</a></li>
                                        </ul>
                                    </div>
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
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $_GET['status'] ?? '' ?>"><?= $i ?></a>
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

<!-- FAB Mobile -->
<div class="fab-container d-lg-none">
    <button class="fab" onclick="location.href='<?= APP_URL ?>bookings/create'">
        <i class="bi bi-plus-lg"></i>
    </button>
</div>

<script>
async function sendVoucher(id) {
    if (!confirm('Enviar voucher por WhatsApp para o cliente?')) return;
    
    showLoading();
    try {
        const result = await apiRequest('bookings/' + id + '/voucher', 'POST');
        hideLoading();
        showToast(result.message, result.success ? 'success' : 'error');
    } catch (error) {
        hideLoading();
        showToast('Erro ao enviar voucher', 'error');
    }
}
</script>
