<?php
/**
 * Database Configuration
 * Establishes the global $mysqli connection
 */

// Only create connection if constants are defined and connection doesn't exist
if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    if (!extension_loaded('mysqli')) {
        debug_log('mysqli extension is not loaded — enable it in cPanel Select PHP Version');
    } elseif (!isset($mysqli) || !$mysqli instanceof mysqli) {
        $db_port = defined('DB_PORT') ? DB_PORT : 3306;

        try {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $db_port);

            if ($mysqli->connect_error) {
                debug_log('Database connection error: ' . $mysqli->connect_error);
                $mysqli = null;
            } else {
                $mysqli->set_charset('utf8mb4');
                debug_log('Database connection established successfully');
            }
        } catch (Throwable $e) {
            debug_log('Database connection exception: ' . $e->getMessage());
            $mysqli = null;
        }
    }
} else {
    debug_log('Database configuration constants not defined. Please check your platform config file.');
}
