<?php
/**
 * Template Configuration File
 * Copy this file to development.config.php, staging.config.php, or production.config.php
 * and update the values for your environment
 * 
 * DO NOT commit platform-specific config files to git
 */

// Platform identification
define('PLATFORM', 'development'); // development, staging, or production

// Database Configuration
define('DB_HOST', 'db'); // Use 'db' in Docker, 'localhost' or actual host elsewhere
define('DB_USER', 'unicapress_user');
define('DB_PASS', 'unicapress_password');
define('DB_NAME', 'unicapress');
define('DB_PORT', 3306);

// Application URLs
define('APP_URL', 'http://localhost:8080');
define('APP_API_URL', 'http://localhost:8080/api');

// Paths - IMPORTANT: All paths must be within PUBLIC_ROOT for shared hosting compatibility
// LOG_PATH MUST always be within PUBLIC_ROOT - never route logs outside the application
define('PUBLIC_ROOT', dirname(__DIR__)); // Directory where this config file's parent is located (/public)
define('PROJECT_ROOT', dirname(PUBLIC_ROOT)); // One level up from public (repository root) - for reference only
// LOG_PATH must be defined by platform config per standards - ALWAYS within PUBLIC_ROOT
define('LOG_PATH', PUBLIC_ROOT . '/logs'); // MUST be inside public directory for shared hosting (e.g., /home/username/public_html/logs)

// Session Configuration
define('SESSION_NAME', 'unicapress_session');
define('SESSION_LIFETIME', 1440); // 24 hours in minutes

// Security
define('API_TOKEN_SECRET', 'CHANGE_THIS_IN_PRODUCTION'); // Change this in production!

// Error Reporting (set to 0 in production)
define('ERROR_REPORTING', 1); // 1 for development, 0 for production
define('DISPLAY_ERRORS', 1); // 1 for development, 0 for production

// Timezone
date_default_timezone_set('UTC');
