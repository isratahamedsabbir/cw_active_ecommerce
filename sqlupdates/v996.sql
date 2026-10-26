ALTER TABLE `coupons` ADD COLUMN `delivery_free` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;

UPDATE `business_settings` SET `value` = '9.9.6' WHERE `business_settings`.`type` = 'current_version';

COMMIT;
