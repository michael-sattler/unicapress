<?php
require_once __DIR__ . "/../../config/config.php";
require_once PUBLIC_ROOT . "/app/includes/functions-universal.php";
require_once PUBLIC_ROOT . "/app/includes/functions-admin.php";

adminonly();

$pagetitle = "Event Logs";

// --- Handle eventlogtypes catalog writes ----------------------------------
// eventlogs itself is append-only audit data and is never edited/deleted here.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventtype_id = isset($_POST['eventtype_id']) && $_POST['eventtype_id'] !== '' ? (int) $_POST['eventtype_id'] : null;
    $eventtype_name = trim((string) ($_POST['eventtype_name'] ?? ''));
    $eventtype_description = trim((string) ($_POST['eventtype_description'] ?? ''));
    $eventtype_active = isset($_POST['eventtype_active']) ? 1 : 0;

    if (isset($_POST['delete']) && $eventtype_id) {
        $stmt = $mysqli->prepare('DELETE FROM eventlogtypes WHERE eventtype_id = ?');
        $stmt->bind_param('i', $eventtype_id);
        $deleted = $stmt->execute();
        $stmt->close();
        if ($deleted) {
            log_event('eventtype.update', $_SESSION['admin_id'], "deleted eventtype_id={$eventtype_id}");
            $_SESSION['result']['type'] = 'success';
            $_SESSION['result']['message'] = 'Event type deleted.';
        } else {
            $_SESSION['result']['type'] = 'error';
            $_SESSION['result']['message'] = 'Could not delete — existing eventlogs likely reference this type.';
        }
    } elseif ($eventtype_name === '') {
        $_SESSION['result']['type'] = 'error';
        $_SESSION['result']['message'] = 'Name is required.';
    } elseif ($eventtype_id) {
        $stmt = $mysqli->prepare('UPDATE eventlogtypes SET eventtype_name = ?, eventtype_description = ?, eventtype_active = ? WHERE eventtype_id = ?');
        $stmt->bind_param('ssii', $eventtype_name, $eventtype_description, $eventtype_active, $eventtype_id);
        if ($stmt->execute()) {
            log_event('eventtype.update', $_SESSION['admin_id'], "updated eventtype_id={$eventtype_id}");
            $_SESSION['result']['type'] = 'success';
            $_SESSION['result']['message'] = 'Event type saved.';
        } else {
            $_SESSION['result']['type'] = 'error';
            $_SESSION['result']['message'] = $mysqli->errno === 1062 ? 'That name is already in use.' : 'Save failed.';
        }
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare('INSERT INTO eventlogtypes (eventtype_name, eventtype_description, eventtype_active) VALUES (?, ?, ?)');
        $stmt->bind_param('ssi', $eventtype_name, $eventtype_description, $eventtype_active);
        if ($stmt->execute()) {
            log_event('eventtype.update', $_SESSION['admin_id'], "created eventtype_id={$stmt->insert_id}");
            $_SESSION['result']['type'] = 'success';
            $_SESSION['result']['message'] = 'Event type created.';
        } else {
            $_SESSION['result']['type'] = 'error';
            $_SESSION['result']['message'] = $mysqli->errno === 1062 ? 'That name is already in use.' : 'Create failed.';
        }
        $stmt->close();
    }

    header('Location: ' . APP_ADMIN_URL . '/event-logs');
    exit();
}

// --- Load eventlogtypes for catalog + filter dropdown ---------------------
$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $mysqli->prepare('SELECT * FROM eventlogtypes WHERE eventtype_id = ? LIMIT 1');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$types = [];
if ($result = $mysqli->query('SELECT * FROM eventlogtypes ORDER BY eventtype_name ASC')) {
    $types = $result->fetch_all(MYSQLI_ASSOC);
}

// --- Load eventlogs (paginated, optionally filtered by type) --------------
$per_page = 50;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;
$filter_type = isset($_GET['type']) && $_GET['type'] !== '' ? (int) $_GET['type'] : null;

