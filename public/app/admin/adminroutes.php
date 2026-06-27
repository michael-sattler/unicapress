<?php
/**
 * Routes Configuration
 * Maps URL paths to their corresponding files
 * Format: 'path' => 'file_path_relative_to_app_directory'
 */

return [
    '/' => 'admin/index.php',
    '/home' => 'admin/index.php',
    '/api-tester' => 'admin/api-tester.php',

    // Add links to API diagnostics and other admin tools here
    // '/api/diagnostic-apihealth.php' => ...
    // '/api/diagnostic-dbhealth.php' => ...
];
