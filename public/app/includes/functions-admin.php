<?php
/**
 * Admin auth functions (docs/standards-architecture+deployment.md, scope-marketing-shell.md S2)
 */

if (!function_exists('adminonly')) {
    function adminonly($redirect_url = null) {
        if (empty($_SESSION['admin_id'])) {
            $_SESSION['last_url'] = $_SERVER['REQUEST_URI'] ?? null;
            header('Location: ' . ($redirect_url ?? APP_ADMIN_URL . '/login'));
            exit();
        }
    }
}

if (!function_exists('validateadminuser')) {
    /**
     * Returns array("success" => true, "adminuser_id" => ..., "adminuser_email" => ...)
     * or array("success" => false, "error" => "...")
     */
    function validateadminuser($email, $password) {
        global $mysqli;

        $email = trim((string) $email);
        $password = (string) $password;

        if ($email === '' || $password === '') {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        if (!$mysqli instanceof mysqli) {
            debug_log('validateadminuser: no database connection');
            return ['success' => false, 'error' => 'Login is temporarily unavailable.'];
        }

        $stmt = $mysqli->prepare(
            'SELECT adminuser_id, adminuser_email, adminuser_password_hash, adminuser_active,
                    adminuser_login_attempts, adminuser_locked_until
             FROM adminusers WHERE adminuser_email = ? LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $adminuser = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$adminuser) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        if (!$adminuser['adminuser_active']) {
            return ['success' => false, 'error' => 'This account is disabled.'];
        }

        if ($adminuser['adminuser_locked_until'] && $adminuser['adminuser_locked_until'] > time()) {
            return ['success' => false, 'error' => 'Too many failed attempts. Try again later.'];
        }

        if (!password_verify($password, $adminuser['adminuser_password_hash'])) {
            $attempts = (int) $adminuser['adminuser_login_attempts'] + 1;
            $locked_until = $attempts >= MAX_LOGIN_ATTEMPTS ? (time() + LOGIN_LOCKOUT_DURATION) : null;
            $now = time();

            $stmt = $mysqli->prepare(
                'UPDATE adminusers SET adminuser_login_attempts = ?, adminuser_locked_until = ?, adminuser_dateupdated = ?
                 WHERE adminuser_id = ?'
            );
            $stmt->bind_param('iiii', $attempts, $locked_until, $now, $adminuser['adminuser_id']);
            $stmt->execute();
            $stmt->close();

            log_event('admin.login_failed', 0, $email);

            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        $now = time();
        $stmt = $mysqli->prepare(
            'UPDATE adminusers SET adminuser_login_attempts = 0, adminuser_locked_until = NULL,
                    adminuser_last_login = ?, adminuser_dateupdated = ?
             WHERE adminuser_id = ?'
        );
        $stmt->bind_param('iii', $now, $now, $adminuser['adminuser_id']);
        $stmt->execute();
        $stmt->close();

        log_event('admin.login', $adminuser['adminuser_id']);

        return [
            'success' => true,
            'adminuser_id' => $adminuser['adminuser_id'],
            'adminuser_email' => $adminuser['adminuser_email'],
        ];
    }
}
