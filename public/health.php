<?php
/**
 * Production boot diagnostic — delete after deploy is stable.
 * https://unicapress.com/health.php
 */
header('Content-Type: text/plain; charset=UTF-8');

echo "UnicaPress boot diagnostic\n";
echo "==========================\n\n";

echo "PHP version: " . PHP_VERSION . "\n";
echo "mysqli loaded: " . (extension_loaded('mysqli') ? 'yes' : 'NO — enable in cPanel Select PHP Version') . "\n\n";

$config_dir = __DIR__ . '/config';
echo "config.php exists: " . (file_exists($config_dir . '/config.php') ? 'yes' : 'no') . "\n";
echo "production.config.php exists: " . (file_exists($config_dir . '/production.config.php') ? 'yes' : 'no') . "\n";
echo "development.config.php exists: " . (file_exists($config_dir . '/development.config.php') ? 'yes' : 'no') . "\n\n";

if (!file_exists($config_dir . '/production.config.php')) {
    echo "STOP: production.config.php missing.\n";
    exit;
}

echo "Loading production.config.php...\n";
try {
    require $config_dir . '/production.config.php';
    echo "  PLATFORM: " . (defined('PLATFORM') ? PLATFORM : 'NOT DEFINED') . "\n";
    echo "  PUBLIC_ROOT: " . (defined('PUBLIC_ROOT') ? PUBLIC_ROOT : 'NOT DEFINED') . "\n";
    echo "  LOG_PATH: " . (defined('LOG_PATH') ? LOG_PATH : 'NOT DEFINED') . "\n";
    echo "  SITE_NAME: " . (defined('SITE_NAME') ? SITE_NAME : 'NOT DEFINED') . "\n";
    echo "  SESSION_NAME: " . (defined('SESSION_NAME') ? SESSION_NAME : 'NOT DEFINED') . "\n";
    echo "  DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
    echo "  DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
} catch (Throwable $e) {
    echo "FAIL loading production.config.php: " . $e->getMessage() . "\n";
    exit;
}

echo "\nLoading auth.php...\n";
try {
    require $config_dir . '/auth.php';
    echo "  auth.php OK\n";
} catch (Throwable $e) {
    echo "FAIL auth.php: " . $e->getMessage() . "\n";
    exit;
}

echo "\nTesting database connection...\n";
if (!extension_loaded('mysqli')) {
    echo "SKIP — mysqli extension not loaded\n";
    exit;
}

try {
    $port = defined('DB_PORT') ? DB_PORT : 3306;
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
    if ($mysqli->connect_error) {
        echo "FAIL: " . $mysqli->connect_error . "\n";
    } else {
        echo "  Database connection OK\n";
        $mysqli->close();
    }
} catch (Throwable $e) {
    echo "FAIL mysqli: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
