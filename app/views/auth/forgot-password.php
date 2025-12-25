<?php
/**
 * CHM Sistema - Esqueci minha senha
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Session;
$csrfToken = Session::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha | CHM Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 20px;
        }
        .card {
            max-width: 420px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .card-body { padding: 40px; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo i { font-size: 48px; color: #e94560; }
        .logo h1 { font-size: 24px; margin: 10px 0 5px; }
        .btn-primary { background: #1a1a2e; border-color: #1a1a2e; }
        .btn-primary:hover { background: #16213e; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-body">
            <div class="logo">
                <i class="bi bi-car-front-fill"></i>
                <h1>Recuperar Senha</h1>
                <p class="text-muted">Digite seu e-mail para receber as instruções</p>
            </div>

            <div id="message"></div>

            <form id="forgotForm" method="POST" action="<?= APP_URL ?>forgot-password">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" name="email" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3" id="btnSubmit">
                    <i class="bi bi-envelope me-2"></i>Enviar
                </button>

                <div class="text-center">
                    <a href="<?= APP_URL ?>login" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Voltar ao login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <p class="text-center text-white-50 mt-4 small">
        &copy; 2006 - <?= date('Y') ?> CHM-SISTEMA-APP
    </p>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('forgotForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const msg = document.getElementById('message');
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                msg.innerHTML = `<div class="alert alert-${result.success ? 'success' : 'danger'}">${result.message}</div>`;
                
                if (result.success) {
                    this.reset();
                }
            } catch (error) {
                msg.innerHTML = '<div class="alert alert-danger">Erro de conexão</div>';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-envelope me-2"></i>Enviar';
            }
        });
    </script>
</body>
</html>
