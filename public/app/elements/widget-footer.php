<footer class="site-footer mt-auto">
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-md-0 site-footer-brand">
                <a class="navbar-brand site-brand" href="/">
                    <img src="/app/assets/images/logo_horiz2.png" alt="Unica Press" class="img-fluid me-2" style="max-height: 32px;" title="<?php echo htmlspecialchars(SITE_NAME); ?>">
                </a>
                </p>
                <p class="small text-muted mb-0"><?php echo htmlspecialchars(SITE_TAGLINE); ?></p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="/about-the-engine" class="text-muted small me-3">About the Engine</a>
                <a href="/contact" class="text-muted small me-3">Contact</a>
                <span class="text-muted small">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?></span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?php echo asset_url('js/main.js'); ?>"></script>
