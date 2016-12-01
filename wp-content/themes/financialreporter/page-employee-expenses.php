<?php // Controls Employee Expenses Page ?>
<?php
    // Only logged in subscribers can access this page
    if(is_user_logged_in()) {
        if(lp_financialReporter_User::getUserRole() != "subscriber") {
            // This user does not have the right role to access this page
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
        <h3>Add an Expense</h3>
        <form id="addNewExpense">
            <label>Category
                <select name="category" required>
                    <option selected disabled class="hidden"></option>
                    <?php
                        $categories = lp_financialReporter_Expense::getAllCategories();
                        foreach($categories as $key => $category){
                            echo "<option value='" . $category->id . "'>" . $category->name . "</option>";
                        }
                    ?>
                </select>
            </label>
            <label>Cost (€)
                <input type="number" name="cost" min="0" step="0.01" required>
            </label>
            <label class="fullWidth">Attach Receipt
                <input type="file" name="receipt">
            </label>
            <label class="fullWidth">Description
                <textarea name="description" rows="4" required></textarea>
            </label>
            <input type="submit" value="Add Expense">
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
                            <th id="id" class="orderHeading">ID</th>
                            <th id="date_submitted" class="orderHeading">Submitted On</th>
                            <th id="category_name" class="orderHeading">Category</th>
                            <th id="cost" class="orderHeading">Cost</th>
                            <th id="receipt" class="orderHeading">Receipt</th>
                            <th id="description" class="orderHeading">Description</th>
                            <th id="status" class="orderHeading">Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="employeeExpenseData">
                        <?php
                            lp_financialReporter_Expense::getAllExpensesForCurrentUser();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
