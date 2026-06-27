<!DOCTYPE html>
<html lang="en">
    <?php include __DIR__ . '/admin-pagehead.php'; ?>

    <body>

        <?php include __DIR__ . '/admin-navtop.php'; ?>
        <div class="main admin w-100 bkgd-lighter" style="margin-top: 80px;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-2 bkgd-lightest border-right vh-100 leftnav" style="max-width: 250px;">
                        <?php include __DIR__ . '/admin-leftnav.php'; ?>
                    </div>
                    <div class="col-10">
                    <?php if (isset($page_content)): ?>
                        <div class="main">
                            <?php echo $page_content; ?>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
       
    </body>
<script>
    $(document).ready(function(){
        setTimeout(function(){
            if ($(".adminalert").length > 0) {
                $(".adminalert").fadeOut(1000);
            }
        }, 3000);
    });
</script>

</html>