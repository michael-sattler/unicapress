<?php
require_once __DIR__ . "/../../config/config.php";
require_once PUBLIC_ROOT . "/app/includes/functions-universal.php";
require_once PUBLIC_ROOT . "/app/includes/functions-admin.php";

adminonly();

$pagetitle = "Waitlist";

// --- Handle writes -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $waitlist_id = isset($_POST['waitlist_id']) && $_POST['waitlist_id'] !== '' ? (int) $_POST['waitlist_id'] : null;

    if (isset($_POST['delete']) && $waitlist_id) {
        $stmt = $mysqli->prepare('DELETE FROM waitlist WHERE waitlist_id = ?');
        $stmt->bind_param('i', $waitlist_id);
        $stmt->execute();
        $stmt->close();
        log_event('waitlist.update', $_SESSION['admin_id'], "deleted waitlist_id={$waitlist_id}");
        $_SESSION['result']['type'] = 'success';
        $_SESSION['result']['message'] = 'Waitlist entry deleted.';
        header('Location: ' . APP_ADMIN_URL . '/waitlist');
        exit();
    }

    if (isset($_POST['toggle_active']) && $waitlist_id) {
        $stmt = $mysqli->prepare('UPDATE waitlist SET waitlist_active = 1 - waitlist_active WHERE waitlist_id = ?');
        $stmt->bind_param('i', $waitlist_id);
        $stmt->execute();
        $stmt->close();
        log_event('waitlist.update', $_SESSION['admin_id'], "toggled active waitlist_id={$waitlist_id}");
        header('Location: ' . APP_ADMIN_URL . '/waitlist');
        exit();
    }

    // Edit name/email/source
    $waitlist_name = trim((string) ($_POST['waitlist_name'] ?? ''));
    $waitlist_email = trim((string) ($_POST['waitlist_email'] ?? ''));
    $waitlist_source = trim((string) ($_POST['waitlist_source'] ?? '')) ?: 'home';

    if ($waitlist_id && $waitlist_name !== '' && filter_var($waitlist_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $mysqli->prepare('UPDATE waitlist SET waitlist_name = ?, waitlist_email = ?, waitlist_source = ? WHERE waitlist_id = ?');
        $stmt->bind_param('sssi', $waitlist_name, $waitlist_email, $waitlist_source, $waitlist_id);
        if ($stmt->execute()) {
            log_event('waitlist.update', $_SESSION['admin_id'], "updated waitlist_id={$waitlist_id}");
            $_SESSION['result']['type'] = 'success';
            $_SESSION['result']['message'] = 'Waitlist entry saved.';
        } else {
            $_SESSION['result']['type'] = 'error';
            $_SESSION['result']['message'] = $mysqli->errno === 1062 ? 'That email is already on the list.' : 'Save failed.';
        }
        $stmt->close();
    } else {
        $_SESSION['result']['type'] = 'error';
        $_SESSION['result']['message'] = 'A name and valid email are required.';
    }

    header('Location: ' . APP_ADMIN_URL . '/waitlist');
    exit();
}

// --- Load data for render -------------------------------------------------
$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $mysqli->prepare('SELECT * FROM waitlist WHERE waitlist_id = ? LIMIT 1');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$entries = [];
if ($result = $mysqli->query('SELECT * FROM waitlist ORDER BY waitlist_datecreated DESC')) {
    $entries = $result->fetch_all(MYSQLI_ASSOC);
}

ob_start();
?>
<h2><?php echo $pagetitle; ?></h2>
<p class="text-muted"><?php echo count($entries); ?> signup(s).</p>

<?php if ($editing): ?>
<div class="card mb-4">
    <div class="card-header">Edit entry</div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="waitlist_id" value="<?php echo (int) $editing['waitlist_id']; ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="waitlist_name" class="form-control" required
                           value="<?php echo htmlspecialchars($editing['waitlist_name']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="waitlist_email" class="form-control" required
                           value="<?php echo htmlspecialchars($editing['waitlist_email']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Source</label>
                    <input type="text" name="waitlist_source" class="form-control"
                           value="<?php echo htmlspecialchars($editing['waitlist_source'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent">Save</button>
                    <a href="<?php echo APP_ADMIN_URL; ?>/waitlist" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr><th>Name</th><th>Email</th><th>Source</th><th>Signed up</th><th>Active</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
        <tr>
            <td><?php echo htmlspecialchars($entry['waitlist_name']); ?></td>
            <td><?php echo htmlspecialchars($entry['waitlist_email']); ?></td>
            <td><?php echo htmlspecialchars($entry['waitlist_source'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars(date('Y-m-d', $entry['waitlist_datecreated'])); ?></td>
            <td>
                <form method="post" class="d-inline">
                    <input type="hidden" name="waitlist_id" value="<?php echo (int) $entry['waitlist_id']; ?>">
                    <button type="submit" name="toggle_active" value="1" class="btn btn-sm <?php echo $entry['waitlist_active'] ? 'btn-success' : 'btn-secondary'; ?>">
                        <?php echo $entry['waitlist_active'] ? 'Active' : 'Inactive'; ?>
                    </button>
                </form>
            </td>
            <td class="text-end">
                <a href="<?php echo APP_ADMIN_URL; ?>/waitlist?edit=<?php echo (int) $entry['waitlist_id']; ?>" class="btn btn-sm btn-primary-outline">Edit</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this signup?');">
                    <input type="hidden" name="waitlist_id" value="<?php echo (int) $entry['waitlist_id']; ?>">
                    <button type="submit" name="delete" value="1" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($entries)): ?>
        <tr><td colspan="6" class="text-muted">No signups yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/elements/admin-layout.php';
