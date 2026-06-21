<?php
/**
 * Waitlist signup helpers
 */

if (!function_exists('add_waitlist_signup')) {
    /**
     * @return array{success: bool, message: string}
     */
    function add_waitlist_signup($name, $email, $source = 'home') {
        global $mysqli;

        if (!isset($mysqli) || !$mysqli instanceof mysqli) {
            debug_log('Waitlist signup failed: database unavailable');
            return [
                'success' => false,
                'message' => 'Unable to save your signup right now. Please try again shortly.',
            ];
        }

        $name = trim((string) $name);
        $email = trim((string) $email);
        $source = trim((string) $source) ?: 'home';

        if ($name === '') {
            return ['success' => false, 'message' => 'Please enter your name.'];
        }

        if (strlen($name) > 255) {
            return ['success' => false, 'message' => 'Name is too long.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }

        if (strlen($source) > 64) {
            $source = substr($source, 0, 64);
        }

        $name_esc = mysqli_real_escape_string($mysqli, $name);
        $email_esc = mysqli_real_escape_string($mysqli, $email);
        $source_esc = mysqli_real_escape_string($mysqli, $source);
        $now = time();

        $sql = "INSERT INTO waitlist (waitlist_name, waitlist_email, waitlist_source, waitlist_datecreated)
                VALUES ('{$name_esc}', '{$email_esc}', '{$source_esc}', {$now})";

        if ($mysqli->query($sql)) {
            debug_log('Waitlist signup saved', ['email' => $email, 'source' => $source]);
            return [
                'success' => true,
                'message' => 'Thank you — we will be in touch when testing opens.',
            ];
        }

        if ((int) $mysqli->errno === 1062) {
            return [
                'success' => true,
                'message' => 'You are already on the list — we will be in touch.',
            ];
        }

        debug_log('Waitlist insert failed: ' . $mysqli->error);
        return [
            'success' => false,
            'message' => 'Something went wrong. Please try again.',
        ];
    }
}
