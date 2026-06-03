-- KKson Framework v0.10.4.1 — IP ban performance (optional)
-- Run against your application database (MySQL 5.7+ / MariaDB 10.2+ with JSON support).
--
-- The app works without this script. Apply it for indexed client_ip on login logs.
-- Intended to run once. On re-run, ignore "Duplicate column" / "Duplicate key name" errors.
--
-- File location after Composer install:
--   vendor/kksonthomas/kkson-framework/sql/patch-v0.10.4.1-ip-ban.sql

-- 1) Normalized client IP on system_log (new rows are filled by the application)
ALTER TABLE `system_log`
  ADD COLUMN `client_ip` VARCHAR(45) NULL DEFAULT NULL AFTER `header_ip_data`;

-- 2) Best-effort backfill when header_ip_data is a JSON array (first element)
--    Rows that stay NULL still match via header_ip_data in application code.
UPDATE `system_log`
SET `client_ip` = NULLIF(TRIM(BOTH '"' FROM JSON_UNQUOTE(JSON_EXTRACT(`header_ip_data`, '$[0]'))), '')
WHERE `client_ip` IS NULL
  AND `header_ip_data` IS NOT NULL
  AND `header_ip_data` != ''
  AND JSON_VALID(`header_ip_data`)
  AND JSON_TYPE(JSON_EXTRACT(`header_ip_data`, '$[0]')) IN ('STRING', 'INTEGER');

-- 3) Indexes for IP ban / login log queries
CREATE INDEX `idx_system_log_type_client_ip_creation_date`
  ON `system_log` (`type`, `client_ip`, `creation_date`);

CREATE INDEX `idx_ban_ip_list_ip_unbanned_date`
  ON `ban_ip_list` (`ip`, `unbanned_date`);

CREATE INDEX `idx_ban_ip_list_ip_auto_unban_creation`
  ON `ban_ip_list` (`ip`, `is_auto_unban`, `creation_date`);
