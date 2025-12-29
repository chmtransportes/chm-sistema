<?php
/**
 * CHM Sistema - Serviço de Backup Espelhado (Zero Trust / Fail-Safe)
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 29/12/2025 16:40
 * @version 2.0.0
 * 
 * Sistema de backup automatizado com espelhamento para múltiplos destinos
 * Garante recuperação mesmo em caso de falha total do provedor de hospedagem
 */

namespace CHM\Core;

class BackupMirrorService
{
    private string $backupDir;
    private string $sourceDir;
    private string $tempDir;
    private Database $db;
    private array $config;
    
    // Política de retenção (dias)
    private const RETENTION_DAILY = 7;
    private const RETENTION_WEEKLY = 30;
    private const RETENTION_MONTHLY = 90;
    
    // Diretórios a excluir do backup
    private const EXCLUDE_DIRS = [
        'backup',
        'backups', 
        'vendor',
        'node_modules',
        '.git',
        'cache',
        'tmp',
        'temp'
    ];
    
    // Arquivos a excluir
    private const EXCLUDE_FILES = [
        '*.log',
        '*.tmp',
        '.DS_Store',
        'Thumbs.db'
    ];

    public function __construct()
    {
        $this->backupDir = defined('BACKUP_PATH') ? BACKUP_PATH : dirname(dirname(__DIR__)) . '/backup/';
        $this->sourceDir = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(__DIR__)) . '/';
        $this->tempDir = sys_get_temp_dir() . '/chm_backup/';
        
        $this->config = [
            'ftp_host' => defined('FTP_HOST') ? FTP_HOST : EnvLoader::get('FTP_HOST', ''),
            'ftp_user' => defined('FTP_USER') ? FTP_USER : EnvLoader::get('FTP_USER', ''),
            'ftp_pass' => defined('FTP_PASS') ? FTP_PASS : EnvLoader::get('FTP_PASS', ''),
            'ftp_backup_dir' => '/backups/',
            'db_host' => DB_HOST,
            'db_name' => DB_NAME,
            'db_user' => DB_USER,
            'db_pass' => DB_PASS
        ];
        
