<?php
/**
 * CHM Sistema - CRON de Backup Automático
 * Execute via crontab: */10 * * * * php /path/to/cron-backup.php
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 20/01/2025
 * @version 1.1.0
 */

// Define constante para permitir acesso
define('CHM_SISTEMA', true);

// Carrega configurações
require_once __DIR__ . '/app/config/config.php';

// Autoloader simples
spl_autoload_register(function ($class) {
    $prefix = 'CHM\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    
    $relativeClass = substr($class, strlen($prefix));
    $parts = explode('\\', $relativeClass);
    
    $namespaceMap = ['Core' => 'core'];
    if (isset($namespaceMap[$parts[0]])) {
        $parts[0] = $namespaceMap[$parts[0]];
    }
    
    $file = APP_PATH . strtolower(implode('/', $parts)) . '.php';
    if (!file_exists($file)) {
        $file = APP_PATH . implode('/', $parts) . '.php';
    }
    if (file_exists($file)) require_once $file;
});

use CHM\Core\BackupService;
use CHM\Core\Helpers;

try {
    $backup = new BackupService();
    $result = $backup->runAutoBackup();
    
    if ($result) {
        if ($result['success']) {
            $msg = "Backup automático realizado: {$result['name']}";
            Helpers::logAction($msg, 'backup');
            echo "[" . date('Y-m-d H:i:s') . "] $msg\n";
        } else {
            $msg = "Erro no backup: " . ($result['error'] ?? 'Desconhecido');
            Helpers::logAction($msg, 'backup');
            echo "[" . date('Y-m-d H:i:s') . "] $msg\n";
        }
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] Backup não necessário ainda\n";
    }
} catch (Exception $e) {
    $msg = "Exceção no backup: " . $e->getMessage();
    echo "[" . date('Y-m-d H:i:s') . "] $msg\n";
    
    // Tenta logar se possível
    if (class_exists('CHM\Core\Helpers')) {
        Helpers::logAction($msg, 'backup');
    }
}
