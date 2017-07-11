

-- ------------------------------ --
-- This SQL file contains basic   --
-- structure of capture table     --
-- ------------------------------ --

SET NAMES utf8mb4;

CREATE TABLE request_capture (
  `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `route` VARCHAR(100) COMMENT "controller_id/action_id",
  `client_ip` VARCHAR(20) COMMENT "IP adress of the request client",
  `request_headers` TEXT COMMENT "Headers accepted",
  `query` TEXT COMMENT "$_GET",
  `form` TEXT COMMENT "$_POST, data received from HTTP entity body",
  `session` TEXT COMMENT "$_SESSION",
  `file` TEXT COMMENT "$_FILES",
  `response_body` TEXT COMMENT "HTTP entity body send from server",
  `response_headers` TEXT COMMENT "Headers sent",
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE MyISAM DEFAULT CHAR SET utf8mb4 COLLATE utf8mb4_general_ci