        $this->ensureDirectories();
        
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            // Permite funcionar sem banco para restauração de emergência
        }
    }

    private function ensureDirectories(): void
    {
        $dirs = [
            $this->backupDir,
            $this->backupDir . 'daily/',
            $this->backupDir . 'weekly/',
            $this->backupDir . 'monthly/',
            $this->tempDir
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Executa backup completo com espelhamento
     */
    public function runFullBackup(bool $mirrorToFtp = true): array
    {
        $startTime = microtime(true);
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "chm_backup_{$timestamp}";
        
        $result = [
            'success' => false,
            'timestamp' => $timestamp,
            'backup_name' => $backupName,
            'local_path' => null,
            'ftp_path' => null,
            'code_size' => 0,
            'db_size' => 0,
            'total_size' => 0,
            'files_count' => 0,
            'duration' => 0,
            'errors' => []
        ];
        
        try {
            $this->log("Iniciando backup completo: {$backupName}");
            
            // 1. Cria diretório temporário para o backup
            $tempBackupDir = $this->tempDir . $backupName . '/';
            if (!mkdir($tempBackupDir, 0755, true)) {
                throw new \Exception("Falha ao criar diretório temporário");
            }
            
            // 2. Backup do código-fonte
            $this->log("Fazendo backup do código-fonte...");
            $codeResult = $this->backupSourceCode($tempBackupDir . 'code/');
            $result['code_size'] = $codeResult['size'];
            $result['files_count'] = $codeResult['count'];
            
            // 3. Backup do banco de dados
            $this->log("Fazendo backup do banco de dados...");
            $dbFile = $tempBackupDir . 'database.sql';
            $dbResult = $this->backupDatabase($dbFile);
            $result['db_size'] = $dbResult['size'];
            
            // 4. Cria arquivo de manifesto
            $manifest = $this->createManifest($backupName, $result);
            file_put_contents($tempBackupDir . 'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
            
            // 5. Compacta o backup
            $this->log("Compactando backup...");
            $archivePath = $this->backupDir . 'daily/' . $backupName . '.tar.gz';
            $this->createArchive($tempBackupDir, $archivePath);
            $result['total_size'] = filesize($archivePath);
            $result['local_path'] = $archivePath;
            
            // 6. Espelha para FTP externo (segundo local)
            if ($mirrorToFtp && !empty($this->config['ftp_host'])) {
                $this->log("Espelhando para FTP externo...");
                try {
                    $ftpResult = $this->mirrorToFtp($archivePath, $backupName . '.tar.gz');
                    $result['ftp_path'] = $ftpResult['path'];
                } catch (\Exception $e) {
                    $result['errors'][] = "FTP: " . $e->getMessage();
                    $this->log("AVISO: Falha no espelhamento FTP - " . $e->getMessage());
                }
            }
            
            // 7. Gerencia retenção (mantém backups antigos conforme política)
            $this->manageRetention();
            
            // 8. Limpa arquivos temporários
            $this->deleteDirectory($tempBackupDir);
            
            // 9. Registra no banco de dados
            $this->logBackupToDatabase($result);
            
            $result['success'] = true;
            $result['duration'] = round(microtime(true) - $startTime, 2);
            
            $this->log("Backup concluído com sucesso em {$result['duration']}s");
            
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            $this->log("ERRO no backup: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Backup apenas do banco de dados (mais frequente)
     */
    public function runDatabaseBackup(bool $mirrorToFtp = true): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "chm_db_{$timestamp}";
        
        $result = [
            'success' => false,
            'backup_name' => $backupName,
            'local_path' => null,
            'ftp_path' => null,
            'size' => 0,
            'errors' => []
        ];
        
        try {
            $this->log("Iniciando backup do banco de dados: {$backupName}");
            
            // Backup do banco
            $sqlFile = $this->tempDir . $backupName . '.sql';
            $dbResult = $this->backupDatabase($sqlFile);
            
            // Compacta
            $archivePath = $this->backupDir . 'daily/' . $backupName . '.sql.gz';
            $this->compressFile($sqlFile, $archivePath);
            @unlink($sqlFile);
            
            $result['size'] = filesize($archivePath);
            $result['local_path'] = $archivePath;
            
            // Espelha para FTP
            if ($mirrorToFtp && !empty($this->config['ftp_host'])) {
                try {
                    $ftpResult = $this->mirrorToFtp($archivePath, $backupName . '.sql.gz');
                    $result['ftp_path'] = $ftpResult['path'];
                } catch (\Exception $e) {
                    $result['errors'][] = "FTP: " . $e->getMessage();
                }
            }
            
            $result['success'] = true;
            $this->log("Backup do banco concluído: " . $this->formatBytes($result['size']));
            
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            $this->log("ERRO no backup do banco: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Backup do código-fonte
     */
    private function backupSourceCode(string $destination): array
    {
        $count = 0;
        $size = 0;
        
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $relativePath = str_replace($this->sourceDir, '', $item->getPathname());
            
            // Verifica exclusões de diretório
            if ($this->shouldExclude($relativePath)) {
                continue;
            }
            
            $destPath = $destination . $relativePath;
            
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                // Verifica exclusões de arquivo
                $filename = $item->getFilename();
                $exclude = false;
                foreach (self::EXCLUDE_FILES as $pattern) {
                    if (fnmatch($pattern, $filename)) {
                        $exclude = true;
                        break;
                    }
                }
                
                if (!$exclude) {
                    $dir = dirname($destPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    copy($item->getPathname(), $destPath);
                    $count++;
                    $size += $item->getSize();
                }
            }
        }
        
        return ['count' => $count, 'size' => $size];
    }

    /**
     * Backup do banco de dados MySQL
     */
    private function backupDatabase(string $destination): array
    {
        $output = "-- CHM Sistema Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Server: " . $this->config['db_host'] . "\n";
        $output .= "-- Database: " . $this->config['db_name'] . "\n";
        $output .= "-- Version: " . (defined('CHM_VERSION') ? CHM_VERSION : 'unknown') . "\n\n";
        $output .= "SET NAMES utf8mb4;\n";
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";
        
        $tables = $this->db->fetchAll("SHOW TABLES");
        
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            
            // DROP + CREATE
            $output .= "-- Table: {$tableName}\n";
            $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            
            $create = $this->db->fetchOne("SHOW CREATE TABLE `{$tableName}`");
            $output .= $create['Create Table'] . ";\n\n";
            
            // Dados
            $rows = $this->db->fetchAll("SELECT * FROM `{$tableName}`");
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                $output .= "-- Data for {$tableName}\n";
                
                foreach ($rows as $row) {
                    $values = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        return "'" . addslashes($val) . "'";
                    }, array_values($row));
                    
                    $output .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }
        
        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $output .= "-- End of backup\n";
        
        file_put_contents($destination, $output);
        
        return ['size' => filesize($destination)];
    }

    /**
     * Espelha backup para FTP externo
     */
    private function mirrorToFtp(string $localFile, string $remoteFilename): array
    {
        $conn = ftp_connect($this->config['ftp_host'], 21, 90);
        if (!$conn) {
            throw new \Exception("Não foi possível conectar ao FTP");
        }
        
        if (!ftp_login($conn, $this->config['ftp_user'], $this->config['ftp_pass'])) {
            ftp_close($conn);
            throw new \Exception("Falha no login FTP");
        }
        
        ftp_pasv($conn, true);
        
        // Cria diretório de backup se não existir
        $remoteDir = $this->config['ftp_backup_dir'];
        @ftp_mkdir($conn, $remoteDir);
        
        $remotePath = $remoteDir . $remoteFilename;
        
        if (!ftp_put($conn, $remotePath, $localFile, FTP_BINARY)) {
            ftp_close($conn);
            throw new \Exception("Falha ao enviar arquivo para FTP");
        }
        
        // Limpa backups antigos no FTP (mantém últimos 14)
        $this->cleanOldFtpBackups($conn, $remoteDir, 14);
        
        ftp_close($conn);
        
        return ['path' => $remotePath];
    }

    /**
     * Limpa backups antigos no FTP
     */
    private function cleanOldFtpBackups($conn, string $dir, int $keep): void
    {
        $files = ftp_nlist($conn, $dir);
        if (!$files) return;
        
        // Filtra apenas arquivos de backup
        $backups = array_filter($files, function($f) {
            return strpos(basename($f), 'chm_') === 0;
        });
        
        // Ordena por nome (mais recente primeiro devido ao timestamp)
        rsort($backups);
        
        // Remove os mais antigos
        $toDelete = array_slice($backups, $keep);
        foreach ($toDelete as $file) {
            @ftp_delete($conn, $file);
        }
    }

    /**
     * Gerencia política de retenção local
     */
    private function manageRetention(): void
    {
        $now = time();
        
        // Diário: mantém últimos 7 dias
        $this->cleanOldBackups($this->backupDir . 'daily/', self::RETENTION_DAILY);
        
        // Move backups semanais (domingo) para pasta semanal
        $this->promoteBackups('daily', 'weekly', 7);
        
        // Move backups mensais (dia 1) para pasta mensal
        $this->promoteBackups('weekly', 'monthly', 30);
        
        // Limpa backups semanais antigos
        $this->cleanOldBackups($this->backupDir . 'weekly/', self::RETENTION_WEEKLY);
        
        // Limpa backups mensais antigos
        $this->cleanOldBackups($this->backupDir . 'monthly/', self::RETENTION_MONTHLY);
    }

    /**
     * Promove backups entre categorias de retenção
     */
    private function promoteBackups(string $from, string $to, int $intervalDays): void
    {
        $fromDir = $this->backupDir . $from . '/';
        $toDir = $this->backupDir . $to . '/';
        
        $files = glob($fromDir . 'chm_backup_*.tar.gz');
        $interval = $intervalDays * 86400;
        
        foreach ($files as $file) {
            $age = time() - filemtime($file);
            
            // Se o arquivo tem a idade certa, copia para o próximo nível
            if ($age >= $interval && $age < ($interval + 86400)) {
                $destFile = $toDir . basename($file);
                if (!file_exists($destFile)) {
                    copy($file, $destFile);
                    $this->log("Backup promovido: {$from} -> {$to}: " . basename($file));
                }
            }
        }
    }

    /**
     * Remove backups mais antigos que o limite de dias
     */
    private function cleanOldBackups(string $dir, int $maxDays): int
    {
        $deleted = 0;
        $maxAge = $maxDays * 86400;
        
        $files = glob($dir . 'chm_*');
        
        foreach ($files as $file) {
            $age = time() - filemtime($file);
            
            if ($age > $maxAge) {
                if (is_dir($file)) {
                    $this->deleteDirectory($file);
                } else {
                    @unlink($file);
                }
                $deleted++;
                $this->log("Backup antigo removido: " . basename($file));
            }
        }
        
        return $deleted;
    }

    /**
     * Cria arquivo tar.gz
     */
    private function createArchive(string $sourceDir, string $archivePath): bool
    {
        $sourceDir = rtrim($sourceDir, '/');
        $parentDir = dirname($sourceDir);
        $dirName = basename($sourceDir);
        
        // Usa tar nativo se disponível
        if (function_exists('exec')) {
            $cmd = sprintf(
                'tar -czf %s -C %s %s 2>&1',
                escapeshellarg($archivePath),
                escapeshellarg($parentDir),
                escapeshellarg($dirName)
            );
            exec($cmd, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($archivePath)) {
                return true;
            }
        }
        
        // Fallback: usa PharData
        try {
            $tarFile = str_replace('.tar.gz', '.tar', $archivePath);
            $phar = new \PharData($tarFile);
            $phar->buildFromDirectory($sourceDir);
            $phar->compress(\Phar::GZ);
            @unlink($tarFile);
            return true;
        } catch (\Exception $e) {
            $this->log("Erro ao criar arquivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compacta arquivo com gzip
     */
    private function compressFile(string $source, string $destination): bool
    {
        $data = file_get_contents($source);
        $compressed = gzencode($data, 9);
        return file_put_contents($destination, $compressed) !== false;
    }

    /**
     * Cria manifesto do backup
     */
    private function createManifest(string $backupName, array $stats): array
    {
        return [
            'backup_name' => $backupName,
            'created_at' => date('Y-m-d H:i:s'),
            'created_at_utc' => gmdate('Y-m-d H:i:s'),
            'system_version' => defined('CHM_VERSION') ? CHM_VERSION : 'unknown',
            'php_version' => PHP_VERSION,
            'server' => php_uname('n'),
            'source_path' => $this->sourceDir,
            'database' => [
                'host' => $this->config['db_host'],
                'name' => $this->config['db_name']
            ],
            'statistics' => [
                'files_count' => $stats['files_count'] ?? 0,
                'code_size' => $stats['code_size'] ?? 0,
                'database_size' => $stats['db_size'] ?? 0
            ],
            'checksums' => [
                'algorithm' => 'sha256'
            ]
        ];
    }

    /**
     * Verifica se path deve ser excluído
     */
    private function shouldExclude(string $path): bool
    {
        foreach (self::EXCLUDE_DIRS as $exclude) {
            if (strpos($path, DIRECTORY_SEPARATOR . $exclude . DIRECTORY_SEPARATOR) !== false ||
                strpos($path, DIRECTORY_SEPARATOR . $exclude) === strlen($path) - strlen($exclude) - 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove diretório recursivamente
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) return false;
        
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }
        return @rmdir($dir);
    }

    /**
     * Registra backup no banco de dados
     */
    private function logBackupToDatabase(array $result): void
    {
        if (!isset($this->db)) return;
        
        try {
            $this->db->insert('backups', [
                'filename' => $result['backup_name'],
                'path' => $result['local_path'] ?? '',
                'size' => $result['total_size'] ?? $result['size'] ?? 0,
                'type' => 'auto_mirror',
                'status' => $result['success'] ? 'completed' : 'failed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Silenciosamente ignora se a tabela não existe ou tem estrutura diferente
            $this->log("Aviso: Log de backup não registrado no banco");
        }
    }

    /**
     * Registra log
     */
    private function log(string $message): void
    {
        $logFile = (defined('LOGS_PATH') ? LOGS_PATH : dirname(dirname(__DIR__)) . '/logs/') . 'backup.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[{$timestamp}] {$message}\n";
        
        @file_put_contents($logFile, $entry, FILE_APPEND);
        
        // Também exibe no console se rodando via CLI
        if (php_sapi_name() === 'cli') {
            echo $entry;
        }
    }

    /**
     * Formata bytes para exibição
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Lista todos os backups disponíveis
     */
    public function listAllBackups(): array
    {
        $backups = [];
        $categories = ['daily', 'weekly', 'monthly'];
        
        foreach ($categories as $category) {
            $dir = $this->backupDir . $category . '/';
            if (!is_dir($dir)) continue;
            
            $files = glob($dir . 'chm_*');
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'category' => $category,
                    'path' => $file,
                    'size' => filesize($file),
                    'size_formatted' => $this->formatBytes(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'age_days' => floor((time() - filemtime($file)) / 86400)
                ];
            }
        }
        
        // Ordena por data (mais recente primeiro)
        usort($backups, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        
        return $backups;
    }

    /**
     * Verifica integridade de um backup
     */
    public function verifyBackup(string $backupPath): array
    {
        $result = [
            'valid' => false,
            'readable' => false,
            'extractable' => false,
            'has_manifest' => false,
            'has_database' => false,
            'has_code' => false,
            'errors' => []
        ];
        
        if (!file_exists($backupPath)) {
            $result['errors'][] = "Arquivo não encontrado";
            return $result;
        }
        
        $result['readable'] = is_readable($backupPath);
        
        // Tenta extrair e verificar conteúdo
        try {
            $tempExtract = $this->tempDir . 'verify_' . time() . '/';
            mkdir($tempExtract, 0755, true);
            
            // Extrai
            exec(sprintf('tar -xzf %s -C %s 2>&1', 
                escapeshellarg($backupPath), 
                escapeshellarg($tempExtract)
            ), $output, $code);
            
            if ($code === 0) {
                $result['extractable'] = true;
                
                // Verifica conteúdo
                $dirs = glob($tempExtract . '*', GLOB_ONLYDIR);
                $extractedDir = $dirs[0] ?? $tempExtract;
                
                $result['has_manifest'] = file_exists($extractedDir . '/manifest.json');
                $result['has_database'] = file_exists($extractedDir . '/database.sql');
                $result['has_code'] = is_dir($extractedDir . '/code');
                
                $result['valid'] = $result['has_manifest'] && ($result['has_database'] || $result['has_code']);
            }
            
            // Limpa
            $this->deleteDirectory($tempExtract);
            
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }
        
        return $result;
    }
}
