<?php
/**
 * CHM Sistema - Layout Principal
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */

use CHM\Core\Session;
use CHM\Core\Helpers;

$pageTitle = $pageTitle ?? 'CHM Sistema';
$userName = Session::get('user_name', 'Usuário');
$userProfile = Session::getUserProfile();
$csrfToken = Session::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <meta name="description" content="CHM Sistema - CRM para Transportes Executivos">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= PWA_SHORT_NAME ?>">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Favicon CHM -->
    <link rel="icon" href="<?= ASSETS_URL ?>icons/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= ASSETS_URL ?>icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= ASSETS_URL ?>icons/favicon-16x16.png">
    <link rel="apple-touch-icon" href="<?= ASSETS_URL ?>icons/apple-touch-icon.png">
    <link rel="manifest" href="<?= APP_URL ?>manifest.json">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>css/app.css?v=<?= CHM_VERSION ?>" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <a href="<?= APP_URL ?>dashboard" class="sidebar-brand">
                <img src="<?= ASSETS_URL ?>img/logo-chm.png" alt="CHM Transportes" class="sidebar-logo">
            </a>
            <button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                <span class="user-role"><?= strip_tags(Helpers::profileLabel($userProfile)) ?></span>
            </div>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="<?= APP_URL ?>dashboard" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                </a>
            </li>

            <li class="nav-header">Agenda</li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>calendar" class="nav-link">
                    <i class="bi bi-calendar3"></i><span>Calendário</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>bookings" class="nav-link">
                    <i class="bi bi-journal-bookmark"></i><span>Agendamentos</span>
                </a>
            </li>

            <?php if ($userProfile === PROFILE_ADMIN): ?>
            <li class="nav-header">Cadastros</li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>clients" class="nav-link">
                    <i class="bi bi-people"></i><span>Clientes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>drivers" class="nav-link">
                    <i class="bi bi-person-badge"></i><span>Motoristas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>vehicles" class="nav-link">
                    <i class="bi bi-car-front"></i><span>Veículos</span>
                </a>
            </li>

            <li class="nav-header">Financeiro</li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>finance" class="nav-link">
                    <i class="bi bi-cash-stack"></i><span>Financeiro</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>reports" class="nav-link">
                    <i class="bi bi-graph-up"></i><span>Relatórios</span>
                </a>
            </li>

            <li class="nav-header">Comunicação</li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>whatsapp" class="nav-link">
                    <i class="bi bi-whatsapp"></i><span>WhatsApp</span>
                </a>
            </li>

            <li class="nav-header">Sistema</li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>settings" class="nav-link">
                    <i class="bi bi-gear"></i><span>Configurações</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>backup" class="nav-link">
                    <i class="bi bi-cloud-arrow-up"></i><span>Backup</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item mt-auto desktop-only">
                <a href="<?= APP_URL ?>logout" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-left"></i><span>Sair</span>
                </a>
            </li>
            
            <li class="nav-item mobile-only">
                <a href="/logout" class="nav-link">
                    <span>Sair</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main id="main-content" class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <button class="btn-hamburger" onclick="toggleSidebar()" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
            
            <a href="<?= APP_URL ?>dashboard" class="topbar-brand">
                <img src="<?= ASSETS_URL ?>img/logo-chm.png" alt="CHM" class="topbar-logo-mobile">
            </a>

            <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <?php foreach ($breadcrumb as $i => $item): ?>
                    <?php if (isset($item['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $item['url'] ?>"><?= $item['label'] ?></a></li>
                    <?php else: ?>
                    <li class="breadcrumb-item active"><?= $item['label'] ?></li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>

            <div class="topbar-actions ms-auto d-flex align-items-center gap-2">
                <span class="user-name-topbar d-none d-md-inline"><?= htmlspecialchars($userName) ?></span>
                <a href="<?= APP_URL ?>logout" class="btn btn-outline-danger btn-sm" title="Sair do sistema">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-md-inline ms-1">Sair</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if ($flash = Session::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($flash = Session::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Page Content -->
        <div class="page-content">
            <?= $content ?>
        </div>
    </main>

    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const APP_URL = '<?= APP_URL ?>';
        const CSRF_TOKEN = '<?= $csrfToken ?>';

        function toggleSidebar() {
            document.body.classList.toggle('sidebar-open');
        }

        // Fecha sidebar ao clicar em link no mobile
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    document.body.classList.remove('sidebar-open');
                }
            });
        });

        // Toast de notificação
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // AJAX helper
        async function apiRequest(url, method = 'GET', data = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            if (data) options.body = JSON.stringify(data);
            
            const response = await fetch(APP_URL + url, options);
            return response.json();
        }
    </script>
    <script src="<?= ASSETS_URL ?>js/app.js?v=<?= CHM_VERSION ?>"></script>

    <?php if (file_exists(APP_PATH . 'pwa/sw-register.js')): ?>
    <script src="<?= APP_URL ?>pwa/sw-register.js"></script>
    <?php endif; ?>
</body>
</html>
