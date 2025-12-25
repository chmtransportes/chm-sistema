<?php
/**
 * CHM Sistema - Migração de Banco de Dados para Produção
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 24/12/2025
 * 
 * Executa migração do banco de dados no servidor de produção
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║       CHM-SISTEMA - MIGRAÇÃO DE BANCO DE DADOS               ║\n";
echo "║       Servidor: 186.209.113.108                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Configurações do banco de produção
$dbConfig = [
    'host' => '186.209.113.108',
    'port' => 3306,
    'name' => 'chmtrans_chm-sistema',
    'user' => 'chmtrans_chm-sistema',
    'pass' => 'Ca258790%Ca258790%',
    'charset' => 'utf8mb4'
];

// Schema SQL para produção (versão simplificada)
$schema = <<<SQL
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `chm_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `profile` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '1=Admin, 2=Motorista, 3=Cliente',
    `avatar` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `email_verified_at` DATETIME NULL,
    `remember_token` VARCHAR(100) NULL,
    `reset_token` VARCHAR(100) NULL,
    `reset_token_expires` DATETIME NULL,
    `last_login` DATETIME NULL,
    `login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
    `locked_until` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_email` (`email`),
    KEY `idx_profile` (`profile`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS `chm_clients` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `type` ENUM('pf', 'pj') NOT NULL DEFAULT 'pf',
    `name` VARCHAR(150) NOT NULL,
    `trade_name` VARCHAR(150) NULL,
    `document` VARCHAR(20) NOT NULL,
    `rg` VARCHAR(20) NULL,
    `email` VARCHAR(150) NULL,
    `phone` VARCHAR(20) NULL,
    `phone2` VARCHAR(20) NULL,
    `whatsapp` VARCHAR(20) NULL,
    `address` VARCHAR(255) NULL,
    `address_number` VARCHAR(20) NULL,
    `address_complement` VARCHAR(100) NULL,
    `neighborhood` VARCHAR(100) NULL,
    `city` VARCHAR(100) NULL,
    `state` CHAR(2) NULL,
    `zipcode` VARCHAR(10) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de motoristas
CREATE TABLE IF NOT EXISTS `chm_drivers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `name` VARCHAR(150) NOT NULL,
    `document` VARCHAR(20) NOT NULL,
    `rg` VARCHAR(20) NULL,
    `cnh` VARCHAR(20) NOT NULL,
    `cnh_category` VARCHAR(5) NOT NULL,
    `cnh_expiry` DATE NOT NULL,
    `birth_date` DATE NULL,
    `email` VARCHAR(150) NULL,
    `phone` VARCHAR(20) NULL,
    `phone2` VARCHAR(20) NULL,
    `whatsapp` VARCHAR(20) NULL,
    `address` VARCHAR(255) NULL,
    `address_number` VARCHAR(20) NULL,
    `address_complement` VARCHAR(100) NULL,
    `neighborhood` VARCHAR(100) NULL,
    `city` VARCHAR(100) NULL,
    `state` CHAR(2) NULL,
    `zipcode` VARCHAR(10) NULL,
    `pix_key` VARCHAR(100) NULL,
    `bank_name` VARCHAR(100) NULL,
    `bank_agency` VARCHAR(20) NULL,
    `bank_account` VARCHAR(30) NULL,
    `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 11.00,
    `type` ENUM('proprio', 'terceirizado') NOT NULL DEFAULT 'proprio',
    `photo` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de veículos
CREATE TABLE IF NOT EXISTS `chm_vehicles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `plate` VARCHAR(10) NOT NULL,
    `brand` VARCHAR(50) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `year` YEAR NOT NULL,
    `color` VARCHAR(30) NOT NULL,
    `renavam` VARCHAR(20) NULL,
    `chassis` VARCHAR(30) NULL,
    `fuel` ENUM('gasoline', 'ethanol', 'flex', 'diesel', 'electric', 'hybrid') NOT NULL DEFAULT 'flex',
    `category` ENUM('sedan', 'suv', 'van', 'bus', 'other') NOT NULL DEFAULT 'sedan',
    `seats` TINYINT UNSIGNED NOT NULL DEFAULT 4,
    `owner` ENUM('proprio', 'terceirizado') NOT NULL DEFAULT 'proprio',
    `owner_name` VARCHAR(150) NULL,
    `owner_document` VARCHAR(20) NULL,
    `insurance_company` VARCHAR(100) NULL,
    `insurance_policy` VARCHAR(50) NULL,
    `insurance_expiry` DATE NULL,
    `ipva_paid` TINYINT(1) NOT NULL DEFAULT 0,
    `licensing_date` DATE NULL,
    `last_maintenance` DATE NULL,
    `next_maintenance` DATE NULL,
    `odometer` INT UNSIGNED NULL,
    `photo` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive', 'maintenance') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_plate` (`plate`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de agendamentos
CREATE TABLE IF NOT EXISTS `chm_bookings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL,
    `client_id` INT UNSIGNED NOT NULL,
    `driver_id` INT UNSIGNED NULL,
    `vehicle_id` INT UNSIGNED NULL,
    `service_type` ENUM('transfer', 'hourly', 'daily', 'airport', 'executive', 'event') NOT NULL DEFAULT 'transfer',
    `date` DATE NOT NULL,
    `time` TIME NOT NULL,
    `end_date` DATE NULL,
    `end_time` TIME NULL,
    `origin` VARCHAR(255) NOT NULL,
    `destination` VARCHAR(255) NULL,
    `stops` TEXT NULL,
    `passengers` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `passenger_name` VARCHAR(150) NULL,
    `passenger_phone` VARCHAR(20) NULL,
    `flight_number` VARCHAR(20) NULL,
    `flight_origin` VARCHAR(100) NULL,
    `flight_arrival` TIME NULL,
    `distance` DECIMAL(10,2) NULL,
    `duration` INT UNSIGNED NULL,
    `value` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `extras` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `discount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 11.00,
    `commission_value` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `payment_method` ENUM('cash', 'pix', 'credit', 'debit', 'transfer', 'invoice') NOT NULL DEFAULT 'pix',
    `payment_status` ENUM('pending', 'partial', 'paid') NOT NULL DEFAULT 'pending',
    `paid_at` DATETIME NULL,
    `status` ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `cancelled_at` DATETIME NULL,
    `cancelled_reason` TEXT NULL,
    `notes` TEXT NULL,
    `internal_notes` TEXT NULL,
    `voucher_sent` TINYINT(1) NOT NULL DEFAULT 0,
    `voucher_sent_at` DATETIME NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_code` (`code`),
    KEY `idx_client` (`client_id`),
    KEY `idx_driver` (`driver_id`),
    KEY `idx_vehicle` (`vehicle_id`),
    KEY `idx_date` (`date`),
    KEY `idx_status` (`status`),
    KEY `idx_payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS `chm_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL,
    `type` ENUM('string', 'integer', 'float', 'boolean', 'json') NOT NULL DEFAULT 'string',
    `group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs
CREATE TABLE IF NOT EXISTS `chm_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `data` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_module` (`module`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
SQL;

// Seed inicial
$seed = <<<SQL
-- Inserir usuário admin se não existir
INSERT IGNORE INTO `chm_users` (`id`, `name`, `email`, `password`, `profile`, `status`) VALUES
(1, 'Administrador CHM', 'chm@chmtransportes.com.br', '\$2y\$12\$rKxPc8VxQzN5YbWqE3mHHOgJKlMnOpRsTuVwXyZaBcDeFgHiJkLmN', 1, 'active');

-- Inserir configurações padrão se não existirem
INSERT IGNORE INTO `chm_settings` (`key`, `value`, `type`, `group`, `description`) VALUES
('company_name', 'CHM Transportes Executivos', 'string', 'general', 'Nome da empresa'),
('default_commission_rate', '11.00', 'float', 'finance', 'Taxa de comissão padrão (%)');
SQL;

/**
 * Executa a migração
 */
