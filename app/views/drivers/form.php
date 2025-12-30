<?php
/**
 * CHM Sistema - Formulário de Motorista
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Session;
use CHM\Core\Helpers;

$isEdit = isset($driver);
$csrfToken = Session::getCsrfToken();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-person-badge me-2"></i>
            <?= $isEdit ? 'Editar Motorista' : 'Novo Motorista' ?>
        </h4>
        <a href="<?= APP_URL ?>drivers" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <form method="POST" action="<?= APP_URL ?>drivers<?= $isEdit ? '/' . $driver['id'] : '' ?>" enctype="multipart/form-data" data-ajax data-reload>
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-8">
                <!-- Dados Pessoais -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-person me-2"></i>Dados Pessoais</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome Completo *</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($driver['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CPF *</label>
                                <input type="text" name="document" class="form-control" data-mask="cpf" required value="<?= htmlspecialchars($driver['document'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Número</label>
                                <input type="text" name="driver_number" class="form-control bg-white" value="<?= htmlspecialchars($driver['driver_number'] ?? $nextDriverNumber) ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CNH -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-card-text me-2"></i>CNH</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Número da CNH *</label>
                                <input type="text" name="cnh" class="form-control" required value="<?= htmlspecialchars($driver['cnh'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Categoria *</label>
                                <select name="cnh_category" class="form-select" required>
                                    <option value="">Selecione</option>
                                    <option value="A" <?= ($driver['cnh_category'] ?? '') === 'A' ? 'selected' : '' ?>>A</option>
                                    <option value="B" <?= ($driver['cnh_category'] ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                                    <option value="AB" <?= ($driver['cnh_category'] ?? '') === 'AB' ? 'selected' : '' ?>>AB</option>
                                    <option value="C" <?= ($driver['cnh_category'] ?? '') === 'C' ? 'selected' : '' ?>>C</option>
                                    <option value="D" <?= ($driver['cnh_category'] ?? '') === 'D' ? 'selected' : '' ?>>D</option>
                                    <option value="E" <?= ($driver['cnh_category'] ?? '') === 'E' ? 'selected' : '' ?>>E</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Validade *</label>
                                <input type="date" name="cnh_expiry" class="form-control" required value="<?= $driver['cnh_expiry'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contato -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-telephone me-2"></i>Contato</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Celular/WhatsApp</label>
                                <input type="text" name="phone" class="form-control" data-mask="phone" value="<?= htmlspecialchars($driver['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($driver['email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dados Financeiros -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-cash me-2"></i>Dados Financeiros</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Chave PIX</label>
                                <input type="text" name="pix_key" class="form-control" value="<?= htmlspecialchars($driver['pix_key'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Banco</label>
                                <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($driver['bank_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Agência</label>
                                <input type="text" name="bank_agency" class="form-control" value="<?= htmlspecialchars($driver['bank_agency'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Conta</label>
                                <input type="text" name="bank_account" class="form-control" value="<?= htmlspecialchars($driver['bank_account'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Fotos -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-camera me-2"></i>Fotos</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Foto do Motorista</label>
                            <div class="preview-container mb-2 <?= empty($driver['photo']) ? 'd-none' : '' ?>" id="containerPhoto">
                                <img id="previewPhoto" src="<?= !empty($driver['photo']) ? APP_URL . 'uploads/drivers/' . $driver['photo'] : '' ?>" class="img-thumbnail" style="max-height: 100px;">
                                <button type="button" class="btn-remove" onclick="removeImage('Photo')" title="Remover foto">&times;</button>
                            </div>
                            <input type="file" name="photo" id="inputPhoto" class="form-control" accept="image/*" onchange="previewImage(this, 'Photo')">
                            <small class="text-muted">Foto 3x4 ou similar</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto da CNH</label>
                            <div class="preview-container mb-2 <?= empty($driver['cnh_photo']) ? 'd-none' : '' ?>" id="containerCnhPhoto">
                                <img id="previewCnhPhoto" src="<?= !empty($driver['cnh_photo']) ? APP_URL . 'uploads/drivers/' . $driver['cnh_photo'] : '' ?>" class="img-thumbnail" style="max-height: 100px;">
                                <button type="button" class="btn-remove" onclick="removeImage('CnhPhoto')" title="Remover foto">&times;</button>
                            </div>
                            <input type="file" name="cnh_photo" id="inputCnhPhoto" class="form-control" accept="image/*" onchange="previewImage(this, 'CnhPhoto')">
                            <small class="text-muted">Documento CNH (frente ou verso)</small>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="status" value="active">

                <!-- Observações -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-chat-text me-2"></i>Observações</div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($driver['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-check-lg me-1"></i>Salvar
                </button>
                <a href="<?= APP_URL ?>drivers" class="btn btn-outline-secondary w-100">Cancelar</a>
            </div>
        </div>
    </form>
</div>

<style>
.address-row {
    display: flex !important;
    width: 100% !important;
    gap: 12px !important;
    align-items: flex-start !important;
    flex-wrap: nowrap !important;
}
.address-row .field-bairro {
    flex: 0 0 25% !important;
    min-width: 120px;
}
.address-row .field-cidade {
    flex: 1 1 auto !important;
}
.address-row .field-uf {
    flex: 0 0 70px !important;
    max-width: 70px !important;
    min-width: 70px !important;
}
.address-row input {
    height: 42px;
}
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
