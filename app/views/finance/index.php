<?php
/**
 * CHM Sistema - Financeiro
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Helpers;
use CHM\Core\Session;
$csrfToken = Session::getCsrfToken();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Financeiro</h4>
        <div class="btn-group">
            <a href="<?= APP_URL ?>finance/payable/create" class="btn btn-danger">
                <i class="bi bi-arrow-up-circle me-1"></i>Nova Saída
            </a>
            <a href="<?= APP_URL ?>finance/receivable/create" class="btn btn-success">
                <i class="bi bi-arrow-down-circle me-1"></i>Nova Entrada
            </a>
        </div>
    </div>

    <!-- Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-arrow-down-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= Helpers::moeda($summary['receivables_received'] ?? 0) ?></h3>
                    <p>Entradas</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-arrow-up-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= Helpers::moeda($summary['payables_paid'] ?? 0) ?></h3>
                    <p>Saídas</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-info">
                    <h3><?= Helpers::moeda(($summary['receivables_total'] ?? 0) - ($summary['receivables_received'] ?? 0)) ?></h3>
                    <p>A Receber</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon <?= ($summary['balance'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-info">
                    <h3><?= Helpers::moeda($summary['balance'] ?? 0) ?></h3>
                    <p>Saldo</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Data Inicial</label>
                    <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data Final</label>
                    <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="type" class="form-select">
                        <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>Todos</option>
                        <option value="receivable" <?= $type === 'receivable' ? 'selected' : '' ?>>Entradas</option>
                        <option value="payable" <?= $type === 'payable' ? 'selected' : '' ?>>Saídas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
                        <option value="partial" <?= ($status ?? '') === 'partial' ? 'selected' : '' ?>>Parcial</option>
                        <option value="paid" <?= ($status ?? '') === 'paid' ? 'selected' : '' ?>>Pago</option>
                        <option value="received" <?= ($status ?? '') === 'received' ? 'selected' : '' ?>>Recebido</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                    <a href="<?= APP_URL ?>finance" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <!-- Contas a Receber (Entradas) -->
        <?php if ($type === 'all' || $type === 'receivable'): ?>
        <div class="col-lg-<?= $type === 'all' ? '6' : '12' ?>">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-arrow-down-circle me-2"></i>Entradas (A Receber)</span>
                    <span class="badge bg-light text-success"><?= count($receivables) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($receivables)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-inbox"></i>
                        <p>Nenhuma entrada encontrada</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Vencimento</th>
                                    <th>Descrição</th>
                                    <th class="hide-mobile">Cliente</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th width="80">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($receivables as $r): ?>
                                <tr>
                                    <td><?= Helpers::dataBr($r['due_date']) ?></td>
                                    <td><?= htmlspecialchars(Helpers::truncate($r['description'], 30)) ?></td>
                                    <td class="hide-mobile"><?= htmlspecialchars($r['client_name'] ?? '-') ?></td>
                                    <td class="text-success fw-bold"><?= Helpers::moeda($r['value']) ?></td>
                                    <td><?= Helpers::financeStatus($r['status']) ?></td>
                                    <td>
                                        <?php if ($r['status'] !== 'received'): ?>
                                        <button class="btn btn-sm btn-success" onclick="receivePayment(<?= $r['id'] ?>, <?= $r['value'] - $r['received_value'] ?>)" title="Receber">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <?php else: ?>
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contas a Pagar (Saídas) -->
        <?php if ($type === 'all' || $type === 'payable'): ?>
        <div class="col-lg-<?= $type === 'all' ? '6' : '12' ?>">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-arrow-up-circle me-2"></i>Saídas (A Pagar)</span>
                    <span class="badge bg-light text-danger"><?= count($payables) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($payables)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-inbox"></i>
                        <p>Nenhuma saída encontrada</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Vencimento</th>
                                    <th>Descrição</th>
                                    <th class="hide-mobile">Categoria</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th width="80">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payables as $p): ?>
                                <tr class="<?= $p['due_date'] < date('Y-m-d') && $p['status'] === 'pending' ? 'table-danger' : '' ?>">
                                    <td><?= Helpers::dataBr($p['due_date']) ?></td>
                                    <td><?= htmlspecialchars(Helpers::truncate($p['description'], 30)) ?></td>
                                    <td class="hide-mobile"><?= htmlspecialchars($p['category'] ?? '-') ?></td>
                                    <td class="text-danger fw-bold"><?= Helpers::moeda($p['value']) ?></td>
                                    <td><?= Helpers::financeStatus($p['status']) ?></td>
                                    <td>
                                        <?php if ($p['status'] !== 'paid'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="payBill(<?= $p['id'] ?>, <?= $p['value'] - $p['paid_value'] ?>)" title="Pagar">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <?php else: ?>
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Pagamento -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalTitle">Registrar Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="paymentId">
                <input type="hidden" id="paymentType">
                <div class="mb-3">
                    <label class="form-label">Valor</label>
                    <input type="text" id="paymentValue" class="form-control" data-mask="money">
                </div>
                <div class="mb-3">
                    <label class="form-label">Forma de Pagamento</label>
                    <select id="paymentMethod" class="form-select">
                        <option value="pix">PIX</option>
                        <option value="cash">Dinheiro</option>
                        <option value="transfer">Transferência</option>
                        <option value="credit">Cartão Crédito</option>
                        <option value="debit">Cartão Débito</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmPayment()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- FAB Mobile -->
<div class="fab-container d-lg-none">
    <button class="fab" data-bs-toggle="dropdown">
        <i class="bi bi-plus-lg"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?= APP_URL ?>finance/receivable/create"><i class="bi bi-arrow-down-circle text-success me-2"></i>Nova Entrada</a></li>
        <li><a class="dropdown-item" href="<?= APP_URL ?>finance/payable/create"><i class="bi bi-arrow-up-circle text-danger me-2"></i>Nova Saída</a></li>
    </ul>
</div>

<script>
const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));

function payBill(id, remaining) {
    document.getElementById('paymentId').value = id;
    document.getElementById('paymentType').value = 'payable';
    document.getElementById('paymentValue').value = 'R$ ' + remaining.toFixed(2).replace('.', ',');
    document.getElementById('paymentModalTitle').textContent = 'Registrar Pagamento';
    paymentModal.show();
}

function receivePayment(id, remaining) {
    document.getElementById('paymentId').value = id;
    document.getElementById('paymentType').value = 'receivable';
    document.getElementById('paymentValue').value = 'R$ ' + remaining.toFixed(2).replace('.', ',');
    document.getElementById('paymentModalTitle').textContent = 'Registrar Recebimento';
    paymentModal.show();
}

async function confirmPayment() {
    const id = document.getElementById('paymentId').value;
    const type = document.getElementById('paymentType').value;
    const value = document.getElementById('paymentValue').value;
    const method = document.getElementById('paymentMethod').value;

    const url = type === 'payable' ? `finance/payable/${id}/pay` : `finance/receivable/${id}/receive`;

    try {
        const result = await apiRequest(url, 'POST', { value, payment_method: method });
        paymentModal.hide();
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) {
            setTimeout(() => location.reload(), 1000);
        }
    } catch (error) {
        showToast('Erro ao processar', 'error');
    }
}
</script>
