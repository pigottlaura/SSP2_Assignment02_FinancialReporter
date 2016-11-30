<!DOCTYPE html>
<html class="noMargin">
    <head>
        <title>
            <?php
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
                show_admin_bar(false);

                echo "<div class='row' id='customAdminBar'>";
                echo "<div class='col-xs-12'>";

                $userRole = lp_financialReporter_User::getUserRole();
                $currentUser = wp_get_current_user();
                if($userRole == "subscriber"){
                    echo "Welcome back " . $currentUser->display_name . "!";
                } else if($userRole == "administrator"){
                    echo "Hello Admin " . $currentUser->display_name . "!";
                    echo "<button><a href='" . home_url("/expense-categories") . "'>View Expense Categories</a></button>";
                }

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
                       wp_nav_menu(array("menu_id" => get_option("lp_financialReporter_navMenuId")));
                    ?>
                </nav>
            </div>
        </div>