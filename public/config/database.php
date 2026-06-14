<?php
/**
 * Database Configuration
 * Establishes the global $mysqli connection
 */

// Only create connection if constants are defined and connection doesn't exist
if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    if (!isset($mysqli) || !$mysqli instanceof mysqli) {
        $db_port = defined('DB_PORT') ? DB_PORT : 3306;
        
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $db_port);
        
        if ($mysqli->connect_error) {
            debug_log("Database connection error: " . $mysqli->connect_error);
            die("Database connection failed. Please check your configuration.");
        }
        
        // Set charset to utf8mb4
        $mysqli->set_charset("utf8mb4");
        
        debug_log("Database connection established successfully");
    }
} else {
    debug_log("Database configuration constants not defined. Please check your platform config file.");
}
