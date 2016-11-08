<!DOCTYPE html>
    <head>
        <title>
            <?php
                echo "Financial REporter";
            ?>
        </title>
        <?php wp_head(); ?>
    </head>
    <body>
        <nav>
            <div>
                <?php wp_nav_menu( array( 'menu' => 'header-menu')); ?>
            </div>
        </nav>

        <h1>
            <?php
                echo "Financial Reporter";
            ?>
        </h1>