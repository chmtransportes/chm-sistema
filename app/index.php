<?php
/**
 * CHM Sistema - Front Controller
 * Produção
 */

declare(strict_types=1);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Caminho raiz do projeto
define('BASE_PATH', __DIR__ . '/');

// Autoload simples
spl_autoload_register(function ($class) {
    $prefix = 'CHM\\';
    $baseDir = BASE_PATH . 'core/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Carrega configuração (UMA VEZ)
require_once BASE_PATH . 'config/config.php';

// Dispara o router
use CHM\Core\Router;

Router::getInstance()->dispatch();
