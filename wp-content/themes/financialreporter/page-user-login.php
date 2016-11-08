<?php // Controls User Login Page ?>
<?php
    // Only people that are not logged in can access this page page
    if(is_user_logged_in()){
        wp_redirect("/ssp2/assignment02/expenses");
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
                <?php // The Loop ?>
                <?php if(have_posts()) : while(have_posts()) : the_post(); ?>
                    <h2><?php the_title(); ?></h2>
                    <?php the_content(); ?>
                <?php endwhile; endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <?php
                    $loginFormArgs = array(
                        "label_remember" => __("Remember Me"),
                        "label_log_in" => __("Login"),
                        "redirect" => "/ssp2/assignment02/expenses",
                        "remember" => true
                    );
                    wp_login_form($loginFormArgs);
                ?>
                or <a href="/ssp2/assignment02/user-register">Register</a>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
