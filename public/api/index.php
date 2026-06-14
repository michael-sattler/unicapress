<?php
// Handle CORS for ALL requests - must be first
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Define allowed origins - simnple security for now
$allowed_origins = [
    // list production domains with subdomain variants
    // 'https://productiondomain.com',
    // List local development domains with subdomain variants
    // 'http://localhost'
];


// Allow origin if it's in our whitelist
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    // For development/localhost, allow any origin
    if (strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }
}

// Always set these headers
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-Api-Token, X-Session-Data');
header('Access-Control-Max-Age: 86400');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Debug at the very start of the file
error_reporting(E_ALL);
ini_set('display_errors', 0);

//////////////////////// ROUTE LOGGING ////////////////////////
$routelogging = 0; // set this to 1 to chase API routing issues

// Log all incoming request details
$debug_info = [
    'time' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'headers' => getallheaders(),
    'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'none'
];

// Start output buffering to catch any unwanted output
ob_start();

// THIS IS NOW THE ROUTER FOR ALL API REQUESTS. ROUES ARE SET IN THE CONFIG-API/ROUTES.PHP FILE.
// Set JSON headers
header('Content-Type: application/json');

// Include configuration
require_once __DIR__ . "/config-api/config.php";

// Start session to access session variables globally for all API endpoints
if (session_status() === PHP_SESSION_NONE) {
    debug_log("API index: Starting session with domain: " . ini_get('session.cookie_domain'));
    debug_log("API index: Session path: " . ini_get('session.cookie_path'));
    debug_log("API index: Session secure: " . ini_get('session.cookie_secure'));
    session_start();
}

// Process session sync for all requests BEFORE routing
processSessionSync();

// Parse the request URI
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = parse_url(APP_API_URL, PHP_URL_PATH);

if($routelogging > 0) {debug_log("API index: Request received - " . $_SERVER['REQUEST_METHOD'] . " " . $request_uri);}

// If base_path is just '/', don't strip anything
$path = $base_path === '/' ? ltrim($request_uri, '/') : substr($request_uri, strlen($base_path));
$path = trim($path, '/');

// Store original path before any modifications
$original_path = $path;

// Initialize router response
$response = [
    'status' => 'error',
    'message' => 'Not Found',
    'data' => null
];
$status_code = 404;

// Get routes from configuration
$routes = require __DIR__ . "/config-api/routes.php";

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

if($routelogging > 0) {debug_log("API index: Method: $method, Path: $original_path");}

// Debug output
$debug = [
    'request_uri' => $request_uri,
    'base_path' => $base_path,
    'original_path' => $original_path,
    'method' => $method,
    'available_routes' => isset($routes[$method]) ? array_keys($routes[$method]) : []
];

// Route the request
// come up with a way to route the inbound request to the correct directory and handler/endpoint file that makes it easy to add new endpoints


