<?php
// Include project config which sets up error logging
require_once __DIR__ . "/../config/config.php";

// Test debug_log after config is loaded
//debug_log("/public/api/config-api/config.php loaded");

// Define public endpoints (no origin check required)
define('PUBLIC_ENDPOINTS', [
    '/health',
    '/index',
    '/'  // Root path
]);



/**
 * Check if request comes from an allowed origin
 * For testing, always return true
 */
function checkOrigin() {
    return true;
}

/**
 * Check authentication for the current request
 * For testing, always return true
 */
function checkAuthentication() {
    return true;
}

// Test logging is working
//debug_log('/public/api/config-api/config.php API config loaded ok');