$where = $filter_type ? 'WHERE e.event_typeid = ?' : '';
$count_sql = "SELECT COUNT(*) AS total FROM eventlogs e {$where}";
if ($filter_type) {
    $stmt = $mysqli->prepare($count_sql);
    $stmt->bind_param('i', $filter_type);
    $stmt->execute();
    $total = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
} else {
    $total = (int) $mysqli->query($count_sql)->fetch_assoc()['total'];
}
$total_pages = max(1, (int) ceil($total / $per_page));

$logs_sql = "SELECT e.eventlog_id, e.event_source, e.event_datecreated, t.eventtype_name, a.adminuser_email
             FROM eventlogs e
             JOIN eventlogtypes t ON t.eventtype_id = e.event_typeid
             LEFT JOIN adminusers a ON a.adminuser_id = e.adminuser_id
             {$where}
             ORDER BY e.eventlog_id DESC
             LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($logs_sql);
if ($filter_type) {
    $stmt->bind_param('iii', $filter_type, $per_page, $offset);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ob_start();
?>
<h2><?php echo $pagetitle; ?></h2>

<div class="card mb-4">
    <div class="card-header"><?php echo $editing ? 'Edit event type' : 'New event type'; ?></div>
    <div class="card-body">
        <form method="post">
            <?php if ($editing): ?>
                <input type="hidden" name="eventtype_id" value="<?php echo (int) $editing['eventtype_id']; ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name (code)</label>
                    <input type="text" name="eventtype_name" class="form-control" required placeholder="e.g. admin.login"
                           value="<?php echo htmlspecialchars($editing['eventtype_name'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description</label>
                    <input type="text" name="eventtype_description" class="form-control"
                           value="<?php echo htmlspecialchars($editing['eventtype_description'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="eventtype_active" id="eventtype_active"
                               <?php echo (!$editing || $editing['eventtype_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="eventtype_active">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><?php echo $editing ? 'Save' : 'Create'; ?></button>
                    <?php if ($editing): ?>
                        <a href="<?php echo APP_ADMIN_URL; ?>/event-logs" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<table class="table table-striped table-sm mb-5">
    <thead><tr><th>Name</th><th>Description</th><th>Active</th><th></th></tr></thead>
    <tbody>
        <?php foreach ($types as $type): ?>
        <tr>
            <td><code><?php echo htmlspecialchars($type['eventtype_name']); ?></code></td>
            <td><?php echo htmlspecialchars($type['eventtype_description'] ?? ''); ?></td>
            <td><?php echo $type['eventtype_active'] ? 'Yes' : 'No'; ?></td>
            <td class="text-end">
                <a href="<?php echo APP_ADMIN_URL; ?>/event-logs?edit=<?php echo (int) $type['eventtype_id']; ?>" class="btn btn-sm btn-primary-outline">Edit</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this event type? This will fail if any eventlogs still reference it.');">
                    <input type="hidden" name="eventtype_id" value="<?php echo (int) $type['eventtype_id']; ?>">
                    <button type="submit" name="delete" value="1" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Log entries</h3>
<form method="get" class="row g-2 mb-3 align-items-end">
    <div class="col-md-3">
        <label class="form-label">Filter by type</label>
        <select name="type" class="form-select" onchange="this.form.submit()">
            <option value="">All types</option>
            <?php foreach ($types as $type): ?>
            <option value="<?php echo (int) $type['eventtype_id']; ?>" <?php echo $filter_type === (int) $type['eventtype_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($type['eventtype_name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<table class="table table-striped table-sm">
    <thead><tr><th>When</th><th>Type</th><th>Admin user</th><th>Source / detail</th></tr></thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', $log['event_datecreated'])); ?></td>
            <td><code><?php echo htmlspecialchars($log['eventtype_name']); ?></code></td>
            <td><?php echo htmlspecialchars($log['adminuser_email'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($log['event_source']); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
        <tr><td colspan="4" class="text-muted">No log entries<?php echo $filter_type ? ' for this type' : ''; ?>.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
<nav>
    <ul class="pagination">
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $p; ?><?php echo $filter_type ? '&type=' . $filter_type : ''; ?>"><?php echo $p; ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/elements/admin-layout.php';
