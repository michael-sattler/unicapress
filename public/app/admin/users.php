<?php
require_once __DIR__ . "/../../config/config.php";
require_once PUBLIC_ROOT . "/app/includes/functions-universal.php";
require_once PUBLIC_ROOT . "/app/includes/functions-admin.php";

adminonly();

$pagetitle = "Admin Users";
$valid_roles = ['staff', 'admin'];

// --- Handle writes -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminuser_id = isset($_POST['adminuser_id']) && $_POST['adminuser_id'] !== '' ? (int) $_POST['adminuser_id'] : null;
    $now = time();

    if (isset($_POST['delete']) && $adminuser_id) {
        if ($adminuser_id === (int) $_SESSION['admin_id']) {
            $_SESSION['result']['type'] = 'error';
            $_SESSION['result']['message'] = "You can't delete your own account while logged in as it.";
        } else {
            $stmt = $mysqli->prepare('DELETE FROM adminusers WHERE adminuser_id = ?');
            $stmt->bind_param('i', $adminuser_id);
            $stmt->execute();
            $stmt->close();
            log_event('adminuser.update', $_SESSION['admin_id'], "deleted adminuser_id={$adminuser_id}");
            $_SESSION['result']['type'] = 'success';
            $_SESSION['result']['message'] = 'Admin user deleted.';
        }
        header('Location: ' . APP_ADMIN_URL . '/users');
        exit();
    }

    if (isset($_POST['unlock']) && $adminuser_id) {
        $stmt = $mysqli->prepare('UPDATE adminusers SET adminuser_login_attempts = 0, adminuser_locked_until = NULL, adminuser_dateupdated = ? WHERE adminuser_id = ?');
        $stmt->bind_param('ii', $now, $adminuser_id);
        $stmt->execute();
        $stmt->close();
        log_event('adminuser.update', $_SESSION['admin_id'], "unlocked adminuser_id={$adminuser_id}");
        $_SESSION['result']['type'] = 'success';
        $_SESSION['result']['message'] = 'Account unlocked.';
        header('Location: ' . APP_ADMIN_URL . '/users');
        exit();
    }

    $adminuser_email = trim((string) ($_POST['adminuser_email'] ?? ''));
    $adminuser_firstname = trim((string) ($_POST['adminuser_firstname'] ?? ''));
    $adminuser_lastname = trim((string) ($_POST['adminuser_lastname'] ?? ''));
    $adminuser_role = in_array($_POST['adminuser_role'] ?? '', $valid_roles, true) ? $_POST['adminuser_role'] : 'staff';
    $adminuser_active = isset($_POST['adminuser_active']) ? 1 : 0;
    $password = (string) ($_POST['adminuser_password'] ?? '');

    if (!filter_var($adminuser_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['result']['type'] = 'error';
        $_SESSION['result']['message'] = 'A valid email is required.';
    } elseif (!$adminuser_id && $password === '') {
        $_SESSION['result']['type'] = 'error';
        $_SESSION['result']['message'] = 'A password is required for new accounts.';
    } elseif ($adminuser_id && $adminuser_id === (int) $_SESSION['admin_id'] && $adminuser_active === 0) {
        $_SESSION['result']['type'] = 'error';
        $_SESSION['result']['message'] = "You can't deactivate your own account while logged in as it.";
    } elseif ($adminuser_id) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
            $stmt = $mysqli->prepare(
                'UPDATE adminusers SET adminuser_email = ?, adminuser_password_hash = ?, adminuser_firstname = ?, adminuser_lastname = ?, adminuser_role = ?, adminuser_active = ?, adminuser_dateupdated = ?
                 WHERE adminuser_id = ?'
            );
            $stmt->bind_param('sssssiii', $adminuser_email, $hash, $adminuser_firstname, $adminuser_lastname, $adminuser_role, $adminuser_active, $now, $adminuser_id);
        } else {
            $stmt = $mysqli->prepare(
                'UPDATE adminusers SET adminuser_email = ?, adminuser_firstname = ?, adminuser_lastname = ?, adminuser_role = ?, adminuser_active = ?, adminuser_dateupdated = ?
                 WHERE adminuser_id = ?'
            );
            $stmt->bind_param('ssssiii', $adminuser_email, $adminuser_firstname, $adminuser_lastname, $adminuser_role, $adminuser_active, $now, $adminuser_id);
        }
        if ($stmt->execute()) {
            log_event('adminuser.update', $_SESSION['admin_id'], "updated adminuser_id={$adminuser_id}");
            $_SESSION['result']['type'] = 'success';
            $_SESSION['result']['message'] = 'Admin user saved.';
        } else {
            $_SESSION['result']['type'] = 'error';
            $_SESSION['result']['message'] = $mysqli->errno === 1062 ? 'That email is already in use.' : 'Save failed.';
        }
        $stmt->close();
        header('Location: ' . APP_ADMIN_URL . '/users');
        exit();
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        $stmt = $mysqli->prepare(
            'INSERT INTO adminusers (adminuser_email, adminuser_password_hash, adminuser_firstname, adminuser_lastname, adminuser_role, adminuser_active, adminuser_datecreated, adminuser_dateupdated)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssssiii', $adminuser_email, $hash, $adminuser_firstname, $adminuser_lastname, $adminuser_role, $adminuser_active, $now, $now);
        if ($stmt->execute()) {
            log_event('adminuser.update', $_SESSION['admin_id'], "created adminuser_id={$stmt->insert_id}");
            $_SESSION['result']['type'] = 'success';
            $_SESSION['result']['message'] = 'Admin user created.';
        } else {
            $_SESSION['result']['type'] = 'error';
            $_SESSION['result']['message'] = $mysqli->errno === 1062 ? 'That email is already in use.' : 'Create failed.';
        }
        $stmt->close();
        header('Location: ' . APP_ADMIN_URL . '/users');
        exit();
    }
}

