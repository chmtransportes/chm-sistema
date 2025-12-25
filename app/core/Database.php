<?php
/**
 * CHM Sistema - Classe de Conexão com Banco de Dados
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $queryLog = [];

    private function __construct()
    {
        $this->connect();
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->logError('Erro de conexão: ' . $e->getMessage());
            throw new \Exception('Erro ao conectar com o banco de dados.');
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $start = microtime(true);
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            if (DEBUG_MODE) {
                $this->queryLog[] = ['sql' => $sql, 'params' => $params, 'time' => microtime(true) - $start];
            }
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('Erro na query: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new \Exception('Erro ao executar query: ' . $e->getMessage());
        }
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO " . DB_PREFIX . "{$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        return (int) $this->connection->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $sql = "UPDATE " . DB_PREFIX . "{$table} SET " . implode(', ', $set) . " WHERE {$where}";
        $stmt = $this->query($sql, array_merge($data, $whereParams));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM " . DB_PREFIX . "{$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    private function logError(string $message): void
    {
        $logFile = LOGS_PATH . 'database-errors.log';
        $timestamp = date('Y-m-d H:i:s');
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
        file_put_contents($logFile, "[{$timestamp}] {$message}" . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function __clone() {}
    public function __wakeup() { throw new \Exception("Cannot unserialize singleton"); }
}
