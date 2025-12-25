<?php
/**
 * CHM Sistema - Backups
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Helpers;
use CHM\Core\Session;
$csrfToken = Session::getCsrfToken();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-archive me-2"></i>Backups</h4>
        <div class="btn-group">
            <button class="btn btn-primary" onclick="createBackup('database')">
                <i class="bi bi-database me-1"></i>Backup BD
            </button>
            <button class="btn btn-success" onclick="createBackup('full')">
                <i class="bi bi-hdd me-1"></i>Backup Completo
            </button>
        </div>
    </div>

    <!-- Informações -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-archive text-primary fs-1 mb-2"></i>
                    <h3><?= count($backups) ?></h3>
                    <p class="text-muted mb-0">Backups Disponíveis</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-success fs-1 mb-2"></i>
                    <h3><?= !empty($backups) ? Helpers::dataBr($backups[0]['created_at']) : '-' ?></h3>
                    <p class="text-muted mb-0">Último Backup</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-gear text-warning fs-1 mb-2"></i>
                    <h3><?= defined('BACKUP_INTERVAL') ? (BACKUP_INTERVAL / 60) . ' min' : 'Desativado' ?></h3>
                    <p class="text-muted mb-0">Intervalo Automático</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Backups -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list me-2"></i>Lista de Backups</span>
            <button class="btn btn-sm btn-outline-danger" onclick="cleanBackups()">
                <i class="bi bi-trash me-1"></i>Limpar Antigos
            </button>
        </div>
        <div class="card-body p-0">
            <?php if (empty($backups)): ?>
            <div class="empty-state py-4">
                <i class="bi bi-archive"></i>
                <p>Nenhum backup encontrado</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Tamanho</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $b): ?>
                        <tr>
                            <td>
                                <i class="bi bi-<?= $b['type'] === 'full' ? 'hdd' : 'database' ?> me-2"></i>
                                <?= htmlspecialchars($b['name']) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $b['type'] === 'full' ? 'success' : 'primary' ?>">
                                    <?= $b['type'] === 'full' ? 'Completo' : 'Banco' ?>
                                </span>
                            </td>
                            <td><?= Helpers::formatBytes($b['size']) ?></td>
                            <td><?= $b['created_at'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function createBackup(type) {
    if (!confirm(`Criar backup ${type === 'full' ? 'completo' : 'do banco de dados'}?`)) return;

    showToast('Criando backup...', 'info');

    try {
        const result = await apiRequest('backup/create', 'POST', { type });
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) {
            setTimeout(() => location.reload(), 1500);
        }
    } catch (error) {
        showToast('Erro ao criar backup', 'error');
    }
}

async function cleanBackups() {
    if (!confirm('Remover backups antigos?')) return;

    try {
        const result = await apiRequest('backup/clean', 'POST');
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) {
            setTimeout(() => location.reload(), 1500);
        }
    } catch (error) {
        showToast('Erro ao limpar backups', 'error');
    }
}
</script>