// --- Load data for render -------------------------------------------------
$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $mysqli->prepare('SELECT * FROM adminusers WHERE adminuser_id = ? LIMIT 1');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$entries = [];
if ($result = $mysqli->query('SELECT * FROM adminusers ORDER BY adminuser_email ASC')) {
    $entries = $result->fetch_all(MYSQLI_ASSOC);
}

ob_start();
?>
<h2><?php echo $pagetitle; ?></h2>
<p class="text-muted">Staff accounts that can sign in to this admin console. Leave password blank when editing to keep the current one.</p>

<div class="card mb-4">
    <div class="card-header"><?php echo $editing ? 'Edit admin user' : 'New admin user'; ?></div>
    <div class="card-body">
        <form method="post">
            <?php if ($editing): ?>
                <input type="hidden" name="adminuser_id" value="<?php echo (int) $editing['adminuser_id']; ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="adminuser_email" class="form-control" required
                           value="<?php echo htmlspecialchars($editing['adminuser_email'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">First name</label>
                    <input type="text" name="adminuser_firstname" class="form-control"
                           value="<?php echo htmlspecialchars($editing['adminuser_firstname'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last name</label>
                    <input type="text" name="adminuser_lastname" class="form-control"
                           value="<?php echo htmlspecialchars($editing['adminuser_lastname'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Password <?php echo $editing ? '(leave blank to keep)' : ''; ?></label>
                    <input type="password" name="adminuser_password" class="form-control" autocomplete="new-password">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Role</label>
                    <select name="adminuser_role" class="form-select">
                        <?php foreach ($valid_roles as $role): ?>
                        <option value="<?php echo $role; ?>" <?php echo (($editing['adminuser_role'] ?? 'staff') === $role) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="adminuser_active" id="adminuser_active"
                               <?php echo (!$editing || $editing['adminuser_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="adminuser_active">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><?php echo $editing ? 'Save' : 'Create'; ?></button>
                    <?php if ($editing): ?>
                        <a href="<?php echo APP_ADMIN_URL; ?>/users" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr><th>Email</th><th>Name</th><th>Role</th><th>Active</th><th>Last login</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
        <tr>
            <td><?php echo htmlspecialchars($entry['adminuser_email']); ?></td>
            <td><?php echo htmlspecialchars(trim($entry['adminuser_firstname'] . ' ' . $entry['adminuser_lastname'])); ?></td>
            <td><?php echo htmlspecialchars($entry['adminuser_role']); ?></td>
            <td><?php echo $entry['adminuser_active'] ? 'Yes' : 'No'; ?></td>
            <td><?php echo $entry['adminuser_last_login'] ? htmlspecialchars(date('Y-m-d H:i', $entry['adminuser_last_login'])) : '—'; ?></td>
            <td>
                <?php if ($entry['adminuser_locked_until'] && $entry['adminuser_locked_until'] > time()): ?>
                    <span class="badge bg-danger">Locked until <?php echo htmlspecialchars(date('H:i', $entry['adminuser_locked_until'])); ?></span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <a href="<?php echo APP_ADMIN_URL; ?>/users?edit=<?php echo (int) $entry['adminuser_id']; ?>" class="btn btn-sm btn-primary-outline">Edit</a>
                <?php if ($entry['adminuser_locked_until']): ?>
                <form method="post" class="d-inline">
                    <input type="hidden" name="adminuser_id" value="<?php echo (int) $entry['adminuser_id']; ?>">
                    <button type="submit" name="unlock" value="1" class="btn btn-sm btn-secondary">Unlock</button>
                </form>
                <?php endif; ?>
                <?php if ((int) $entry['adminuser_id'] !== (int) $_SESSION['admin_id']): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this admin user?');">
                    <input type="hidden" name="adminuser_id" value="<?php echo (int) $entry['adminuser_id']; ?>">
                    <button type="submit" name="delete" value="1" class="btn btn-sm btn-danger">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/elements/admin-layout.php';
