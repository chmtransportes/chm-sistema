<?php
/**
 * CHM Sistema - Sistema de Migrações
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 24/12/2025
 * 
 * Executa migrações pendentes de forma idempotente
 */

// Pode ser executado via CLI ou HTTP
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    header('Content-Type: application/json');
}

// Carregar configurações
require_once __DIR__ . '/../config/env_loader.php';
EnvLoader::load();

class Migrator
{
    private PDO $pdo;
    private string $migrationsPath;
    private array $log = [];

    public function __construct()
    {
        $this->migrationsPath = __DIR__ . '/migrations/';
        $this->connect();
        $this->createMigrationsTable();
    }

    private function connect(): void
    {
        $host = EnvLoader::get('DB_HOST', 'localhost');
        $port = EnvLoader::get('DB_PORT', '3306');
        $name = EnvLoader::get('DB_NAME', 'chm_sistema');
        $user = EnvLoader::get('DB_USER', 'root');
        $pass = EnvLoader::get('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $this->log[] = "Conectado ao banco: {$name}@{$host}";
    }

    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL UNIQUE,
            checksum VARCHAR(64) NOT NULL,
            ran_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_filename (filename)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->pdo->exec($sql);
        $this->log[] = "Tabela migrations verificada/criada";
    }

    public function run(): array
    {
        $result = [
            'success' => true,
            'executed' => [],
            'skipped' => [],
            'errors' => [],
            'log' => []
        ];

        try {
            $files = glob($this->migrationsPath . '*.sql');
            sort($files);

            if (empty($files)) {
                $this->log[] = "Nenhuma migração encontrada";
                $result['log'] = $this->log;
                return $result;
            }

            foreach ($files as $file) {
                $filename = basename($file);
                $checksum = md5_file($file);

                // Verificar se já foi executada
                $stmt = $this->pdo->prepare("SELECT checksum FROM migrations WHERE filename = ?");
                $stmt->execute([$filename]);
                $existing = $stmt->fetch();

                if ($existing) {
                    if ($existing['checksum'] === $checksum) {
                        $result['skipped'][] = $filename;
                        $this->log[] = "SKIP: {$filename} (já executada)";
                        continue;
                    } else {
                        $result['errors'][] = "CHECKSUM MISMATCH: {$filename}";
                        $result['success'] = false;
                        $this->log[] = "ERRO: {$filename} foi modificada após execução!";
                        break;
                    }
                }

                // Executar migração
                $this->log[] = "Executando: {$filename}";
                $sql = file_get_contents($file);

                $this->pdo->beginTransaction();
                try {
                    $this->pdo->exec($sql);
                    
                    // Registrar migração
                    $stmt = $this->pdo->prepare("INSERT INTO migrations (filename, checksum) VALUES (?, ?)");
                    $stmt->execute([$filename, $checksum]);
                    
                    $this->pdo->commit();
                    $result['executed'][] = $filename;
                    $this->log[] = "OK: {$filename}";
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    $result['errors'][] = "{$filename}: " . $e->getMessage();
                    $result['success'] = false;
                    $this->log[] = "ERRO: {$filename} - " . $e->getMessage();
                    break;
                }
            }
        } catch (Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
            $this->log[] = "ERRO FATAL: " . $e->getMessage();
        }

        $result['log'] = $this->log;
        return $result;
    }

    public function status(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM migrations ORDER BY ran_at DESC");
        return $stmt->fetchAll();
    }
}

// Executar
try {
    $migrator = new Migrator();
    $result = $migrator->run();

    if ($isCli) {
        echo "\n=== CHM Sistema - Migrações ===\n\n";
        foreach ($result['log'] as $line) {
            echo "  {$line}\n";
        }
        echo "\nExecutadas: " . count($result['executed']) . "\n";
        echo "Ignoradas: " . count($result['skipped']) . "\n";
        echo "Erros: " . count($result['errors']) . "\n";
        echo "\nStatus: " . ($result['success'] ? 'SUCESSO' : 'FALHA') . "\n\n";
        exit($result['success'] ? 0 : 1);
    } else {
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
} catch (Exception $e) {
    $error = ['success' => false, 'error' => $e->getMessage()];
    if ($isCli) {
        echo "ERRO: " . $e->getMessage() . "\n";
        exit(1);
    } else {
        echo json_encode($error);
    }
}
