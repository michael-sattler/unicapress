<?php
// pagehead.php emits a full <head>...</head>; splice the admin-only stylesheet
// in before the closing tag rather than appending it after (invalid placement).
ob_start();
include __DIR__ . '/../../elements/pagehead.php';
$head_markup = ob_get_clean();

$admin_head_extras = '    <link rel="stylesheet" href="' . htmlspecialchars(asset_url('css/admin.css')) . '" />' . "\n"
    // The public site has no jQuery dependency, but the inherited admin templates
    // (admin-navtop.php, admin-layout.php) use $(document).ready handlers — loaded admin-only.
    . '    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>' . "\n";

echo str_replace('</head>', $admin_head_extras . '</head>', $head_markup);
?>
