<?php
/**
 * CHM Sistema - Script de Restauração via CLI
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 29/12/2025 16:55
 * @version 2.0.0
 * 
 * Permite restauração rápida do sistema via linha de comando
 * 
 * Uso:
 *   php restore-cli.php list                    - Lista backups disponíveis
 *   php restore-cli.php full <arquivo>          - Restauração completa
 *   php restore-cli.php code <arquivo>          - Restaura apenas código
 *   php restore-cli.php database <arquivo>      - Restaura apenas banco
 *   php restore-cli.php date <YYYY-MM-DD>       - Restaura backup mais próximo da data
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Este script só pode ser executado via linha de comando.\n");
}

define('CHM_SISTEMA', true);
define('CHM_CLI', true);

$configFile = dirname(__DIR__) . '/app/config/config.php';
if (!file_exists($configFile)) {
    die("Erro: Arquivo de configuração não encontrado.\n");
}
require_once $configFile;

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

use CHM\Core\RestoreService;

$action = $argv[1] ?? 'help';
$target = $argv[2] ?? null;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║       CHM-SISTEMA - RESTAURAÇÃO / ROLLBACK                   ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  Data/Hora: " . str_pad(date('d/m/Y H:i:s'), 47) . " ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

try {
    $restore = new RestoreService();
    
    switch ($action) {
        case 'list':
            echo "[*] Backups disponíveis para restauração:\n\n";
            $backups = $restore->listAvailableBackups();
            
            if (empty($backups)) {
                echo "  Nenhum backup encontrado.\n";
            } else {
                printf("  %-3s %-35s %-8s %-10s %s\n", "#", "NOME", "TIPO", "TAMANHO", "DATA");
                printf("  %s\n", str_repeat('-', 80));
                
                foreach ($backups as $i => $b) {
                    printf("  %-3d %-35s %-8s %-10s %s\n",
                        $i + 1,
                        substr($b['name'], 0, 33),
                        $b['type'],
                        $b['size_formatted'],
                        $b['date']
                    );
                }
                
                echo "\n  Para restaurar, use:\n";
                echo "  php restore-cli.php full <caminho_do_arquivo>\n";
            }
            break;
            
        case 'full':
            if (!$target) {
                die("Erro: Especifique o arquivo de backup.\n");
            }
            
            $backupFile = resolveBackupPath($target);
            if (!file_exists($backupFile)) {
                die("Erro: Arquivo não encontrado: {$backupFile}\n");
            }
            
            echo "⚠️  ATENÇÃO: Isso irá sobrescrever o sistema atual!\n";
            echo "   Arquivo: " . basename($backupFile) . "\n\n";
            echo "   Um backup de segurança será criado antes da restauração.\n\n";
            
            if (!confirmAction("Deseja continuar?")) {
                echo "Operação cancelada.\n";
                exit(0);
            }
            
            echo "\n[*] Iniciando restauração completa...\n\n";
            $result = $restore->restoreFull($backupFile);
            
            showResult($result);
            break;
            
        case 'code':
            if (!$target) {
                die("Erro: Especifique o arquivo de backup.\n");
            }
            
            $backupFile = resolveBackupPath($target);
            if (!file_exists($backupFile)) {
                die("Erro: Arquivo não encontrado: {$backupFile}\n");
            }
            
            echo "⚠️  ATENÇÃO: Isso irá sobrescrever os arquivos do sistema!\n\n";
            
            if (!confirmAction("Deseja continuar?")) {
                echo "Operação cancelada.\n";
                exit(0);
            }
            
            echo "\n[*] Restaurando código-fonte...\n\n";
            $result = $restore->restoreCodeOnly($backupFile);
            
            showResult($result);
            break;
            
        case 'database':
        case 'db':
            if (!$target) {
                die("Erro: Especifique o arquivo de backup.\n");
            }
            
            $backupFile = resolveBackupPath($target);
            if (!file_exists($backupFile)) {
                die("Erro: Arquivo não encontrado: {$backupFile}\n");
            }
            
            echo "⚠️  ATENÇÃO: Isso irá sobrescrever o banco de dados!\n\n";
            
            if (!confirmAction("Deseja continuar?")) {
                echo "Operação cancelada.\n";
                exit(0);
            }
            
            echo "\n[*] Restaurando banco de dados...\n\n";
            $result = $restore->restoreDatabaseOnly($backupFile);
            
            showResult($result);
            break;
            
        case 'date':
            if (!$target) {
                die("Erro: Especifique a data (YYYY-MM-DD).\n");
            }
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $target)) {
                die("Erro: Formato de data inválido. Use YYYY-MM-DD.\n");
            }
            
            echo "⚠️  ATENÇÃO: Isso irá restaurar o sistema para a data {$target}!\n\n";
            
            if (!confirmAction("Deseja continuar?")) {
                echo "Operação cancelada.\n";
                exit(0);
            }
            
            echo "\n[*] Buscando e restaurando backup da data {$target}...\n\n";
            $result = $restore->restoreFromDate($target);
            
            showResult($result);
            break;
            
        case 'help':
        default:
            echo "Uso: php restore-cli.php <ação> [opções]\n\n";
            echo "Ações disponíveis:\n";
            echo "  list              Lista backups disponíveis\n";
            echo "  full <arquivo>    Restauração completa (código + banco)\n";
            echo "  code <arquivo>    Restaura apenas código-fonte\n";
            echo "  database <arq>    Restaura apenas banco de dados\n";
            echo "  date <YYYY-MM-DD> Restaura backup mais próximo da data\n\n";
            echo "Exemplos:\n";
            echo "  php restore-cli.php list\n";
            echo "  php restore-cli.php full /backup/daily/chm_backup_2025-12-29.tar.gz\n";
            echo "  php restore-cli.php date 2025-12-25\n\n";
            break;
    }
    
} catch (Exception $e) {
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✗ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

function resolveBackupPath(string $target): string {
    if (file_exists($target)) {
        return $target;
    }
    
    $backupDir = defined('BACKUP_PATH') ? BACKUP_PATH : dirname(__DIR__) . '/backup/';
    $categories = ['daily/', 'weekly/', 'monthly/', ''];
    
    foreach ($categories as $cat) {
        $path = $backupDir . $cat . $target;
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return $target;
}

function confirmAction(string $message): bool {
    echo "{$message} (s/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return strtolower(trim($line)) === 's';
}

function showResult(array $result): void {
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    
    if ($result['success']) {
        echo "✓ RESTAURAÇÃO CONCLUÍDA COM SUCESSO\n";
        
        if (!empty($result['pre_restore_backup'])) {
            echo "  Backup de segurança: " . basename($result['pre_restore_backup']) . "\n";
        }
        if (isset($result['code_restored'])) {
            echo "  Código: " . ($result['code_restored'] ? 'Restaurado' : 'Não restaurado') . "\n";
        }
        if (isset($result['database_restored'])) {
            echo "  Banco: " . ($result['database_restored'] ? 'Restaurado' : 'Não restaurado') . "\n";
        }
        
        if (!empty($result['warnings'])) {
            echo "\n  Avisos:\n";
            foreach ($result['warnings'] as $w) {
                echo "  ⚠️ {$w}\n";
            }
        }
        
        echo "\n  Recomendação: Limpe o cache do navegador e teste o sistema.\n";
        
        exit(0);
    } else {
        echo "✗ RESTAURAÇÃO FALHOU\n";
        
        if (!empty($result['errors'])) {
            echo "  Erros:\n";
            foreach ($result['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
        
        exit(1);
    }
}
