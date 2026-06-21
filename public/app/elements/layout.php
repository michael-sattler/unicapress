<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/pagehead.php'; ?>
<body class="site-body d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/widget-waitlist-toast.php'; ?>
    <?php include __DIR__ . '/widget-alert.php'; ?>
    <?php include __DIR__ . '/widget-navtop.php'; ?>

    <main class="site-main flex-grow-1">
        <?php if (isset($page_content)) echo $page_content; ?>
    </main>

    <?php include __DIR__ . '/widget-footer.php'; ?>
</body>
</html>
