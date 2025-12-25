<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CHM_SISTEMA', true);

echo "=== DEBUG ROUTER ===\n\n";

require_once __DIR__ . '/config/config.php';

echo "CHM_ENVIRONMENT: " . CHM_ENVIRONMENT . "\n";
echo "APP_PATH: " . APP_PATH . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n\n";

// Testar autoload
echo "Testando autoload...\n";

try {
    spl_autoload_register(function ($class) {
        $prefix = 'CHM\\';
        $len = strlen($prefix);
        
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $parts = explode('\\', $relativeClass);
        
        $namespaceMap = [
            'Core' => 'core',
            'Auth' => 'auth',
        ];

        if (isset($namespaceMap[$parts[0]])) {
            $parts[0] = $namespaceMap[$parts[0]];
        }

        $file = APP_PATH . strtolower(implode('/', $parts)) . '.php';
        
        echo "Tentando carregar: $file\n";
        
        if (!file_exists($file)) {
            $file = APP_PATH . implode('/', $parts) . '.php';
            echo "Tentativa 2: $file\n";
        }

        if (file_exists($file)) {
            require_once $file;
            echo "OK: $class carregado\n";
        } else {
            echo "ERRO: Arquivo não encontrado para $class\n";
        }
    });

    $router = \CHM\Core\Router::getInstance();
    echo "\nRouter: OK\n";
    
    $session = \CHM\Core\Session::class;
    echo "Session: OK\n";

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
