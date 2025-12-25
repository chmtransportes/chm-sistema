<?php
/**
 * CHM Sistema - Seed Idempotente
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 24/12/2025
 * 
 * Insere dados iniciais sem duplicar registros
 */

$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    header('Content-Type: application/json');
}

require_once __DIR__ . '/../config/env_loader.php';
EnvLoader::load();

class Seeder
{
    private PDO $pdo;
    private array $log = [];

    public function __construct()
    {
        $this->connect();
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

        $this->log[] = "Conectado ao banco: {$name}";
    }

    public function run(): array
    {
        $result = [
            'success' => true,
            'inserted' => [],
            'skipped' => [],
            'errors' => [],
            'log' => []
        ];

        try {
            // Seed de usuário admin
            $this->seedAdmin($result);

            // Seed de configurações
            $this->seedSettings($result);

        } catch (Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
            $this->log[] = "ERRO: " . $e->getMessage();
        }

        $result['log'] = $this->log;
        return $result;
    }

    private function seedAdmin(array &$result): void
    {
        $email = 'chm@chmtransportes.com.br';
        
        $stmt = $this->pdo->prepare("SELECT id FROM chm_users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $result['skipped'][] = "Admin: {$email}";
            $this->log[] = "SKIP: Usuário admin já existe";
            return;
        }

        $password = password_hash('Ca258790$', PASSWORD_BCRYPT);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO chm_users (name, email, password, profile, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute(['Administrador CHM', $email, $password, 1, 'active']);

        $result['inserted'][] = "Admin: {$email}";
        $this->log[] = "OK: Usuário admin criado";
    }

    private function seedSettings(array &$result): void
    {
        $settings = [
            ['company_name', 'CHM Transportes Executivos'],
            ['company_document', ''],
            ['company_phone', ''],
            ['company_email', 'contato@chmtransportes.com.br'],
            ['company_address', ''],
            ['default_commission', '11'],
            ['whatsapp_enabled', '0'],
            ['backup_enabled', '1'],
            ['backup_interval', '10'],
        ];

        foreach ($settings as [$key, $value]) {
            $stmt = $this->pdo->prepare("SELECT id FROM chm_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            
            if ($stmt->fetch()) {
                $result['skipped'][] = "Setting: {$key}";
                continue;
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO chm_settings (setting_key, setting_value, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$key, $value]);
            $result['inserted'][] = "Setting: {$key}";
            $this->log[] = "OK: Configuração {$key} criada";
        }
    }
}

// Executar
try {
    $seeder = new Seeder();
    $result = $seeder->run();

    if ($isCli) {
        echo "\n=== CHM Sistema - Seed ===\n\n";
        foreach ($result['log'] as $line) {
            echo "  {$line}\n";
        }
        echo "\nInseridos: " . count($result['inserted']) . "\n";
        echo "Ignorados: " . count($result['skipped']) . "\n";
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
