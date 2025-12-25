<?php
/**
 * CHM Sistema - Formulário de Veículo
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Session;

$isEdit = isset($vehicle);
$csrfToken = Session::getCsrfToken();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-car-front me-2"></i>
            <?= $isEdit ? 'Editar Veículo' : 'Novo Veículo' ?>
        </h4>
        <a href="<?= APP_URL ?>vehicles" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <form method="POST" action="<?= APP_URL ?>vehicles<?= $isEdit ? '/' . $vehicle['id'] : '' ?>" enctype="multipart/form-data" data-ajax data-reload>
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-8">
                <!-- Dados do Veículo -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-car-front me-2"></i>Dados do Veículo</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Placa *</label>
                                <input type="text" name="plate" class="form-control" data-mask="plate" required value="<?= htmlspecialchars($vehicle['plate'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Marca *</label>
                                <input type="text" name="brand" class="form-control" required value="<?= htmlspecialchars($vehicle['brand'] ?? '') ?>" list="brands">
                                <datalist id="brands">
                                    <option value="Chevrolet">
                                    <option value="Fiat">
                                    <option value="Ford">
                                    <option value="Honda">
                                    <option value="Hyundai">
                                    <option value="Jeep">
                                    <option value="Mercedes-Benz">
                                    <option value="Nissan">
                                    <option value="Renault">
                                    <option value="Toyota">
                                    <option value="Volkswagen">
                                </datalist>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Modelo *</label>
                                <input type="text" name="model" class="form-control" required value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Ano *</label>
                                <input type="number" name="year" class="form-control" required min="1990" max="<?= date('Y') + 1 ?>" value="<?= $vehicle['year'] ?? date('Y') ?>">
                            </div>
                            </div>
                        <div class="row g-3 align-items-end">
                            <div class="col">
                                <label class="form-label">Cor *</label>
                                <input type="text" name="color" class="form-control" required value="<?= htmlspecialchars($vehicle['color'] ?? '') ?>">
                            </div>
                            <div class="col">
                                <label class="form-label">Categoria</label>
                                <select name="category" class="form-select">
                                    <option value="">Selecione</option>
                                    <option value="hatch" <?= ($vehicle['category'] ?? '') === 'hatch' ? 'selected' : '' ?>>Hatch</option>
                                    <option value="sedan" <?= ($vehicle['category'] ?? '') === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                                    <option value="spin" <?= ($vehicle['category'] ?? '') === 'spin' ? 'selected' : '' ?>>Spin</option>
                                    <option value="suv" <?= ($vehicle['category'] ?? '') === 'suv' ? 'selected' : '' ?>>SUV</option>
                                    <option value="van" <?= ($vehicle['category'] ?? '') === 'van' ? 'selected' : '' ?>>Van</option>
                                    <option value="microonibus" <?= ($vehicle['category'] ?? '') === 'microonibus' ? 'selected' : '' ?>>Microônibus</option>
                                    <option value="onibus" <?= ($vehicle['category'] ?? '') === 'onibus' ? 'selected' : '' ?>>Ônibus</option>
                                    <option value="outros" <?= ($vehicle['category'] ?? '') === 'outros' ? 'selected' : '' ?>>Outros</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Combustível</label>
                                <select name="fuel" class="form-select">
                                    <option value="">Selecione</option>
                                    <option value="flex" <?= ($vehicle['fuel'] ?? '') === 'flex' ? 'selected' : '' ?>>Flex</option>
                                    <option value="gasoline" <?= ($vehicle['fuel'] ?? '') === 'gasoline' ? 'selected' : '' ?>>Gasolina</option>
                                    <option value="ethanol" <?= ($vehicle['fuel'] ?? '') === 'ethanol' ? 'selected' : '' ?>>Etanol</option>
                                    <option value="gnv" <?= ($vehicle['fuel'] ?? '') === 'gnv' ? 'selected' : '' ?>>GNV</option>
                                    <option value="diesel" <?= ($vehicle['fuel'] ?? '') === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                                    <option value="electric" <?= ($vehicle['fuel'] ?? '') === 'electric' ? 'selected' : '' ?>>Elétrico</option>
                                    <option value="hybrid" <?= ($vehicle['fuel'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Híbrido</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Tipo</label>
                                <select name="ownership" class="form-select">
                                    <option value="">Selecione</option>
                                    <option value="proprio" <?= ($vehicle['ownership'] ?? '') === 'proprio' ? 'selected' : '' ?>>Próprio</option>
                                    <option value="alugado" <?= ($vehicle['ownership'] ?? '') === 'alugado' ? 'selected' : '' ?>>Alugado</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Lugares</label>
                                <input type="number" name="seats" class="form-control" min="1" max="50" value="<?= $vehicle['seats'] ?? 4 ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seguro -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-shield-check me-2"></i>Seguro</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Seguradora</label>
                                <input type="text" name="insurance_company" class="form-control" value="<?= htmlspecialchars($vehicle['insurance_company'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apólice</label>
                                <input type="text" name="insurance_policy" class="form-control" value="<?= htmlspecialchars($vehicle['insurance_policy'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Validade</label>
                                <input type="date" name="insurance_expiry" class="form-control" value="<?= $vehicle['insurance_expiry'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Status -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-toggle-on me-2"></i>Status</div>
                    <div class="card-body">
                        <select name="status" class="form-select">
                            <option value="active" <?= ($vehicle['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inactive" <?= ($vehicle['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
                            <option value="maintenance" <?= ($vehicle['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Em Manutenção</option>
                        </select>
                    </div>
                </div>

                <!-- Foto do Veículo -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-camera me-2"></i>Foto do Veículo</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Foto (Frente com Placa)</label>
                            <div class="preview-container mb-2 <?= empty($vehicle['photo']) ? 'd-none' : '' ?>" id="containerPhoto">
                                <img id="previewPhoto" src="<?= !empty($vehicle['photo']) ? APP_URL . 'uploads/vehicles/' . $vehicle['photo'] : '' ?>" class="img-thumbnail" style="max-height: 120px;">
                                <button type="button" class="btn-remove" onclick="removeImage('Photo')" title="Remover foto">&times;</button>
                            </div>
                            <input type="file" name="photo" id="inputPhoto" class="form-control" accept="image/*" onchange="previewImage(this, 'Photo')">
                            <small class="text-muted">Foto frontal mostrando a placa</small>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-chat-text me-2"></i>Observações</div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($vehicle['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-check-lg me-1"></i>Salvar
                </button>
                <a href="<?= APP_URL ?>vehicles" class="btn btn-outline-secondary w-100">Cancelar</a>
            </div>
        </div>
    </form>
</div>

<style>
.preview-container {
    position: relative;
    display: inline-block;
}
.btn-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #dc3545;
    color: white;
    border: 2px solid white;
    font-size: 16px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.btn-remove:hover {
    background: #c82333;
}
</style>

<script>
document.getElementById('ownerType').addEventListener('change', function() {
    const isTerceirizado = this.value === 'terceirizado';
    document.getElementById('ownerNameField').style.display = isTerceirizado ? '' : 'none';
    document.getElementById('ownerDocField').style.display = isTerceirizado ? '' : 'none';
});

function previewImage(input, suffix) {
    const container = document.getElementById('container' + suffix);
    const preview = document.getElementById('preview' + suffix);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(suffix) {
    const container = document.getElementById('container' + suffix);
    const preview = document.getElementById('preview' + suffix);
    const input = document.getElementById('input' + suffix);
    
    container.classList.add('d-none');
    preview.src = '';
    input.value = '';
}
</script>
