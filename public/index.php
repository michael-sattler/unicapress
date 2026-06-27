<?php
/**
 * Main Application Router
 * Routes all requests to appropriate files based on URL path
 */

ob_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/includes/functions-universal.php';

$request_uri = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($request_uri);
$path = isset($parsed_url['path']) ? $parsed_url['path'] : '/';

$route_path = rtrim($path, '/');
if ($route_path === '') {
    $route_path = '/';
}

$routes_file = __DIR__ . '/app/routes.php';
$routes = file_exists($routes_file) ? require $routes_file : [];

if (empty($routes)) {
    $routes = [
        '/' => 'app/home.php',
        '/home' => 'app/home.php',
    ];
}

if (isset($routes[$route_path])) {
    $target_file = $routes[$route_path];

    if (is_string($target_file)) {
        $file_path = __DIR__ . '/' . $target_file;

        if (file_exists($file_path)) {
            require $file_path;
            exit;
        }
    } elseif (is_array($target_file) && isset($target_file['file'])) {
        $file_path = __DIR__ . '/' . $target_file['file'];

        if (file_exists($file_path)) {
            require $file_path;
            exit;
        }
    }
}

http_response_code(404);
$pagetitle = 'Page Not Found';
ob_start();
?>
<div class="container py-5 text-center">
    <h1 class="display-4"><i class="fa-solid fa-map-signs text-muted"></i> 404</h1>
    <p class="lead">The page you requested could not be found.</p>
    <a href="/" class="btn btn-primary"><i class="fa-solid fa-house me-2"></i>Return home</a>
</div>
<?php
$page_content = ob_get_clean();
require __DIR__ . '/app/elements/layout.php';
ob_end_flush();
