<!DOCTYPE html>
<html class="noMargin">
    <head>
        <title>
            <?php
                // Displaying the title as the name of the site, followed
                // by the page name (if not the home page)
                echo get_bloginfo("name");
                if(!is_home()){
                    echo " | ";
                    the_title();
                }
            ?>
        </title>
        <link rel="shortcut icon" href="<?php echo get_bloginfo('template_url'); ?>/favicon.ico" />
        <?php wp_head(); ?>
    </head>
    <body class="container-fluid<?php if(is_user_logged_in()){ echo ' wp-logged-in wp-role-' . lp_financialReporter_User::getUserRole(); } ?>">
        <?php
            if(is_user_logged_in()){
                // Hiding the default WP admin bar
                show_admin_bar(false);

                echo "<div class='row' id='customAdminBar'>";
                echo "<div class='col-xs-12'>";

                // Getting the details of the current user
                $currentUser = wp_get_current_user();

                // Displaying their name in the custom admin bar
                echo "Welcome back " . $currentUser->display_name . "!";

                // Checking if this user is a admin, and if so then displaying
                // the button to view expense categories
                if(lp_financialReporter_User::getUserRole() == "administrator"){
                    echo "<button><a href='" . home_url("/expense-categories") . "'>View Expense Categories</a></button>";
                }

                // Displaying the logout button, using the logout url (which will redirect
                // back to the home page once the user has logged out)
                echo "<button><a href='" . wp_logout_url(home_url()) . "'>Logout</a></button>";
                echo "</div>";
                echo "</div>";
            }
        ?>
        <div class="row">
            <div class="col-xs-3">
                <h1>
                    <?php
                    echo "Financial Reporter";
                    ?>
                </h1>
            </div>
            <div class="col-xs-8">
                <nav class="navbar navbar-default">
                    <?php
                        // Displaying the custom nav menu
                        wp_nav_menu(array("menu_id" => get_option("lp_financialReporter_navMenuId")));
                    ?>
                </nav>
            </div>
        </div>