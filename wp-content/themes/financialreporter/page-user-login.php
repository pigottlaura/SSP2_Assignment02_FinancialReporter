<?php // Controls User Login Page ?>
<?php
    // Only people that are not logged in can access this page page
    if(is_user_logged_in()){
        wp_redirect(home_url("/expenses"));
    }
?>
<?php get_header(); ?>

<div class="row">
    <div class="col-xs-3">
        <?php include("sidebar.php"); ?>
    </div>
    <div class="col-xs-9">
        <div class="row">
            <div class="col-xs-12">
                <?php include("components/the_loop_noLinks.php"); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <?php
                    $loginFormArgs = array(
                        "label_remember" => __("Remember Me"),
                        "label_log_in" => __("Login"),
                        "redirect" => home_url("/expenses"),
                        "remember" => true
                    );
                    wp_login_form($loginFormArgs);
                ?>
                <a href="<?php echo home_url("/user-register"); ?>">Register</a> or
                <a href="<?php echo wp_lostpassword_url(home_url("/user-login")); ?>">Lost Password My Password</a>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
