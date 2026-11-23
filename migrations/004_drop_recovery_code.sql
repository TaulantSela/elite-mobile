-- Recovery codes are gone: password resets go through the account's email
-- (or scripts/set-admin-password.php). Run this AFTER deploying the code that
-- stops reading the column.
ALTER TABLE `users` DROP COLUMN `recovery_code`;
