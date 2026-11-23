-- Password recovery: a recovery email and a hashed recovery code per admin,
-- and single-use, expiring reset links. Additive only; safe to run on a live
-- database before deploying the code that uses it.
ALTER TABLE `users`
  ADD COLUMN `email` varchar(255) DEFAULT NULL,
  ADD COLUMN `recovery_code` varchar(255) DEFAULT NULL;

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `username_created` (`username`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
