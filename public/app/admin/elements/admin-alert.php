<?php if(isset($_SESSION['result'])){ ?>
        <div class="adminalert alert alert-<?php echo $_SESSION['result']['type']; ?>" style="width: 500px; position: fixed; right:10px; top:0; z-index: 1500; max-height: 60px;">
            <?php echo $_SESSION['result']['message']; ?>
        </div>
<?php unset($_SESSION['result']);   } ?>