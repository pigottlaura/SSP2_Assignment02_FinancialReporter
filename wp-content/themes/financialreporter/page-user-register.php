<?php // Controls User Registeration Page ?>
<?php
    // Only people that are not logged in can access this page page
    if(is_user_logged_in()){
        wp_redirect("/ssp2/assignment02/expenses");
    } else {
        // If the user has submitted the registration form
        if(count($_POST) > 0){
            $userData = array(
                "user_login" => $_POST["username"],
                "user_pass" => null,
                "display_name" => $_POST["first_name"] . " " . $_POST["last_name"],
                "user_pass" => $_POST["password"]
            );

            $userId = wp_insert_user($userData);

            if(!is_wp_error($userId)){
                update_user_meta($userId, "first_name", $_POST["first_name"]);
                update_user_meta($userId, "last_name", $_POST["last_name"]);

                wp_set_current_user($userId);
                wp_set_auth_cookie($userId);
                // wp_mail();
                wp_redirect("/ssp2/assignment02/expenses");
            }
        }
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
            <div class="col-xs-8">
                <form method="POST" action="./">
                    <label>First Name
                        <input type="text" name="first_name">
                    </label>
                    <label>Last Name
                        <input type="text" name="last_name">
                    </label>
                    <label>Username
                        <input type="text" name="username">
                    </label>
                    <label>Password
                        <input type="password" name="password">
                    </label>
                    <input type="submit" value="Register as an Employee">
                </form>
                or <a href="/ssp2/assignment02/user-login">Login</a>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
