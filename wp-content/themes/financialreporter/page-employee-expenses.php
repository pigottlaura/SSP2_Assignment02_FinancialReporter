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
        global $wpdb;

        switch($_GET["action"]) {
            // Adding an expense
            case "addExpense": {
                if (count($_POST) > 0) {
                    if (lp_validate_data($_POST, array())) {
                        $wpdb->query($wpdb->prepare(
                            "INSERT INTO lp_financialReporter_expense (employee_id, category, cost, description) VALUES(%d, %d, %d, %s)",
                            array(get_current_user_id(), number_format($_POST['category'], 0), number_format($_POST['cost'], 2), $_POST['description'])
                        ));
                        wp_redirect("./");
                    }
                }
                break;
            }
            // Allowing employees to remove expenses that have not yet been approved
            case "removeExpense": {
                if(isset($_GET["expenseId"])){
                    $wpdb->delete(
                        "lp_financialReporter_expense",
                        array("id" => $_GET["expenseId"], "status" => "Pending"),
                        array("%d", "%s")
                    );
                    wp_redirect("./");
                }
                break;
            }
        }
    }
?>
<?php get_header(); ?>

<div class="row">
    <div class="col-xs-3">
        <h3>Add an Expense</h3>
        <form method="POST" action="./?action=addExpense">
            <label>Category
                <select name="category" required>
                    <option selected disabled class="hidden"></option>
                    <?php
                        // Loading Categories in from Database
                        global $wpdb;
                        $categories = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense_category");

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
                <h3>Expenses</h3>
                <table>
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
                    <?php
                        global $wpdb;
                        if(isset($_COOKIE["orderBy"]) && isset($_COOKIE["order"])){
                            $orderBy = $_COOKIE["orderBy"];
                            $order = $_COOKIE["order"];
                        } else {
                            $orderBy = "date_submitted";
                            $order = "asc";
                        }
                        $expenses = $wpdb->get_results("SELECT lp_financialReporter_expense.*, lp_financialReporter_expense_category.name as 'category_name' FROM lp_financialReporter_expense LEFT JOIN lp_financialReporter_expense_category ON lp_financialReporter_expense.category = lp_financialReporter_expense_category.id WHERE lp_financialReporter_expense.employee_id = " . get_current_user_id() . " ORDER BY " . $orderBy . " " . $order);


                        if(count($expenses) > 0){
                            foreach ($expenses as $key => $expense){
                                // Setting up values
                                $expenseDate = date_create($expense->date_submitted);

                                // Creating Table Row
                                echo "<tr>";
                                echo "<td>#" . $expense->id . "</td>";
                                echo "<td>" . date_format($expenseDate, "jS M Y @ G:ia") . "</td>";
                                echo "<td>" . lp_get_category($expense->category) . "</td>";
                                echo "<td>&euro;" . $expense->cost . "</td>";
                                if($expense->receipt == null){
                                    echo "<td>None</td>";
                                } else {
                                    echo "<td><a href='" . $expense->receipt . "' target='_blank'>View</a></td>";
                                }
                                echo "<td>" . $expense->description . "</td>";
                                echo "<td>" . $expense->status . "</td>";
                                if($expense->status == "Pending"){
                                    echo "<td><a href='./?action=removeExpense&expenseId=" . $expense->id . "'>Remove</a></td>";
                                } else {
                                    echo "<td>None</td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>You no previous expense claims</td></tr>";
                        }

                    ?>
                </table>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
