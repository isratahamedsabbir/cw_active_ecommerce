ALTER TABLE `orders` ADD COLUMN `admin_discount` DECIMAL(20,2) NOT NULL DEFAULT 0 AFTER `coupon_discount`;

UPDATE `business_settings` SET `value` = '9.9.7' WHERE `business_settings`.`type` = 'current_version';

COMMIT;
