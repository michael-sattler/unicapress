<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/functions-universal.php';

$pagetitle = 'Contact';
$pagdescription = 'Get in touch with Unica Press.';

ob_start();
?>
<div class="page-header">
    <div class="container container-narrow">
        <h1>Contact</h1>
        <p class="lead text-muted mb-0">Questions, press inquiries, and general correspondence.</p>
    </div>
</div>

<div class="container container-narrow pb-5">
    <div class="placeholder-banner">
        <p class="mb-2"><i class="fa-solid fa-envelope me-2"></i><strong>Contact form coming soon</strong></p>
        <p class="text-muted mb-0">
            Phase S5 will wire a public inquiry form to the staff support inbox.
            For now, this page confirms routing and layout are working.
        </p>
    </div>
</div>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/../elements/layout.php';
