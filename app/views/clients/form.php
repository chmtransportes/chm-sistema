<?php
/**
 * CHM Sistema - Formulário de Cliente
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */

use CHM\Core\Session;

$isEdit = isset($client);
$csrfToken = Session::getCsrfToken();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-person me-2"></i>
            <?= $isEdit ? 'Editar Cliente' : 'Novo Cliente' ?>
        </h4>
        <a href="<?= APP_URL ?>clients" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>clients<?= $isEdit ? '/' . $client['id'] : '' ?>" data-ajax data-reload>
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <div class="row g-3 align-items-end">
                    <!-- Tipo -->
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="type" class="form-select" id="clientType">
                            <option value="pf" <?= ($client['type'] ?? 'pf') === 'pf' ? 'selected' : '' ?>>Pessoa Física</option>
                            <option value="pj" <?= ($client['type'] ?? '') === 'pj' ? 'selected' : '' ?>>Pessoa Jurídica</option>
                        </select>
                    </div>

                    <!-- Nome -->
                    <div class="col-md-8">
                        <label class="form-label">Nome / Razão Social *</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($client['name'] ?? '') ?>">
                    </div>

                    <!-- Número do Cliente -->
                    <div class="col-md-2">
                        <label class="form-label">Número</label>
                        <input type="text" name="client_number" class="form-control" value="<?= htmlspecialchars($client['client_number'] ?? ($nextClientNumber ?? '')) ?>" readonly style="background-color: #fff;">
                    </div>

                    <!-- Nome Fantasia (PJ) -->
                    <div class="col-md-3" id="tradeNameField" style="<?= ($client['type'] ?? 'pf') === 'pf' ? 'display:none' : '' ?>">
                        <label class="form-label">Nome Fantasia</label>
                        <input type="text" name="trade_name" class="form-control" value="<?= htmlspecialchars($client['trade_name'] ?? '') ?>">
                    </div>

                    <!-- CPF/CNPJ -->
                    <div class="col-md-4">
                        <label class="form-label" id="documentLabel">CPF *</label>
                        <input type="text" name="document" class="form-control" required 
                               id="documentInput" data-mask="cpf"
                               value="<?= htmlspecialchars($client['document'] ?? '') ?>">
                    </div>

                    <!-- RG -->
                    <div class="col-md-4" id="rgField">
                        <label class="form-label">RG</label>
                        <input type="text" name="rg" class="form-control" value="<?= htmlspecialchars($client['rg'] ?? '') ?>">
                    </div>

                    <!-- Status -->
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($client['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inactive" <?= ($client['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>

                    <div class="col-12"><hr></div>

                    <!-- E-mail -->
                    <div class="col-md-4">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client['email'] ?? '') ?>">
                    </div>

                    <!-- Celular/WhatsApp 1 -->
                    <div class="col-md-4">
                        <label class="form-label">Celular/WhatsApp 1</label>
                        <input type="text" name="phone" class="form-control" data-mask="phone" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                    </div>

                    <!-- Celular/WhatsApp 2 -->
                    <div class="col-md-4">
                        <label class="form-label">Celular/WhatsApp 2</label>
                        <input type="text" name="whatsapp" class="form-control" data-mask="phone" value="<?= htmlspecialchars($client['whatsapp'] ?? '') ?>">
                    </div>

                    <div class="col-12"><hr></div>

                    <!-- CEP -->
                    <div class="col-md-3">
                        <label class="form-label">CEP</label>
                        <input type="text" name="zipcode" class="form-control" data-mask="cep" data-cep-search value="<?= htmlspecialchars($client['zipcode'] ?? '') ?>">
                    </div>

                    <!-- Endereço -->
                    <div class="col-md-6">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($client['address'] ?? '') ?>">
                    </div>

                    <!-- Número -->
                    <div class="col-md-3">
                        <label class="form-label">Número</label>
                        <input type="text" name="address_number" class="form-control" value="<?= htmlspecialchars($client['address_number'] ?? '') ?>">
                    </div>

                    <!-- Bairro, Cidade, UF -->
                    <div class="col-md-3">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="neighborhood" class="form-control" value="<?= htmlspecialchars($client['neighborhood'] ?? '') ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($client['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">UF</label>
                        <input type="text" name="state" class="form-control" maxlength="2" value="<?= htmlspecialchars($client['state'] ?? '') ?>">
                    </div>

                    <div class="col-12"><hr></div>

                    <!-- Observações -->
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Salvar
                    </button>
                    <a href="<?= APP_URL ?>clients" class="btn btn-outline-secondary ms-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('clientType').addEventListener('change', function() {
    const isPJ = this.value === 'pj';
    document.getElementById('tradeNameField').style.display = isPJ ? '' : 'none';
    document.getElementById('documentLabel').textContent = isPJ ? 'CNPJ *' : 'CPF *';
    document.getElementById('rgField').style.display = isPJ ? 'none' : '';
    
    const docInput = document.getElementById('documentInput');
    docInput.value = '';
    docInput.dataset.mask = isPJ ? 'cnpj' : 'cpf';
});
</script>
