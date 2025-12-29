<?php
/**
 * CHM Sistema - Tela de Login
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
    <meta name="theme-color" content="#0f172a">
    <title>Login | CHM Sistema</title>
    
    <!-- Favicon CHM -->
    <link rel="icon" href="<?= ASSETS_URL ?>icons/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= ASSETS_URL ?>icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= ASSETS_URL ?>icons/favicon-16x16.png">
    <link rel="apple-touch-icon" href="<?= ASSETS_URL ?>icons/apple-touch-icon.png">
    <link rel="manifest" href="<?= APP_URL ?>manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo i {
            font-size: 48px;
            color: #e94560;
        }
        .login-logo h1 {
            font-size: 18px;
            font-weight: 600;
            color: #6c757d;
            margin: 10px 0 5px;
        }
        .login-logo p {
            color: #6c757d;
            font-size: 14px;
        }
        .form-floating {
            margin-bottom: 15px;
        }
        .form-floating .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            height: 55px;
        }
        .form-floating .form-control:focus {
            border-color: #1a1a2e;
            box-shadow: none;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            background: #1a1a2e;
            border: none;
            font-weight: 600;
            font-size: 16px;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #16213e;
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
        }
        .login-footer a {
            color: #1a1a2e;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        @media (max-width: 480px) {
            .login-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="<?= ASSETS_URL ?>img/logo-chm.png" alt="CHM" style="max-width: 150px;">
                <h1>CHM-SISTEMA-APP</h1>
            </div>

            <?php if ($error = Session::getFlash('error')): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success = Session::getFlash('success')): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="<?= APP_URL ?>login" data-ajax>
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="E-mail" required autofocus>
                    <label for="email"><i class="bi bi-envelope me-2"></i>E-mail</label>
                </div>

                <div class="form-floating position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required>
                    <label for="password"><i class="bi bi-lock me-2"></i>Senha</label>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Lembrar de mim</label>
                </div>

                <button type="submit" class="btn btn-primary btn-login" id="btnSubmit">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                </button>
            </form>

            <div class="login-footer">
                <a href="<?= APP_URL ?>forgot-password">Esqueci minha senha</a>
            </div>
        </div>

        <p class="text-center text-white-50 mt-4 small">
            &copy; 2006 - <?= date('Y') ?> CHM-SISTEMA-APP
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Entrando...';

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.success) {
                    window.location.href = result.data?.redirect || '<?= APP_URL ?>dashboard';
                } else {
                    showError(result.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                showError('Erro de conexão. Tente novamente.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });

        function showError(message) {
            let alert = document.querySelector('.alert-danger');
            if (!alert) {
                alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                document.querySelector('.login-logo').after(alert);
            }
            alert.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>' + message;
        }
    </script>
</body>
</html>
