-- 06-06-2017  -- for saving hash password in db
ALTER TABLE `userpanel` ADD `password_hash` CHAR(76) NULL DEFAULT NULL AFTER `password`;

-- create table for user tocken
CREATE TABLE `vivekbindra`.`tbl_user_tokens` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `user_type` ENUM('1','2') NOT NULL DEFAULT '1' COMMENT '1=> user, 2=> admin' , `token` CHAR(76) NOT NULL , `created_at` DATETIME NOT NULL , `updated_at` DATETIME NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM