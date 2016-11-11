<?php // Controls Employer Expenses Page ?>
<?php
    // Only logged in administrators can access this page
    if(is_user_logged_in()) {
        if(get_user_role() != "administrator"){
            wp_redirect("/ssp2/assignment02/expenses");
        }
    } else {
        wp_redirect("/ssp2/assignment02/user-login");
    }

    if(isset($_GET["action"])){
        global $wpdb;

        switch($_GET["action"]){
            case "expenseApproval": {
                if(isset($_GET["expenseId"]) && isset($_GET["decision"])){
                    $expenseDecision = $_GET["decision"] == 0 ? "No" : "Yes";
                    $wpdb->update("expense",
                        array("approved" => $expenseDecision),
                        array("id" => $_GET["expenseId"]),
                        array("%s"),
                        array("%d")
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
            <div class="col-xs-12">
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
                        $expenses = $wpdb->get_results("SELECT * FROM expense");
                        foreach($expenses as $key => $expense){
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
                                echo "<td>";
                                echo "<a href='./?action=expenseApproval&decision=1&expenseId=" . $expense->id . "'>Approve</a>";
                                echo " / ";
                                echo "<a href='./?action=expenseApproval&decision=0&expenseId=" . $expense->id . "'>Reject</a>";
                                echo "</td>";
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
