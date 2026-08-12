-- KKson Framework greenfield database init.
-- Use on a blank MariaDB / MySQL 5.7+ database for a new application.
-- New databases: import this file only (includes system_log.client_ip + IP-ban indexes).
-- Existing databases missing client_ip/indexes: use patch-v0.10.4.1-ip-ban.sql instead.
--
-- Roles match KKsonFramework\RedBeanPHP\Model\User constants:
--   SYSADMIN, ADMIN, GENERAL USER
-- Default seed user: sysadmin / sysadmin (change after first login)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Core auth / audit
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'GENERAL USER',
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `creation_date` datetime DEFAULT NULL,
  `creation_user_id` int(10) unsigned DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `modified_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_active` (`username`, `_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(191) NOT NULL,
  `display_weight` int(11) NOT NULL DEFAULT 0,
  `creation_date` datetime DEFAULT NULL,
  `creation_user_id` int(10) unsigned DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `modified_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `display_name` (`display_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `permission_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_permission_user_permission` (`permission_id`),
  KEY `FK_permission_user_user` (`user_id`),
  CONSTRAINT `FK_permission_user_permission` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_permission_user_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(200) NOT NULL DEFAULT '0',
  `creation_date` datetime DEFAULT NULL,
  `creation_user_id` int(10) unsigned DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `modified_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `system_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `server_name` varchar(255) DEFAULT NULL,
  `server_addr` varchar(255) DEFAULT NULL,
  `request_uri` varchar(1000) DEFAULT NULL,
  `header_ip_data` text DEFAULT NULL,
  `client_ip` varchar(45) DEFAULT NULL,
  `log` mediumtext DEFAULT NULL,
  `creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `creation_user_id` int(10) unsigned DEFAULT NULL,
  `creation_real_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_system_log_type_client_ip_creation_date` (`type`, `client_ip`, `creation_date`),
  KEY `creation_date` (`creation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ban_ip_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(100) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `reason_chi` varchar(255) DEFAULT NULL,
  `creation_date` datetime DEFAULT NULL,
  `unbanned_date` datetime DEFAULT NULL,
  `is_auto_unban` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `creation_date` (`creation_date`),
  KEY `idx_ban_ip_list_ip_unbanned_date` (`ip`, `unbanned_date`),
  KEY `idx_ban_ip_list_ip_auto_unban_creation` (`ip`, `is_auto_unban`, `creation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `record_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL,
  `ref_id` int(10) unsigned NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_data` mediumtext DEFAULT NULL,
  `new_data` mediumtext DEFAULT NULL,
  `creation_date` datetime DEFAULT NULL,
  `creation_user_id` int(10) unsigned DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `modified_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_name_ref_id` (`table_name`(191), `ref_id`),
  KEY `creation_date` (`creation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `user_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `salt_token` varchar(191) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `data` mediumtext DEFAULT NULL,
  `expiry_date` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `is_voided` tinyint(1) NOT NULL DEFAULT 0,
  `creation_date` datetime DEFAULT NULL,
  `creation_user_id` int(10) unsigned DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `modified_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `salt_token` (`salt_token`),
  KEY `FK_user_token_user` (`user_id`),
  CONSTRAINT `FK_user_token_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Seed: framework permissions + default SYSADMIN
-- Password for sysadmin: sysadmin
-- ---------------------------------------------------------------------------

INSERT INTO `permission` (`id`, `group`, `name`, `display_name`, `display_weight`, `creation_date`)
VALUES
  (1, 'user', 'USER_ADMIN_VIEW', '查看系統用戶', 10, NOW()),
  (2, 'user', 'USER_ADMIN_MODIFY', '修改系統用戶', 20, NOW()),
  (3, 'system', 'BACKEND_LOGIN_AS', '後台登入為其他用戶', 30, NOW()),
  (4, 'system', 'FRONTEND_LOGIN_AS', '前台登入為其他用戶', 40, NOW()),
  (5, 'system', 'EMAIL_HISTORY_VIEW', '查看電郵紀錄', 50, NOW())
ON DUPLICATE KEY UPDATE
  `display_name` = VALUES(`display_name`),
  `display_weight` = VALUES(`display_weight`);

INSERT INTO `user` (`id`, `username`, `password`, `role`, `active`, `_deleted`, `creation_date`)
VALUES (
  1,
  'sysadmin',
  '$2y$10$SAj1kqp9YSyq3prsuMT0teSo4JUTNvXsnkUf7FDWsVYsQztAJ1IaS',
  'SYSADMIN',
  1,
  0,
  NOW()
)
ON DUPLICATE KEY UPDATE
  `password` = VALUES(`password`),
  `role` = VALUES(`role`),
  `active` = 1,
  `_deleted` = 0;
