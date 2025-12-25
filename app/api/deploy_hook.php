<?php
/**
 * CHM Sistema - Deploy Hook Endpoint
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 24/12/2025
 * 
 * Endpoint para execução remota de deploy
 * Ações: migrate | seed | status | runall
 */

header('Content-Type: application/json');
header('X-Robots-Tag: noindex, nofollow');

// Carregar configurações
require_once __DIR__ . '/../config/env_loader.php';
EnvLoader::load();

// Verificar secret
$secret = $_GET['secret'] ?? $_POST['secret'] ?? $_SERVER['HTTP_X_DEPLOY_SECRET'] ?? '';
$expectedSecret = EnvLoader::get('DEPLOY_SECRET', '');

if (empty($expectedSecret) || $secret !== $expectedSecret) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Obter ação
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

// Log de deploy (sem senhas)
function logDeploy(string $action, string $status, string $message = ''): void
{
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/deploy-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $logLine = "[{$timestamp}] [{$ip}] [{$action}] [{$status}] {$message}\n";
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

$result = ['success' => true, 'action' => $action, 'timestamp' => date('c')];

try {
    switch ($action) {
        case 'migrate':
            ob_start();
            include __DIR__ . '/../database/migrate.php';
            $output = ob_get_clean();
            $result['migrate'] = json_decode($output, true) ?: $output;
            logDeploy('migrate', 'OK');
            break;

        case 'seed':
            ob_start();
            include __DIR__ . '/../database/seed.php';
            $output = ob_get_clean();
            $result['seed'] = json_decode($output, true) ?: $output;
            logDeploy('seed', 'OK');
            break;

        case 'runall':
            // Migrate
            ob_start();
            include __DIR__ . '/../database/migrate.php';
            $migrateOutput = ob_get_clean();
            $result['migrate'] = json_decode($migrateOutput, true) ?: $migrateOutput;

            // Seed
            ob_start();
            include __DIR__ . '/../database/seed.php';
            $seedOutput = ob_get_clean();
            $result['seed'] = json_decode($seedOutput, true) ?: $seedOutput;

            logDeploy('runall', 'OK');
            break;

        case 'status':
            $result['status'] = [
                'php_version' => PHP_VERSION,
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
                'env' => EnvLoader::get('APP_ENV', 'unknown'),
                'db_host' => EnvLoader::get('DB_HOST', 'unknown'),
                'db_name' => EnvLoader::get('DB_NAME', 'unknown'),
            ];
            logDeploy('status', 'OK');
            break;

        case 'health':
            // Verificar conexão com banco
            try {
                $host = EnvLoader::get('DB_HOST');
                $port = EnvLoader::get('DB_PORT', '3306');
                $name = EnvLoader::get('DB_NAME');
                $user = EnvLoader::get('DB_USER');
                $pass = EnvLoader::get('DB_PASS');

                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass);
                $pdo->query("SELECT 1");
                
                $result['health'] = [
                    'database' => 'OK',
                    'timestamp' => date('c')
                ];
            } catch (Exception $e) {
                $result['health'] = [
                    'database' => 'FAIL',
                    'error' => $e->getMessage()
                ];
                $result['success'] = false;
            }
            logDeploy('health', $result['success'] ? 'OK' : 'FAIL');
            break;

        default:
            $result['success'] = false;
            $result['error'] = 'Ação inválida. Use: migrate, seed, runall, status, health';
            logDeploy($action, 'INVALID');
    }
} catch (Exception $e) {
    $result['success'] = false;
    $result['error'] = $e->getMessage();
    logDeploy($action, 'ERROR', $e->getMessage());
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
