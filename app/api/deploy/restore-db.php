<?php
/**
 * CHM Sistema - Restore Database API (Deploy Hook)
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 27/12/2025 14:30
 * @version 2.3.4
 * 
 * Endpoint para restauração de banco via CI/CD
 * Restaura backup mais recente da data especificada
 */

header('Content-Type: application/json; charset=utf-8');

// Validação de segredo
$secret = $_GET['secret'] ?? '';
$expectedSecret = getenv('DEPLOY_SECRET') ?: 'chm-deploy-2025';

if (empty($secret) || !hash_equals($expectedSecret, $secret)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

// Carrega configuração
define('CHM_SISTEMA', true);
require_once dirname(dirname(__DIR__)) . '/config/config.php';

$targetDate = $_GET['date'] ?? date('Y-m-d');
$backupDir = defined('BACKUP_PATH') ? BACKUP_PATH : dirname(dirname(dirname(__DIR__))) . '/backup/';

// Busca backups da data especificada
$pattern = $backupDir . "backup-{$targetDate}*.sql";
$backups = glob($pattern);

// Se não encontrou, busca qualquer backup recente
if (empty($backups)) {
    $pattern = $backupDir . "backup-*.sql";
    $backups = glob($pattern);
    
    // Filtra backups mais recentes
    usort($backups, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
}

if (empty($backups)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Nenhum backup encontrado',
        'searched' => $pattern
    ]);
    exit;
}

// Usa o backup mais recente
$backupFile = $backups[0];

// Verifica se é legível
if (!is_readable($backupFile)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Backup não legível',
        'file' => basename($backupFile)
    ]);
    exit;
}

try {
    // Conecta ao banco
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Lê o backup
    $sql = file_get_contents($backupFile);
    
    // Executa em transação
    $pdo->beginTransaction();
    
    // Divide em statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $executed = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            $executed++;
        }
    }
    
    $pdo->commit();
    
    // Registra log
    $logFile = defined('LOGS_PATH') ? LOGS_PATH . 'restore.log' : dirname(dirname(__DIR__)) . '/logs/restore.log';
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " - Restore: " . basename($backupFile) . " ({$executed} statements)\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'backup_file' => basename($backupFile),
        'backup_date' => date('Y-m-d H:i:s', filemtime($backupFile)),
        'statements_executed' => $executed
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao restaurar banco',
        'details' => $e->getMessage()
    ]);
}
