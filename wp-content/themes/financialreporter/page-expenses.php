<?php // Controls Expenses Page ?>
<?php
    // Only logged in users can access this page
    if(!is_user_logged_in()){
        wp_redirect("/ssp2/assignment02/user-login");
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
            <div class="col-xs-12">
                <?php
                    if(isset($_GET["action"])){
                        if($_GET["action"] == "addExpense"){
                            echo "Add a new expense";
                        } else if ($_GET["action"] == "viewAll"){
                            echo "View All Expenses";
                        }
                    }
                ?>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
