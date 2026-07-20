ALTER TABLE `user`
ADD COLUMN `otp_code` VARCHAR(6) NULL AFTER `token`,
ADD COLUMN `otp_expires_at` DATETIME NULL AFTER `otp_code`;