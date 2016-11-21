<?php
    class lp_financialReporter_User {

        function __construct(){
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        // Get user's role i.e. admin/subscriber
        public static function getUserRole() {

            // Accessing the global "wp_roles" variable
            global $wp_roles;

            // Creating a temporary variable to store the resulting role (if one is returned)
            $usersRole = "";

            // Looping through each of the role names
            foreach ($wp_roles->role_names as $role => $name ) {
                // Checking if this user has this role i.e. have they this level of permission
                if (current_user_can($role) ){
                    // Setting the temporary variable to the user's role
                    $usersRole = $role;
                }
            }

            // Returning the resulting role to the caller
            return $usersRole;
        }

        // Attempting to call an action based on the action param passed to the query string of the url
        public static function attemptAction($action){

            // Checking which action was specified, and calling the relevant method of the
            // relevant class, to complete the action if a match is found
            switch ($action) {
                case "addExpense": {
                    lp_financialReporter_Expense::addExpense($_POST, $_FILES);
                    break;
                }
                case "removeExpense": {
                    lp_financialReporter_Expense::removeExpense($_GET["expenseId"]);
                    break;
                }
                case "expenseApproval": {
                    lp_financialReporter_Expense::makeDecisionOnExpense($_GET["expenseId"], $_GET["decision"]);
                    break;
                }
                case "addNewExpenseCategory": {
                    lp_financialReporter_Expense::addCategory($_POST["categoryName"]);
                    break;
                }
                case "removeExpenseCategory": {
                    lp_financialReporter_Expense::removeCategory($_GET["categoryId"]);
                    break;
                }
            }
        }

        // Registering a new user
        public static function registerNewUser($registrationData) {

            // Checking if the data provied is valid, based on the options specified
            if(lp_financialReporter_InputData::validateData($registrationData, array())) {

                // Sanitising the data provided
                $sanitisedData = lp_financialReporter_InputData::sanitiseData($registrationData);

                // Creating a temporary user data array, that wil be passed to the wp_insert_user
                // function. Using the sanitised data to access the values.
                $userData = array(
                    "user_login" => $sanitisedData["username"],
                    "user_pass" => null,
                    "display_name" => $sanitisedData["first_name"] . " " . $sanitisedData["last_name"],
                    "user_email" => $sanitisedData["email"]
                );

                // Creating a new user, and storing the resulting ID in a temporary variable
                $userId = wp_insert_user($userData);


                // Checking if the attempt to create the user resulted in any errors
                if(is_wp_error($userId)) {
                    // TO DO
                } else {
                    // If there was no error returned from the insert, then log this user in

                    // Setting the user's meta data for their first and last name
                    update_user_meta($userId, "first_name", $sanitisedData["first_name"]);
                    update_user_meta($userId, "last_name", $sanitisedData["last_name"]);

                    // Setting the current user to be the one that we just registered,
                    // by setting the "current user" and "auth cookie" to be equal
                    // to their ID
                    wp_set_current_user($userId);
                    wp_set_auth_cookie($userId);

                    // Sending a new user notification to the user
                    wp_new_user_notification($userId, null, "both");

                    // Redirecting the user to the expenses page, where they will be
                    // directed to the appropriate layout for their role
                    wp_redirect("/ssp2/assignment02/expenses");
                }
            }
        }
    }
?>