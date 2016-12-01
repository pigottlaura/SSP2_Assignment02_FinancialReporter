<?php // Controls Employer Expense Categories Page ?>
<?php
    // Only logged in administrators can access this page
    if(is_user_logged_in()) {
        if(lp_financialReporter_User::getUserRole() != "administrator") {
            // This user is not an administrator
            wp_redirect(home_url("/expenses"));
        }
    } else {
        // This user is not logged in
        wp_redirect(home_url("/user-login"));
    }
?>
<?php get_header(); ?>

<div class="row">
    <div class="col-xs-3">
        <h3>Add a New Category</h3>
        <form id="addNewExpenseCategory">
            <label class="fullWidth">Category Name
                <input type="text" name="categoryName" required>
            </label>
            <input type="submit" value="Add Category">
        </form>
    </div>
    <div class="col-xs-9">
        <div class="row">
            <div class="col-xs-12">
                <?php include("components/the_loop_noLinks.php"); ?>
            </div>
        </div>
        <div class="row">
                <table>
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="employerExpenseCategories">
                        <?php
                            $categories = lp_financialReporter_Expense::getAllCategories();
                            echo $categories->html;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>