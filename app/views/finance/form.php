<?php
/**
 * CHM Sistema - Formulário Financeiro
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Session;

$isPayable = ($type ?? 'payable') === 'payable';
$csrfToken = Session::getCsrfToken();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-<?= $isPayable ? 'arrow-up-circle text-danger' : 'arrow-down-circle text-success' ?> me-2"></i>
            <?= $isPayable ? 'Nova Conta a Pagar (Saída)' : 'Nova Conta a Receber (Entrada)' ?>
        </h4>
        <a href="<?= APP_URL ?>finance" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="<?= APP_URL ?>finance/<?= $isPayable ? 'payable' : 'receivable' ?>" data-ajax>
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <div class="card mb-3">
                    <div class="card-header">
                        <i class="bi bi-info-circle me-2"></i>Dados da <?= $isPayable ? 'Saída' : 'Entrada' ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Descrição *</label>
                                <input type="text" name="description" class="form-control" required 
                                       placeholder="<?= $isPayable ? 'Ex: Combustível, Pedágio, Comissão motorista...' : 'Ex: Serviço de transfer, Diária...' ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Categoria</label>
                                <select name="category" class="form-select">
                                    <option value="">Selecione...</option>
                                    <?php if ($isPayable): ?>
                                    <option value="combustivel">Combustível</option>
                                    <option value="pedagio">Pedágio</option>
                                    <option value="manutencao">Manutenção</option>
                                    <option value="comissao">Comissão Motorista</option>
                                    <option value="seguro">Seguro</option>
                                    <option value="impostos">Impostos</option>
                                    <option value="outros">Outros</option>
                                    <?php else: ?>
                                    <option value="servico">Serviço</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="diaria">Diária</option>
                                    <option value="evento">Evento</option>
                                    <option value="outros">Outros</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Data de Vencimento *</label>
                                <input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Valor *</label>
                                <input type="text" name="value" class="form-control" data-mask="money" required placeholder="R$ 0,00">
                            </div>

                            <?php if ($isPayable): ?>
                            <div class="col-md-6">
                                <label class="form-label">Fornecedor</label>
                                <input type="text" name="supplier" class="form-control" placeholder="Nome do fornecedor">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Motorista (se aplicável)</label>
                                <select name="driver_id" class="form-select">
                                    <option value="">Nenhum</option>
                                    <?php foreach ($drivers ?? [] as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php else: ?>
                            <div class="col-md-6">
                                <label class="form-label">Cliente</label>
                                <select name="client_id" class="form-select">
                                    <option value="">Nenhum</option>
                                    <?php foreach ($clients ?? [] as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Informações adicionais..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-<?= $isPayable ? 'danger' : 'success' ?> flex-grow-1">
                        <i class="bi bi-check-lg me-1"></i>Salvar <?= $isPayable ? 'Saída' : 'Entrada' ?>
                    </button>
                    <a href="<?= APP_URL ?>finance" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
