<?php
require_once __DIR__ . "/../../config-app/config.php"; // App config. should in turn pull in project config
require_once APP_PUBLIC_PATH . "/includes/functions-universal.php";

adminonly(); // this will redirect to adminlogin.php if not logged in

$pagetitle = "Public Home";

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