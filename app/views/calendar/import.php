<?php
/**
 * CHM Sistema - Importar Calendário Google
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-download me-2"></i>
                        Importar do Google Calendar
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Instruções -->
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>Como exportar do Google Calendar:</h6>
                        <ol class="mb-0">
                            <li>Acesse <a href="https://calendar.google.com/calendar/u/0/r/settings/export" target="_blank" class="alert-link">Google Calendar → Configurações → Exportar</a></li>
                            <li>Clique em <strong>"Exportar"</strong> para baixar o arquivo .zip</li>
                            <li>Extraia o arquivo .zip</li>
                            <li>Selecione o arquivo <strong>.ics</strong> abaixo</li>
                        </ol>
                    </div>

                    <!-- Formulário de Upload -->
                    <form id="importForm" enctype="multipart/form-data" class="mt-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Arquivo ICS do Google Calendar</label>
                            <input type="file" name="ics_file" id="icsFile" class="form-control form-control-lg" accept=".ics" required>
                            <div class="form-text">Formato aceito: .ics (iCalendar)</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnImport">
                                <i class="bi bi-upload me-2"></i>Importar Eventos
                            </button>
                            <a href="<?= APP_URL ?>calendar" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Voltar para Agenda
                            </a>
                        </div>
                    </form>

                    <!-- Progresso -->
                    <div id="progressArea" class="mt-4 d-none">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%">
                                Processando eventos...
                            </div>
                        </div>
                    </div>

                    <!-- Resultado -->
                    <div id="resultArea" class="mt-4 d-none"></div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Informações</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Todos os eventos do arquivo serão importados</li>
                        <li>Eventos duplicados (mesmo UID) serão ignorados</li>
                        <li>Histórico desde 2008 é suportado</li>
                        <li>Datas, horários, descrições e locais são preservados</li>
                        <li>Não há limite de eventos por importação</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('importForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('icsFile');
    if (!fileInput.files.length) {
        showToast('Selecione um arquivo .ics', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('ics_file', fileInput.files[0]);
    
    const btnImport = document.getElementById('btnImport');
    const progressArea = document.getElementById('progressArea');
    const resultArea = document.getElementById('resultArea');
    
    btnImport.disabled = true;
    btnImport.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importando...';
    progressArea.classList.remove('d-none');
    resultArea.classList.add('d-none');
    
    try {
        const response = await fetch('<?= APP_URL ?>calendar/import', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        progressArea.classList.add('d-none');
        resultArea.classList.remove('d-none');
        
        if (result.success) {
            resultArea.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Sucesso!</strong> ${result.message}
                </div>
                <div class="d-grid">
                    <a href="<?= APP_URL ?>calendar" class="btn btn-success btn-lg">
                        <i class="bi bi-calendar-check me-2"></i>Ver Agenda
                    </a>
                </div>
            `;
        } else {
            resultArea.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <strong>Erro:</strong> ${result.message}
                </div>
            `;
        }
    } catch (error) {
        progressArea.classList.add('d-none');
        resultArea.classList.remove('d-none');
        resultArea.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Erro:</strong> Falha ao processar importação
            </div>
        `;
    } finally {
        btnImport.disabled = false;
        btnImport.innerHTML = '<i class="bi bi-upload me-2"></i>Importar Eventos';
    }
});
</script>
