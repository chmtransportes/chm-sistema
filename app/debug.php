<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG ===\n";

// Testar autoload
define('CHM_SISTEMA', true);
define('APP_PATH', __DIR__ . '/');

echo "APP_PATH: " . APP_PATH . "\n";

// Testar config
try {
    require_once APP_PATH . 'config/config.php';
    echo "Config: OK\n";
    echo "ENV: " . CHM_ENVIRONMENT . "\n";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "APP_URL: " . APP_URL . "\n";
} catch (Exception $e) {
    echo "Config Error: " . $e->getMessage() . "\n";
}
