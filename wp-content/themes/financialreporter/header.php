<!DOCTYPE html>
    <head>
        <title>
            <?php
                echo "Financial Reporter";
            ?>
        </title>
        <link rel="shortcut icon" href="<?php echo get_bloginfo('template_url'); ?>/favicon.ico" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('template_url'); ?>/style.css">
        <?php wp_head(); ?>
    </head>
    <body class="container-fluid">
        <?php
            if(is_user_logged_in()){
                if(get_user_role() == "subscriber"){
                    $currentUser = wp_get_current_user();
                    show_admin_bar(false);

                    echo "<div class='row' id='customAdminBar'>";
                    echo "Welcome back " . $currentUser->display_name . "!";
                    echo "<button><a href='" . wp_logout_url(home_url()) . "'>Logout</a></button>";
                    echo "</div>";
                }
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