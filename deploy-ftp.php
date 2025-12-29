<?php
/**
 * CHM Sistema - Script de Deploy FTP
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 24/12/2025
 * 
 * Executa deploy automático via FTP para o servidor Napoleão
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║       CHM-SISTEMA - DEPLOY AUTOMÁTICO VIA FTP                ║\n";
echo "║       Servidor: Napoleão (186.209.113.108)                   ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Configurações FTP
$ftpConfig = [
    'host' => '186.209.113.108',
    'port' => 21,
    'user' => 'chm-sistema@chm-sistema.com.br',
    'pass' => 'Ca258790%Ca258790%',
    'root' => '/',
    'timeout' => 90
];

// Diretório local do projeto
$localDir = __DIR__ . '/app';
$rootDir = __DIR__;

// Arquivos raiz que devem ser enviados para /
$rootFiles = [
    'index.php',
    '.htaccess'
];

// Arquivos/pastas a ignorar no upload
$ignore = [
    '.git',
    '.gitignore',
    '.github',
    'node_modules',
    '.DS_Store',
    'Thumbs.db',
    '*.log',
    '*.md',
    'backups',
    'backup',
    'logs',
    'uploads',
    'scripts',
    'docs',
    'deploy-ftp.php',
    'install.php',
    'migrate-production.php',
    'backup-auto.php',
    'backup-automatico.sh',
    'cron-backup.php',
    'upload-fix.php',
    'sync_config.jsonc',
    'debug*.php',
    'test.php',
    'index-debug.php'
];

// Contadores
$stats = [
    'uploaded' => 0,
    'skipped' => 0,
    'errors' => 0,
    'dirs_created' => 0
];

/**
 * Conecta ao servidor FTP
 */
function ftpConnect($config) {
    echo "[1/5] Conectando ao servidor FTP...\n";
    
    $conn = ftp_connect($config['host'], $config['port'], $config['timeout']);
    if (!$conn) {
        throw new Exception("Não foi possível conectar ao servidor FTP: {$config['host']}");
    }
    
    if (!ftp_login($conn, $config['user'], $config['pass'])) {
        throw new Exception("Falha no login FTP. Verifique as credenciais.");
    }
    
    // Modo passivo (necessário para a maioria dos servidores)
    ftp_pasv($conn, true);
    
    echo "    ✓ Conectado com sucesso!\n\n";
    return $conn;
}

/**
 * Cria diretório remoto recursivamente
 */
function ftpMkdirRecursive($conn, $dir) {
    global $stats;
    
    $parts = explode('/', trim($dir, '/'));
    $path = '';
    
    foreach ($parts as $part) {
        $path .= '/' . $part;
        @ftp_mkdir($conn, $path);
    }
    
    $stats['dirs_created']++;
}

/**
 * Verifica se deve ignorar o arquivo/pasta
 */
function shouldIgnore($path, $ignoreList) {
    $basename = basename($path);
    
    foreach ($ignoreList as $pattern) {
        if ($basename === $pattern) return true;
        if (fnmatch($pattern, $basename)) return true;
        if (fnmatch($pattern, $path)) return true;
    }
    
    return false;
}

/**
 * Faz upload de um diretório recursivamente
 */
function uploadDirectory($conn, $localPath, $remotePath, $ignoreList) {
    global $stats;
    
    if (!is_dir($localPath)) {
        echo "    ⚠ Diretório não encontrado: {$localPath}\n";
        return;
    }
    
    $items = scandir($localPath);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $localFile = $localPath . '/' . $item;
        $remoteFile = $remotePath . '/' . $item;
        
        if (shouldIgnore($item, $ignoreList)) {
            $stats['skipped']++;
            continue;
        }
        
        if (is_dir($localFile)) {
            ftpMkdirRecursive($conn, $remoteFile);
            uploadDirectory($conn, $localFile, $remoteFile, $ignoreList);
        } else {
            // Upload do arquivo
            $mode = FTP_BINARY;
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            
            // Arquivos de texto em modo ASCII
            if (in_array($ext, ['php', 'html', 'css', 'js', 'json', 'txt', 'md', 'xml', 'svg', 'htaccess'])) {
                $mode = FTP_ASCII;
            }
            
            if (@ftp_put($conn, $remoteFile, $localFile, $mode)) {
                $stats['uploaded']++;
                echo "    ↑ " . str_replace($remotePath . '/', '', $remoteFile) . "\n";
            } else {
                $stats['errors']++;
                echo "    ✗ ERRO: " . $item . "\n";
            }
        }
    }
}

/**
 * Executa o deploy
 */
function executeDeploy() {
    global $ftpConfig, $localDir, $ignore, $stats;
    
    try {
        // 1. Conectar FTP
        $conn = ftpConnect($ftpConfig);
        
        // 2. Ir para diretório raiz
        echo "[2/5] Navegando para diretório raiz...\n";
        if (!@ftp_chdir($conn, $ftpConfig['root'])) {
            ftp_mkdir($conn, $ftpConfig['root']);
            ftp_chdir($conn, $ftpConfig['root']);
        }
        echo "    ✓ Diretório: {$ftpConfig['root']}\n\n";
        
        // 3. Upload dos arquivos raiz (index.php, .htaccess)
        echo "[3/5] Enviando arquivos raiz...\n";
        $rootDir = dirname($localDir);
        foreach (['index.php', '.htaccess'] as $rootFile) {
            $localFile = $rootDir . '/' . $rootFile;
            if (file_exists($localFile)) {
                if (@ftp_put($conn, '/' . $rootFile, $localFile, FTP_ASCII)) {
                    echo "    ✓ {$rootFile} enviado\n";
                    $stats['uploaded']++;
                } else {
                    echo "    ✗ Erro ao enviar {$rootFile}\n";
                    $stats['errors']++;
                }
            }
        }
        echo "\n";
        
        // 4. Fazer upload da pasta app
        echo "[4/5] Enviando pasta app...\n";
        echo "    Origem: {$localDir}\n";
        echo "    Destino: /app/\n\n";
        
        ftpMkdirRecursive($conn, '/app');
        uploadDirectory($conn, $localDir, '/app', $ignore);
        
        echo "\n";
        
        // 5. Finalizar
        echo "[5/5] Finalizando deploy...\n";
        ftp_close($conn);
        
        echo "\n╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    DEPLOY CONCLUÍDO!                         ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  Arquivos enviados:    " . str_pad($stats['uploaded'], 6) . "                             ║\n";
        echo "║  Arquivos ignorados:   " . str_pad($stats['skipped'], 6) . "                             ║\n";
        echo "║  Diretórios criados:   " . str_pad($stats['dirs_created'], 6) . "                             ║\n";
        echo "║  Erros:                " . str_pad($stats['errors'], 6) . "                             ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  URL: https://chm-sistema.com.br                             ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "\n❌ ERRO NO DEPLOY: " . $e->getMessage() . "\n";
        return false;
    }
}

// Executar deploy
$success = executeDeploy();
exit($success ? 0 : 1);
