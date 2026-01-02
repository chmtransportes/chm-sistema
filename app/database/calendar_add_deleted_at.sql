-- CHM Sistema - Adicionar campo deleted_at para sistema de lixeira
-- @author ch-mestriner (https://ch-mestriner.com.br)
-- @date 31/12/2025

ALTER TABLE `chm_calendar_events` 
ADD COLUMN `deleted_at` DATETIME DEFAULT NULL AFTER `updated_at`,
ADD INDEX `idx_deleted_at` (`deleted_at`);
