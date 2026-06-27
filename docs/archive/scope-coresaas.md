*SUPERSEDED — see /docs/scope-marketing-shell.md for the current scope and workplan (the "Shell": marketing site + staff admin/ops console). This file is kept only as a reference for table definitions; not all tables below are in scope (e.g. `users` and `promptlibrary` are dropped from the Shell — see scope-marketing-shell.md §3 and §5).*

*SCOPE FOR SETTING UP A CORE SAAS APPLICATION FRAMEWORK WITHOUT SPECIFIC BUSINESS LOGIC*
[ ] Create docker container and test (See /docs/specs-platforms.md)
[ ] Create core database entities
  - users
  - adminusers
  - contentlibrary
  - emaillibrary
  - promptlibrary
  - eventlogs
  - eventlogtypes
  - supportmessages
[ ] Create new git repo
[ ] Create config system
[ ] Create placeholder files for major admin interfaces, includes, and files
[ ] Test admin functionality
[ ] Create placeholder files for major interfaces, includes, and files
[ ] Test app functionality

[ ] Create placeholder files for API
[ ] Test app health
[ ] Test app-tester from admin to be sure API responses are received

[ ] Create registration v1
[ ] Create authentication v1
[ ] Create contentlibrary v1
[ ] Create emaillibrary v1
[ ] Create imagegallery v1
[ ] Create SupportMessaging v1
[ ] Create PromptLibrary v1
[ ] Create FeatureFlags v1

*** WHEN COMPLETE WE'LL MOVE ON TO BUSINESS-LOGIC SPECIFICS

*////////////// Major Subsystems //////////////*
- **Registration**
  - Registration includes email validation and uniqueness checks, hashed password storage, too many attempts limits
  - Password complexity verification
  - OAuth [LATER]
