<?php // Controls Employer Expense Categories Page ?>
<?php
    // Only logged in administrators can access this page
    if(is_user_logged_in()) {
        if(lp_financialReporter_User::getUserRole() == "administrator") {
            if (isset($_GET["action"])) {
                lp_financialReporter_User::attemptAction($_GET["action"]);
            }
        } else{
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
        <form method="POST" action="./?action=addNewExpenseCategory">
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
                    <tr>
                        <th>Category Name</th>
                        <th>Action</th>
                    </tr>
                <?php
                    $categories = lp_financialReporter_Expense::getAllCategories();

                    foreach($categories as $key => $category){
                        echo "<tr>";
                        echo "<td>" . $category->name . "</td>";
                        echo "<td>";
                        if(lp_financialReporter_Expense::categoryInUse($category->id)){
                            echo "None - category in use";
                        } else {
                            echo "<a href='./?action=removeExpenseCategory&categoryId=" . $category->id . "'>Remove</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                ?>
                </table>
            </div>
        </div>
    </div>
</div>
