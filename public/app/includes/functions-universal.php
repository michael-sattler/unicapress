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
        if ($path === '/worldbuilder' && $current === '/worldbuilding') {
            return true;
        }
        return $current === $path;
    }
}

if (!function_exists('nav_link_class')) {
    function nav_link_class($path, $base_class = 'nav-link') {
        return $base_class . (is_current_route($path) ? ' active' : '');
    }
}

if (!function_exists('log_event')) {
    /**
     * Append-only audit log (docs/scope-coresaas.md — Event logging).
     * $eventtype_name must already exist in eventlogtypes.
     */
    function log_event($eventtype_name, $adminuser_id = 0, $source = null) {
        global $mysqli;
        if (!$mysqli instanceof mysqli) {
            return false;
        }

        $source = $source ?? ($_SERVER['REQUEST_URI'] ?? '');

        $stmt = $mysqli->prepare('SELECT eventtype_id FROM eventlogtypes WHERE eventtype_name = ? LIMIT 1');
        $stmt->bind_param('s', $eventtype_name);
        $stmt->execute();
        $eventtype = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$eventtype) {
            debug_log("log_event: unknown eventtype_name '{$eventtype_name}'");
            return false;
        }

        $now = time();
        $stmt = $mysqli->prepare('INSERT INTO eventlogs (event_typeid, adminuser_id, event_source, event_datecreated) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iisi', $eventtype['eventtype_id'], $adminuser_id, $source, $now);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}

if (!function_exists('interpolate_library_vars')) {
    /**
     * Replaces %%VARIABLE%% placeholders. Unmatched placeholders are left as-is
     * so missing data is visible rather than silently disappearing.
     */
    function interpolate_library_vars($text, array $vars = []) {
        if ($text === null || $text === '' || empty($vars)) {
            return (string) $text;
        }

        $replacements = [];
        foreach ($vars as $key => $value) {
            $replacements['%%' . $key . '%%'] = (string) $value;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}

if (!function_exists('displayContentLibrary')) {
    /**
     * Looks up a content_library row by content_id (int) or content_name (string)
     * and returns its body with %%VARIABLE%% placeholders interpolated.
     * Only returns active entries to non-admin callers should that distinction matter later.
     */
    function displayContentLibrary($key, array $vars = []) {
        global $mysqli;
        if (!$mysqli instanceof mysqli) {
            return '';
        }

        if (is_numeric($key)) {
            $stmt = $mysqli->prepare('SELECT content_text FROM content_library WHERE content_id = ? AND content_active = 1 LIMIT 1');
            $stmt->bind_param('i', $key);
        } else {
            $stmt = $mysqli->prepare('SELECT content_text FROM content_library WHERE content_name = ? AND content_active = 1 LIMIT 1');
            $stmt->bind_param('s', $key);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return '';
        }

        return interpolate_library_vars($row['content_text'], $vars);
    }
}
