<?php
/**
 * CHM Sistema - Dashboard Admin
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Dashboard</h4>
        <span class="text-muted"><?= date('d/m/Y') ?></span>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['bookings_today'] ?? 0 ?></h3>
                    <p>Serviços Hoje</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['bookings_pending'] ?? 0 ?></h3>
                    <p>Pendentes</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['clients_total'] ?? 0 ?></h3>
                    <p>Clientes</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['drivers_total'] ?? 0 ?></h3>
                    <p>Motoristas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Faturamento do Mês -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-success fs-1 mb-2"></i>
                    <h3 class="text-success"><?= Helpers::moeda($monthStats['revenue'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Faturamento</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-down-circle text-success fs-1 mb-2"></i>
                    <h3 class="text-success"><?= Helpers::moeda($financeSummary['receivables_received'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Entradas</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-up-circle text-danger fs-1 mb-2"></i>
                    <h3 class="text-danger"><?= Helpers::moeda($financeSummary['payables_paid'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Saídas</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-wallet2 <?= ($financeSummary['balance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?> fs-1 mb-2"></i>
                    <h3 class="<?= ($financeSummary['balance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>"><?= Helpers::moeda($financeSummary['balance'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Saldo</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 hide-on-mobile">
        <!-- Próximos Agendamentos -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar3 me-2"></i>Próximos Agendamentos</span>
                    <a href="<?= APP_URL ?>bookings" class="btn btn-sm btn-outline-primary">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($upcomingBookings)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-calendar-x"></i>
                        <p>Nenhum agendamento próximo</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Cliente</th>
                                    <th class="hide-mobile">Origem</th>
                                    <th class="hide-mobile">Motorista</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingBookings as $booking): ?>
                                <tr onclick="location.href='<?= APP_URL ?>bookings/<?= $booking['id'] ?>'" style="cursor:pointer">
                                    <td>
                                        <strong><?= Helpers::dataBr($booking['date']) ?></strong><br>
                                        <small class="text-muted"><?= substr($booking['time'], 0, 5) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($booking['client_name'] ?? '-') ?></td>
                                    <td class="hide-mobile"><?= htmlspecialchars(Helpers::truncate($booking['origin'] ?? '', 30)) ?></td>
                                    <td class="hide-mobile"><?= htmlspecialchars($booking['driver_name'] ?? '-') ?></td>
                                    <td><?= Helpers::statusLabel($booking['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <div class="col-lg-4">
            <!-- Contas Vencidas -->
            <?php if (!empty($overduePayables) || !empty($overdueReceivables)): ?>
            <div class="card mb-3">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i>Contas Vencidas
                </div>
                <div class="card-body">
                    <?php if (!empty($overduePayables)): ?>
                    <p class="mb-2"><strong><?= count($overduePayables) ?></strong> conta(s) a pagar vencida(s)</p>
                    <?php endif; ?>
                    <?php if (!empty($overdueReceivables)): ?>
                    <p class="mb-0"><strong><?= count($overdueReceivables) ?></strong> conta(s) a receber vencida(s)</p>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>finance" class="btn btn-sm btn-outline-danger mt-2">Ver detalhes</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- CNH Vencendo -->
            <?php if (!empty($expiringCnh)): ?>
            <div class="card mb-3">
                <div class="card-header bg-warning">
                    <i class="bi bi-exclamation-circle me-2"></i>CNH Próxima de Vencer
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php foreach (array_slice($expiringCnh, 0, 3) as $driver): ?>
                        <li class="mb-2">
                            <strong><?= htmlspecialchars($driver['name']) ?></strong><br>
                            <small class="text-muted">Vence: <?= Helpers::dataBr($driver['cnh_expiry']) ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ações Rápidas -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning me-2"></i>Ações Rápidas
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= APP_URL ?>bookings/create" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Novo Agendamento
                        </a>
                        <a href="<?= APP_URL ?>clients/create" class="btn btn-outline-secondary">
                            <i class="bi bi-person-plus me-2"></i>Novo Cliente
                        </a>
                        <a href="<?= APP_URL ?>calendar" class="btn btn-outline-secondary">
                            <i class="bi bi-calendar3 me-2"></i>Ver Agenda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB Mobile -->
<div class="fab-container d-lg-none">
    <button class="fab" onclick="location.href='<?= APP_URL ?>bookings/create'">
        <i class="bi bi-plus-lg"></i>
    </button>
</div>
