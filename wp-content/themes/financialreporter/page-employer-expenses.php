<?php // Controls Employer Expenses Page ?>
<?php
    // Only logged in administrators can access this page
    if(is_user_logged_in()) {
        if(get_user_role() == "administrator") {
            // Allow this user to complete the following actions
            if (isset($_GET["action"])) {
                global $wpdb;

                switch ($_GET["action"]) {
                    case "expenseApproval": {
                        if (isset($_GET["expenseId"]) && isset($_GET["decision"])) {
                            $expenseDecision = $_GET["decision"] == 0 ? "Rejected" : "Approved";
                            $wpdb->update("expense",
                                array("status" => $expenseDecision, "decision_date" => date("Y-m-d H:i:s")),
                                array("id" => $_GET["expenseId"]),
                                array("%s", "%s"),
                                array("%d")
                            );
                            wp_redirect("./");
                        }
                        break;
                    }
                }
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
                <?php // The Loop ?>
                <?php if(have_posts()) : while(have_posts()) : the_post(); ?>
                    <h2><?php the_title(); ?></h2>
                    <?php the_content(); ?>
                <?php endwhile; endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
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
                        <th id="approved" class="orderHeading">Approved</th>
                        <th>Action</th>
                    </tr>
                    <?php
                        global $wpdb;
                        if(isset($_COOKIE["orderBy"]) && isset($_COOKIE["order"])){
                            $orderBy = $_COOKIE["orderBy"];
                            $order = $_COOKIE["order"];
                        } else {
                            $orderBy = "date_submitted";
                            $order = "asc";
                        }
                        $expenses = $wpdb->get_results("SELECT expense.*, wp_users.display_name, expense_category.name as 'category_name' FROM expense LEFT JOIN wp_users ON expense.employee_id = wp_users.id LEFT JOIN expense_category ON expense.category = expense_category.id ORDER BY " . $orderBy . " " . $order);

                        if(count($expenses) > 0) {
                            foreach($expenses as $key => $expense){
                                // Setting up values
                                $expenseDate = date_create($expense->date_submitted);

                                // Creating Table Row
                                echo "<tr>";
                                echo "<td>#" . $expense->id . "</td>";
                                echo "<td>#" . $expense->employee_id . "</td>";
                                echo "<td>" . $expense->display_name . "</td>";
                                echo "<td>" . date_format($expenseDate, "jS M Y @ G:ia") . "</td>";
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
