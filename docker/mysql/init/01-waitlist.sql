CREATE TABLE IF NOT EXISTS `waitlist` (
  `waitlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `waitlist_name` varchar(255) NOT NULL,
  `waitlist_email` varchar(255) NOT NULL,
  `waitlist_source` varchar(64) DEFAULT 'home' COMMENT 'which page/form captured this',
  `waitlist_active` tinyint(4) NOT NULL DEFAULT 1,
  `waitlist_datecreated` bigint(20) NOT NULL,
  PRIMARY KEY (`waitlist_id`),
  UNIQUE KEY `idx_waitlist_email` (`waitlist_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Worldbuilder waitlist signups';
