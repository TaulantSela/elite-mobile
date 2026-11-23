-- Every admin account gets a required, unique email that is verified before
-- it changes; reset links can also be invites.
-- Needs every existing account to have an email already (production does).
ALTER TABLE `users`
  ADD COLUMN `email_verified_at` datetime DEFAULT NULL;

UPDATE `users` SET `email_verified_at` = UTC_TIMESTAMP() WHERE `email` IS NOT NULL AND `email` <> '';

ALTER TABLE `users`
  MODIFY `email` varchar(255) NOT NULL,
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `password_resets`
  ADD COLUMN `purpose` varchar(10) NOT NULL DEFAULT 'reset';

CREATE TABLE `email_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `new_email` varchar(255) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `username_created` (`username`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
