<?php
/**
 * CHM Sistema - Bootstrap da Aplicação
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

// Define constante para prevenir acesso direto aos arquivos
define('CHM_SISTEMA', true);

// Carrega configurações
require_once __DIR__ . '/config/config.php';

// Autoloader
spl_autoload_register(function ($class) {
    // Remove namespace prefix
    $prefix = 'CHM\\';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $parts = explode('\\', $relativeClass);
    
    // Mapeia namespace para diretório
    $namespaceMap = [
        'Core' => 'core',
        'Auth' => 'auth',
        'Users' => 'users',
        'Clients' => 'clients',
        'Drivers' => 'drivers',
        'Vehicles' => 'vehicles',
        'Bookings' => 'bookings',
        'Calendar' => 'calendar',
        'Finance' => 'finance',
        'Reports' => 'reports',
        'Vouchers' => 'vouchers',
        'WhatsApp' => 'whatsapp'
    ];

    if (isset($namespaceMap[$parts[0]])) {
        $parts[0] = $namespaceMap[$parts[0]];
    }

    $file = APP_PATH . strtolower(implode('/', $parts)) . '.php';
    
    // Tenta também com case original
    if (!file_exists($file)) {
        $file = APP_PATH . implode('/', $parts) . '.php';
    }

    if (file_exists($file)) {
        require_once $file;
    }
});

// Inicia sessão
use CHM\Core\Session;
use CHM\Core\Router;

Session::start();

// Cria diretórios necessários
$directories = [LOGS_PATH, UPLOADS_PATH, BACKUP_PATH];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configura router
$router = Router::getInstance();

// Middlewares
$router->middleware('auth', function() {
    if (!Session::isAuthenticated()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Router::json(['success' => false, 'message' => 'Não autorizado'], 401);
        }
        Router::redirect(APP_URL . 'login');
        return false;
    }
    return true;
});

$router->middleware('admin', function() {
    if (!Session::isAdmin()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Router::json(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        Session::flash('error', 'Acesso restrito.');
        Router::redirect(APP_URL . 'dashboard');
        return false;
    }
    return true;
});

// ============================================
// ROTAS PÚBLICAS
// ============================================

// Login
$router->get('/login', [\CHM\Auth\AuthController::class, 'showLogin']);
$router->post('/login', [\CHM\Auth\AuthController::class, 'login']);
$router->get('/logout', [\CHM\Auth\AuthController::class, 'logout']);

// Recuperação de senha
$router->get('/forgot-password', [\CHM\Auth\AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [\CHM\Auth\AuthController::class, 'forgotPassword']);
$router->get('/reset-password/{token}', [\CHM\Auth\AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [\CHM\Auth\AuthController::class, 'resetPassword']);

// Vouchers públicos
$router->get('/voucher/{id}', [\CHM\Vouchers\VoucherController::class, 'voucher']);
$router->get('/receipt/{id}', [\CHM\Vouchers\VoucherController::class, 'receipt']);

// Manifest PWA
$router->get('/manifest.json', function() {
    header('Content-Type: application/manifest+json');
    readfile(APP_PATH . 'pwa/manifest.json');
});

// ============================================
// ROTAS AUTENTICADAS
// ============================================

// Dashboard
$router->get('/', [\CHM\Users\DashboardController::class, 'index'], ['auth']);
$router->get('/dashboard', [\CHM\Users\DashboardController::class, 'index'], ['auth']);

// Alterar senha
$router->get('/change-password', [\CHM\Auth\AuthController::class, 'showChangePassword'], ['auth']);
$router->post('/change-password', [\CHM\Auth\AuthController::class, 'changePassword'], ['auth']);

// Calendário
$router->get('/calendar', [\CHM\Calendar\CalendarController::class, 'index'], ['auth']);
$router->get('/calendar/day', [\CHM\Calendar\CalendarController::class, 'dayView'], ['auth']);
$router->get('/calendar/week', [\CHM\Calendar\CalendarController::class, 'weekView'], ['auth']);

// API Calendário
$router->get('/api/calendar/events', [\CHM\Calendar\CalendarController::class, 'events'], ['auth']);

// Clientes
$router->get('/clients', [\CHM\Clients\ClientController::class, 'index'], ['auth']);
$router->get('/clients/create', [\CHM\Clients\ClientController::class, 'create'], ['auth']);
$router->post('/clients', [\CHM\Clients\ClientController::class, 'store'], ['auth']);
$router->get('/clients/{id}', [\CHM\Clients\ClientController::class, 'show'], ['auth']);
$router->get('/clients/{id}/edit', [\CHM\Clients\ClientController::class, 'edit'], ['auth']);
$router->put('/clients/{id}', [\CHM\Clients\ClientController::class, 'update'], ['auth']);
$router->delete('/clients/{id}', [\CHM\Clients\ClientController::class, 'delete'], ['auth', 'admin']);
$router->get('/api/clients', [\CHM\Clients\ClientController::class, 'apiList'], ['auth']);

// Motoristas
$router->get('/drivers', [\CHM\Drivers\DriverController::class, 'index'], ['auth']);
$router->get('/drivers/create', [\CHM\Drivers\DriverController::class, 'create'], ['auth']);
$router->post('/drivers', [\CHM\Drivers\DriverController::class, 'store'], ['auth']);
$router->get('/drivers/{id}/edit', [\CHM\Drivers\DriverController::class, 'edit'], ['auth']);
$router->put('/drivers/{id}', [\CHM\Drivers\DriverController::class, 'update'], ['auth']);
$router->delete('/drivers/{id}', [\CHM\Drivers\DriverController::class, 'delete'], ['auth', 'admin']);
$router->get('/drivers/{id}/closing', [\CHM\Drivers\DriverController::class, 'closing'], ['auth']);
$router->get('/api/drivers', [\CHM\Drivers\DriverController::class, 'apiList'], ['auth']);
$router->get('/api/drivers/available', [\CHM\Drivers\DriverController::class, 'apiAvailable'], ['auth']);

// Veículos
$router->get('/vehicles', [\CHM\Vehicles\VehicleController::class, 'index'], ['auth']);
$router->get('/vehicles/create', [\CHM\Vehicles\VehicleController::class, 'create'], ['auth']);
$router->post('/vehicles', [\CHM\Vehicles\VehicleController::class, 'store'], ['auth']);
$router->get('/vehicles/{id}/edit', [\CHM\Vehicles\VehicleController::class, 'edit'], ['auth']);
$router->put('/vehicles/{id}', [\CHM\Vehicles\VehicleController::class, 'update'], ['auth']);
$router->delete('/vehicles/{id}', [\CHM\Vehicles\VehicleController::class, 'delete'], ['auth', 'admin']);
$router->get('/api/vehicles', [\CHM\Vehicles\VehicleController::class, 'apiList'], ['auth']);
$router->get('/api/vehicles/available', [\CHM\Vehicles\VehicleController::class, 'apiAvailable'], ['auth']);

// Agendamentos
$router->get('/bookings', [\CHM\Bookings\BookingController::class, 'index'], ['auth']);
$router->get('/bookings/create', [\CHM\Bookings\BookingController::class, 'create'], ['auth']);
$router->post('/bookings', [\CHM\Bookings\BookingController::class, 'store'], ['auth']);
$router->get('/bookings/{id}', [\CHM\Bookings\BookingController::class, 'show'], ['auth']);
$router->get('/bookings/{id}/edit', [\CHM\Bookings\BookingController::class, 'edit'], ['auth']);
$router->put('/bookings/{id}', [\CHM\Bookings\BookingController::class, 'update'], ['auth']);
$router->post('/bookings/{id}/status', [\CHM\Bookings\BookingController::class, 'updateStatus'], ['auth']);
$router->post('/bookings/{id}/voucher', [\CHM\Bookings\BookingController::class, 'sendVoucher'], ['auth']);
$router->get('/api/bookings/calendar', [\CHM\Bookings\BookingController::class, 'apiCalendar'], ['auth']);
$router->get('/api/bookings/stats', [\CHM\Bookings\BookingController::class, 'apiStats'], ['auth']);

// Relatórios
$router->get('/reports', [\CHM\Reports\ReportController::class, 'index'], ['auth']);
$router->get('/reports/bookings', [\CHM\Reports\ReportController::class, 'bookings'], ['auth']);
$router->get('/reports/revenue-client', [\CHM\Reports\ReportController::class, 'revenueByClient'], ['auth']);
$router->get('/reports/revenue-payment', [\CHM\Reports\ReportController::class, 'revenueByPayment'], ['auth']);
$router->get('/reports/revenue-service', [\CHM\Reports\ReportController::class, 'revenueByService'], ['auth']);
$router->get('/reports/revenue-driver', [\CHM\Reports\ReportController::class, 'revenueByDriver'], ['auth']);
$router->get('/reports/revenue-vehicle', [\CHM\Reports\ReportController::class, 'revenueByVehicle'], ['auth']);
$router->get('/reports/commissions', [\CHM\Reports\ReportController::class, 'commissions'], ['auth']);
$router->get('/reports/driver-closing', [\CHM\Reports\ReportController::class, 'driverClosing'], ['auth']);
$router->get('/reports/cash-flow', [\CHM\Reports\ReportController::class, 'cashFlow'], ['auth', 'admin']);
$router->get('/reports/dre', [\CHM\Reports\ReportController::class, 'dre'], ['auth', 'admin']);

// Vouchers
$router->get('/vouchers', [\CHM\Vouchers\VoucherController::class, 'list'], ['auth']);

// Financeiro
$router->get('/finance', [\CHM\Finance\FinanceController::class, 'index'], ['auth', 'admin']);
$router->get('/finance/payable/create', [\CHM\Finance\FinanceController::class, 'createPayable'], ['auth', 'admin']);
$router->get('/finance/receivable/create', [\CHM\Finance\FinanceController::class, 'createReceivable'], ['auth', 'admin']);
$router->post('/finance/payable', [\CHM\Finance\FinanceController::class, 'storePayable'], ['auth', 'admin']);
$router->post('/finance/receivable', [\CHM\Finance\FinanceController::class, 'storeReceivable'], ['auth', 'admin']);
$router->post('/finance/payable/{id}/pay', [\CHM\Finance\FinanceController::class, 'payPayable'], ['auth', 'admin']);
$router->post('/finance/receivable/{id}/receive', [\CHM\Finance\FinanceController::class, 'receivePayment'], ['auth', 'admin']);
$router->get('/api/finance/summary', [\CHM\Finance\FinanceController::class, 'apiSummary'], ['auth']);

// WhatsApp
$router->get('/whatsapp', [\CHM\WhatsApp\WhatsAppController::class, 'index'], ['auth', 'admin']);
$router->post('/whatsapp/send', [\CHM\WhatsApp\WhatsAppController::class, 'sendTest'], ['auth', 'admin']);
$router->post('/whatsapp/template', [\CHM\WhatsApp\WhatsAppController::class, 'storeTemplate'], ['auth', 'admin']);
$router->post('/whatsapp/tag', [\CHM\WhatsApp\WhatsAppController::class, 'storeTag'], ['auth', 'admin']);
$router->get('/api/whatsapp/status', [\CHM\WhatsApp\WhatsAppController::class, 'apiStatus'], ['auth']);
$router->get('/webhook/whatsapp', [\CHM\WhatsApp\WhatsAppController::class, 'webhook']);
$router->post('/webhook/whatsapp', [\CHM\WhatsApp\WhatsAppController::class, 'webhook']);

// Backup
$router->get('/backup', [\CHM\Core\BackupController::class, 'index'], ['auth', 'admin']);
$router->post('/backup/create', [\CHM\Core\BackupController::class, 'create'], ['auth', 'admin']);
$router->post('/backup/clean', [\CHM\Core\BackupController::class, 'clean'], ['auth', 'admin']);
$router->get('/api/backup/status', [\CHM\Core\BackupController::class, 'apiStatus'], ['auth']);

// Notificações
$router->get('/notifications', [\CHM\Core\NotificationController::class, 'index'], ['auth']);
$router->get('/api/notifications', [\CHM\Core\NotificationController::class, 'apiAlerts'], ['auth']);
$router->get('/api/notifications/count', [\CHM\Core\NotificationController::class, 'apiCount'], ['auth']);

// Despacha a rota
$router->dispatch();
