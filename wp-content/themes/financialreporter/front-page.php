<?php // Controls the Home Page?>
<?php get_header(); ?>

<div class="row">
    <div class="col-xs-3">
        <?php include("sidebar.php"); ?>
    </div>
    <div class="col-xs-9">
        home
        <?php include("components/the_loop_noLinks.php"); ?>
    </div>
</div>


<?php get_footer(); ?>