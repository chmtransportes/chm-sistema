<?php
/**
 * CHM Sistema - Formulário de Agendamento
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Session;
use CHM\Core\Helpers;

$isEdit = isset($booking);
$csrfToken = Session::getCsrfToken();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-journal-bookmark me-2"></i>
            <?= $isEdit ? 'Editar Agendamento' : 'Novo Agendamento' ?>
        </h4>
        <a href="<?= APP_URL ?>bookings" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <form method="POST" action="<?= APP_URL ?>bookings<?= $isEdit ? '/' . $booking['id'] : '' ?>" data-ajax>
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="row g-3">
            <!-- Dados do Serviço -->
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-info-circle me-2"></i>Dados do Serviço</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Cliente -->
                            <div class="col-md-6">
                                <label class="form-label">Cliente *</label>
                                <select name="client_id" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($clients ?? [] as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($booking['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Tipo de Serviço -->
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Serviço</label>
                                <select name="service_type" class="form-select">
                                    <option value="transfer" <?= ($booking['service_type'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                                    <option value="hourly" <?= ($booking['service_type'] ?? '') === 'hourly' ? 'selected' : '' ?>>Por Hora</option>
                                    <option value="daily" <?= ($booking['service_type'] ?? '') === 'daily' ? 'selected' : '' ?>>Diária</option>
                                    <option value="airport" <?= ($booking['service_type'] ?? '') === 'airport' ? 'selected' : '' ?>>Aeroporto</option>
                                    <option value="executive" <?= ($booking['service_type'] ?? '') === 'executive' ? 'selected' : '' ?>>Executivo</option>
                                    <option value="event" <?= ($booking['service_type'] ?? '') === 'event' ? 'selected' : '' ?>>Evento</option>
                                </select>
                            </div>

                            <!-- Data -->
                            <div class="col-md-4">
                                <label class="form-label">Data *</label>
                                <input type="date" name="date" class="form-control" required value="<?= $booking['date'] ?? $_GET['date'] ?? date('Y-m-d') ?>">
                            </div>

                            <!-- Hora -->
                            <div class="col-md-4">
                                <label class="form-label">Hora *</label>
                                <input type="time" name="time" class="form-control" required value="<?= isset($booking['time']) ? substr($booking['time'], 0, 5) : '' ?>">
                            </div>

                            <!-- Passageiros -->
                            <div class="col-md-4">
                                <label class="form-label">Passageiros</label>
                                <input type="number" name="passengers" class="form-control" min="1" max="50" value="<?= $booking['passengers'] ?? 1 ?>">
                            </div>

                            <!-- Origem -->
                            <div class="col-12">
                                <label class="form-label">Origem *</label>
                                <input type="text" name="origin" class="form-control" required value="<?= htmlspecialchars($booking['origin'] ?? '') ?>" placeholder="Endereço de origem">
                            </div>

                            <!-- Destino -->
                            <div class="col-12">
                                <label class="form-label">Destino</label>
                                <input type="text" name="destination" class="form-control" value="<?= htmlspecialchars($booking['destination'] ?? '') ?>" placeholder="Endereço de destino">
                            </div>

                            <!-- Voo -->
                            <div class="col-md-4">
                                <label class="form-label">Número do Voo</label>
                                <input type="text" name="flight_number" class="form-control" value="<?= htmlspecialchars($booking['flight_number'] ?? '') ?>">
                            </div>

                            <!-- Origem do Voo -->
                            <div class="col-md-4">
                                <label class="form-label">Origem do Voo</label>
                                <input type="text" name="flight_origin" class="form-control" value="<?= htmlspecialchars($booking['flight_origin'] ?? '') ?>">
                            </div>

                            <!-- Chegada do Voo -->
                            <div class="col-md-4">
                                <label class="form-label">Horário Chegada</label>
                                <input type="time" name="flight_arrival" class="form-control" value="<?= isset($booking['flight_arrival']) ? substr($booking['flight_arrival'], 0, 5) : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Motorista e Veículo -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-car-front me-2"></i>Motorista e Veículo</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Motorista</label>
                                <select name="driver_id" class="form-select">
                                    <option value="">Não atribuído</option>
                                    <?php foreach ($drivers ?? [] as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= ($booking['driver_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($d['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Veículo</label>
                                <select name="vehicle_id" class="form-select">
                                    <option value="">Não atribuído</option>
                                    <?php foreach ($vehicles ?? [] as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= ($booking['vehicle_id'] ?? '') == $v['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v['model']) ?> - <?= $v['plate'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="card">
                    <div class="card-header"><i class="bi bi-chat-text me-2"></i>Observações</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Observações (visível para cliente)</label>
                                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($booking['notes'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Observações Internas</label>
                                <textarea name="internal_notes" class="form-control" rows="3"><?= htmlspecialchars($booking['internal_notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valores e Status -->
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-currency-dollar me-2"></i>Valores</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Valor do Serviço *</label>
                            <input type="text" name="value" class="form-control" data-mask="money" required value="<?= isset($booking['value']) ? 'R$ ' . number_format($booking['value'], 2, ',', '.') : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Extras (pedágios, etc)</label>
                            <input type="text" name="extras" class="form-control" data-mask="money" value="<?= isset($booking['extras']) ? 'R$ ' . number_format($booking['extras'], 2, ',', '.') : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Desconto</label>
                            <input type="text" name="discount" class="form-control" data-mask="money" value="<?= isset($booking['discount']) ? 'R$ ' . number_format($booking['discount'], 2, ',', '.') : '' ?>">
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>TOTAL:</strong>
                            <span class="fs-4 text-primary fw-bold" id="total-display">
                                <?= isset($booking['total']) ? Helpers::moeda($booking['total']) : 'R$ 0,00' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-credit-card me-2"></i>Pagamento</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Forma de Pagamento</label>
                            <select name="payment_method" class="form-select">
                                <option value="pix" <?= ($booking['payment_method'] ?? '') === 'pix' ? 'selected' : '' ?>>PIX</option>
                                <option value="cash" <?= ($booking['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Dinheiro</option>
                                <option value="credit" <?= ($booking['payment_method'] ?? '') === 'credit' ? 'selected' : '' ?>>Cartão Crédito</option>
                                <option value="debit" <?= ($booking['payment_method'] ?? '') === 'debit' ? 'selected' : '' ?>>Cartão Débito</option>
                                <option value="transfer" <?= ($booking['payment_method'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transferência</option>
                                <option value="invoice" <?= ($booking['payment_method'] ?? '') === 'invoice' ? 'selected' : '' ?>>Faturado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comissão (%)</label>
                            <input type="number" name="commission_rate" class="form-control" step="0.01" value="<?= $booking['commission_rate'] ?? 11 ?>">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><i class="bi bi-flag me-2"></i>Status</div>
                    <div class="card-body">
                        <select name="status" class="form-select">
                            <option value="pending" <?= ($booking['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pendente</option>
                            <option value="confirmed" <?= ($booking['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmado</option>
                            <option value="in_progress" <?= ($booking['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>Em Andamento</option>
                            <option value="completed" <?= ($booking['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Concluído</option>
                            <option value="cancelled" <?= ($booking['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-1"></i>Salvar Agendamento
                    </button>
                    <a href="<?= APP_URL ?>bookings" class="btn btn-outline-secondary w-100">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>
