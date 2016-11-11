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
                            "INSERT INTO expense (employee_id, category, cost, description) VALUES(%d, %d, %d, %s)",
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
                        "expense",
                        array("id" => $_GET["expenseId"], "approved" => 0),
                        array("%d", "%d")
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
                        $categories = $wpdb->get_results("SELECT * FROM expense_category");

                        foreach($categories as $key => $category){
                            echo "<option value='" . $category->id . "'>" . $category->name . "</option>";
                        }
                    ?>
                </select>
            </label>
            <label>Cost (€)
                <input type="number" name="cost" min="0" required>
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
                        <th>ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Category</th>
                        <th>Cost</th>
                        <th>Receipt</th>
                        <th>Description</th>
                        <th>Approved</th>
                        <th>Action</th>
                    </tr>
                    <?php
                        global $wpdb;
                        $expenses = $wpdb->get_results("SELECT * FROM expense WHERE employee_id=" . get_current_user_id());

                        foreach ($expenses as $key => $expense){
                            // Setting up values
                            $expenseDate = date_create($expense->date_submitted);

                            // Creating Table Row
                            echo "<tr>";
                            echo "<td>#" . $expense->id . "</td>";
                            echo "<td>" . date_format($expenseDate, "jS M Y") . "</td>";
                            echo "<td>" . date_format($expenseDate, "G:ha") . "</td>";
                            echo "<td>" . lp_get_category($expense->category) . "</td>";
                            echo "<td>&euro;" . $expense->cost . "</td>";
                            if($expense->receipt == null){
                                echo "<td>None</td>";
                            } else {
                                echo "<td><a href='" . $expense->receipt . "' target='_blank'>View</a></td>";
                            }
                            echo "<td>" . $expense->description . "</td>";
                            echo "<td>" . $expense->approved . "</td>";
                            if($expense->approved == "Pending"){
                                echo "<td><a href='./?action=removeExpense&expenseId=" . $expense->id . "'>Remove</a></td>";
                            } else {
                                echo "<td>None</td>";
                            }
                            echo "</tr>";
                        }
                    ?>
                </table>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
