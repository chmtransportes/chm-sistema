<?php
// 🔒 Bloqueio de acesso externo
if (php_sapi_name() !== 'cli' && $_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR']) {
    http_response_code(403);
    exit('Acesso negado.');
}

/**
 * CHM Sistema - Backup Automático
 * Cria cópia completa do sistema dentro da pasta /backup
 */

date_default_timezone_set('America/Sao_Paulo');

$baseDir   = __DIR__;
$backupDir = $baseDir . '/backup';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$timestamp = date('Y-m-d_H-i-s');
$destino   = $backupDir . '/backup_' . $timestamp;

mkdir($destino, 0755, true);

function copiarArquivos($origem, $destino) {
    $itens = scandir($origem);

    foreach ($itens as $item) {
        if ($item === '.' || $item === '..') continue;
        if ($item === 'backup') continue; // evita loop infinito

        $origemItem  = $origem . '/' . $item;
        $destinoItem = $destino . '/' . $item;

        if (is_dir($origemItem)) {
            mkdir($destinoItem, 0755, true);
            copiarArquivos($origemItem, $destinoItem);
        } else {
            copy($origemItem, $destinoItem);
        }
    }
}

copiarArquivos($baseDir, $destino);

echo "<h2>✅ Backup criado com sucesso</h2>";
echo "<p><strong>Pasta:</strong> backup_" . $timestamp . "</p>";
