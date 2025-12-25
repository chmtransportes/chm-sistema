<?php
/**
 * CHM Sistema - Notificações
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-bell me-2"></i>Notificações e Alertas</h4>
    </div>

    <!-- Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-bell text-primary fs-1 mb-2"></i>
                    <h3><?= $summary['total'] ?></h3>
                    <p class="text-muted mb-0">Total</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-danger">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-danger fs-1 mb-2"></i>
                    <h3 class="text-danger"><?= $summary['critical'] ?></h3>
                    <p class="text-muted mb-0">Críticos</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-circle text-warning fs-1 mb-2"></i>
                    <h3 class="text-warning"><?= $summary['warning'] ?></h3>
                    <p class="text-muted mb-0">Atenção</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-info">
                <div class="card-body text-center">
                    <i class="bi bi-info-circle text-info fs-1 mb-2"></i>
                    <h3 class="text-info"><?= $summary['info'] ?></h3>
                    <p class="text-muted mb-0">Informações</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Alertas -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-list-check me-2"></i>Lista de Alertas
        </div>
        <div class="card-body p-0">
            <?php if (empty($alerts)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 48px;"></i>
                <p class="mt-3">Nenhum alerta pendente!</p>
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($alerts as $alert): ?>
                <a href="<?= $alert['link'] ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex align-items-center">
                        <div class="alert-icon bg-<?= $alert['color'] ?> text-white rounded-circle p-2 me-3">
                            <i class="bi <?= $alert['icon'] ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong class="text-<?= $alert['color'] ?>"><?= htmlspecialchars($alert['title']) ?></strong>
                                <small class="text-muted"><?= Helpers::dataBr($alert['date']) ?></small>
                            </div>
                            <p class="mb-0 text-muted"><?= htmlspecialchars($alert['message']) ?></p>
                        </div>
                        <i class="bi bi-chevron-right text-muted ms-2"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.alert-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
