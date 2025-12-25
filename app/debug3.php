<?php
// Simular o index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CHM_SISTEMA', true);

echo "=== INDEX SIMULATION ===\n\n";

require_once __DIR__ . '/config/config.php';

echo "ENV: " . CHM_ENVIRONMENT . "\n";
echo "APP_PATH: " . APP_PATH . "\n";
echo "APP_URL: " . APP_URL . "\n\n";

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'CHM\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $parts = explode('\\', $relativeClass);
    $namespaceMap = ['Core' => 'core', 'Auth' => 'auth', 'Users' => 'users'];
    if (isset($namespaceMap[$parts[0]])) $parts[0] = $namespaceMap[$parts[0]];
    $file = APP_PATH . strtolower(implode('/', $parts)) . '.php';
    if (!file_exists($file)) $file = APP_PATH . implode('/', $parts) . '.php';
    if (file_exists($file)) require_once $file;
});

use CHM\Core\Session;
use CHM\Core\Router;

Session::start();
echo "Session: OK\n";

$router = Router::getInstance();
echo "Router: OK\n\n";

// Registrar rota de teste
$router->get('/login', function() {
    echo "LOGIN ROUTE MATCHED!\n";
});

$router->get('/', function() {
    echo "ROOT ROUTE MATCHED!\n";
});

echo "Testando dispatch com URI simulada '/login'...\n";
$_SERVER['REQUEST_URI'] = '/login';
$router->dispatch();
