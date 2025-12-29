<?php
/**
 * CHM Sistema - Script de Backup Automático para CRON
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 29/12/2025 16:50
 * @version 2.0.0
 * 
 * Configuração CRON recomendada (copie e ajuste os caminhos):
 * 
 * Backup completo diário às 03:00:
 * 0 3 [asterisk] [asterisk] [asterisk] php /path/to/scripts/backup-cron.php full
 * 
 * Backup do banco a cada 6 horas:
 * 0 0,6,12,18 [asterisk] [asterisk] [asterisk] php /path/to/scripts/backup-cron.php database
 * 
 * Verificação semanal (domingo às 04:00):
 * 0 4 [asterisk] [asterisk] 0 php /path/to/scripts/backup-cron.php verify
 * 
 * Substitua [asterisk] por * no crontab
 */

// Garante execução apenas via CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Este script só pode ser executado via linha de comando.\n");
}

// Define constante de sistema
define('CHM_SISTEMA', true);
define('CHM_CRON', true);

// Carrega configurações
$configFile = dirname(__DIR__) . '/app/config/config.php';
if (!file_exists($configFile)) {
    die("Erro: Arquivo de configuração não encontrado.\n");
}
require_once $configFile;

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'CHM\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    
    $relativeClass = substr($class, strlen($prefix));
    $parts = explode('\\', $relativeClass);
    
    $namespaceMap = [
        'Core' => 'core',
        'Auth' => 'auth',
        'Bookings' => 'bookings',
        'Clients' => 'clients',
        'Drivers' => 'drivers',
        'Finance' => 'finance',
        'Vehicles' => 'vehicles'
    ];
    
    if (isset($namespaceMap[$parts[0]])) {
        $parts[0] = $namespaceMap[$parts[0]];
    }
    
    $file = APP_PATH . strtolower(implode('/', $parts)) . '.php';
    if (!file_exists($file)) {
        $file = APP_PATH . implode('/', $parts) . '.php';
    }
    if (file_exists($file)) require_once $file;
});

use CHM\Core\BackupMirrorService;
use CHM\Core\RestoreService;

// Obtém o tipo de backup a executar
$type = $argv[1] ?? 'full';
$mirrorToFtp = !in_array('--no-ftp', $argv);

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║       CHM-SISTEMA - BACKUP AUTOMÁTICO (CRON)                 ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  Data/Hora: " . str_pad(date('d/m/Y H:i:s'), 47) . " ║\n";
echo "║  Tipo: " . str_pad(strtoupper($type), 52) . " ║\n";
echo "║  FTP Mirror: " . str_pad($mirrorToFtp ? 'SIM' : 'NÃO', 46) . " ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

try {
    $backup = new BackupMirrorService();
    
    switch ($type) {
        case 'full':
            echo "[*] Iniciando backup completo (código + banco)...\n\n";
            $result = $backup->runFullBackup($mirrorToFtp);
            break;
            
        case 'database':
        case 'db':
            echo "[*] Iniciando backup do banco de dados...\n\n";
            $result = $backup->runDatabaseBackup($mirrorToFtp);
            break;
            
        case 'verify':
            echo "[*] Verificando integridade dos backups...\n\n";
            $backups = $backup->listAllBackups();
            $verified = 0;
            $failed = 0;
            
            foreach (array_slice($backups, 0, 5) as $b) {
                echo "  Verificando: {$b['name']}... ";
                $check = $backup->verifyBackup($b['path']);
                if ($check['valid']) {
                    echo "OK\n";
                    $verified++;
                } else {
                    echo "FALHOU\n";
                    $failed++;
                }
            }
            
            $result = [
                'success' => $failed === 0,
                'verified' => $verified,
                'failed' => $failed
            ];
            break;
            
        case 'list':
            echo "[*] Listando backups disponíveis...\n\n";
            $backups = $backup->listAllBackups();
            
            if (empty($backups)) {
                echo "  Nenhum backup encontrado.\n";
            } else {
                printf("  %-40s %-10s %-10s %s\n", "NOME", "CATEGORIA", "TAMANHO", "DATA");
                printf("  %s\n", str_repeat('-', 80));
                
                foreach ($backups as $b) {
                    printf("  %-40s %-10s %-10s %s\n",
                        substr($b['name'], 0, 38),
                        $b['category'],
                        $b['size_formatted'],
                        $b['created_at']
                    );
                }
            }
            
            $result = ['success' => true, 'count' => count($backups)];
            break;
            
        case 'clean':
            echo "[*] Limpando backups antigos...\n\n";
            // A limpeza é feita automaticamente pelo runFullBackup
            $result = $backup->runFullBackup(false);
            break;
            
        default:
            echo "Uso: php backup-cron.php [tipo] [opções]\n\n";
            echo "Tipos disponíveis:\n";
            echo "  full      - Backup completo (código + banco)\n";
            echo "  database  - Apenas banco de dados\n";
            echo "  verify    - Verifica integridade dos backups\n";
            echo "  list      - Lista backups disponíveis\n";
            echo "  clean     - Limpa backups antigos\n\n";
            echo "Opções:\n";
            echo "  --no-ftp  - Não espelhar para FTP externo\n\n";
            exit(1);
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    
    if ($result['success']) {
        echo "✓ BACKUP CONCLUÍDO COM SUCESSO\n";
        
        if (isset($result['local_path'])) {
            echo "  Local: " . basename($result['local_path']) . "\n";
        }
        if (isset($result['ftp_path'])) {
            echo "  FTP: {$result['ftp_path']}\n";
        }
        if (isset($result['total_size'])) {
            echo "  Tamanho: " . formatBytes($result['total_size']) . "\n";
        }
        if (isset($result['duration'])) {
            echo "  Duração: {$result['duration']}s\n";
        }
        
        exit(0);
    } else {
        echo "✗ BACKUP FALHOU\n";
        
        if (!empty($result['errors'])) {
            echo "  Erros:\n";
            foreach ($result['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
        
        exit(1);
    }
    
} catch (Exception $e) {
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✗ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    exit(1);
}

function formatBytes(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
}
