<?php // Controls Employer Expenses Page ?>
<?php
    // Only logged in administrators can access this page
    if(is_user_logged_in()) {
        if(lp_financialReporter_User::getUserRole() != "administrator") {
            // This user is not an administrator
            wp_redirect(home_url("/expenses"));
        }
    } else {
        wp_redirect(home_url("/user-login"));
    }
?>
<?php get_header(); ?>

<div class="row">
    <div class="col-xs-12">
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
                            <th id="employee_id" class="orderHeading">Employee ID</th>
                            <th id="display_name" class="orderHeading">Employee Name</th>
                            <th id="date_submitted" class="orderHeading">Submitted On</th>
                            <th id="category_name" class="orderHeading">Category</th>
                            <th id="cost" class="orderHeading">Cost</th>
                            <th id="receipt" class="orderHeading">Receipt</th>
                            <th id="description" class="orderHeading">Description</th>
                            <th id="status" class="orderHeading">Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="employerExpenseData">
                        <?php
                            $expenseData = lp_financialReporter_Expense::getAllExpenses();
                            echo $expenseData->html;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
