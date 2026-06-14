<?php
$alert_types = [
    'error_message' => ['class' => 'danger', 'icon' => 'fa-circle-exclamation'],
    'success_message' => ['class' => 'success', 'icon' => 'fa-circle-check'],
    'info_message' => ['class' => 'info', 'icon' => 'fa-circle-info'],
];

foreach ($alert_types as $session_key => $alert):
    if (!empty($_SESSION[$session_key])):
?>
<div class="alert alert-<?php echo $alert['class']; ?> alert-dismissible fade show site-alert mb-0 rounded-0 border-0" role="alert">
    <i class="fa-solid <?php echo $alert['icon']; ?> me-2"></i>
    <?php echo htmlspecialchars($_SESSION[$session_key]); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php
        unset($_SESSION[$session_key]);
    endif;
endforeach;
