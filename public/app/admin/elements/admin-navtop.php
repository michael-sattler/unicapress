<div class="navtop" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; max-height: 60px;">
    <?php include __DIR__ . '/admin-alert.php'; ?>

    <div class="row">
        <div class="col-12 bkgd-primary text-white">
            <div class="float-start p-1">
                <a href="<?php echo APP_ADMIN_URL; ?>" class="text-typewritten text-xl mb-0 text-lightest" style="text-decoration: none;"><i class="fa-solid fa-pen-nib text-lg"></i> <span> <?php echo htmlspecialchars(SITE_NAME); ?></span></a>
                <div class="text-xs pt-1 ms-4 text-lightest" style="margin-top: -10px;">STAFF ADMIN CONSOLE</div>
            </div>
            <div class="float-start">
                <div class="p-3">
                    <h3 class="text-lightest">Admin</h3>
                </div>
            </div>
            <div class="p-3 float-end">
                <div class="navtop-right-user">
                    <button class="btn btn-tiny btn-light-outline" onclick="window.open('<?php echo APP_URL; ?>', '_blank')"> Mainsite <i class="fa-solid fa-up-right-from-square"></i></button>
                    <i class="fa-solid fa-bug session-slider-toggle text-admin" style="cursor: pointer;"></i>
                    <i class="fa-solid fa-user text-lightest"></i>
                    <span class="text-lightest">Logged in as: <?php if(isset($_SESSION['admin_email'])){ echo $_SESSION['admin_email']; }else{ echo "Guest"; } ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="sessionslider" class="sessionslider collapsed">
    <i class="fa-solid fa-xmark session-slider-close" style="position: absolute;top: 10px;right: 10px;cursor: pointer;"></i>
    <h5>SESSION</h5>   
    <?php if(isset($_SESSION)){ ?>
    <pre><?php print_r($_SESSION); ?></pre>
    <?php } else { ?>
    <p>No session data</p>
    <?php } ?>
    <?php if(isset($_COOKIE)){ ?>
    <h5>COOKIES</h5>   
    <pre><?php print_r($_COOKIE); ?></pre>
    <?php } else { ?>
    <p>No cookie data</p>
    <?php } ?>
</div>

<script>
    $(document).ready(function(){
        console.log("Admin navtop script loaded");
        console.log("Toggle elements found:", $('.session-slider-toggle').length);
        console.log("Session slider found:", $("#sessionslider").length);
        
        $('.session-slider-toggle').click(function() {
            console.log("session-slider-toggle clicked");
            $("#sessionslider").toggleClass("collapsed expanded");
        });
        
        $('.session-slider-close').click(function() {
            console.log("session-slider-close clicked");
            $("#sessionslider").toggleClass("collapsed expanded");
        });
    });
</script>
