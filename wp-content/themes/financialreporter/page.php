<?php // Controls Default Pages ?>
<?php get_header(); ?>

<div class="row">
    <div class="col-xs-3">
        <?php include("sidebar.php"); ?>
    </div>
    <div class="col-xs-9">
        <?php include("components/the_loop.php"); ?>
    </div>
</div>


<?php get_footer(); ?>
