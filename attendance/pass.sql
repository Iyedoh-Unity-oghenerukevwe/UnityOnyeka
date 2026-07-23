ALTER TABLE `user`
ADD COLUMN `reset_otp` VARCHAR(6),
ADD COLUMN `reset_otp_expires_at` DATETIME;