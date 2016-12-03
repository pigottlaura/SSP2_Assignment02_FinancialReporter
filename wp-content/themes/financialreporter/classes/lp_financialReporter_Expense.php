<?php
    class lp_financialReporter_Expense {
        // Creating a common date format, that will be used across employee and
        // employer displays of expenses i.e. 21st Nov 2016 @ 20:39pm
        private static $expenseDateFormat = "jS M Y @ G:ia";

        function __construct() {
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        // Publicly used method, invoked when an AJAX request is received
        public static function addExpense($expenseData, $files=null){
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and any html that is to be displayed will be stored as a string
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );

            // Ensuring this user is a subscriber i.e. employee
            if(lp_financialReporter_User::getUserRole() == "subscriber") {

                // Checking that the expense data has been provided
                if (count($expenseData) > 0) {

                    // Validating the data provided by the user i.e. to ensure that it is
                    // in the expected format
                    $dataValidated = lp_financialReporter_InputData::validateData($expenseData, array(
                        "required" => array("category", "cost", "description"),
                        "number" => array("category", "cost")
                    ));

                    // Looping through any errors returned by the data validation, and adding
                    // them to the response's errors array
                    foreach($dataValidated->errors as $key => $error){
                        array_push($response->errors, $error);
                    }

                    // Checking if the data validation was successful
                    if ($dataValidated->dataValidated) {

                        // Sanitising the data passed as part of the expense
                        $sanitisedData = lp_financialReporter_InputData::sanitiseData($expenseData);

                        // Creating a default of null for the receipt path. This may remain
                        // num if 1) no files were uploaded along with this request or
                        // 2) the file fails to save to the server
                        $receiptPath = null;

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
                                // Storing these errors in the response error object
                                foreach ($saveFile->errors as $key => $error) {
                                    array_push($response->errors, $error);

                                }
                                // Setting the response's success to false
                                $response->successful = false;
                            }

                            // Checking if a file path was returned from the saveFile attempt
                            if(isset($saveFile->filepath)) {
                                // If so, then storing it in the temporary receiptPath variable,
                                // so that it can be passed to the database and stored in the receipt
                                // column of the expense
                                $receiptPath = $saveFile->filepath;
                            }
                        }

                        if($receiptPath == null) {
                            // Since no receipt was uploaded with this expense, checking if it is a requirement
                            // i.e. has the employer specified that a receipt must be included for all expenses
                            if(get_option("lp_financialReporter_receiptsRequiredForAllExpenses") == "true") {
                                // Since a receipt is a requirement, and none was supplied, adding this
                                // as an error to the response object
                                array_push($response->errors, "A receipt must be supplied for all expenses");

                                // Returning the response object to the caller, as this expense cannot be added
                                // to the database without a receipt
                                return $response;
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
                        // float value. Passing the receiptPath variable, whether or not a receipt
                        // was uploaded, as this will have defaulted to null if no file was successfully
                        // loaded, which is an acceptable value for this column. Determining whether or
                        // not this insert was successful based on the response returned from the query,
                        // and storing this in the response's success boolean
                        $response->successful = $wpdb->query($wpdb->prepare(
                            "INSERT INTO lp_financialReporter_expense (employee_id, category, receipt, cost, description) VALUES(%d, %d, %s, %d, %s)",
                            array(get_current_user_id(), number_format($sanitisedData['category'], 0), $receiptPath, number_format($sanitisedData['cost'], 2), $sanitisedData['description'])
                        ));

                        // Setting the html of the response object to be equal to all of the
                        // expenses of the current user (which will now contain the new
                        // addition made above. This is so the user won't have to make an additional
                        // AJAX request in order to update their expenses on screen
                        $response->html = self::getUpdatedExpensesForCurrentUser();
                    }
                }
            } else {
                array_push($response->errors, "This user does not have permission to add an expense");
            }

            // Returning the response object to the caller, which will contain the
            // successful boolean, array of any errors, and any HTML that is to
            // be displayed
            return $response;
        }

        // Publicly used method, invoked when an AJAX request is received.
        // Allowing employees to remove expenses that have not yet been approved
        public static function removeExpense($expenseId){
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and any html that is to be displayed will be stored as a string
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );
            
            // Ensuring this user is a subscriber i.e. employee
            if(lp_financialReporter_User::getUserRole() == "subscriber") {

                // Checking that the expenseId has infact been passed as a param
                // to the query string
                if (isset($expenseId)) {
                    // Deleting the receipt associated with this expense (if there is one)
                    lp_financialReporter_File::deleteReceipt($expenseId);

                    // Accessing the global wpdb variable, to access the database
                    global $wpdb;

                    // If debug is on, then log all errors from the database (TESTING PURPOSES)
                    if(get_option("lp_financialReporter_debug") == "on"){
                        $wpdb->show_errors(true);
                    }

                    // Deleting this expense, double checking that not only does the
                    // id match, but that the status is defiantly pending (as expenses
                    // that have already been decided on cannot be deleted). Using the
                    // wpdb delete method, ensuring that the id is a number, and the
                    // status is a string
                    $response->successful = $wpdb->delete(
                        "lp_financialReporter_expense",
                        array("id" => $expenseId, "status" => "Pending"),
                        array("%d", "%s")
                    );

                    // Setting the html of the response object to be equal to all of the
                    // expenses of the current user (which will no longer contain the expense
                    // whcih was deleted above. This is so the user won't have to make an additional
                    // AJAX request in order to update their expenses on screen
                    $response->html = self::getUpdatedExpensesForCurrentUser();
                }
            } else {
                array_push($response->errors, "This user does not have permission to delete an expense");
            }

            // Returning the response object to the caller, which will contain the
            // successful boolean, array of any errors, and any HTML that is to
            // be displayed
            return $response;
        }

        // Publicly used method, envoked when a user is deleted from the WP admin
        // panel, based on an action defined in the Setup class
        public static function removeAllExpensesForUser($userId){
            // Accessing the global wpdb variable, to access the database
            global $wpdb;
            $wpdb->delete(
                "lp_financialReporter_expense",
                array("employee_id" => $userId),
                array("%d")
            );
        }

        // Publicly used method, invoked when an AJAX request is received
        public static function makeDecisionOnExpense($expenseId, $decision) {
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and any html that is to be displayed will be stored as a string
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );

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

                    // If debug is on, then log all errors from the database (TESTING PURPOSES)
                    if(get_option("lp_financialReporter_debug") == "on"){
                        $wpdb->show_errors(true);
                    }

                    // Updating the expense's status and decision date, based on the
                    // decising specified by the employer ie. Rejected or Approved, as
                    // well as passing the current date/time as the decision date. Using the
                    // wpdb update method, ensuring that the expense decision and date are both
                    // strings, and that the id used to identify the row is a number
                    $response->successful = $wpdb->update("lp_financialReporter_expense",
                        array("status" => $expenseDecision, "decision_date" => date("Y-m-d H:i:s")),
                        array("id" => $expenseId),
                        array("%s", "%s"),
                        array("%d")
                    );

                    // Setting the html of the response object to be equal to all of the
                    // expenses of the current user (which will now contain the updated status
                    // of the expense decision made above. This is so the user won't have to
                    // make an additional AJAX request in order to update their expenses on screen
                    $response->html = self::getUpdatedExpenses();
                }
            } else {
                array_push($response->errors, "This user does not have permission to make a decison on an expense");
            }

            // Returning the response object to the caller, which will contain the
            // successful boolean, array of any errors, and any HTML that is to
            // be displayed
            return $response;
        }

        // Publicly used method, invoked when an AJAX request is received
        public static function getAllExpenses() {
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and any html that is to be displayed will be stored as a string
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

                $response->successful = true;

                // Checking if any expenses were returned from the database
                if(count($allExpenses) > 0) {

                    // Looping through all expenses
                    foreach($allExpenses as $key => $expense){
                        // Creating a new data object, using the date on which the expense was first
                        // submitted, so that it can be formated for display in the table
                        $expenseDate = date_create($expense->date_submitted);

                        // Creating a new table row for every expense. Displaying the expense id, employee id,
                        // employee name, the date which the expense was first submitted (formated using the format
                        // defined at the top of this class_, the category name and cost. Checking if this expense
                        // has a receipt url, and if so, providing a link to same. Displaying the description and
                        // status of the expense (which will be approved, pending or rejected). Checking if this expense
                        // has had a decision made on it ie. to see if it is still pending. If so, then giving the
                        // user two buttons, one to approve it, one to reject it.
                        $response->html .= "<tr>";
                        $response->html .= "<td>#" . $expense->id . "</td>";
                        $response->html .= "<td>#" . $expense->employee_id . "</td>";
                        $response->html .= "<td>" . $expense->display_name . "</td>";
                        $response->html .= "<td>" . date_format($expenseDate, self::$expenseDateFormat) . "</td>";
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
                            $response->html .= "<button id='" . $expense->id . "' class='expenseApproval' data-decision=1>Approve</button>";
                            $response->html .= " / ";
                            $response->html .= "<button id='" . $expense->id . "' class='expenseApproval' data-decision=0>Reject</button>";
                            $response->html .= "</td>";
                        } else {
                            $response->html .= "<td>Completed</td>";
                        }
                        $response->html .= "</tr>";
                    }
                } else {
                    // As there are no expenses in the expense table, returning a single row to be displayed
                    // in the table
                    $response->html .= "<tr><td colspan='10'>No employees have claimed for expenses yet</td></tr>";
                }
            } else {
                array_push($response->errors, "This user does not have permission to view all employee expenses");
            }

            // Returning the response object to the caller, which will contain the
            // successful boolean, array of any errors, and any HTML that is to
            // be displayed
            return $response;
        }

        // Publicly used method, invoked when an AJAX request is received
        public static function getAllExpensesForCurrentUser() {
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and any html that is to be displayed will be stored as a string
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );

            // Initialising the result that will be returned to the caller
            // before checking if this user has permissions to carry out this
            // action or not, so that an empty array can be returned regardless
            $userExpenses = array();

            // Ensuring this user is a subscriber i.e. Employee
            if(lp_financialReporter_User::getUserRole() == "subscriber") {

                // Accessing the global wpdb variable, to access the database
                global $wpdb;

                // Getting the sort order values from cookies, or defaults if no
                // cookies were provided
                $sortOrder = self::checkCookiesForSortOrder();

                // If debug is on, then log all errors from the database (TESTING PURPOSES)
                if(get_option("lp_financialReporter_debug") == "on"){
                    $wpdb->show_errors(true);
                }

                // Querying the expense database for all columns in the expense, as well as the
                // category name (by completing a left join in the expense and expense_category
                // tables, based on the category id of the expense matching with the id of a category
                // in the expense_category table). Ordering the resulting rows as specified by the
                // values above (either Cookie's or defaults)
                $userExpenses = $wpdb->get_results("SELECT lp_financialReporter_expense.*, lp_financialReporter_expense_category.name as 'category_name' FROM lp_financialReporter_expense LEFT JOIN lp_financialReporter_expense_category ON lp_financialReporter_expense.category = lp_financialReporter_expense_category.id WHERE lp_financialReporter_expense.employee_id = " . get_current_user_id() . " ORDER BY " . $sortOrder->orderBy . " " . $sortOrder->order);

                $response->successful = true;

                // Checking if any expenses were returned from the database
                if(count($userExpenses) > 0){

                    // Looping through all the expenses of the user
                    foreach ($userExpenses as $key => $expense){
                        // Creating a new date object, based on the date stored in the database
                        // for when the expense was first created (so that it can be formated below)
                        $expenseDate = date_create($expense->date_submitted);

                        // Creating a new table row for each expense. Displaying the expense id, the
                        // date on which the expense was created (formated using the format declared at the
                        // top of this class - so that it will be consistent throughout). Checking if this
                        // expense contains a url to a receipt, and if it does, displaying a link to same.
                        // Displaying the description and status of the expense (i.e. approved, pending, rejected).
                        // Checking if this expense is still pending, in which case the user will be provided
                        // with a delete button (only expenses that have not yet been approved/rejected can be deleted)
                        $response->html .= "<tr>";
                        $response->html .= "<td>#" . $expense->id . "</td>";
                        $response->html .= "<td>" . date_format($expenseDate, self::$expenseDateFormat) . "</td>";
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
                    // As this user has no expenses, then returning a single row to notify them
                    // that they have no previous claims
                    $response->html .= "<tr><td colspan='8'>You have no previous expense claims</td></tr>";
                }
            } else {
                array_push($response->errors, "This user does not have permission to view individual employee expenses");
            }
            // Returning the response object to the caller, which will contain the
            // successful boolean, array of any errors, and any HTML that is to
            // be displayed
            return $response;
        }

        // Publicly used method, invoked when a page containing the categories is loaded,
        // or when an AJAX request is received to add/remove a category
        public static function getAllCategories() {
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and any html that is to be displayed will be stored as a string
            $response = (object)array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );

            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // If debug is on, then log all errors from the database (TESTING PURPOSES)
            if (get_option("lp_financialReporter_debug") == "on") {
                $wpdb->show_errors(true);
            }

            // Querying the database for all categories in the expense_category table
            $categories = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense_category");

            // Determining which type of user made this request, so the appropriate HTML
            // can be returned to them
            if (lp_financialReporter_User::getUserRole() == "administrator") {
                // As this user is an Employer, setting the html of the response object to be equal
                // to all of the categories as a table
                $response->html = self::displayCategoriesAsTable($categories);

            } else if (lp_financialReporter_User::getUserRole() == "subscriber") {
                // As this user is an Employee, setting the html of the response object to be equal
                // to all of the categories as a list of options, which will be used to generate a
                // select menu for adding expenses
                $response->html = self::displayCategoriesAsOptions($categories);
            } else {
                array_push($response->errors, "This user does not have permission to view categories");
            }

            // Returning the response object to the caller, which will contain the
            // successful boolean, array of any errors, and any HTML that is to
            // be displayed
            return $response;
        }

        // Publicly used method, invoked when an AJAX request is received
        public static function addCategory($categoryName){
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and any html that is to be displayed will be stored as a string
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );

            // Ensuring this user is a administrator i.e. Employer
            if(lp_financialReporter_User::getUserRole() == "administrator") {

                // Checking that the category does not already exist. All category names
                // in the categories table must be unique anyway, so this is just a check
                // to prevent potential errors if a duplicate entry were attempted
                if (self::categoryExists($categoryName) == false) {

                    // Accessing the global wpdb variable, to access the database
                    global $wpdb;

                    // Adding the new category to the expense_category table,
                    // using a prepared statement. Passing the category name, which
                    // must be a string
                    $response->successful = $wpdb->query($wpdb->prepare(
                        "INSERT INTO lp_financialReporter_expense_category (name) VALUES(%s)",
                        array($categoryName)
                    ));

                    // Setting the html of the response object to be equal to all of the
                    // categories (including the new one added above). This is so the user won't
                    // have to make an additional AJAX request in order to update the categories
                    // on screen
                    $response->html = self::getUpdatedCategories();
                } else {
                    array_push($response->errors, "A category with this name already exists");
                }
            } else {
                array_push($response->errors, "This user does not have permission to add categories");
            }

            // Returning the response object to the caller, which will contain the
            // successful boolean, array of any errors, and any HTML that is to
            // be displayed
            return $response;
        }

        // Publicly used method, invoked when an AJAX request is received
        public static function removeCategory($categoryId) {
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and any html that is to be displayed will be stored as a string
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "html" => ""
            );

            // Ensuring this user is a administrator i.e. Employer
            if(lp_financialReporter_User::getUserRole() == "administrator") {

                // Checking that this category is not currently in use (if it is, then
                // it can't be deleted). Technically, a user wouldn't have been given
                // the option to delete this category is it was already in use, so this
                // is more to double check that it hasn't been used since their page was
                // last loaded, or that this request wasn't a malicious one (again, because
                // the option wouldn't have been there to begin with)
                if (self::categoryInUse($categoryId) == false) {

                    // Accessing the global wpdb variable, to access the database
                    global $wpdb;

                    // If debug is on, then log all errors from the database (TESTING PURPOSES)
                    if(get_option("lp_financialReporter_debug") == "on"){
                        $wpdb->show_errors(true);
                    }

                    // Deleting the category from the expense_category database, using the wpdb
                    // delete method. Passing in the category id, which must be a number
                    $response->successful = $wpdb->delete(
                        "lp_financialReporter_expense_category",
                        array("id" => $categoryId),
                        array("%d")
                    );

                    // Setting the html of the response object to be equal to all of the
                    // categories (which will no longer include the one that was just deleted
                    // above). This is so the user won't have to make an additional AJAX request
                    // in order to update the categories on screen
                    $response->html = self::getUpdatedCategories();
                } else {
                    array_push($response->errors, "This category is currently in use, and cannot be deleted");
                }
            } else {
                array_push($response->errors, "This user does not have permission to remove categories");
            }

            // Returning the response object to the caller, which will contain the
            // successful boolean, array of any errors, and any HTML that is to
            // be displayed
            return $response;
        }

        // Privately used method, used to return the HTML representation of the
        // current categories as a table i.e. for use with an administrator
        // (Employer). This will be invoked by the getAllCategories method is called
        private static function displayCategoriesAsTable($categories) {
            // Creating an empty string, to store the HTML generated within this method,
            // so that it can be returned to the caller
            $html = "";

            // Looping through all of the categories passed to the method
            foreach($categories as $key => $category){
                // Creating a new row for each category, which will contain it's name, as well
                // as any possible actions that can be used on it i.e. checking if this category
                // is currently in use, and if it is not then providing the user with a delete button
                // so that they can remove it (only categories not currenlty in use can be deleted)
                $html .= "<tr>";
                $html .= "<td>" . $category->name . "</td>";
                $html .= "<td>";
                if(self::categoryInUse($category->id)){
                    $html .= "None - category in use";
                } else {
                    $html .= "<button id='" . $category->id . "' class='removeExpenseCategory'>Remove</button>";
                }
                $html .= "</td>";
                $html .= "</tr>";
            }

            // Returning the HTML to the caller i.e. so it can be included in the
            // HTML property of the response object
            return $html;
        }

        // Privately used method, used to return the HTML representation of the
        // current categories as options i.e. for use with a subscriber
        // (Employee). This will be invoked by the getAllCategories method is called
        private static function displayCategoriesAsOptions($categories) {
            // Creating an empty string, to store the HTML generated within this method,
            // so that it can be returned to the caller
            $html = "";

            // Looping through all of the categories passed to the method
            foreach($categories as $key => $category){
                // Concatenating the current value of the HTML string, with a new option
                // element, with a value of the category id, and inner HTML of the category name
                $html .= "<option value='" . $category->id . "'>" . $category->name . "</option>";
            }

            // Returning the HTML to the caller i.e. so it can be included in the
            // HTML property of the response object
            return $html;
        }

        // Privately used method, which is used to access the updated HTML of
        // expenses for the current user following a change i.e. addition, deletion
        // of an expense, so that the caller does not have to make an additional
        // AJAX request in order to update the result of the action on screen
        private static function getUpdatedExpensesForCurrentUser(){
            // Getting the response object, which will contain the success, errors
            // and html of getting all expenses for the current user
            $updatedExpenses = self::getAllExpensesForCurrentUser();

            // Returning the HTML of this object to the caller
            return $updatedExpenses->html;
        }

        // Privately used method, which is used to access the updated HTML of
        // all expenses following a change i.e. approval/rejection of an expense,
        // so that the caller does not have to make an additional AJAX request in
        // order to update the result of the action on screen
        private static function getUpdatedExpenses(){
            // Getting the response object, which will contain the success, errors
            // and html of getting all expenses for all users
            $updatedExpenses = self::getAllExpenses();

            // Returning the HTML of this object to the caller
            return $updatedExpenses->html;
        }

        // Privately used method, which is used to access the updated HTML of all
        // categories following a change i.e. addition, deletion of a category, so
        // that the caller does not have to make an additional AJAX request in order
        // to update the result of the action on screen
        private static function getUpdatedCategories(){
            // Getting the response object, which will contain the success, errors
            // and html of getting all categories
            $updatedCategories = self::getAllCategories();

            // Returning the HTML of this object to the caller. NOTE- the html
            // returned from the getAllCategories method will vary depending
            // on whether this request was made by an administrator (Employer)
            // or subscriber (Employee)
            return $updatedCategories->html;
        }

        // Privately used method, to check if a category with the same name already
        // exists, before creating a new category i.e. to prevent duplication of
        // categories with the same name. This is prevented in the database, as
        // each category name is required to be unique, so this check is just to avoid
        // potential errors
        private static function categoryExists($categoryName) {
            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Querying the expense_category table for categories with the same name
            // as the one specified. Storing the result in a temporary variable
            $results = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense_category WHERE name=" . $categoryName);

            // Determining whether or not the category already exists based the number
            // of results from the database i.e. if there was more than 0, then the
            // category already exists, otherwise it does not
            $categoryExists = count($results) > 0 ? true : false;

            // Returning the result to the caller i.e. a boolean value which indicates whether the
            // category already exists or not
            return $categoryExists;
        }

        // Privately used method, to check if a category is currently in use by any expenses.
        // This is used when offering administrators (Employers) the option to delete a category,
        // i.e. if a category is currently in use, they will no be allowed to delete it
        private static function categoryInUse($categoryId) {
            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Querying the expense table for expenses which have their category
            // set to the one specified. Storing the result in a temporary variable
            $results = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense WHERE category=" . $categoryId);

            // Determining whether or not the category is currently in use based the number
            // of results from the database i.e. if there was more than 0, then the
            // category is in use in at least one expense, otherwise it is not
            $categoryInUse = count($results) > 0 ? true : false;

            // Returning the result to the caller, which will be a boolean
            // specifying whether this category is currently in use or not
            return $categoryInUse;
        }

        // Privately used method, used to get the sort order options for returning
        // expense data to be viewed by both administrators (Employers) and subscribers
        // (Employees). Checking for cookie data that would contain the relevant
        // sort order variable i.e. what to sort by (column name) and which order to
        // sort it in (ascending or descending). If these are not present, then default
        // values will be used
        private static function checkCookiesForSortOrder() {
            // Creating an empty object to store the resulting values i.e. the
            // variables by which the results from the expense table will be sorted
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

            // Returning the result to the caller, which will contain the
            // array of sort order variables (either from the cookies, or
            // the default values)
            return $result;
        }
    }
?>