<?php
/**
 * Universal functions used across the public site and admin
 */

if (!function_exists('asset_url')) {
    function asset_url($path) {
        return rtrim(APP_URL, '/') . '/app/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('is_current_route')) {
    function is_current_route($path) {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        $current = parse_url($request_uri, PHP_URL_PATH) ?: '/';
        $current = rtrim($current, '/') ?: '/';
        $path = rtrim($path, '/') ?: '/';
        return $current === $path;
    }
}

if (!function_exists('nav_link_class')) {
    function nav_link_class($path, $base_class = 'nav-link') {
        return $base_class . (is_current_route($path) ? ' active' : '');
    }
}