- **Auth**
  - Login sets session variables on success
  - Password reset
  - 2FA via SMS [LATER]
  - 2FA via Authenticator [LATER]
  - users table
    CREATE TABLE IF NOT EXISTS `users` (
    `user_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_email` varchar(255) NOT NULL,
    `user_password_hash` varchar(255) NOT NULL,
    `user_firstname` varchar(128) DEFAULT NULL,
    `user_lastname` varchar(128) DEFAULT NULL,
    `user_active` tinyint(4) NOT NULL DEFAULT 1,
    `user_email_verified` tinyint(4) NOT NULL DEFAULT 0,
    `user_email_verification_token` varchar(64) DEFAULT NULL,
    `user_password_reset_token` varchar(64) DEFAULT NULL,
    `user_password_reset_expires` bigint(20) DEFAULT NULL,
    `user_last_login` bigint(20) DEFAULT NULL,
    `user_login_attempts` int(11) DEFAULT 0,
    `user_locked_until` bigint(20) DEFAULT NULL,
    `user_datecreated` bigint(20) NOT NULL,
    `user_dateupdated` bigint(20) NOT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `idx_user_email` (`user_email`),
    KEY `idx_user_active` (`user_active`),
    KEY `idx_user_email_verified` (`user_email_verified`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Regular application users';
  - adminusers table
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Administrative users with access to admin panel';
- **ContentLibrary** A database-manageable repository of copy that displays inline or in popups on public pages so non-engineers can manage it without changing code. Inline copy should be called by id using a universal function displayContentLibrary([content_id]). Admin tool to manage the text of all content. Should include standard embeddable variables like %%VARIABLE%% to be completed before text is returned.
  - contentlibrary table
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='For helptext and general content management';

- **EmailLibrary** All email messages sent from the system should go through a universal sendEmailFromLibrary() function rather than being embedded in the application itself. Admin tools to manage the text of messages. Should include standard embeddable variables like %%VARIABLE%% to be completed before message is sent.
  - emaillibrary table
    CREATE TABLE IF NOT EXISTS `emaillibrary` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Templated email messages for system use';

- **Event logging** Relevant user- and machine-taken activity should be logged to the database using the EventLog system. This allows us to retrieve activity history (last login, last upload) with a simple database query if needed
  - eventlogtypes table
    CREATE TABLE IF NOT EXISTS `eventlogtypes` (
    `eventtype_id` int(11) NOT NULL AUTO_INCREMENT,
    `eventtype_name` varchar(128) NOT NULL,
    `eventtype_description` text DEFAULT NULL,
    `eventtype_active` tinyint(4) NOT NULL DEFAULT 1,
    PRIMARY KEY (`eventtype_id`),
    UNIQUE KEY `idx_eventtype_name` (`eventtype_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Types of events that can be logged';
  - eventlogs table
    CREATE TABLE IF NOT EXISTS `eventlogs` (
    `eventlog_id` int(11) NOT NULL AUTO_INCREMENT,
    `event_typeid` int(11) NOT NULL,
    `user_id` int(11) DEFAULT 0,
    `adminuser_id` int(11) DEFAULT 0,
    `event_source` varchar(255) NOT NULL,
    `event_datecreated` bigint(20) NOT NULL,
    PRIMARY KEY (`eventlog_id`),
    KEY `idx_event_typeid` (`event_typeid`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_adminuser_id` (`adminuser_id`),
    KEY `idx_event_datecreated` (`event_datecreated`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Log of system events and user activity';

- **ImageGallery** Images associated with entities should be stored in a system containing an images table, an imagegallery table (that associates an image with an entity by type), and retrieved through a universal displayImageFromGallery([entity],[entityid]) function in place of the URI. Admin tool to manage images and upload centralized images directly
  - images table
        CREATE TABLE IF NOT EXISTS `images` (
        `image_id` int(11) NOT NULL AUTO_INCREMENT,
        `image_filename` varchar(255) NOT NULL,
        `image_aspectratio` varchar(255) DEFAULT NULL,
        `image_path` varchar(255) NOT NULL,
        `image_backupfilename` varchar(255) DEFAULT NULL,
        `image_contentbase64` text DEFAULT NULL COMMENT 'Temporary storage only - for upload/processing',
        `image_source` varchar(64) DEFAULT NULL COMMENT 'userupload, staffupload, generated, or other sources',
        `image_datecreated` bigint(20) NOT NULL,
        `image_dateupdated` bigint(20) NOT NULL,
        PRIMARY KEY (`image_id`),
        KEY `idx_image_path` (`image_path`),
        KEY `idx_image_source` (`image_source`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

   - imagegallery table
        CREATE TABLE IF NOT EXISTS `imagegallery` (
        `imagegallery_id` int(11) NOT NULL AUTO_INCREMENT,
        `image_id` int(11) NOT NULL,
        `entity` varchar(255) NOT NULL,
        `entity_id` int(11) NOT NULL,
        `imagegallery_format` enum('unknown','jpg','png','gif','webp') DEFAULT 'unknown' COMMENT 'format of stored image',
        `imagegallery_type` varchar(255) NOT NULL COMMENT 'banner, square, headshot',
        `imagegallery_ordinal` int(11) NOT NULL,
        `imagegallery_datecreated` bigint(20) NOT NULL,
        PRIMARY KEY (`imagegallery_id`),
        KEY `idx_image_id` (`image_id`),
        KEY `idx_entity` (`entity`, `entity_id`),
        KEY `idx_imagegallery_type` (`imagegallery_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    - user-specific images should be stored as files in /public/app/images/uploads/[userid]-[uniqueimageid]-[unixdatestamp].[filetype]; base64 should only be temporary
    - universal images for business entities should be stored in public/app/images/universal/[rationalname]-[unixdatestamp].[filetype]
    - Note: image_contentbase64 field is for temporary storage during upload/processing only

- **SupportMessaging** A simple in-house chat system for live message exchanges with support agents. Admin tool to manage requests from users.
  - supportmessages table
        CREATE TABLE IF NOT EXISTS `supportmessages` (
        `supportmessage_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL COMMENT 'null for public messages from non-logged in users',
        `anonymoususer_token` varchar(32) DEFAULT NULL COMMENT 'if user is not logged in, this is a random string that gets assigned for a given session. we could also store this as a cookie to let it persist between reloads',
        `supportmessage_text` varchar(512) NOT NULL,
        `supportmessage_author` enum('user','staff') NOT NULL,
        `supportmessage_status` enum('new','responded') DEFAULT 'new' COMMENT 'either new or null, which means responded',
        `supportmessage_sentfrom` varchar(256) DEFAULT NULL COMMENT 'url when the user sent the message',
        `supportmessage_created` int(11) DEFAULT NULL COMMENT 'unix datestamp when the message was written',
        `supportmessage_updated` int(11) DEFAULT NULL COMMENT 'unix datestamp when the message was updated',
        PRIMARY KEY (`supportmessage_id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_anonymoususer_token` (`anonymoususer_token`),
        KEY `idx_supportmessage_status` (`supportmessage_status`),
        KEY `idx_supportmessage_created` (`supportmessage_created`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

- **PromptLibrary** A way for admins to manage the text of prompts sent to AI endpoints without having to modify code. prompts should be retrievable by code using getPromptFromLibrary([prompt_id]). Admin tool to manage the text of these prompts for rapid iteration and improvement. Should include standard embeddable variables like %%VARIABLE%% to be completed before prompt is returned.
  - promptlibrary table
        CREATE TABLE IF NOT EXISTS `promptlibrary` (
        `prompt_id` int(11) NOT NULL AUTO_INCREMENT,
        `entity` varchar(255) NOT NULL,
        `prompt_type` varchar(255) NOT NULL,
        `prompt_title` varchar(255) NOT NULL,
        `prompt_body` text NOT NULL,
        `prompt_datecreated` bigint(20) NOT NULL,
        `prompt_dateupdated` bigint(20) NOT NULL,
        PRIMARY KEY (`prompt_id`),
        KEY `idx_entity` (`entity`),
        KEY `idx_prompt_type` (`prompt_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

- **FeatureFlags** A way to protect a page or section from users without a specific feature flag
 - If featureflagreuired([featureflag_id],[redirectur]) is present at the top of the page, redirect if user doesn't have that feature flag. Use $_SESSION['featureflags'][[featureflag_id]] if $_SESSION['featureflags'] exists; otherwise 
  query DB
  - Use (isset(hasfeatureflag([featureflag_id])) == TRUE) as an if-then to display page elements that are only visible to those with the flag
  - featureflags table
    CREATE TABLE IF NOT EXISTS `featureflags` (
    `featureflag_id` int(11) NOT NULL AUTO_INCREMENT,
    `featureflag_name` varchar(128) NOT NULL,
    `featureflag_description` text DEFAULT NULL,
    `featureflag_active` tinyint(4) NOT NULL DEFAULT 1,
    `featureflag_datecreated` bigint(20) NOT NULL,
    PRIMARY KEY (`featureflag_id`),
    UNIQUE KEY `idx_featureflag_name` (`featureflag_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Feature flags for gradual feature rollout';
  - user_featureflags table (many-to-many relationship)
    CREATE TABLE IF NOT EXISTS `user_featureflags` (
    `user_featureflag_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `featureflag_id` int(11) NOT NULL,
    `featureflag_datecreated` bigint(20) NOT NULL,
    PRIMARY KEY (`user_featureflag_id`),
    UNIQUE KEY `idx_user_featureflag` (`user_id`, `featureflag_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_featureflag_id` (`featureflag_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Association between users and their feature flags';

  - Regenerate $_SESSION['featureflags'] on login

- **Billing System** TBD

