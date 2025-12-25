-- CHM Sistema - Schema do Banco de Dados
-- @author ch-mestriner (https://ch-mestriner.com.br)
-- @date 23/12/2025
-- @version 1.0.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Criação do banco
CREATE DATABASE IF NOT EXISTS `chm_sistema` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `chm_sistema`;

-- Tabela de usuários
DROP TABLE IF EXISTS `chm_users`;
CREATE TABLE `chm_users` (
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
DROP TABLE IF EXISTS `chm_clients`;
CREATE TABLE `chm_clients` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `type` ENUM('pf', 'pj') NOT NULL DEFAULT 'pf',
    `name` VARCHAR(150) NOT NULL,
    `trade_name` VARCHAR(150) NULL COMMENT 'Nome fantasia',
    `document` VARCHAR(20) NOT NULL COMMENT 'CPF ou CNPJ',
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
    UNIQUE KEY `uk_document` (`document`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_name` (`name`),
    CONSTRAINT `fk_clients_user` FOREIGN KEY (`user_id`) REFERENCES `chm_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de motoristas
DROP TABLE IF EXISTS `chm_drivers`;
CREATE TABLE `chm_drivers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `name` VARCHAR(150) NOT NULL,
    `document` VARCHAR(20) NOT NULL COMMENT 'CPF',
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
    UNIQUE KEY `uk_document` (`document`),
    UNIQUE KEY `uk_cnh` (`cnh`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_name` (`name`),
    CONSTRAINT `fk_drivers_user` FOREIGN KEY (`user_id`) REFERENCES `chm_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de veículos
DROP TABLE IF EXISTS `chm_vehicles`;
CREATE TABLE `chm_vehicles` (
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

-- Tabela de agendamentos/reservas
DROP TABLE IF EXISTS `chm_bookings`;
CREATE TABLE `chm_bookings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL COMMENT 'Código único do agendamento',
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
    `stops` TEXT NULL COMMENT 'JSON com paradas intermediárias',
    `passengers` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `passenger_name` VARCHAR(150) NULL,
    `passenger_phone` VARCHAR(20) NULL,
    `flight_number` VARCHAR(20) NULL,
    `flight_origin` VARCHAR(100) NULL,
    `flight_arrival` TIME NULL,
    `distance` DECIMAL(10,2) NULL COMMENT 'Distância em km',
    `duration` INT UNSIGNED NULL COMMENT 'Duração em minutos',
    `value` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `extras` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Valores extras (pedágios, etc)',
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
    KEY `idx_payment_status` (`payment_status`),
    CONSTRAINT `fk_bookings_client` FOREIGN KEY (`client_id`) REFERENCES `chm_clients` (`id`),
    CONSTRAINT `fk_bookings_driver` FOREIGN KEY (`driver_id`) REFERENCES `chm_drivers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_bookings_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `chm_vehicles` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_bookings_created_by` FOREIGN KEY (`created_by`) REFERENCES `chm_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de contas a pagar
DROP TABLE IF EXISTS `chm_accounts_payable`;
CREATE TABLE `chm_accounts_payable` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `description` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NULL,
    `supplier` VARCHAR(150) NULL,
    `due_date` DATE NOT NULL,
    `value` DECIMAL(10,2) NOT NULL,
    `paid_value` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `paid_at` DATETIME NULL,
    `payment_method` ENUM('cash', 'pix', 'credit', 'debit', 'transfer', 'boleto') NULL,
    `status` ENUM('pending', 'partial', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'pending',
    `recurrent` TINYINT(1) NOT NULL DEFAULT 0,
    `recurrent_type` ENUM('weekly', 'monthly', 'yearly') NULL,
    `booking_id` INT UNSIGNED NULL,
    `driver_id` INT UNSIGNED NULL,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_status` (`status`),
    KEY `idx_driver` (`driver_id`),
    CONSTRAINT `fk_payable_booking` FOREIGN KEY (`booking_id`) REFERENCES `chm_bookings` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_payable_driver` FOREIGN KEY (`driver_id`) REFERENCES `chm_drivers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_payable_created_by` FOREIGN KEY (`created_by`) REFERENCES `chm_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de contas a receber
DROP TABLE IF EXISTS `chm_accounts_receivable`;
CREATE TABLE `chm_accounts_receivable` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `description` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NULL,
    `client_id` INT UNSIGNED NULL,
    `booking_id` INT UNSIGNED NULL,
    `due_date` DATE NOT NULL,
    `value` DECIMAL(10,2) NOT NULL,
    `received_value` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `received_at` DATETIME NULL,
    `payment_method` ENUM('cash', 'pix', 'credit', 'debit', 'transfer', 'invoice') NULL,
    `status` ENUM('pending', 'partial', 'received', 'overdue', 'cancelled') NOT NULL DEFAULT 'pending',
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_status` (`status`),
    KEY `idx_client` (`client_id`),
    CONSTRAINT `fk_receivable_client` FOREIGN KEY (`client_id`) REFERENCES `chm_clients` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_receivable_booking` FOREIGN KEY (`booking_id`) REFERENCES `chm_bookings` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_receivable_created_by` FOREIGN KEY (`created_by`) REFERENCES `chm_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de vouchers
DROP TABLE IF EXISTS `chm_vouchers`;
CREATE TABLE `chm_vouchers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `type` ENUM('voucher', 'receipt') NOT NULL DEFAULT 'voucher',
    `file_path` VARCHAR(255) NULL,
    `sent_at` DATETIME NULL,
    `sent_to` VARCHAR(150) NULL,
    `sent_method` ENUM('email', 'whatsapp', 'both') NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_code` (`code`),
    KEY `idx_booking` (`booking_id`),
    CONSTRAINT `fk_vouchers_booking` FOREIGN KEY (`booking_id`) REFERENCES `chm_bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de mensagens WhatsApp
DROP TABLE IF EXISTS `chm_whatsapp_messages`;
CREATE TABLE `chm_whatsapp_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `message_id` VARCHAR(100) NULL COMMENT 'ID da mensagem no WhatsApp',
    `phone` VARCHAR(20) NOT NULL,
    `direction` ENUM('incoming', 'outgoing') NOT NULL,
    `type` ENUM('text', 'image', 'document', 'audio', 'video', 'template') NOT NULL DEFAULT 'text',
    `content` TEXT NOT NULL,
    `template_name` VARCHAR(100) NULL,
    `template_params` TEXT NULL,
    `status` ENUM('pending', 'sent', 'delivered', 'read', 'failed') NOT NULL DEFAULT 'pending',
    `error_message` TEXT NULL,
    `client_id` INT UNSIGNED NULL,
    `driver_id` INT UNSIGNED NULL,
    `booking_id` INT UNSIGNED NULL,
    `sent_at` DATETIME NULL,
    `delivered_at` DATETIME NULL,
    `read_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_phone` (`phone`),
    KEY `idx_direction` (`direction`),
    KEY `idx_status` (`status`),
    KEY `idx_client` (`client_id`),
    KEY `idx_driver` (`driver_id`),
    KEY `idx_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de templates WhatsApp
DROP TABLE IF EXISTS `chm_whatsapp_templates`;
CREATE TABLE `chm_whatsapp_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `content` TEXT NOT NULL,
    `variables` TEXT NULL COMMENT 'JSON com variáveis disponíveis',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tags WhatsApp
DROP TABLE IF EXISTS `chm_whatsapp_tags`;
CREATE TABLE `chm_whatsapp_tags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tag` VARCHAR(50) NOT NULL COMMENT 'Ex: #cliente, #data',
    `description` VARCHAR(255) NULL,
    `field_reference` VARCHAR(100) NULL COMMENT 'Campo de referência no sistema',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configurações
DROP TABLE IF EXISTS `chm_settings`;
CREATE TABLE `chm_settings` (
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

-- Tabela de logs do sistema
DROP TABLE IF EXISTS `chm_logs`;
CREATE TABLE `chm_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `data` TEXT NULL COMMENT 'JSON com dados adicionais',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_module` (`module`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de backups
DROP TABLE IF EXISTS `chm_backups`;
CREATE TABLE `chm_backups` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(255) NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `type` ENUM('auto', 'manual') NOT NULL DEFAULT 'auto',
    `status` ENUM('completed', 'failed') NOT NULL DEFAULT 'completed',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_type` (`type`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário admin padrão
INSERT INTO `chm_users` (`name`, `email`, `password`, `profile`, `status`) VALUES
('Administrador CHM', 'chm@chmtransportes.com.br', '$2y$12$rKxPc8VxQzN5YbWqE3mHHOgJKlMnOpRsTuVwXyZaBcDeFgHiJkLmN', 1, 'active');
-- Login: chm@chmtransportes.com.br / Senha: Ca258790$

-- Inserir configurações padrão
INSERT INTO `chm_settings` (`key`, `value`, `type`, `group`, `description`) VALUES
('company_name', 'CHM Transportes Executivos', 'string', 'general', 'Nome da empresa'),
('company_document', '', 'string', 'general', 'CNPJ da empresa'),
('company_phone', '', 'string', 'general', 'Telefone principal'),
('company_email', '', 'string', 'general', 'E-mail principal'),
('company_address', '', 'string', 'general', 'Endereço completo'),
('default_commission_rate', '11.00', 'float', 'finance', 'Taxa de comissão padrão (%)'),
('whatsapp_phone_id', '', 'string', 'whatsapp', 'ID do telefone WhatsApp Business'),
('whatsapp_token', '', 'string', 'whatsapp', 'Token de acesso WhatsApp API'),
('whatsapp_verify_token', '', 'string', 'whatsapp', 'Token de verificação webhook'),
('backup_interval', '600', 'integer', 'backup', 'Intervalo de backup em segundos'),
('backup_retention_days', '30', 'integer', 'backup', 'Dias de retenção de backups');

-- Inserir tags padrão do WhatsApp
INSERT INTO `chm_whatsapp_tags` (`tag`, `description`, `field_reference`) VALUES
('#cliente', 'Nome do cliente', 'clients.name'),
('#data', 'Data do serviço', 'bookings.date'),
('#hora', 'Hora do serviço', 'bookings.time'),
('#origem', 'Local de origem', 'bookings.origin'),
('#destino', 'Local de destino', 'bookings.destination'),
('#motorista', 'Nome do motorista', 'drivers.name'),
('#veiculo', 'Modelo do veículo', 'vehicles.model'),
('#placa', 'Placa do veículo', 'vehicles.plate'),
('#voo', 'Número do voo', 'bookings.flight_number'),
('#valor', 'Valor do serviço', 'bookings.total'),
('#codigo', 'Código do agendamento', 'bookings.code'),
('#empresa', 'Nome da empresa', 'settings.company_name');

SET FOREIGN_KEY_CHECKS = 1;
