<?php // Controls Employee Expenses Page ?>
<?php
    // Only logged in subscribers can access this page
    if(is_user_logged_in()) {
        if(get_user_role() != "subscriber"){
            wp_redirect("/ssp2/assignment02/expenses");
        }
    } else {
        wp_redirect("/ssp2/assignment02/user-login");
    }

    if(isset($_GET["action"])){
        if($_GET["action"] == "addExpense"){
            //var_dump($_POST);
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
            <div class="col-xs-6">
                <h3>Add an Expense</h3>
                <form method="POST" action="./?action=addExpense">
                    <label>Category
                        <select name="category">
                            <option selected disabled class="hidden"></option>
                            <option value="food">Food</option>
                            <option value="petrol">Petrol</option>
                            <option value="accommodation">Accommodation</option>
                            <option value="transport">Transport</option>
                            <option value="other">Other</option>
                        </select>
                    </label>
                    <label>Cost (€)
                        <input type="text" name="cost">
                    </label>
                    <label class="fullWidth">Attach Receipt
                        <input type="file" name="receipt">
                    </label>
                    <label class="fullWidth">Description
                        <textarea name="description" rows="4">
                        </textarea>
                    </label>
                    <input type="submit" value="Add Expense">
                </form>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
