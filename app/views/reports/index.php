<?php
/**
 * CHM Sistema - Menu de Relatórios
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-graph-up me-2"></i>Relatórios</h4>
    </div>

    <div class="row g-3">
        <!-- Agendamentos -->
        <div class="col-md-6 col-lg-4">
            <a href="<?= APP_URL ?>reports/bookings" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-journal-bookmark text-primary fs-1 mb-3"></i>
                    <h5 class="card-title">Agendamentos</h5>
                    <p class="card-text text-muted">Relatório completo de todos os agendamentos</p>
                </div>
            </a>
        </div>

        <!-- Faturamento por Cliente -->
        <div class="col-md-6 col-lg-4">
            <a href="<?= APP_URL ?>reports/revenue-client" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people text-success fs-1 mb-3"></i>
                    <h5 class="card-title">Faturamento por Cliente</h5>
                    <p class="card-text text-muted">Receita gerada por cada cliente</p>
                </div>
            </a>
        </div>

        <!-- Faturamento por Pagamento -->
        <div class="col-md-6 col-lg-4">
            <a href="<?= APP_URL ?>reports/revenue-payment" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-credit-card text-info fs-1 mb-3"></i>
                    <h5 class="card-title">Por Forma de Pagamento</h5>
                    <p class="card-text text-muted">Receita por tipo de pagamento</p>
                </div>
            </a>
        </div>

        <!-- Faturamento por Serviço -->
        <div class="col-md-6 col-lg-4">
            <a href="<?= APP_URL ?>reports/revenue-service" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-tags text-warning fs-1 mb-3"></i>
                    <h5 class="card-title">Por Tipo de Serviço</h5>
                    <p class="card-text text-muted">Receita por categoria de serviço</p>
                </div>
            </a>
        </div>

        <!-- Faturamento por Motorista -->
        <div class="col-md-6 col-lg-4">
            <a href="<?= APP_URL ?>reports/revenue-driver" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-person-badge text-danger fs-1 mb-3"></i>
                    <h5 class="card-title">Por Motorista</h5>
                    <p class="card-text text-muted">Receita e comissões por motorista</p>
                </div>
            </a>
        </div>

        <!-- Faturamento por Veículo -->
        <div class="col-md-6 col-lg-4">
            <a href="<?= APP_URL ?>reports/revenue-vehicle" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-car-front text-secondary fs-1 mb-3"></i>
                    <h5 class="card-title">Por Veículo</h5>
                    <p class="card-text text-muted">Utilização e receita por veículo</p>
                </div>
            </a>
        </div>

        <!-- Comissões -->
        <div class="col-md-6 col-lg-4">
            <a href="<?= APP_URL ?>reports/commissions" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-percent text-primary fs-1 mb-3"></i>
                    <h5 class="card-title">Comissões (11%)</h5>
                    <p class="card-text text-muted">Total de comissões do período</p>
                </div>
            </a>
        </div>

        <!-- Fechamento de Motorista -->
        <div class="col-md-6 col-lg-4">
            <a href="<?= APP_URL ?>reports/driver-closing" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cash-stack text-success fs-1 mb-3"></i>
                    <h5 class="card-title">Fechamento de Motorista</h5>
                    <p class="card-text text-muted">Fechamento mensal por motorista</p>
                </div>
            </a>
        </div>
    </div>
</div>
