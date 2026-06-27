<?php
/**
 * Email Library send path (docs/scope-coresaas.md — EmailLibrary).
 * All outbound email should route through sendEmailFromLibrary() rather than
 * being composed inline, so templates stay admin-editable.
 */

if (!function_exists('sendEmailFromLibrary')) {
    /**
     * Looks up an email_library row by email_id (int) or email_name (string),
     * interpolates %%VARIABLE%% placeholders, and sends it.
     *
     * Provider integration (Resend/SMTP) is intentionally not wired yet — see
     * docs/scope-marketing-shell.md S3.4. For now this renders the template,
     * logs the attempt to event_logs, and returns what would have been sent so
     * dev/staging can verify content without an API key. Swap the body of the
     * "TODO: send" block for the real provider call when ready.
     */
    function sendEmailFromLibrary($key, $to, array $vars = []) {
        global $mysqli;

        if (!$mysqli instanceof mysqli) {
            return ['success' => false, 'error' => 'No database connection.'];
        }

        if (is_numeric($key)) {
            $stmt = $mysqli->prepare('SELECT email_id, email_subject, email_body FROM email_library WHERE email_id = ? AND email_active = 1 LIMIT 1');
            $stmt->bind_param('i', $key);
        } else {
            $stmt = $mysqli->prepare('SELECT email_id, email_subject, email_body FROM email_library WHERE email_name = ? AND email_active = 1 LIMIT 1');
            $stmt->bind_param('s', $key);
        }
        $stmt->execute();
        $template = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$template) {
            return ['success' => false, 'error' => "Email template '{$key}' not found or inactive."];
        }

        $subject = interpolate_library_vars($template['email_subject'], $vars);
        $body = interpolate_library_vars($template['email_body'], $vars);

        // TODO (scope-marketing-shell.md S3.4): wire to Resend (RESEND_API_KEY / RESEND_FROM_EMAIL)
        // once those are added to the platform config. Until then, log and no-op so templates
        // can be authored and previewed without live sends.
        debug_log('sendEmailFromLibrary (provider not wired): would send', [
            'to' => $to,
            'subject' => $subject,
            'email_id' => $template['email_id'],
        ]);

        log_event('email.sent', 0, "email_library#{$template['email_id']} -> {$to}");

        return [
            'success' => true,
            'sent' => false,
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
