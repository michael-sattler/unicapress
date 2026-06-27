-- Shell core schema (docs/scope-marketing-shell.md §5)

CREATE TABLE IF NOT EXISTS `adminusers` (
  `adminuser_id` int(11) NOT NULL AUTO_INCREMENT,
  `adminuser_email` varchar(255) NOT NULL,
  `adminuser_password_hash` varchar(255) NOT NULL,
  `adminuser_firstname` varchar(128) DEFAULT NULL,
  `adminuser_lastname` varchar(128) DEFAULT NULL,
  `adminuser_role` varchar(64) DEFAULT 'staff',
  `adminuser_active` tinyint(4) NOT NULL DEFAULT 1,
  `adminuser_last_login` bigint(20) DEFAULT NULL,
  `adminuser_login_attempts` int(11) DEFAULT 0,
  `adminuser_locked_until` bigint(20) DEFAULT NULL,
  `adminuser_datecreated` bigint(20) NOT NULL,
  `adminuser_dateupdated` bigint(20) NOT NULL,
  PRIMARY KEY (`adminuser_id`),
  UNIQUE KEY `idx_adminuser_email` (`adminuser_email`),
  KEY `idx_adminuser_active` (`adminuser_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Staff/admin accounts';

CREATE TABLE IF NOT EXISTS `content_library` (
  `content_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_name` varchar(128) DEFAULT NULL,
  `content_text` text DEFAULT NULL,
  `content_location` varchar(64) DEFAULT NULL,
  `content_type` varchar(32) DEFAULT NULL,
  `content_active` tinyint(4) NOT NULL DEFAULT 0,
  `content_datecreated` bigint(20) NOT NULL,
  `content_dateupdated` bigint(20) NOT NULL,
  PRIMARY KEY (`content_id`),
  KEY `idx_content_location` (`content_location`),
  KEY `idx_content_active` (`content_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Marketing copy, %%VARIABLE%% interpolated';

CREATE TABLE IF NOT EXISTS `email_library` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_name` varchar(128) NOT NULL,
  `email_subject` varchar(255) DEFAULT NULL,
  `email_body` text NOT NULL,
  `email_type` varchar(64) DEFAULT NULL,
  `email_active` tinyint(4) NOT NULL DEFAULT 1,
  `email_datecreated` bigint(20) NOT NULL,
  `email_dateupdated` bigint(20) NOT NULL,
  PRIMARY KEY (`email_id`),
  KEY `idx_email_name` (`email_name`),
  KEY `idx_email_active` (`email_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Templated outbound email';

CREATE TABLE IF NOT EXISTS `eventlogtypes` (
  `eventtype_id` int(11) NOT NULL AUTO_INCREMENT,
  `eventtype_name` varchar(128) NOT NULL,
  `eventtype_description` text DEFAULT NULL,
  `eventtype_active` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`eventtype_id`),
  UNIQUE KEY `idx_eventtype_name` (`eventtype_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catalog of auditable event codes';

CREATE TABLE IF NOT EXISTS `eventlogs` (
  `eventlog_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_typeid` int(11) NOT NULL,
  `adminuser_id` int(11) DEFAULT 0,
  `event_source` varchar(255) NOT NULL,
  `event_datecreated` bigint(20) NOT NULL,
  PRIMARY KEY (`eventlog_id`),
  KEY `idx_event_typeid` (`event_typeid`),
  KEY `idx_adminuser_id` (`adminuser_id`),
  KEY `idx_event_datecreated` (`event_datecreated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Append-only staff/system event log';

CREATE TABLE IF NOT EXISTS `featureflags` (
  `featureflag_id` int(11) NOT NULL AUTO_INCREMENT,
  `featureflag_name` varchar(128) NOT NULL,
  `featureflag_description` text DEFAULT NULL,
  `featureflag_active` tinyint(4) NOT NULL DEFAULT 1,
  `featureflag_datecreated` bigint(20) NOT NULL,
  PRIMARY KEY (`featureflag_id`),
  UNIQUE KEY `idx_featureflag_name` (`featureflag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Staff-only feature gating';

CREATE TABLE IF NOT EXISTS `adminuser_featureflags` (
  `adminuser_featureflag_id` int(11) NOT NULL AUTO_INCREMENT,
  `adminuser_id` int(11) NOT NULL,
  `featureflag_id` int(11) NOT NULL,
  `featureflag_datecreated` bigint(20) NOT NULL,
  PRIMARY KEY (`adminuser_featureflag_id`),
  UNIQUE KEY `idx_adminuser_featureflag` (`adminuser_id`, `featureflag_id`),
  KEY `idx_adminuser_id` (`adminuser_id`),
  KEY `idx_featureflag_id` (`featureflag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Association between staff and their feature flags';

CREATE TABLE IF NOT EXISTS `supportmessages` (
  `supportmessage_id` int(11) NOT NULL AUTO_INCREMENT,
  `anonymoususer_token` varchar(32) DEFAULT NULL COMMENT 'random token for an unauthenticated contact-form sender',
  `supportmessage_text` varchar(512) NOT NULL,
  `supportmessage_author` enum('user','staff') NOT NULL,
  `supportmessage_status` enum('new','responded') DEFAULT 'new',
  `supportmessage_sentfrom` varchar(256) DEFAULT NULL COMMENT 'url when the message was written',
  `supportmessage_created` int(11) DEFAULT NULL,
  `supportmessage_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`supportmessage_id`),
  KEY `idx_anonymoususer_token` (`anonymoususer_token`),
  KEY `idx_supportmessage_status` (`supportmessage_status`),
  KEY `idx_supportmessage_created` (`supportmessage_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Public contact-form inbox';

CREATE TABLE IF NOT EXISTS `images` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `image_filename` varchar(255) NOT NULL,
  `image_aspectratio` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_backupfilename` varchar(255) DEFAULT NULL,
  `image_contentbase64` text DEFAULT NULL COMMENT 'Temporary storage only - for upload/processing',
  `image_source` varchar(64) DEFAULT NULL COMMENT 'staffupload, generated, or other sources',
  `image_datecreated` bigint(20) NOT NULL,
  `image_dateupdated` bigint(20) NOT NULL,
  PRIMARY KEY (`image_id`),
  KEY `idx_image_path` (`image_path`),
  KEY `idx_image_source` (`image_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Marketing/admin asset library';

CREATE TABLE IF NOT EXISTS `imagegallery` (
  `imagegallery_id` int(11) NOT NULL AUTO_INCREMENT,
  `image_id` int(11) NOT NULL,
  `entity` varchar(255) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `imagegallery_format` enum('unknown','jpg','png','gif','webp') DEFAULT 'unknown',
  `imagegallery_type` varchar(255) NOT NULL COMMENT 'banner, square, headshot',
  `imagegallery_ordinal` int(11) NOT NULL,
  `imagegallery_datecreated` bigint(20) NOT NULL,
  PRIMARY KEY (`imagegallery_id`),
  KEY `idx_image_id` (`image_id`),
  KEY `idx_entity` (`entity`, `entity_id`),
  KEY `idx_imagegallery_type` (`imagegallery_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Associates an image with an entity by type';
