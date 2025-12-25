-- CHM Sistema - Schema Produção
-- @author ch-mestriner (https://ch-mestriner.com.br)
-- @date 24/12/2025

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `chm_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `profile` TINYINT UNSIGNED NOT NULL DEFAULT 3,
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
    UNIQUE KEY `uk_email` (`email`)
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
    UNIQUE KEY `uk_document` (`document`)
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
    `car_photo` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_document` (`document`),
    UNIQUE KEY `uk_cnh` (`cnh`)
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
    UNIQUE KEY `uk_plate` (`plate`)
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
    UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de contas a pagar
CREATE TABLE IF NOT EXISTS `chm_accounts_payable` (
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
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de contas a receber
CREATE TABLE IF NOT EXISTS `chm_accounts_receivable` (
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
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de vouchers
CREATE TABLE IF NOT EXISTS `chm_vouchers` (
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
    UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de mensagens WhatsApp
CREATE TABLE IF NOT EXISTS `chm_whatsapp_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `message_id` VARCHAR(100) NULL,
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
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de templates WhatsApp
CREATE TABLE IF NOT EXISTS `chm_whatsapp_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `content` TEXT NOT NULL,
    `variables` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tags WhatsApp
CREATE TABLE IF NOT EXISTS `chm_whatsapp_tags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tag` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    `field_reference` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS `chm_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NULL,
    `setting_type` ENUM('string', 'integer', 'float', 'boolean', 'json') NOT NULL DEFAULT 'string',
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_key` (`setting_key`)
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
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de backups
CREATE TABLE IF NOT EXISTS `chm_backups` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(255) NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `type` ENUM('auto', 'manual') NOT NULL DEFAULT 'auto',
    `status` ENUM('completed', 'failed') NOT NULL DEFAULT 'completed',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de migrações
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL UNIQUE,
    `checksum` VARCHAR(64) NOT NULL,
    `ran_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
