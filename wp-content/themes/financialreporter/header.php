<!DOCTYPE html>
<html class="noMargin">
    <head>
        <title>
            <?php
                echo "Financial Reporter";
            ?>
        </title>
        <link rel="shortcut icon" href="<?php echo get_bloginfo('template_url'); ?>/favicon.ico" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('template_url'); ?>/style.css">
        <script src="<?php echo get_bloginfo('template_url'); ?>/script.js"></script>
        <?php wp_head(); ?>
    </head>
    <body class="container-fluid<?php if(is_user_logged_in()){ echo ' wp-logged-in wp-role-' . get_user_role(); } ?>">
        <?php
            if(is_user_logged_in()){
                show_admin_bar(false);

                echo "<div class='row' id='customAdminBar'>";
                echo "<div class='col-xs-12'>";

                $userRole = get_user_role();
                $currentUser = wp_get_current_user();
                if($userRole == "subscriber"){

                    echo "Welcome back " . $currentUser->display_name . "!";
                    echo "<button><a href='/ssp2/assignment02/expenses?action=addExpense'>Add an Expense</a></button>";
                    echo "<button><a href='/ssp2/assignment02/expenses?action=viewAll'>View my Expenses</a></button>";
                } else if($userRole == "administrator"){
                    echo "Hello Admin " . $currentUser->display_name . "!";
                    echo "<button><a href='/ssp2/assignment02/expenses?action=viewAll'>View all Expenses</a></button>";
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
                    <?php wp_nav_menu( array( 'menu' => 'header-menu')); ?>
                </nav>
            </div>
        </div>