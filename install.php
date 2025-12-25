<?php
/**
 * CHM Sistema - Script de Instalação
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 20/01/2025
 * @version 1.1.0
 * 
 * ATENÇÃO: Delete este arquivo após a instalação!
 */

// Configurações do banco
$dbHost = 'localhost';
$dbName = 'chm_sistema';
$dbUser = 'root';
$dbPass = '';

// Credenciais do admin
$adminEmail = 'chm@chmtransportes.com.br';
$adminPassword = 'Ca258790$';
$adminName = 'Administrador CHM';

// Gera hash da senha
$passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);

echo "<pre style='font-family: monospace; background: #1a1a2e; color: #fff; padding: 30px; margin: 0; min-height: 100vh;'>";
echo "═══════════════════════════════════════════════════════════\n";
echo "   CHM SISTEMA - INSTALAÇÃO v1.1.0\n";
echo "═══════════════════════════════════════════════════════════\n\n";

try {
    // Conecta ao MySQL (sem selecionar banco)
    echo "[1/5] Conectando ao MySQL... ";
    $pdo = new PDO("mysql:host={$dbHost}", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    echo "✓ OK\n";

    // Cria banco se não existe
    echo "[2/5] Criando banco de dados... ";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");
    echo "✓ OK\n";

    // Importa schema
    echo "[3/5] Importando estrutura... ";
    $schemaPath = __DIR__ . '/app/database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Arquivo schema.sql não encontrado!");
    }
    
    $sql = file_get_contents($schemaPath);
    
    // Remove a inserção padrão do admin (vamos inserir com hash correto)
    $sql = preg_replace("/INSERT INTO `chm_users`.*?;/s", "", $sql);
    
    // Executa cada comando separadamente
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    $pdo->exec($sql);
    echo "✓ OK\n";

    // Insere admin com hash correto
    echo "[4/5] Criando usuário admin... ";
    $stmt = $pdo->prepare("INSERT INTO `chm_users` (`name`, `email`, `password`, `profile`, `status`) VALUES (?, ?, ?, 1, 'active') ON DUPLICATE KEY UPDATE password = VALUES(password)");
    $stmt->execute([$adminName, $adminEmail, $passwordHash]);
    echo "✓ OK\n";

    // Verifica instalação
    echo "[5/5] Verificando instalação... ";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ OK (" . count($tables) . " tabelas criadas)\n";

    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "   ✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "📧 Login: <strong>{$adminEmail}</strong>\n";
    echo "🔑 Senha: <strong>{$adminPassword}</strong>\n\n";
    
    echo "🌐 Acesse: <a href='app/' style='color: #e94560;'>http://localhost/chm-sistema/app/</a>\n\n";
    
    echo "⚠️  <strong style='color: #ffc107;'>IMPORTANTE: Delete este arquivo (install.php) após a instalação!</strong>\n\n";

    // Hash gerado para referência
    echo "───────────────────────────────────────────────────────────\n";
    echo "Hash gerado: {$passwordHash}\n";
    echo "───────────────────────────────────────────────────────────\n";

} catch (PDOException $e) {
    echo "✗ ERRO\n\n";
    echo "❌ Erro de banco de dados:\n";
    echo "   " . $e->getMessage() . "\n\n";
    
    echo "Verifique:\n";
    echo "   1. MySQL/MariaDB está rodando\n";
    echo "   2. Credenciais estão corretas (dbUser/dbPass)\n";
    echo "   3. Usuário tem permissão para criar banco\n";
    
} catch (Exception $e) {
    echo "✗ ERRO\n\n";
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "</pre>";
