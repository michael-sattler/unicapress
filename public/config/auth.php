<?php
/**
 * Authentication Configuration
 * Defines authentication-related constants and functions
 * IMPORTANT: This file must be included BEFORE any output is sent to the browser
 */

// Session configuration - must be set before session_start() or any output
if (defined('SESSION_NAME') && session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', SESSION_NAME);
}

if (defined('SESSION_LIFETIME') && session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME * 60); // Convert minutes to seconds
}

// Password hashing options
define('PASSWORD_ALGORITHM', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);

// Login attempt limits
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_DURATION', 900); // 15 minutes in seconds

// Session cookie settings (for development) - must be set before session_start()
if (defined('APP_URL') && session_status() === PHP_SESSION_NONE) {
    $domain = parse_url(APP_URL, PHP_URL_HOST);
    $secure = (parse_url(APP_URL, PHP_URL_SCHEME) === 'https');
    
    ini_set('session.cookie_domain', $domain);
    ini_set('session.cookie_secure', $secure ? '1' : '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
}
