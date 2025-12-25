<?php
/**
 * CHM Sistema - Fluxo de Caixa
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Fluxo de Caixa</h4>
        <a href="<?= APP_URL ?>reports" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Data Inicial</label>
                    <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($start) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Final</label>
                    <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($end) ?>">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-down-circle text-success fs-1"></i>
                    <h4 class="text-success mt-2"><?= Helpers::moeda($totals['received']) ?></h4>
                    <p class="text-muted mb-0">Recebido</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-up-circle text-danger fs-1"></i>
                    <h4 class="text-danger mt-2"><?= Helpers::moeda($totals['paid']) ?></h4>
                    <p class="text-muted mb-0">Pago</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning fs-1"></i>
                    <h4 class="text-warning mt-2"><?= Helpers::moeda($totals['pending_receive']) ?></h4>
                    <p class="text-muted mb-0">A Receber</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card <?= $totals['balance'] >= 0 ? 'border-success' : 'border-danger' ?>">
                <div class="card-body text-center">
                    <i class="bi bi-wallet2 <?= $totals['balance'] >= 0 ? 'text-success' : 'text-danger' ?> fs-1"></i>
                    <h4 class="<?= $totals['balance'] >= 0 ? 'text-success' : 'text-danger' ?> mt-2"><?= Helpers::moeda($totals['balance']) ?></h4>
                    <p class="text-muted mb-0">Saldo</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Entradas -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-arrow-down-circle me-2"></i>Entradas
                </div>
                <div class="card-body p-0">
                    <?php if (empty($receivables)): ?>
                    <div class="empty-state py-4"><i class="bi bi-inbox"></i><p>Nenhuma entrada</p></div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Data</th><th>Descrição</th><th>Valor</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($receivables as $r): ?>
                                <tr>
                                    <td><?= Helpers::dataBr($r['due_date']) ?></td>
                                    <td><?= htmlspecialchars(Helpers::truncate($r['description'], 25)) ?></td>
                                    <td class="text-success"><?= Helpers::moeda($r['value']) ?></td>
                                    <td><?= Helpers::financeStatus($r['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Saídas -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-arrow-up-circle me-2"></i>Saídas
                </div>
                <div class="card-body p-0">
                    <?php if (empty($payables)): ?>
                    <div class="empty-state py-4"><i class="bi bi-inbox"></i><p>Nenhuma saída</p></div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Data</th><th>Descrição</th><th>Valor</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($payables as $p): ?>
                                <tr>
                                    <td><?= Helpers::dataBr($p['due_date']) ?></td>
                                    <td><?= htmlspecialchars(Helpers::truncate($p['description'], 25)) ?></td>
                                    <td class="text-danger"><?= Helpers::moeda($p['value']) ?></td>
                                    <td><?= Helpers::financeStatus($p['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
