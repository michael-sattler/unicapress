<?php
/**
 * Docker Test Page
 * Use this to verify your Docker setup is working correctly
 * Access at: http://localhost:8080/test-docker.php
 */

// Start output buffering to prevent headers already sent errors
ob_start();

// Load configuration (must be before any output)
require_once __DIR__ . '/config/config.php';

// Now set headers
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Docker Setup Test - UnicaPress</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐳 Docker Setup Test</h1>
        
        <h2>Configuration</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Platform</td>
                <td><?php echo defined('PLATFORM') ? PLATFORM : 'Not defined'; ?></td>
                <td><?php echo defined('PLATFORM') ? '<span class="success">✓</span>' : '<span class="error">✗</span>'; ?></td>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?php echo PHP_VERSION; ?></td>
                <td><span class="success">✓</span></td>
            </tr>
            <tr>
                <td>Project Root</td>
                <td><?php echo defined('PROJECT_ROOT') ? PROJECT_ROOT : 'Not defined'; ?></td>
                <td><?php echo defined('PROJECT_ROOT') ? '<span class="success">✓</span>' : '<span class="error">✗</span>'; ?></td>
            </tr>
            <tr>
                <td>Log Path</td>
                <td><?php echo defined('LOG_PATH') ? LOG_PATH : 'Not defined'; ?></td>
                <td><?php echo defined('LOG_PATH') ? '<span class="success">✓</span>' : '<span class="error">✗</span>'; ?></td>
            </tr>
            <tr>
                <td>App URL</td>
                <td><?php echo defined('APP_URL') ? APP_URL : 'Not defined'; ?></td>
                <td><?php echo defined('APP_URL') ? '<span class="success">✓</span>' : '<span class="error">✗</span>'; ?></td>
            </tr>
        </table>

        <h2>PHP Extensions</h2>
        <table>
            <tr>
                <th>Extension</th>
                <th>Status</th>
            </tr>
            <?php
            $required_extensions = ['mysqli', 'pdo_mysql', 'gd', 'zip', 'intl', 'mbstring', 'opcache', 'session'];
            foreach ($required_extensions as $ext) {
                $loaded = extension_loaded($ext);
                echo "<tr>";
                echo "<td>$ext</td>";
                echo "<td>" . ($loaded ? '<span class="success">✓ Loaded</span>' : '<span class="error">✗ Not loaded</span>') . "</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <h2>Database Connection</h2>
        <?php
        if (isset($mysqli) && $mysqli instanceof mysqli) {
            if ($mysqli->ping()) {
                echo '<div class="info">';
                echo '<strong class="success">✓ Database connection successful!</strong><br>';
                echo 'Server Info: ' . $mysqli->server_info . '<br>';
                echo 'Host: ' . (defined('DB_HOST') ? DB_HOST : 'N/A') . '<br>';
                echo 'Database: ' . (defined('DB_NAME') ? DB_NAME : 'N/A') . '<br>';
                echo '</div>';
            } else {
                echo '<div class="info">';
                echo '<strong class="error">✗ Database connection failed - connection is not alive</strong>';
                echo '</div>';
            }
        } else {
            echo '<div class="info">';
            echo '<strong class="error">✗ Database connection not established</strong><br>';
            if (!defined('DB_HOST')) {
                echo 'Database configuration constants are not defined.';
            } else {
                echo 'Check your database configuration in development.config.php';
            }
            echo '</div>';
        }
        ?>

        <h2>File Permissions</h2>
        <?php if (defined('LOG_PATH') && defined('PUBLIC_ROOT')): 
            // Validate that LOG_PATH is within PUBLIC_ROOT
            $log_path_normalized = rtrim(str_replace('\\', '/', LOG_PATH), '/');
            $public_root_normalized = rtrim(str_replace('\\', '/', PUBLIC_ROOT), '/');
            $is_within_public = ($log_path_normalized === $public_root_normalized || strpos($log_path_normalized, $public_root_normalized . '/') === 0);
        ?>
            <div class="info">
                <strong>LOG_PATH Configuration Check:</strong><br>
                <?php if ($is_within_public): ?>
                    <span class="success">✓ LOG_PATH is correctly configured within PUBLIC_ROOT (required for shared hosting)</span><br>
                <?php else: ?>
                    <span class="error">✗ LOG_PATH is OUTSIDE PUBLIC_ROOT - this will cause problems on shared hosting!</span><br>
                <?php endif; ?>
                <strong>PUBLIC_ROOT:</strong> <code><?php echo PUBLIC_ROOT; ?></code><br>
                <strong>LOG_PATH:</strong> <code><?php echo LOG_PATH; ?></code><br>
                <small>In Docker, /var/www/html is the public directory, so /var/www/html/logs is correct (inside public).<br>
                On host: <?php echo str_replace('/var/www/html', './public', LOG_PATH); ?><br>
                In production (cPanel): logs will be inside public_html directory</small>
            </div>
        <?php endif; ?>
        <table>
            <tr>
                <th>Path</th>
                <th>Status</th>
            </tr>
            <?php
            if (defined('LOG_PATH')) {
                $paths_to_check = [
                    'Logs Directory (LOG_PATH)' => LOG_PATH,
                    'Debug Log File' => LOG_PATH . '/debug.log',
                    'Sessions Directory' => LOG_PATH . '/sessions',
                ];
                
                foreach ($paths_to_check as $label => $path) {
                    if (strpos($label, 'File') !== false) {
                        // For files, check if directory is writable (file may not exist yet)
                        $dir = dirname($path);
                        $exists = is_dir($dir);
                        $writable = $exists && is_writable($dir);
                        $file_exists = file_exists($path);
                        $status = $exists ? ($writable ? ($file_exists ? '<span class="success">✓ Directory writable, file exists</span>' : '<span class="success">✓ Directory writable (file will be created)</span>') : '<span class="error">✗ Directory not writable</span>') : '<span class="error">✗ Directory does not exist</span>';
                    } else {
                        // For directories
                        $exists = is_dir($path);
                        $writable = $exists && is_writable($path);
                        $status = $exists ? ($writable ? '<span class="success">✓ Exists & Writable</span>' : '<span class="error">✗ Exists but not writable</span>') : '<span class="error">✗ Does not exist</span>';
                    }
                    echo "<tr><td>$label<br><small style='color:#666;'>$path</small></td><td>$status</td></tr>";
                }
            } else {
                echo "<tr><td colspan='2'><span class='error'>✗ LOG_PATH not defined in platform config</span></td></tr>";
            }
            ?>
        </table>

        <div class="info">
            <strong>Next Steps:</strong><br>
            1. If all checks pass, your Docker setup is ready!<br>
            2. You can delete this test file when done: <code>public/test-docker.php</code><br>
            3. Start building your application in the <code>public/app</code> directory
        </div>
    </div>
</body>
</html>
<?php
// Flush output buffer
ob_end_flush();