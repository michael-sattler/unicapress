<nav class="navbar navbar-expand-lg navbar-light site-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand site-brand" href="/">
            <img src="/app/assets/images/logo_horiz2.png" alt="Unica Press" class="img-fluid me-2" style="max-height: 32px;" title="<?php echo htmlspecialchars(SITE_NAME); ?>">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-controls="siteNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="<?php echo nav_link_class('/'); ?>" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="<?php echo nav_link_class('/about'); ?>" href="/about">About</a>
                </li>
                <li class="nav-item">
                    <a class="<?php echo nav_link_class('/worldbuilding'); ?>" href="/worldbuilding">Worldbuilding</a>
                </li>
                <li class="nav-item">
                    <a class="<?php echo nav_link_class('/about-the-engine'); ?>" href="/about-the-engine">About the Engine</a>
                </li>
                <li class="nav-item d-none">
                    <a class="<?php echo nav_link_class('/contact'); ?>" href="/contact">Contact</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-accent btn-sm disabled" href="<?php echo htmlspecialchars(APP_ARCHIVE_URL); ?>" target="_blank" rel="noopener noreferrer">
                        Visit the Grand Archive <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
