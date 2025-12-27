-- CHM Sistema - Tabela de Eventos do Calendário (Importados)
-- @author ch-mestriner (https://ch-mestriner.com.br)
-- @date 25/12/2025

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
    `source` VARCHAR(50) DEFAULT 'manual',
    `user_id` INT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_start` (`start_datetime`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
