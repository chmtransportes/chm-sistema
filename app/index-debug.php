<?php
// Debug version of index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CHM_SISTEMA', true);
require_once __DIR__ . '/config/config.php';

// Log
file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " URI: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'CHM\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $parts = explode('\\', $relativeClass);
    $namespaceMap = [
        'Core' => 'core', 'Auth' => 'auth', 'Users' => 'users',
        'Clients' => 'clients', 'Drivers' => 'drivers', 'Vehicles' => 'vehicles',
        'Bookings' => 'bookings', 'Calendar' => 'calendar', 'Finance' => 'finance',
        'Reports' => 'reports', 'Vouchers' => 'vouchers', 'WhatsApp' => 'whatsapp'
    ];
    if (isset($namespaceMap[$parts[0]])) $parts[0] = $namespaceMap[$parts[0]];
    $file = APP_PATH . strtolower(implode('/', $parts)) . '.php';
    if (!file_exists($file)) $file = APP_PATH . implode('/', $parts) . '.php';
    if (file_exists($file)) require_once $file;
});

use CHM\Core\Session;
use CHM\Core\Router;

Session::start();

$router = Router::getInstance();

// Rotas
$router->get('/login', [\CHM\Auth\AuthController::class, 'showLogin']);
$router->post('/login', [\CHM\Auth\AuthController::class, 'login']);
$router->get('/logout', [\CHM\Auth\AuthController::class, 'logout']);
$router->get('/', function() { Router::redirect(APP_URL . 'login'); });
$router->get('/dashboard', [\CHM\Users\DashboardController::class, 'index']);

$router->dispatch();
