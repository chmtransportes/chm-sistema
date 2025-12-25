<?php
/**
 * CHM Sistema - Detalhes do Agendamento
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 20/01/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-journal-bookmark me-2"></i>Agendamento #<?= $booking['code'] ?></h4>
            <small class="text-muted">Criado em <?= Helpers::dataBr($booking['created_at']) ?></small>
        </div>
        <div>
            <a href="<?= APP_URL ?>bookings/<?= $booking['id'] ?>/edit" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Editar
            </a>
            <a href="<?= APP_URL ?>bookings" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <!-- Dados do Serviço -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-info-circle me-2"></i>Dados do Serviço</span>
                    <?= Helpers::statusLabel($booking['status']) ?>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong>Data:</strong><br>
                            <?= Helpers::dataBr($booking['date']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Horário:</strong><br>
                            <?= substr($booking['time'], 0, 5) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Tipo:</strong><br>
                            <?= Helpers::serviceLabel($booking['service_type']) ?>
                        </div>
                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-geo-alt me-1"></i>Origem:</strong><br>
                            <?= htmlspecialchars($booking['origin']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-geo-alt-fill me-1"></i>Destino:</strong><br>
                            <?= htmlspecialchars($booking['destination'] ?: '-') ?>
                        </div>
                        <?php if ($booking['flight_number']): ?>
                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-md-4">
                            <strong><i class="bi bi-airplane me-1"></i>Voo:</strong><br>
                            <?= htmlspecialchars($booking['flight_number']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Origem do Voo:</strong><br>
                            <?= htmlspecialchars($booking['flight_origin'] ?: '-') ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Chegada:</strong><br>
                            <?= $booking['flight_arrival'] ? substr($booking['flight_arrival'], 0, 5) : '-' ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cliente -->
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-person me-2"></i>Cliente</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><?= htmlspecialchars($booking['client_name']) ?></strong><br>
                            <?php if ($booking['client_phone']): ?>
                            <a href="tel:<?= $booking['client_phone'] ?>" class="text-decoration-none">
                                <i class="bi bi-telephone me-1"></i><?= Helpers::formatPhone($booking['client_phone']) ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($booking['client_email']): ?>
                            <a href="mailto:<?= $booking['client_email'] ?>" class="text-decoration-none">
                                <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($booking['client_email']) ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motorista e Veículo -->
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-car-front me-2"></i>Motorista e Veículo</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Motorista:</strong><br>
                            <?= htmlspecialchars($booking['driver_name'] ?: 'Não atribuído') ?>
                            <?php if ($booking['driver_phone']): ?>
                            <br><small class="text-muted"><?= Helpers::formatPhone($booking['driver_phone']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Veículo:</strong><br>
                            <?php if ($booking['vehicle_model']): ?>
                            <?= htmlspecialchars($booking['vehicle_model']) ?> - 
                            <span class="badge bg-secondary"><?= Helpers::formatPlate($booking['vehicle_plate']) ?></span>
                            <?php if ($booking['vehicle_color']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($booking['vehicle_color']) ?></small>
                            <?php endif; ?>
                            <?php else: ?>
                            Não atribuído
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <?php if ($booking['notes'] || $booking['internal_notes']): ?>
            <div class="card">
                <div class="card-header"><i class="bi bi-chat-text me-2"></i>Observações</div>
                <div class="card-body">
                    <?php if ($booking['notes']): ?>
                    <p class="mb-2"><?= nl2br(htmlspecialchars($booking['notes'])) ?></p>
                    <?php endif; ?>
                    <?php if ($booking['internal_notes']): ?>
                    <div class="alert alert-secondary mb-0">
                        <small><strong>Interno:</strong> <?= nl2br(htmlspecialchars($booking['internal_notes'])) ?></small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- Valores -->
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-currency-dollar me-2"></i>Valores</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Valor do Serviço:</span>
                        <strong><?= Helpers::moeda($booking['value']) ?></strong>
                    </div>
                    <?php if ($booking['extras'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Extras:</span>
                        <strong><?= Helpers::moeda($booking['extras']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($booking['discount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Desconto:</span>
                        <strong class="text-danger">-<?= Helpers::moeda($booking['discount']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fs-5">TOTAL:</span>
                        <strong class="fs-4 text-primary"><?= Helpers::moeda($booking['total']) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Comissão (<?= $booking['commission_rate'] ?>%):</span>
                        <strong><?= Helpers::moeda($booking['commission_value']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pagamento:</span>
                        <span><?= Helpers::paymentLabel($booking['payment_method']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-lightning me-2"></i>Ações</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= APP_URL ?>voucher/<?= $booking['id'] ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-file-text me-2"></i>Ver Voucher
                        </a>
                        <?php if ($booking['status'] === 'completed'): ?>
                        <a href="<?= APP_URL ?>receipt/<?= $booking['id'] ?>" target="_blank" class="btn btn-outline-success">
                            <i class="bi bi-receipt me-2"></i>Ver Recibo
                        </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-success" onclick="sendVoucher(<?= $booking['id'] ?>)">
                            <i class="bi bi-whatsapp me-2"></i>Enviar WhatsApp
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alterar Status -->
            <div class="card">
                <div class="card-header"><i class="bi bi-flag me-2"></i>Alterar Status</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($booking['status'] === 'pending'): ?>
                        <button class="btn btn-info" onclick="updateStatus(<?= $booking['id'] ?>, 'confirmed')">
                            <i class="bi bi-check-circle me-1"></i>Confirmar
                        </button>
                        <?php endif; ?>
                        <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                        <button class="btn btn-primary" onclick="updateStatus(<?= $booking['id'] ?>, 'in_progress')">
                            <i class="bi bi-play-circle me-1"></i>Iniciar
                        </button>
                        <?php endif; ?>
                        <?php if ($booking['status'] === 'in_progress'): ?>
                        <button class="btn btn-success" onclick="updateStatus(<?= $booking['id'] ?>, 'completed')">
                            <i class="bi bi-check2-all me-1"></i>Concluir
                        </button>
                        <?php endif; ?>
                        <?php if ($booking['status'] !== 'cancelled' && $booking['status'] !== 'completed'): ?>
                        <button class="btn btn-outline-danger" onclick="cancelBooking(<?= $booking['id'] ?>)">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function updateStatus(id, status) {
    const statusNames = {
        'confirmed': 'confirmar',
        'in_progress': 'iniciar',
        'completed': 'concluir'
    };
    
    if (!confirm(`Deseja ${statusNames[status]} este agendamento?`)) return;
    
    try {
        const result = await apiRequest('bookings/' + id + '/status', 'POST', { status });
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Erro ao atualizar status', 'error');
    }
}

async function cancelBooking(id) {
    const reason = prompt('Motivo do cancelamento:');
    if (reason === null) return;
    
    try {
        const result = await apiRequest('bookings/' + id + '/status', 'POST', { 
            status: 'cancelled', 
            reason: reason 
        });
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Erro ao cancelar agendamento', 'error');
    }
}

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
