<?php
/**
 * CHM Sistema - Dashboard do Cliente
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <?php if (isset($error)): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php else: ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Olá, <?= htmlspecialchars($client['name']) ?>!</h4>
            <small class="text-muted">Bem-vindo ao CHM Sistema</small>
        </div>
    </div>

    <!-- Próximos Serviços -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-calendar-check me-2"></i>Próximos Serviços</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($upcomingBookings)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                Nenhum serviço agendado
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($upcomingBookings as $booking): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">
                                <i class="bi bi-calendar me-1"></i>
                                <?= Helpers::dataBr($booking['date']) ?> às <?= substr($booking['time'], 0, 5) ?>
                            </h6>
                            <p class="mb-1">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= htmlspecialchars($booking['origin']) ?>
                                <?php if ($booking['destination']): ?>
                                <i class="bi bi-arrow-right mx-1"></i>
                                <?= htmlspecialchars($booking['destination']) ?>
                                <?php endif; ?>
                            </p>
                            <?php if ($booking['driver_name']): ?>
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>Motorista: <?= htmlspecialchars($booking['driver_name']) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        <div class="text-end">
                            <?= Helpers::statusLabel($booking['status']) ?>
                            <div class="mt-2">
                                <a href="<?= APP_URL ?>voucher/<?= $booking['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-text"></i> Voucher
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Histórico -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Histórico de Serviços
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentBookings)): ?>
            <div class="text-center py-4 text-muted">
                Nenhum serviço realizado
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($recentBookings as $booking): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= Helpers::dataBr($booking['date']) ?></strong>
                            <br>
                            <small class="text-muted">
                                <?= htmlspecialchars(Helpers::truncate($booking['origin'], 40)) ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <strong><?= Helpers::moeda($booking['total']) ?></strong>
                            <div class="mt-1">
                                <a href="<?= APP_URL ?>receipt/<?= $booking['id'] ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-receipt"></i> Recibo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>
</div>
