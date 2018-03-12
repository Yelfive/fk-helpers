

-- ------------------------------ --
-- This SQL file contains basic   --
-- structure of capture table     --
-- ------------------------------ --

SET NAMES utf8mb4;

CREATE TABLE `request_capture_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `route` varchar(100) DEFAULT NULL COMMENT 'controller_id/action_id',
  `client_ip` varchar(20) DEFAULT NULL COMMENT 'IP adress of the request client',
  `request_header` text COMMENT 'Headers accepted',
  `query` text COMMENT '$_GET',
  `form` text COMMENT '$_POST, data received from HTTP entity body',
  `request_session` text COMMENT '$_SESSION',
  `response_session` text COMMENT '$_SESSION',
  `file` text COMMENT '$_FILES',
  `response_status` VARCHAR(100) DEFAULT  '',
  `response_header` text COMMENT 'Headers sent',
  `response_body` text COMMENT 'HTTP entity body send from server',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `request_token` varchar(50) DEFAULT '',
  `created_by` int(10) unsigned DEFAULT '0',
  `creator_nickname` varchar(255) DEFAULT '',
  `app_version` varchar(50) DEFAULT '',
  `user_agent` varchar(50) DEFAULT '',
  `response_code` varchar(50) DEFAULT '',
  `time_elapsed_ms` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_general_ci

