<?php
/**
 * CHM Sistema - Serviço de Restauração (Rollback)
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 29/12/2025 16:45
 * @version 2.0.0
 * 
 * Permite restauração rápida do sistema a partir de qualquer backup
 * Suporta restauração completa (código + banco) ou parcial
 */

namespace CHM\Core;

class RestoreService
{
    private string $backupDir;
    private string $targetDir;
    private string $tempDir;
    private array $dbConfig;
    
    public function __construct()
    {
        $this->backupDir = defined('BACKUP_PATH') ? BACKUP_PATH : dirname(dirname(__DIR__)) . '/backup/';
        $this->targetDir = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(__DIR__)) . '/';
        $this->tempDir = sys_get_temp_dir() . '/chm_restore/';
        
        $this->dbConfig = [
            'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
            'name' => defined('DB_NAME') ? DB_NAME : '',
            'user' => defined('DB_USER') ? DB_USER : '',
            'pass' => defined('DB_PASS') ? DB_PASS : ''
        ];
        
        if (!is_dir($this->tempDir)) {
            @mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Restauração completa (código + banco de dados)
     */
    public function restoreFull(string $backupFile): array
    {
        $result = [
            'success' => false,
            'backup_used' => basename($backupFile),
            'code_restored' => false,
            'database_restored' => false,
            'pre_restore_backup' => null,
            'errors' => [],
            'warnings' => []
        ];
        
        try {
            $this->log("=== INICIANDO RESTAURAÇÃO COMPLETA ===");
            $this->log("Backup: " . basename($backupFile));
            
            // 1. Verifica se backup existe
            if (!file_exists($backupFile)) {
                throw new \Exception("Arquivo de backup não encontrado: {$backupFile}");
            }
            
            // 2. Cria backup de segurança antes de restaurar
            $this->log("Criando backup de segurança pré-restauração...");
            $preBackup = $this->createPreRestoreBackup();
            $result['pre_restore_backup'] = $preBackup;
            
            // 3. Extrai backup
            $this->log("Extraindo backup...");
            $extractDir = $this->extractBackup($backupFile);
            
            // 4. Valida conteúdo
            $this->log("Validando conteúdo do backup...");
            $manifest = $this->loadManifest($extractDir);
            
            // 5. Restaura código
            $codeDir = $extractDir . '/code';
            if (is_dir($codeDir)) {
                $this->log("Restaurando código-fonte...");
                $this->restoreCode($codeDir);
                $result['code_restored'] = true;
            } else {
                $result['warnings'][] = "Backup não contém código-fonte";
            }
            
            // 6. Restaura banco de dados
            $dbFile = $extractDir . '/database.sql';
            if (file_exists($dbFile)) {
                $this->log("Restaurando banco de dados...");
                $this->restoreDatabase($dbFile);
                $result['database_restored'] = true;
            } else {
                $result['warnings'][] = "Backup não contém dump do banco de dados";
            }
            
            // 7. Limpa arquivos temporários
            $this->deleteDirectory($extractDir);
            
            // 8. Limpa cache
            $this->clearCache();
            
            $result['success'] = true;
            $this->log("=== RESTAURAÇÃO CONCLUÍDA COM SUCESSO ===");
            
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            $this->log("ERRO: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Restaura apenas o código-fonte
     */
    public function restoreCodeOnly(string $backupFile): array
    {
        $result = [
            'success' => false,
            'errors' => []
        ];
        
        try {
            $this->log("Restaurando apenas código-fonte...");
            
            // Cria backup de segurança
            $this->createPreRestoreBackup();
            
            // Extrai
            $extractDir = $this->extractBackup($backupFile);
            
            $codeDir = $extractDir . '/code';
            if (!is_dir($codeDir)) {
                throw new \Exception("Backup não contém código-fonte");
            }
            
            $this->restoreCode($codeDir);
            $this->deleteDirectory($extractDir);
            $this->clearCache();
            
            $result['success'] = true;
            $this->log("Código restaurado com sucesso");
            
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            $this->log("ERRO: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Restaura apenas o banco de dados
     */
    public function restoreDatabaseOnly(string $backupFile): array
    {
        $result = [
            'success' => false,
            'errors' => []
        ];
        
        try {
            $this->log("Restaurando apenas banco de dados...");
            
            // Determina se é .sql.gz ou backup completo
            if (substr($backupFile, -7) === '.sql.gz') {
                // Arquivo SQL comprimido direto
                $sqlContent = gzdecode(file_get_contents($backupFile));
                $tempSql = $this->tempDir . 'restore_' . time() . '.sql';
                file_put_contents($tempSql, $sqlContent);
                $this->restoreDatabase($tempSql);
                @unlink($tempSql);
            } else {
                // Backup completo, precisa extrair
                $extractDir = $this->extractBackup($backupFile);
                $dbFile = $extractDir . '/database.sql';
                
                if (!file_exists($dbFile)) {
                    throw new \Exception("Backup não contém dump do banco de dados");
                }
                
                $this->restoreDatabase($dbFile);
                $this->deleteDirectory($extractDir);
            }
            
            $result['success'] = true;
            $this->log("Banco de dados restaurado com sucesso");
            
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            $this->log("ERRO: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Restaura a partir de uma data específica
     */
    public function restoreFromDate(string $date): array
    {
        $this->log("Buscando backup mais próximo da data: {$date}");
        
        // Busca backup mais próximo da data especificada
        $backup = $this->findBackupByDate($date);
        
        if (!$backup) {
            return [
                'success' => false,
                'errors' => ["Nenhum backup encontrado para a data: {$date}"]
            ];
        }
        
        $this->log("Backup encontrado: " . $backup['name']);
        return $this->restoreFull($backup['path']);
    }

    /**
     * Lista backups disponíveis para restauração
     */
    public function listAvailableBackups(): array
    {
        $backups = [];
        $categories = ['daily', 'weekly', 'monthly', ''];
        
        foreach ($categories as $category) {
            $dir = $this->backupDir . ($category ? $category . '/' : '');
            if (!is_dir($dir)) continue;
            
            $files = glob($dir . 'chm_*.{tar.gz,sql.gz}', GLOB_BRACE);
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'category' => $category ?: 'root',
                    'size' => filesize($file),
                    'size_formatted' => $this->formatBytes(filesize($file)),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'type' => strpos($file, 'backup') !== false ? 'full' : 'database'
                ];
            }
        }
        
        usort($backups, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
        
        return $backups;
    }

    /**
     * Cria backup de segurança antes de restaurar
     */
    private function createPreRestoreBackup(): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "pre_restore_{$timestamp}";
        $backupPath = $this->backupDir . $backupName . '.tar.gz';
        
        // Cria backup rápido do estado atual
        $tempDir = $this->tempDir . $backupName . '/';
        mkdir($tempDir, 0755, true);
        
        // Backup do banco atual
        $this->dumpCurrentDatabase($tempDir . 'database.sql');
        
        // Cria arquivo compactado
        exec(sprintf('tar -czf %s -C %s . 2>&1',
            escapeshellarg($backupPath),
            escapeshellarg($tempDir)
        ));
        
        $this->deleteDirectory($tempDir);
        
        $this->log("Backup de segurança criado: {$backupName}");
        
        return $backupPath;
    }

    /**
     * Extrai backup para diretório temporário
     */
    private function extractBackup(string $backupFile): string
    {
        $extractDir = $this->tempDir . 'extract_' . time() . '/';
        mkdir($extractDir, 0755, true);
        
        $ext = pathinfo($backupFile, PATHINFO_EXTENSION);
        
        if ($ext === 'gz' && strpos($backupFile, '.tar.gz') !== false) {
            // Arquivo tar.gz
            exec(sprintf('tar -xzf %s -C %s 2>&1',
                escapeshellarg($backupFile),
                escapeshellarg($extractDir)
            ), $output, $code);
            
            if ($code !== 0) {
                throw new \Exception("Falha ao extrair backup: " . implode("\n", $output));
            }
            
            // Se o conteúdo está em um subdiretório, move para raiz
            $subdirs = glob($extractDir . '*', GLOB_ONLYDIR);
            if (count($subdirs) === 1) {
                $subdir = $subdirs[0];
                $items = scandir($subdir);
                foreach ($items as $item) {
                    if ($item !== '.' && $item !== '..') {
                        rename($subdir . '/' . $item, $extractDir . '/' . $item);
                    }
                }
                @rmdir($subdir);
            }
        } else {
            throw new \Exception("Formato de backup não suportado: {$ext}");
        }
        
        return $extractDir;
    }

    /**
     * Carrega manifesto do backup
     */
    private function loadManifest(string $extractDir): ?array
    {
        $manifestFile = $extractDir . '/manifest.json';
        
        if (file_exists($manifestFile)) {
            return json_decode(file_get_contents($manifestFile), true);
        }
        
        return null;
    }

    /**
     * Restaura código-fonte
     */
    private function restoreCode(string $sourceDir): void
    {
        // Diretórios a preservar (não sobrescrever)
        $preserve = ['backup', 'backups', 'logs', 'uploads', '.git'];
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $relativePath = str_replace($sourceDir, '', $item->getPathname());
            $destPath = $this->targetDir . ltrim($relativePath, '/');
            
            // Verifica se deve preservar
            $skip = false;
            foreach ($preserve as $p) {
                if (strpos($relativePath, '/' . $p) === 0 || strpos($relativePath, $p) === 0) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;
            
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                $dir = dirname($destPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                copy($item->getPathname(), $destPath);
            }
        }
    }

    /**
     * Restaura banco de dados
     */
    private function restoreDatabase(string $sqlFile): void
    {
        $sql = file_get_contents($sqlFile);
        
        $pdo = new \PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
                $this->dbConfig['host'],
                $this->dbConfig['name']
            ),
            $this->dbConfig['user'],
            $this->dbConfig['pass'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        
        // Desabilita foreign keys temporariamente
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Divide em statements e executa
        $statements = $this->splitSqlStatements($sql);
        
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (empty($stmt) || strpos($stmt, '--') === 0) continue;
            
            try {
                $pdo->exec($stmt);
            } catch (\PDOException $e) {
                // Log mas continua (algumas queries podem falhar inofensivamente)
                $this->log("Aviso SQL: " . $e->getMessage());
            }
        }
        
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Divide SQL em statements individuais
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';
            
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char === $stringChar && $prev !== '\\') {
                $inString = false;
            }
            
            if ($char === ';' && !$inString) {
                $statements[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        if (trim($current)) {
            $statements[] = $current;
        }
        
        return $statements;
    }

    /**
     * Dump do banco atual
     */
    private function dumpCurrentDatabase(string $destination): void
    {
        $pdo = new \PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
                $this->dbConfig['host'],
                $this->dbConfig['name']
            ),
            $this->dbConfig['user'],
            $this->dbConfig['pass']
        );
        
        $output = "-- Pre-restore database backup\n";
        $output .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $create['Create Table'] . ";\n\n";
            
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $cols = '`' . implode('`, `', array_keys($row)) . '`';
                $vals = array_map(function($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote($v);
                }, array_values($row));
                $output .= "INSERT INTO `{$table}` ({$cols}) VALUES (" . implode(', ', $vals) . ");\n";
            }
            $output .= "\n";
        }
        
        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        file_put_contents($destination, $output);
    }

    /**
     * Encontra backup por data
     */
    private function findBackupByDate(string $date): ?array
    {
        $targetTime = strtotime($date);
        $backups = $this->listAvailableBackups();
        
        $closest = null;
        $closestDiff = PHP_INT_MAX;
        
        foreach ($backups as $backup) {
            if ($backup['type'] !== 'full') continue;
            
            $backupTime = strtotime($backup['date']);
            $diff = abs($backupTime - $targetTime);
            
            if ($diff < $closestDiff) {
                $closestDiff = $diff;
                $closest = $backup;
            }
        }
        
        return $closest;
    }

    /**
     * Limpa cache do sistema
     */
    private function clearCache(): void
    {
        $cacheDirs = [
            $this->targetDir . 'cache/',
            $this->targetDir . 'app/cache/',
            $this->targetDir . 'tmp/'
        ];
        
        foreach ($cacheDirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
        }
        
        // Limpa opcache se disponível
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
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
     * Log
     */
    private function log(string $message): void
    {
        $logFile = (defined('LOGS_PATH') ? LOGS_PATH : dirname(dirname(__DIR__)) . '/logs/') . 'restore.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[{$timestamp}] {$message}\n";
        
        @file_put_contents($logFile, $entry, FILE_APPEND);
        
        if (php_sapi_name() === 'cli') {
            echo $entry;
        }
    }

    /**
     * Formata bytes
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
}
