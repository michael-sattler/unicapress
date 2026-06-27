<?php
require_once __DIR__ . "/../../config/config.php";
require_once PUBLIC_ROOT . "/app/includes/functions-universal.php";
require_once PUBLIC_ROOT . "/app/includes/functions-admin.php";

adminonly();

$pagetitle = "Content Library";

// --- Handle writes -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_id = isset($_POST['content_id']) && $_POST['content_id'] !== '' ? (int) $_POST['content_id'] : null;
    $content_name = trim((string) ($_POST['content_name'] ?? ''));
    $content_text = (string) ($_POST['content_text'] ?? '');
    $content_location = trim((string) ($_POST['content_location'] ?? ''));
    $content_type = trim((string) ($_POST['content_type'] ?? ''));
    $content_active = isset($_POST['content_active']) ? 1 : 0;
    $now = time();

    if (isset($_POST['delete']) && $content_id) {
        $stmt = $mysqli->prepare('DELETE FROM content_library WHERE content_id = ?');
        $stmt->bind_param('i', $content_id);
        $stmt->execute();
        $stmt->close();
        log_event('content.update', $_SESSION['admin_id'], "deleted content_id={$content_id}");
        $_SESSION['result']['type'] = 'success';
        $_SESSION['result']['message'] = 'Content entry deleted.';
    } elseif ($content_name === '') {
        $_SESSION['result']['type'] = 'error';
        $_SESSION['result']['message'] = 'Name is required.';
    } elseif ($content_id) {
        $stmt = $mysqli->prepare(
            'UPDATE content_library SET content_name = ?, content_text = ?, content_location = ?, content_type = ?, content_active = ?, content_dateupdated = ?
             WHERE content_id = ?'
        );
        $stmt->bind_param('ssssiii', $content_name, $content_text, $content_location, $content_type, $content_active, $now, $content_id);
        $stmt->execute();
        $stmt->close();
        log_event('content.update', $_SESSION['admin_id'], "updated content_id={$content_id}");
        $_SESSION['result']['type'] = 'success';
        $_SESSION['result']['message'] = 'Content entry saved.';
    } else {
        $stmt = $mysqli->prepare(
            'INSERT INTO content_library (content_name, content_text, content_location, content_type, content_active, content_datecreated, content_dateupdated)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssssiii', $content_name, $content_text, $content_location, $content_type, $content_active, $now, $now);
        $stmt->execute();
        $new_id = $stmt->insert_id;
        $stmt->close();
        log_event('content.update', $_SESSION['admin_id'], "created content_id={$new_id}");
        $_SESSION['result']['type'] = 'success';
        $_SESSION['result']['message'] = 'Content entry created.';
    }

    header('Location: ' . APP_ADMIN_URL . '/content-library');
    exit();
}

// --- Load data for render -------------------------------------------------
$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $mysqli->prepare('SELECT * FROM content_library WHERE content_id = ? LIMIT 1');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$entries = [];
if ($result = $mysqli->query('SELECT * FROM content_library ORDER BY content_name ASC')) {
    $entries = $result->fetch_all(MYSQLI_ASSOC);
}

ob_start();
?>
<h2><?php echo $pagetitle; ?></h2>
<p class="text-muted">DB-managed copy blocks. Reference inline with <code>displayContentLibrary('key')</code>. Use <code>%%VARIABLE%%</code> placeholders for values filled in at render time.</p>

<div class="card mb-4">
    <div class="card-header"><?php echo $editing ? 'Edit entry' : 'New entry'; ?></div>
    <div class="card-body">
        <form method="post">
            <?php if ($editing): ?>
                <input type="hidden" name="content_id" value="<?php echo (int) $editing['content_id']; ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name (key)</label>
                    <input type="text" name="content_name" class="form-control" required
                           value="<?php echo htmlspecialchars($editing['content_name'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Location</label>
                    <input type="text" name="content_location" class="form-control" placeholder="e.g. home, about"
                           value="<?php echo htmlspecialchars($editing['content_location'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <input type="text" name="content_type" class="form-control" placeholder="e.g. paragraph, popup"
                           value="<?php echo htmlspecialchars($editing['content_type'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Text</label>
                    <textarea name="content_text" class="form-control" rows="5"><?php echo htmlspecialchars($editing['content_text'] ?? ''); ?></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="content_active" id="content_active"
                               <?php echo (!$editing || $editing['content_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="content_active">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><?php echo $editing ? 'Save' : 'Create'; ?></button>
                    <?php if ($editing): ?>
                        <a href="<?php echo APP_ADMIN_URL; ?>/content-library" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr><th>Name</th><th>Location</th><th>Type</th><th>Active</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
        <tr>
            <td><?php echo htmlspecialchars($entry['content_name']); ?></td>
            <td><?php echo htmlspecialchars($entry['content_location'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($entry['content_type'] ?? ''); ?></td>
            <td><?php echo $entry['content_active'] ? 'Yes' : 'No'; ?></td>
            <td class="text-end">
                <a href="<?php echo APP_ADMIN_URL; ?>/content-library?edit=<?php echo (int) $entry['content_id']; ?>" class="btn btn-sm btn-primary-outline">Edit</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this entry?');">
                    <input type="hidden" name="content_id" value="<?php echo (int) $entry['content_id']; ?>">
                    <button type="submit" name="delete" value="1" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($entries)): ?>
        <tr><td colspan="5" class="text-muted">No content entries yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/elements/admin-layout.php';
