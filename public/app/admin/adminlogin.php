<?php
ob_start(); // Move this to the very top before any includes

require_once __DIR__ . "/../../config-app/config.php"; // App config. should in turn pull in project config
require_once APP_PUBLIC_PATH . "/includes/functions-universal.php";

$pagetitle = "Admin Login";
// Start output buffering to capture page content

if(isset($_GET['logout'])){
    unset($_SESSION['admin_email']);
    unset($_SESSION['admin_id']);
    $_SESSION['result']['type'] = "success";
    $_SESSION['result']['message'] = "Logged out successfully";
    header('Location: '.APP_ADMIN_URL.'/adminlogin.php');
    exit();

}
if(isset($_POST['email']) && isset($_POST['password'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $result = validateadminuser($email, $password); // returns array("success" => true, "adminuser_id" => $adminuser['adminuser_id'], "adminuser_email" => $adminuser['adminuser_email']) 
    if($result['success']){
        $_SESSION['admin_email'] = $result['adminuser_email'];
        $_SESSION['admin_id'] = $result['adminuser_id'];
        $_SESSION['result']['type'] = "success";
        $_SESSION['result']['message'] = "Logged in successfully";
        if(isset($_SESSION['last_url'])){
            $last_url = $_SESSION['last_url'];
            unset($_SESSION['last_url']);
            header('Location: '.$last_url);
        }else{
            header('Location: '.APP_ADMIN_URL.'/index.php');
        }
        exit(); // Add exit after redirect to ensure no further code is executed
    }else{
        $_SESSION['result']['type'] = "error";
        $_SESSION['result']['message'] = $result['error'];
    }
}
?>
    <div class="row">
        <div class="col-md-2 offset-md-5" >
            <h2 class="text-center"><?php echo $pagetitle; ?></h2>
            <form action="<?php echo APP_ADMIN_URL; ?>/adminlogin.php" method="post">
                <div class="form-group ">
                    <div class="form-label" for="email">Email</div>
                    <input type="text" name="email" placeholder="Email" class="form-control">
                </div>
                <div class="form-group ">
                    <div class="form-label" for="password">Password</div>
                    <input type="password" name="password" placeholder="Password" class="form-control">
                </div>
                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-primary float-end">Login</button>
                </div>
            </form>

        </div>
    </div>



<?php
// Capture the page content and store it in a variable
$page_content = ob_get_clean();

// Include the layout which will use $page_content
require_once __DIR__ . '/../elements/layout-public.php';
?> 