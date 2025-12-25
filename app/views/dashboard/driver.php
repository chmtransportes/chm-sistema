<?php
/**
 * CHM Sistema - Dashboard do Motorista
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
            <h4 class="mb-0">Olá, <?= htmlspecialchars($driver['name']) ?>!</h4>
            <small class="text-muted"><?= date('d/m/Y') ?></small>
        </div>
    </div>

    <!-- Stats do Mês -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-car-front"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $monthStats['total'] ?? 0 ?></h3>
                    <p>Serviços</p>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-info">
                    <h3><?= Helpers::moeda($monthStats['revenue'] ?? 0) ?></h3>
                    <p>Faturado</p>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-info">
                    <h3><?= Helpers::moeda($monthStats['commission'] ?? 0) ?></h3>
                    <p>Comissão</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Serviços de Hoje -->
    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-calendar-check me-2"></i>Serviços de Hoje
        </div>
        <div class="card-body p-0">
            <?php if (empty($todayBookings)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                Nenhum serviço programado para hoje
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($todayBookings as $booking): ?>
                <a href="<?= APP_URL ?>bookings/<?= $booking['id'] ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= substr($booking['time'], 0, 5) ?></strong> - 
                            <?= htmlspecialchars($booking['client_name'] ?? 'Cliente') ?>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars(Helpers::truncate($booking['origin'], 40)) ?>
                            </small>
                        </div>
                        <div>
                            <?= Helpers::statusLabel($booking['status']) ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Próximos Serviços -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-calendar3 me-2"></i>Próximos Serviços
        </div>
        <div class="card-body p-0">
            <?php if (empty($upcomingBookings)): ?>
            <div class="text-center py-4 text-muted">
                Nenhum serviço agendado
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($upcomingBookings as $booking): ?>
                <a href="<?= APP_URL ?>bookings/<?= $booking['id'] ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= Helpers::dataBr($booking['date']) ?></strong> às 
                            <strong><?= substr($booking['time'], 0, 5) ?></strong>
                            <br>
                            <small><?= htmlspecialchars($booking['client_name'] ?? 'Cliente') ?></small>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars(Helpers::truncate($booking['origin'], 40)) ?>
                            </small>
                        </div>
                        <div>
                            <?= Helpers::statusLabel($booking['status']) ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>
</div>
