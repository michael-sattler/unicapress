<?php if (!empty($_SESSION['waitlist_toast'])): ?>
<div class="site-toast" id="waitlist-toast" role="status" aria-live="polite">
    <div class="site-toast-inner">
        <span class="site-toast-icon" aria-hidden="true">
            <i class="fa-solid fa-circle-check"></i>
        </span>
        <div class="site-toast-body">
            <strong class="site-toast-title">You're on the list</strong>
            <p class="site-toast-message"><?php echo htmlspecialchars($_SESSION['waitlist_toast']); ?></p>
        </div>
        <button type="button" class="site-toast-close" aria-label="Dismiss">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>
<?php unset($_SESSION['waitlist_toast']); endif; ?>