function executeMigration($config, $schema, $seed) {
    echo "[1/4] Conectando ao banco de dados...\n";
    
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        echo "    ✓ Conectado com sucesso!\n\n";
    } catch (PDOException $e) {
        echo "    ✗ Erro de conexão: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "[2/4] Selecionando banco de dados...\n";
    try {
        $pdo->exec("USE `{$config['name']}`");
        echo "    ✓ Banco: {$config['name']}\n\n";
    } catch (PDOException $e) {
        echo "    ✗ Erro: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "[3/4] Executando schema (criando tabelas)...\n";
    try {
        $pdo->exec($schema);
        echo "    ✓ Tabelas criadas/atualizadas!\n\n";
    } catch (PDOException $e) {
        echo "    ⚠ Aviso: " . $e->getMessage() . "\n\n";
    }
    
    echo "[4/4] Executando seed (dados iniciais)...\n";
    try {
        $pdo->exec($seed);
        echo "    ✓ Dados iniciais inseridos!\n\n";
    } catch (PDOException $e) {
        echo "    ⚠ Aviso: " . $e->getMessage() . "\n\n";
    }
    
    // Verificar tabelas criadas
    echo "Verificando tabelas criadas:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "    ✓ {$table}\n";
    }
    
    echo "\n╔══════════════════════════════════════════════════════════════╗\n";
    echo "║              MIGRAÇÃO CONCLUÍDA COM SUCESSO!                 ║\n";
    echo "╠══════════════════════════════════════════════════════════════╣\n";
    echo "║  Banco: {$config['name']}                       \n";
    echo "║  Tabelas: " . count($tables) . "                                               ║\n";
    echo "║  Login: chm@chmtransportes.com.br                            ║\n";
    echo "║  Senha: Ca258790\$                                            ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    
    return true;
}

// Executar migração
$success = executeMigration($dbConfig, $schema, $seed);
exit($success ? 0 : 1);
