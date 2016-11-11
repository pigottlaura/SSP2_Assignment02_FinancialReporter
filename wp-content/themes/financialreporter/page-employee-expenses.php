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
        if($_GET["action"] == "addExpense" && count($_POST) > 0){
            //var_dump($_POST);
            global $wpdb;

            if(validateData($_POST, array())){
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO expense (employee_id, category, cost, description) VALUES(%d, %d, %d, %s)",
                    array(get_current_user_id(), number_format($_POST['category'], 0), number_format($_POST['cost'], 2), $_POST['description'])
                ));
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
            <div class="col-xs-6">
                <h3>Add an Expense</h3>
                <form method="POST" action="./?action=addExpense">
                    <label>Category
                        <select name="category">
                            <option selected disabled class="hidden"></option>
                            <?php
                                // Loading Categories in from Database
                                global $wpdb;
                                $categories = $wpdb->get_results("SELECT * FROM expense_category");

                                foreach($categories as $key => $category){
                                    echo "<option value='" . $category->id . "'>" . $category->name . "</option>";
                                }
                            ?>
                        </select>
                    </label>
                    <label>Cost (€)
                        <input type="number" name="cost" value="0.00" min="0">
                    </label>
                    <label class="fullWidth">Attach Receipt
                        <input type="file" name="receipt">
                    </label>
                    <label class="fullWidth">Description
                        <textarea name="description" rows="4"></textarea>
                    </label>
                    <input type="submit" value="Add Expense">
                </form>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
