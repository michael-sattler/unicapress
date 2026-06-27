<?php
require_once __DIR__ . "/../../config/config.php";
require_once PUBLIC_ROOT . "/app/includes/functions-universal.php";
require_once PUBLIC_ROOT . "/app/includes/functions-admin.php";

adminonly(); // this will redirect to adminlogin.php if not logged in

$pagetitle = "Admin Home";

// Start output buffering to capture page content
ob_start();
?>

    <h2><?php echo $pagetitle; ?></h2>


<?php
// Capture the page content and store it in a variable
$page_content = ob_get_clean();

// Include the layout which will use $page_content
require_once __DIR__ . '/elements/admin-layout.php';
?> 