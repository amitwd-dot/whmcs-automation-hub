-- WHMCS Automation Hub - Database Schema Installation Script
-- Author: Web Wave Digital (https://webwavedigital.co.in)

CREATE TABLE IF NOT EXISTS `mod_automationhub_rules` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `trigger_class` VARCHAR(100) NOT NULL,
  `action_class` VARCHAR(100) NOT NULL,
  `action_config` TEXT DEFAULT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `last_fired_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trigger_enabled` (`trigger_class`, `enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mod_automationhub_logs` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `rule_name` VARCHAR(255) NOT NULL,
  `trigger_class` VARCHAR(100) NOT NULL,
  `action_class` VARCHAR(100) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'success',
  `error_message` TEXT DEFAULT NULL,
  `payload` LONGTEXT DEFAULT NULL,
  `execution_time_ms` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rule_id` (`rule_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
