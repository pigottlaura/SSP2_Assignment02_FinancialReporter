<?php
    class lp_financialReporter_Expense {
        // Creating a common date format, that will be used across employee and
        // employer displays of expenses i.e. 21st Nov 2016 @ 20:39pm
        public static $expenseDateFormat = "jS M Y @ G:ia";

        function __construct() {
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        public static function addExpense($expenseData, $files=null){
            $response = (object) array(
                "successful" => false,
                "errors" => array()
            );

            // Ensuring this user is a subscriber i.e. employee
            if(lp_financialReporter_User::getUserRole() == "subscriber") {
                array_push($response->errors, "This user is a sub");
                // Checking that the expense data has been provided
                if (count($expenseData) > 0) {

                    $dataValidated = lp_financialReporter_InputData::validateData($expenseData, array(
                        "required" => array("category", "cost", "description"),
                        "number" => array("category", "cost")
                    ));

                    foreach($dataValidated->errors as $key => $error){
                        array_push($response->errors, $error);
                    }

                    // Validating the data passed as part of the expense
                    if ($dataValidated->dataValidated) {

                        // Sanitising the data passed as part of the expense
                        $sanitisedData = lp_financialReporter_InputData::sanitiseData($expenseData);

                        // Creating a default of null for the receipt path. This may remain
                        // num if 1) no files were uploaded along with this request or
                        // 2) the file fails to save to the server
                        $receiptPath = null;

                        // Only works locally at the moment (so checking if on localhost)
                        // Checking if the files array contains a "receipt" parameter
                        // i.e. which will reference the file the user has uploaded with the
                        // expense claim, and that the size of the file is greater than 0
                        // i.e. that a file was actually included in the submission
                        if(isset($files["receipt"]) && $files["receipt"]["size"] > 0){
                            // Attempting the save the file using the saveFile static method
                            // of the lp_financialReporter_File class. Storing the result in a
                            // temporary variable
                            $saveFile = lp_financialReporter_File::saveFile($files["receipt"]);

                            // Checking if any errors were returned from the saveFile attempt
                            if(count($saveFile->errors) > 0) {
                                foreach ($saveFile->errors as $key => $error) {
                                    array_push($response->errors, $error);
                                    $response->successful = false;
                                }
                            }

                            // Checking if a file path was returned from the saveFile attempt
                            if(isset($saveFile->filepath)) {
                                // If so, then storing it in the temporary receiptPath variable,
                                // so that it can be passed to the database and stored in the receipt
                                // column of the expense
                                $receiptPath = $saveFile->filepath;
                            }
                        }

                        // Accessing the global wpdb variable, to access the database
                        global $wpdb;

                        // If debug is on, then log all errors from the database (TESTING PURPOSES)
                        if(get_option("lp_financialReporter_debug") == "on"){
                            $wpdb->show_errors(true);
                        }

                        // Inserting the new expense into the expense table, using a prepared statement.
                        // Passing the category as an int (no decimal places), and the cost as a 2 decimal
                        // float value.
                        $wpdb->query($wpdb->prepare(
                            "INSERT INTO lp_financialReporter_expense (employee_id, category, receipt, cost, description) VALUES(%d, %d, %s, %d, %s)",
                            array(get_current_user_id(), number_format($sanitisedData['category'], 0), $receiptPath, number_format($sanitisedData['cost'], 2), $sanitisedData['description'])
                        ));

                        $response->successful *= true;
                    }
                }
            } else {
                array_push($response->errors, "This user is not a subscriber");
            }

            return $response;
        }

        // Allowing employees to remove expenses that have not yet been approved
        public static function removeExpense($expenseId){
            $response = (object) array(
                "successful" => false,
                "errors" => array()
            );
            
            // Ensuring this user is a subscriber i.e. employee
            if(lp_financialReporter_User::getUserRole() == "subscriber") {

                // Checking that the expenseId has infact been passed as a param
                // to the query string
                if (isset($expenseId)) {

                    // Accessing the global wpdb variable, to access the database
                    global $wpdb;

                    // If debug is on, then log all errors from the database (TESTING PURPOSES)
                    if(get_option("lp_financialReporter_debug") == "on"){
                        $wpdb->show_errors(true);
                    }

                    // Deleting this expense, double checking that not only does the
                    // id match, but that the status is defiantly pending (as expenses
                    // that have already been decided on cannot be deleted)
                    $wpdb->delete(
                        "lp_financialReporter_expense",
                        array("id" => $_POST["expenseId"], "status" => "Pending"),
                        array("%d", "%s")
                    );
                    $response->successful = true;
                }
            }
            return $response;
        }

        public static function removeAllExpensesForUser($userId){
            global $wpdb;
            $wpdb->delete(
                "lp_financialReporter_expense",
                array("employee_id" => $userId),
                array("%d")
            );
        }

        public static function makeDecisionOnExpense($expenseId, $decision) {

            // Ensuring this user is a administrator i.e. employer
            if(lp_financialReporter_User::getUserRole() == "administrator") {

                // Ensuring that both the expense id and decision value were passed
                // to the query string
                if (isset($expenseId) && isset($decision)) {

                    // Accessing the global wpdb variable, to access the database
                    global $wpdb;

                    // Determing the employer's decision on this expense, based on a
                    // 0 or 1 value, passed as a param to the query string. The purpose
                    // of this is so no other value could ever be passed, as the status
                    // column of the expense table is of type ENUM, and can only accept
                    // "Rejected", "Approved" or it's default of "Pending"
                    $expenseDecision = $decision == 0 ? "Rejected" : "Approved";

                    // Updating the expense's status and decision date, based on the
                    // decising specified by the employer ie. Rejected or Approved, as
                    // well as passing the current date/time as the decision date
                    $wpdb->update("lp_financialReporter_expense",
                        array("status" => $expenseDecision, "decision_date" => date("Y-m-d H:i:s")),
                        array("id" => $_GET["expenseId"]),
                        array("%s", "%s"),
                        array("%d")
                    );
                }
            }
            // Redirecting the user to the current page (to remove all reference to the POST request,
            // as well as any GET params that were passed as part of this process
            wp_redirect("./");
        }

        public static function getAllExpenses() {
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );

            // Ensuring this user is a administrator i.e. employer
            if(lp_financialReporter_User::getUserRole() == "administrator") {
                // Accessing the global wpdb variable, to access the database
                global $wpdb;

                // Getting the sort order values from cookies, or defaults if no
                // cookies were provided
                $sortOrder = self::checkCookiesForSortOrder();

                // Querying the expense database for all columns in the expense, as well as the
                // category name (by completing a left join in the expense and expense_category
                // tables, based on the category id of the expense matching with the id of a category
                // in the expense_category table) and user's display name (by completing a left join
                // on the wp_users table, based on the employee id of the expense mathcing with an id
                // of a user). Ordering the resulting rows as specified by the values above (either
                // Cookie's or defaults)
                $allExpenses = $wpdb->get_results("SELECT lp_financialReporter_expense.*, wp_users.display_name, lp_financialReporter_expense_category.name as 'category_name' FROM lp_financialReporter_expense LEFT JOIN wp_users ON lp_financialReporter_expense.employee_id = wp_users.id LEFT JOIN lp_financialReporter_expense_category ON lp_financialReporter_expense.category = lp_financialReporter_expense_category.id ORDER BY " . $sortOrder->orderBy . " " . $sortOrder->order);

                if(count($allExpenses) > 0) {
                    foreach($allExpenses as $key => $expense){
                        // Setting up values
                        $expenseDate = date_create($expense->date_submitted);

                        // Creating Table Row
                        $response->html .= "<tr>";
                        $response->html .= "<td>#" . $expense->id . "</td>";
                        $response->html .= "<td>#" . $expense->employee_id . "</td>";
                        $response->html .= "<td>" . $expense->display_name . "</td>";
                        $response->html .= "<td>" . date_format($expenseDate, lp_financialReporter_Expense::$expenseDateFormat) . "</td>";
                        $response->html .= "<td>" . $expense->category_name . "</td>";
                        $response->html .= "<td>&euro;" . $expense->cost . "</td>";
                        if($expense->receipt == null){
                            $response->html .= "<td>None</td>";
                        } else {
                            $response->html .= "<td><a href='" . home_url($expense->receipt) . "' target='_blank'>View</a></td>";
                        }
                        $response->html .= "<td>" . $expense->description . "</td>";
                        $response->html .= "<td>" . $expense->status . "</td>";
                        if($expense->status == "Pending"){
                            $response->html .= "<td>";
                            $response->html .= "<a href='./?action=expenseApproval&decision=1&expenseId=" . $expense->id . "'>Approve</a>";
                            $response->html .= " / ";
                            $response->html .= "<a href='./?action=expenseApproval&decision=0&expenseId=" . $expense->id . "'>Reject</a>";
                            $response->html .= "</td>";
                        } else {
                            $response->html .= "<td>Completed</td>";
                        }
                        $response->html .= "</tr>";
                    }
                } else {
                    $response->html .= "<tr><td colspan='10'>No employees have claimed for expenses yet</td></tr>";
                }
            }

            // Returning the list of expenses to the caller
            return $response;
        }

        public static function getAllExpensesForCurrentUser() {
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );

            // Initialising the result that will be returned to the caller
            // before checking if this user has permissions to carry out this
            // action or not, so that an empty array can be returned regardless
            $userExpenses = array();

            // Ensuring this user is a subscriber i.e. employee
            if(lp_financialReporter_User::getUserRole() == "subscriber") {

                // Accessing the global wpdb variable, to access the database
                global $wpdb;

                // Getting the sort order values from cookies, or defaults if no
                // cookies were provided
                $sortOrder = self::checkCookiesForSortOrder();

                // Querying the expense database for all columns in the expense, as well as the
                // category name (by completing a left join in the expense and expense_category
                // tables, based on the category id of the expense matching with the id of a category
                // in the expense_category table). Ordering the resulting rows as specified by the
                // values above (either Cookie's or defaults)
                $userExpenses = $wpdb->get_results("SELECT lp_financialReporter_expense.*, lp_financialReporter_expense_category.name as 'category_name' FROM lp_financialReporter_expense LEFT JOIN lp_financialReporter_expense_category ON lp_financialReporter_expense.category = lp_financialReporter_expense_category.id WHERE lp_financialReporter_expense.employee_id = " . get_current_user_id() . " ORDER BY " . $sortOrder->orderBy . " " . $sortOrder->order);
            }

            if(count($userExpenses) > 0){
                foreach ($userExpenses as $key => $expense){
                    // Setting up values
                    $expenseDate = date_create($expense->date_submitted);

                    // Creating Table Row
                    $response->html .= "<tr>";
                    $response->html .= "<td>#" . $expense->id . "</td>";
                    $response->html .= "<td>" . date_format($expenseDate, lp_financialReporter_Expense::$expenseDateFormat) . "</td>";
                    $response->html .= "<td>" . $expense->category_name . "</td>";
                    $response->html .= "<td>&euro;" . $expense->cost . "</td>";
                    if($expense->receipt == null){
                        $response->html .= "<td>None</td>";
                    } else {
                        $response->html .= "<td><a href='" . home_url($expense->receipt) . "' target='_blank'>View</a></td>";
                    }
                    $response->html .= "<td>" . $expense->description . "</td>";
                    $response->html .= "<td>" . $expense->status . "</td>";
                    if($expense->status == "Pending"){
                        $response->html .= "<td><button id='" . $expense->id . "' class='removeExpense'>Remove</button></td>";
                    } else {
                        $response->html .= "<td>None</td>";
                    }
                    $response->html .= "</tr>";
                }
            } else {
                $response->html .= "<tr><td colspan='8'>You have no previous expense claims</td></tr>";
            }

            return $response;
        }

        public static function getAllCategories() {
            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Querying the database for all categories in the expense_category table
            $categories = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense_category");

            // Returning the list of categories to the caller
            return $categories;
        }

        // Getting the name of a category based on it's id
        public static function getCategoryName($categoryId) {
            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Getting the value of the category's name column, based on a query
            // to the expense_category database, for the category with the same
            // id as the one specified. Storing the result in a temporary variable
            $categoryName = $wpdb->get_var("SELECT name FROM lp_financialReporter_expense_category WHERE id=" . $categoryId);

            // Returning the category name to the caller
            return $categoryName;
        }

        public static function categoryInUse($categoryId) {
            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Querying the expense table for expenses which have their category
            // set to the one specified. Storing the result in a temporary variable
            $results = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense WHERE category=" . $categoryId);

            // Determining whether or not the category is currently in use based the number
            // of results from the database i.e. if there was more than 0, then the
            // category is in use in at least one expense, otherwise it is not
            $categoryInUse = count($results) > 0 ? true : false;

            // Returning the result to the caller
            return $categoryInUse;
        }

        public static function categoryExists($categoryName) {
            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Querying the expense_category table for categories with the same name
            // as the one specified. Storing the result in a temporary variable
            $results = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense_category WHERE name=" . $categoryName);

            // Determining whether or not the category already exists based the number
            // of results from the database i.e. if there was more than 0, then the
            // category already exists, otherwise it does not
            $categoryExists = count($results) > 0 ? true : false;

            // Returning the result to the caller
            return $categoryExists;
        }

        public static function addCategory($categoryName){

            // Ensuring this user is a administrator i.e. employer
            if(lp_financialReporter_User::getUserRole() == "administrator") {

                // Checking that the category does not already exist
                if (self::categoryExists($categoryName) == false) {

                    // Accessing the global wpdb variable, to access the database
                    global $wpdb;

                    // Adding the new category to the expense_category table,
                    // using a prepared statement
                    $wpdb->query($wpdb->prepare(
                        "INSERT INTO lp_financialReporter_expense_category (name) VALUES(%s)",
                        array($categoryName)
                    ));
                }
            }
            // Redirecting the user to the current page (to remove all reference to the POST request,
            // as well as any GET params that were passed as part of this process
            wp_redirect("./");
        }

        public static function removeCategory($categoryId) {

            // Ensuring this user is a administrator i.e. employer
            if(lp_financialReporter_User::getUserRole() == "administrator") {

                // Checking that this category is not currently in use (if it is, then
                // it can't be deleted
                if (self::categoryInUse($categoryId) == false) {

                    // Accessing the global wpdb variable, to access the database
                    global $wpdb;

                    // Deleting the category from the expense_category database
                    $wpdb->delete(
                        "lp_financialReporter_expense_category",
                        array("id" => $categoryId),
                        array("%d")
                    );
                }
            }
            // Redirecting the user to the current page (to remove all reference to the POST request,
            // as well as any GET params that were passed as part of this process
            wp_redirect("./");
        }

        private static function checkCookiesForSortOrder() {
            // Creating an empty object to store the resulting values
            $result = (object) array();

            // Checking if there are cookies set to specify column and order
            // by which the results should be sorted.
            if (isset($_COOKIE["orderBy"]) && isset($_COOKIE["order"])) {
                // Using the values stored in the cookie's for column and order
                $result->orderBy = $_COOKIE["orderBy"];
                $result->order = $_COOKIE["order"];
            } else {
                // Using the default values for column and order, as no cookies were
                // provided
                $result->orderBy = "date_submitted";
                $result->order = "asc";
            }

            // Returning the result to the caller
            return $result;
        }
    }
?>