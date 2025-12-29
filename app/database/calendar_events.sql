-- CHM Sistema - Tabela de Eventos do Calendário
-- @author ch-mestriner (https://ch-mestriner.com.br)
-- @date 29/12/2025 18:15
-- @version 2.5.0

CREATE TABLE IF NOT EXISTS `chm_calendar_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `google_uid` VARCHAR(255) UNIQUE,
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT,
    `location` VARCHAR(500),
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME,
    `all_day` TINYINT(1) DEFAULT 0,
    `color` VARCHAR(20) DEFAULT '#1a73e8',
    `event_type` VARCHAR(50) DEFAULT 'personal',
    `status` VARCHAR(20) DEFAULT 'confirmed',
    `source` VARCHAR(50) DEFAULT 'manual',
    `booking_id` INT DEFAULT NULL,
    `client_id` INT DEFAULT NULL,
    `driver_id` INT DEFAULT NULL,
    `user_id` INT,
    `notify_email` TINYINT(1) DEFAULT 0,
    `notify_client` TINYINT(1) DEFAULT 0,
    `notify_driver` TINYINT(1) DEFAULT 0,
    `email_sent` TINYINT(1) DEFAULT 0,
    `email_sent_at` DATETIME DEFAULT NULL,
    `recurrence_rule` VARCHAR(255) DEFAULT NULL,
    `recurrence_end` DATE DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_start` (`start_datetime`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_source` (`source`),
    INDEX `idx_status` (`status`),
    INDEX `idx_client` (`client_id`),
    INDEX `idx_driver` (`driver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adiciona colunas se tabela já existir (migração)
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `event_type` VARCHAR(50) DEFAULT 'personal' AFTER `color`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) DEFAULT 'confirmed' AFTER `event_type`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `booking_id` INT DEFAULT NULL AFTER `source`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `client_id` INT DEFAULT NULL AFTER `booking_id`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `driver_id` INT DEFAULT NULL AFTER `client_id`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `notify_email` TINYINT(1) DEFAULT 0 AFTER `user_id`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `notify_client` TINYINT(1) DEFAULT 0 AFTER `notify_email`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `notify_driver` TINYINT(1) DEFAULT 0 AFTER `notify_client`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `email_sent` TINYINT(1) DEFAULT 0 AFTER `notify_driver`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `email_sent_at` DATETIME DEFAULT NULL AFTER `email_sent`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `recurrence_rule` VARCHAR(255) DEFAULT NULL AFTER `email_sent_at`;
-- ALTER TABLE `chm_calendar_events` ADD COLUMN IF NOT EXISTS `recurrence_end` DATE DEFAULT NULL AFTER `recurrence_rule`;
