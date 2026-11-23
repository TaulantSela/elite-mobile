-- Sign-in throttling: failed attempts per username and per hashed client IP.
-- Additive only; safe to run on a live database before deploying the code that uses it.
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_hash` char(64) NOT NULL,
  `attempted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username_time` (`username`, `attempted_at`),
  KEY `ip_time` (`ip_hash`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
