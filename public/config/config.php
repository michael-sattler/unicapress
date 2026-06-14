<?php
/**
 * Main Configuration File
 * This file loads platform-specific configuration based on environment
 * 
 * At the top of every file in /public, include this using:
 * require_once(__DIR__ . '/../config/config.php');
 */

// Detect platform: APP_ENV (Docker / .htaccess SetEnv) or auto-detect from available config files
$platform = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null;

if (!$platform) {
    $has_development = file_exists(__DIR__ . '/development.config.php');
    $has_production = file_exists(__DIR__ . '/production.config.php');

    if ($has_production && !$has_development) {
        $platform = 'production';
    } else {
        $platform = 'development';
    }
}

// Load platform-specific configuration
$platform_config_file = __DIR__ . '/' . $platform . '.config.php';

// Define debug_log() function early so it can be used during config loading
if (!function_exists('debug_log')) {
    function debug_log($message, $context = []) {
        // Only log if LOG_PATH is defined (must be set by platform config)
        if (!defined('LOG_PATH')) {
            return; // Silently fail - log path must be defined by platform config per standards
        }
        
        // Validate that LOG_PATH is within PUBLIC_ROOT for shared hosting compatibility
        // This prevents accidentally routing logs outside the application directory
        if (defined('PUBLIC_ROOT')) {
            // Normalize paths: remove trailing slashes and ensure consistent directory separators
            $public_root = rtrim(str_replace('\\', '/', PUBLIC_ROOT), '/');
            $log_path_normalized = rtrim(str_replace('\\', '/', LOG_PATH), '/');
            
            // Ensure PUBLIC_ROOT ends with a path separator for proper checking
            // Check if LOG_PATH starts with PUBLIC_ROOT followed by '/' or is exactly PUBLIC_ROOT
            // This ensures logs stay within the application directory (critical for shared hosting)
            $public_root_with_sep = $public_root . '/';
            if ($log_path_normalized !== $public_root && strpos($log_path_normalized, $public_root_with_sep) !== 0) {
                // LOG_PATH is outside PUBLIC_ROOT - this violates shared hosting requirement!
                // Silently fail to prevent breaking the application, but log won't be written
                return;
            }
        }
        
        $log_dir = LOG_PATH;
        $log_file = $log_dir . '/debug.log';
        
        // Ensure log directory exists and is writable
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
            @mkdir($log_dir . '/sessions', 0755, true);
        }
        
        // Only try to write if directory exists and is writable
        if (is_dir($log_dir) && is_writable($log_dir)) {
            $timestamp = date('Y-m-d H:i:s');
            $context_str = !empty($context) ? ' ' . json_encode($context) : '';
            $log_message = "[{$timestamp}] {$message}{$context_str}" . PHP_EOL;
            
            @file_put_contents($log_file, $log_message, FILE_APPEND);
        }
        // Silently fail if we can't write logs - don't break the application
    }
}

if (file_exists($platform_config_file)) {
    require_once $platform_config_file;
} else {
    // Fallback to development if platform config doesn't exist
    $fallback_config = __DIR__ . '/development.config.php';
    if (file_exists($fallback_config)) {
        require_once $fallback_config;
        // Now that fallback config is loaded (which defines LOG_PATH), we can log the warning
        if ($platform !== 'development' && defined('LOG_PATH')) {
            debug_log("Warning: Platform config file not found: {$platform_config_file}. Falling back to development.");
        }
    } else {
        die("Error: No configuration file found. Copy public/config/template.config.php to development.config.php (local) or production.config.php (server).");
    }
}

// Set error reporting BEFORE any output (must be first)
if (defined('ERROR_REPORTING')) {
    error_reporting(ERROR_REPORTING ? E_ALL : 0);
} else {
    error_reporting(E_ALL); // Default to showing errors in development
}

if (defined('DISPLAY_ERRORS')) {
    ini_set('display_errors', DISPLAY_ERRORS ? '1' : '0');
} else {
    ini_set('display_errors', '1'); // Default to showing errors in development
}

// Load auth config FIRST (before any output) as it sets session ini settings
if (file_exists(__DIR__ . '/auth.php')) {
    require_once __DIR__ . '/auth.php';
}

// Load database config
if (file_exists(__DIR__ . '/database.php')) {
    require_once __DIR__ . '/database.php';
}

// debug_log() function is defined above (before platform config is loaded)
// This ensures it's available early, but it only works after LOG_PATH is defined by platform config
