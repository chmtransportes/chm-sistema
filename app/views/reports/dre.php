<?php
/**
 * CHM Sistema - DRE Simplificado
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Helpers;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>DRE Simplificado</h4>
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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i>
                    Demonstrativo de Resultado - <?= Helpers::dataBr($start) ?> a <?= Helpers::dataBr($end) ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <!-- Receita Bruta -->
                            <tr class="table-light">
                                <td><strong>RECEITA BRUTA DE SERVIÇOS</strong></td>
                                <td class="text-end" width="150"><strong><?= Helpers::moeda($dre['receita_bruta']) ?></strong></td>
                            </tr>

                            <!-- Deduções -->
                            <tr>
                                <td class="ps-4">(-) Comissões de Motoristas</td>
                                <td class="text-end text-danger">(<?= Helpers::moeda($dre['deducoes']) ?>)</td>
                            </tr>

                            <!-- Receita Líquida -->
                            <tr class="table-primary">
                                <td><strong>RECEITA LÍQUIDA</strong></td>
                                <td class="text-end"><strong><?= Helpers::moeda($dre['receita_liquida']) ?></strong></td>
                            </tr>

                            <!-- Despesas -->
                            <tr class="table-light">
                                <td><strong>DESPESAS OPERACIONAIS</strong></td>
                                <td class="text-end text-danger"><strong>(<?= Helpers::moeda($dre['despesas']) ?>)</strong></td>
                            </tr>

                            <?php 
                            $categoryLabels = [
                                'combustivel' => 'Combustível',
                                'pedagio' => 'Pedágio',
                                'manutencao' => 'Manutenção',
                                'comissao' => 'Comissões',
                                'seguro' => 'Seguro',
                                'impostos' => 'Impostos',
                                'outros' => 'Outros'
                            ];
                            foreach ($dre['despesas_detalhes'] as $d): 
                                $label = $categoryLabels[$d['category'] ?? ''] ?? ucfirst($d['category'] ?? 'Outros');
                            ?>
                            <tr>
                                <td class="ps-4">(-) <?= $label ?></td>
                                <td class="text-end text-danger">(<?= Helpers::moeda($d['value']) ?>)</td>
                            </tr>
                            <?php endforeach; ?>

                            <!-- Resultado -->
                            <tr class="<?= $dre['resultado'] >= 0 ? 'table-success' : 'table-danger' ?>">
                                <td><strong>RESULTADO DO PERÍODO</strong></td>
                                <td class="text-end"><strong class="fs-5"><?= Helpers::moeda($dre['resultado']) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Indicadores -->
            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-muted">Margem Líquida</h5>
                            <?php 
                            $margem = $dre['receita_bruta'] > 0 ? ($dre['receita_liquida'] / $dre['receita_bruta']) * 100 : 0;
                            ?>
                            <h3 class="<?= $margem >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($margem, 1) ?>%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-muted">Margem Operacional</h5>
                            <?php 
                            $margemOp = $dre['receita_bruta'] > 0 ? ($dre['resultado'] / $dre['receita_bruta']) * 100 : 0;
                            ?>
                            <h3 class="<?= $margemOp >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($margemOp, 1) ?>%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-muted">% Comissões</h5>
                            <?php 
                            $pctComissao = $dre['receita_bruta'] > 0 ? ($dre['deducoes'] / $dre['receita_bruta']) * 100 : 0;
                            ?>
                            <h3 class="text-warning"><?= number_format($pctComissao, 1) ?>%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
