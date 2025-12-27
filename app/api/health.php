<?php
/**
 * CHM Sistema - Health Check API
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 27/12/2025 14:30
 * @version 2.3.4
 * 
 * Endpoint para validação pós-deploy e monitoramento
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Carrega configuração mínima
define('CHM_SISTEMA', true);
require_once dirname(__DIR__) . '/config/config.php';

$response = [
    'status' => 'ok',
    'version' => defined('CHM_VERSION') ? CHM_VERSION : 'unknown',
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => defined('CHM_ENVIRONMENT') ? CHM_ENVIRONMENT : 'unknown',
    'checks' => []
];

// Verifica banco de dados
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->query('SELECT 1');
    $response['checks']['database'] = 'ok';
    $response['database'] = 'connected';
} catch (PDOException $e) {
    $response['checks']['database'] = 'error';
    $response['database'] = 'disconnected';
    $response['status'] = 'degraded';
}

// Verifica diretórios essenciais
$dirs = [
    'logs' => defined('LOGS_PATH') ? LOGS_PATH : dirname(__DIR__) . '/logs/',
    'uploads' => defined('UPLOADS_PATH') ? UPLOADS_PATH : dirname(__DIR__) . '/uploads/',
    'config' => defined('CONFIG_PATH') ? CONFIG_PATH : dirname(__DIR__) . '/config/'
];

foreach ($dirs as $name => $path) {
    $response['checks'][$name] = is_dir($path) && is_writable($path) ? 'ok' : 'warning';
}

// Verifica sessão
$response['checks']['session'] = function_exists('session_start') ? 'ok' : 'error';

// Verifica extensões PHP necessárias
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    $response['checks']['php_' . $ext] = extension_loaded($ext) ? 'ok' : 'error';
    if (!extension_loaded($ext)) {
        $response['status'] = 'degraded';
    }
}

// Status HTTP baseado no resultado
$httpCode = $response['status'] === 'ok' ? 200 : ($response['status'] === 'degraded' ? 200 : 500);
http_response_code($httpCode);

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
