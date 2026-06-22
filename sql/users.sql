CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT(20) UNSIGNED NOT NULL,
  `telegram_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `name` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(13) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`telegram_id`, `phone`),
  UNIQUE KEY (`phone`),
  INDEX `xid` (`id`),
  INDEX `xtelegram_id` (`telegram_id`),
  INDEX `xname` (`name`),
  INDEX `xphone` (`phone`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;