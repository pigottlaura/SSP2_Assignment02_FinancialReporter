<?php // Controls Employer Expenses Page ?>
<?php
    // Only logged in administrators can access this page
    if(is_user_logged_in()) {
        if(lp_financialReporter_User::getUserRole() == "administrator") {
            if (isset($_GET["action"])) {
                lp_financialReporter_User::attemptAction($_GET["action"]);
            }
        } else{
            wp_redirect("/ssp2/assignment02/expenses");
        }
    } else {
        wp_redirect("/ssp2/assignment02/user-login");
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
                    <?php
                        $allExpenses = lp_financialReporter_Expense::getAllExpenses();
                        if(count($allExpenses) > 0) {
                            foreach($allExpenses as $key => $expense){
                                // Setting up values
                                $expenseDate = date_create($expense->date_submitted);

                                // Creating Table Row
                                echo "<tr>";
                                echo "<td>#" . $expense->id . "</td>";
                                echo "<td>#" . $expense->employee_id . "</td>";
                                echo "<td>" . $expense->display_name . "</td>";
                                echo "<td>" . date_format($expenseDate, lp_financialReporter_Expense::$expenseDateFormat) . "</td>";
                                echo "<td>" . $expense->category_name . "</td>";
                                echo "<td>&euro;" . $expense->cost . "</td>";
                                if($expense->receipt == null){
                                    echo "<td>None</td>";
                                } else {
                                    echo "<td><a href='" . $expense->receipt . "' target='_blank'>View</a></td>";
                                }
                                echo "<td>" . $expense->description . "</td>";
                                echo "<td>" . $expense->status . "</td>";
                                if($expense->status == "Pending"){
                                    echo "<td>";
                                    echo "<a href='./?action=expenseApproval&decision=1&expenseId=" . $expense->id . "'>Approve</a>";
                                    echo " / ";
                                    echo "<a href='./?action=expenseApproval&decision=0&expenseId=" . $expense->id . "'>Reject</a>";
                                    echo "</td>";
                                } else {
                                    echo "<td>Completed</td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>No employees have claimed for expenses yet</td></tr>";
                        }
                    ?>
                </table>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
