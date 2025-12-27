<?php
/**
 * CHM Sistema - Clear Cache API (Deploy Hook)
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 27/12/2025 14:30
 * @version 2.3.4
 * 
 * Endpoint para limpeza de cache via CI/CD
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

$results = [];

// Limpar OPcache se disponível
if (function_exists('opcache_reset')) {
    $results['opcache'] = @opcache_reset() ? 'cleared' : 'failed';
} else {
    $results['opcache'] = 'not_available';
}

// Limpar arquivos de cache temporários
$cacheDir = dirname(dirname(__DIR__)) . '/cache/';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file) && !str_ends_with($file, '.gitkeep')) {
            @unlink($file);
            $count++;
        }
    }
    $results['file_cache'] = "cleared_{$count}_files";
} else {
    $results['file_cache'] = 'no_cache_dir';
}

// Limpar sessões antigas (opcional)
$sessionPath = session_save_path();
if (!empty($sessionPath) && is_dir($sessionPath)) {
    $results['sessions'] = 'preserved';
}

// Forçar reload de configurações
clearstatcache(true);
$results['stat_cache'] = 'cleared';

echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results
], JSON_PRETTY_PRINT);
