<?php
/**
 * CHM Sistema - Serviço de Backup Automático
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

class BackupService
{
    private string $backupDir;
    private string $sourceDir;
    private Database $db;

    public function __construct()
    {
        $this->backupDir = BACKUP_PATH;
        $this->sourceDir = ROOT_PATH;
        $this->db = Database::getInstance();
        
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    // Cria backup completo (arquivos + banco)
    public function createFull(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_full_{$timestamp}";
        $backupPath = $this->backupDir . $backupName;

        try {
            mkdir($backupPath, 0755, true);

            // Backup dos arquivos
            $filesResult = $this->backupFiles($backupPath . '/files');
            
            // Backup do banco de dados
            $dbResult = $this->backupDatabase($backupPath . '/database.sql');

            // Cria arquivo de informações
            $info = [
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'full',
                'version' => CHM_VERSION,
                'files_count' => $filesResult['count'],
                'files_size' => $filesResult['size'],
                'database_size' => filesize($backupPath . '/database.sql')
            ];
            file_put_contents($backupPath . '/info.json', json_encode($info, JSON_PRETTY_PRINT));

            // Registra no banco
            $this->logBackup($backupName, $backupPath, $info['files_size'] + $info['database_size'], 'auto');

            // Limpa backups antigos
            $this->cleanOldBackups();

            return [
                'success' => true,
                'path' => $backupPath,
                'name' => $backupName,
                'info' => $info
            ];

        } catch (\Exception $e) {
            Helpers::logAction('Erro no backup: ' . $e->getMessage(), 'backup');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Backup apenas do banco de dados
    public function createDatabaseOnly(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_db_{$timestamp}.sql";
        $backupPath = $this->backupDir . $backupName;

        try {
            $this->backupDatabase($backupPath);
            $size = filesize($backupPath);
            
            $this->logBackup($backupName, $backupPath, $size, 'manual');

            return [
                'success' => true,
                'path' => $backupPath,
                'name' => $backupName,
                'size' => $size
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Backup dos arquivos
    private function backupFiles(string $destination): array
    {
        $count = 0;
        $size = 0;
        
        mkdir($destination, 0755, true);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $excludeDirs = ['backup', 'vendor', 'node_modules', '.git', 'logs'];

        foreach ($iterator as $item) {
            $relativePath = str_replace($this->sourceDir, '', $item->getPathname());
            
            // Verifica exclusões
            $skip = false;
            foreach ($excludeDirs as $exclude) {
                if (strpos($relativePath, DIRECTORY_SEPARATOR . $exclude) !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            $destPath = $destination . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $destPath);
                $count++;
                $size += $item->getSize();
            }
        }

        return ['count' => $count, 'size' => $size];
    }

    // Backup do banco de dados
    private function backupDatabase(string $destination): bool
    {
        $tables = $this->db->fetchAll("SHOW TABLES");
        $output = "-- CHM Sistema Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Version: " . CHM_VERSION . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            
            // Estrutura da tabela
            $create = $this->db->fetchOne("SHOW CREATE TABLE `{$tableName}`");
            $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $output .= $create['Create Table'] . ";\n\n";

            // Dados da tabela
            $rows = $this->db->fetchAll("SELECT * FROM `{$tableName}`");
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';

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

        return file_put_contents($destination, $output) !== false;
    }

    // Registra backup no banco
    private function logBackup(string $filename, string $path, int $size, string $type): void
    {
        $this->db->insert('backups', [
            'filename' => $filename,
            'path' => $path,
            'size' => $size,
            'type' => $type,
            'status' => 'completed',
            'created_by' => Session::getUserId(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Remove backups antigos
    public function cleanOldBackups(): int
    {
        $deleted = 0;
        $maxAge = BACKUP_RETENTION_DAYS * 24 * 60 * 60;
        $maxBackups = MAX_BACKUPS;

        // Lista backups ordenados por data
        $backups = glob($this->backupDir . 'backup_*');
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        foreach ($backups as $index => $backup) {
            $age = time() - filemtime($backup);
            
            // Remove se muito antigo ou excede o limite
            if ($age > $maxAge || $index >= $maxBackups) {
                if (is_dir($backup)) {
                    $this->deleteDirectory($backup);
                } else {
                    unlink($backup);
                }
                $deleted++;
            }
        }

        return $deleted;
    }

    // Remove diretório recursivamente
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) return false;
        
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        return rmdir($dir);
    }

    // Lista backups disponíveis
    public function listBackups(): array
    {
        $backups = [];
        $items = glob($this->backupDir . 'backup_*');
        
        foreach ($items as $item) {
            $name = basename($item);
            $backups[] = [
                'name' => $name,
                'path' => $item,
                'size' => is_dir($item) ? $this->getDirectorySize($item) : filesize($item),
                'created_at' => date('Y-m-d H:i:s', filemtime($item)),
                'type' => is_dir($item) ? 'full' : 'database'
            ];
        }

        usort($backups, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        
        return $backups;
    }

    // Calcula tamanho do diretório
    private function getDirectorySize(string $dir): int
    {
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    // Verifica se precisa fazer backup automático
    public function shouldAutoBackup(): bool
    {
        $lastBackup = $this->db->fetchOne(
            "SELECT created_at FROM " . DB_PREFIX . "backups WHERE type = 'auto' ORDER BY created_at DESC LIMIT 1"
        );

        if (!$lastBackup) return true;

        $lastTime = strtotime($lastBackup['created_at']);
        return (time() - $lastTime) >= BACKUP_INTERVAL;
    }

    // Executa backup automático se necessário
    public function runAutoBackup(): ?array
    {
        if ($this->shouldAutoBackup()) {
            return $this->createFull();
        }
        return null;
    }
}
