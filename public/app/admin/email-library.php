<?php
require_once __DIR__ . "/../../config/config.php";
require_once PUBLIC_ROOT . "/app/includes/functions-universal.php";
require_once PUBLIC_ROOT . "/app/includes/functions-admin.php";
require_once PUBLIC_ROOT . "/app/includes/functions-email.php";

adminonly();

$pagetitle = "Email Library";

// --- Handle writes -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['test_send'])) {
    $email_id = isset($_POST['email_id']) && $_POST['email_id'] !== '' ? (int) $_POST['email_id'] : null;
    $email_name = trim((string) ($_POST['email_name'] ?? ''));
    $email_subject = trim((string) ($_POST['email_subject'] ?? ''));
    $email_body = (string) ($_POST['email_body'] ?? '');
    $email_type = trim((string) ($_POST['email_type'] ?? ''));
    $email_active = isset($_POST['email_active']) ? 1 : 0;
    $now = time();

    if (isset($_POST['delete']) && $email_id) {
        $stmt = $mysqli->prepare('DELETE FROM email_library WHERE email_id = ?');
        $stmt->bind_param('i', $email_id);
        $stmt->execute();
        $stmt->close();
        log_event('email.update', $_SESSION['admin_id'], "deleted email_id={$email_id}");
        $_SESSION['result']['type'] = 'success';
        $_SESSION['result']['message'] = 'Email template deleted.';
    } elseif ($email_name === '' || $email_body === '') {
        $_SESSION['result']['type'] = 'error';
        $_SESSION['result']['message'] = 'Name and body are required.';
    } elseif ($email_id) {
        $stmt = $mysqli->prepare(
            'UPDATE email_library SET email_name = ?, email_subject = ?, email_body = ?, email_type = ?, email_active = ?, email_dateupdated = ?
             WHERE email_id = ?'
        );
        $stmt->bind_param('ssssiii', $email_name, $email_subject, $email_body, $email_type, $email_active, $now, $email_id);
        $stmt->execute();
        $stmt->close();
        log_event('email.update', $_SESSION['admin_id'], "updated email_id={$email_id}");
        $_SESSION['result']['type'] = 'success';
        $_SESSION['result']['message'] = 'Email template saved.';
    } else {
        $stmt = $mysqli->prepare(
            'INSERT INTO email_library (email_name, email_subject, email_body, email_type, email_active, email_datecreated, email_dateupdated)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssssiii', $email_name, $email_subject, $email_body, $email_type, $email_active, $now, $now);
        $stmt->execute();
        $new_id = $stmt->insert_id;
        $stmt->close();
        log_event('email.update', $_SESSION['admin_id'], "created email_id={$new_id}");
        $_SESSION['result']['type'] = 'success';
        $_SESSION['result']['message'] = 'Email template created.';
    }

    header('Location: ' . APP_ADMIN_URL . '/email-library');
    exit();
}

// --- Test-send (renders via sendEmailFromLibrary; no provider wired yet) --
$test_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_send'])) {
    $test_result = sendEmailFromLibrary((int) $_POST['email_id'], $_POST['test_to'] ?? 'test@example.com');
}

// --- Load data for render -------------------------------------------------
$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $mysqli->prepare('SELECT * FROM email_library WHERE email_id = ? LIMIT 1');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$entries = [];
if ($result = $mysqli->query('SELECT * FROM email_library ORDER BY email_name ASC')) {
    $entries = $result->fetch_all(MYSQLI_ASSOC);
}

ob_start();
?>
<h2><?php echo $pagetitle; ?></h2>
<p class="text-muted">Templated outbound email. Send via <code>sendEmailFromLibrary('key', $to, $vars)</code>. Use <code>%%VARIABLE%%</code> placeholders.</p>
<p class="text-muted"><strong>Note:</strong> no email provider is wired yet (scope-marketing-shell.md S3.4) — sends are logged and rendered, not delivered.</p>

<?php if ($test_result): ?>
<div class="alert alert-<?php echo $test_result['success'] ? 'info' : 'danger'; ?>">
    <?php if ($test_result['success']): ?>
        <strong>Would send</strong> to <?php echo htmlspecialchars($test_result['to']); ?> — Subject: <?php echo htmlspecialchars($test_result['subject']); ?>
        <pre class="mt-2 mb-0"><?php echo htmlspecialchars($test_result['body']); ?></pre>
    <?php else: ?>
        <?php echo htmlspecialchars($test_result['error']); ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header"><?php echo $editing ? 'Edit template' : 'New template'; ?></div>
    <div class="card-body">
        <form method="post">
            <?php if ($editing): ?>
                <input type="hidden" name="email_id" value="<?php echo (int) $editing['email_id']; ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name (key)</label>
                    <input type="text" name="email_name" class="form-control" required
                           value="<?php echo htmlspecialchars($editing['email_name'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <input type="text" name="email_type" class="form-control" placeholder="e.g. transactional, internal"
                           value="<?php echo htmlspecialchars($editing['email_type'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="email_active" id="email_active"
                               <?php echo (!$editing || $editing['email_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="email_active">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Subject</label>
                    <input type="text" name="email_subject" class="form-control"
                           value="<?php echo htmlspecialchars($editing['email_subject'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Body</label>
                    <textarea name="email_body" class="form-control" rows="6" required><?php echo htmlspecialchars($editing['email_body'] ?? ''); ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><?php echo $editing ? 'Save' : 'Create'; ?></button>
                    <?php if ($editing): ?>
                        <a href="<?php echo APP_ADMIN_URL; ?>/email-library" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <?php if ($editing): ?>
        <form method="post" class="mt-3 border-top pt-3">
            <input type="hidden" name="email_id" value="<?php echo (int) $editing['email_id']; ?>">
            <input type="hidden" name="test_send" value="1">
            <label class="form-label">Test render (to)</label>
            <div class="input-group" style="max-width: 420px;">
                <input type="email" name="test_to" class="form-control" placeholder="you@example.com">
                <button type="submit" class="btn btn-primary-outline">Test render</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr><th>Name</th><th>Subject</th><th>Type</th><th>Active</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
        <tr>
            <td><?php echo htmlspecialchars($entry['email_name']); ?></td>
            <td><?php echo htmlspecialchars($entry['email_subject'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($entry['email_type'] ?? ''); ?></td>
            <td><?php echo $entry['email_active'] ? 'Yes' : 'No'; ?></td>
            <td class="text-end">
                <a href="<?php echo APP_ADMIN_URL; ?>/email-library?edit=<?php echo (int) $entry['email_id']; ?>" class="btn btn-sm btn-primary-outline">Edit</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this template?');">
                    <input type="hidden" name="email_id" value="<?php echo (int) $entry['email_id']; ?>">
                    <button type="submit" name="delete" value="1" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($entries)): ?>
        <tr><td colspan="5" class="text-muted">No email templates yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/elements/admin-layout.php';
