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
            $response = (object) array(
                "successful" => false,
                "errors" => array()
            );

            // Checking which action was specified, and calling the relevant method of the
            // relevant class, to complete the action if a match is found
            if(self::getUserRole() == "administrator") {
                switch ($action) {
                    case "getAllExpenses": {
                        $response = lp_financialReporter_Expense::getAllExpenses();
                        break;
                    }
                    case "expenseApproval": {
                        $response = lp_financialReporter_Expense::makeDecisionOnExpense($_POST["expenseId"], $_POST["decision"]);
                        break;
                    }
                    case "addNewExpenseCategory": {
                        $response = lp_financialReporter_Expense::addCategory($_POST["categoryName"]);
                        break;
                    }
                    case "removeExpenseCategory": {
                        $response = lp_financialReporter_Expense::removeCategory($_POST["categoryId"]);
                        break;
                    }
                    case "saveEmployerSettings": {
                        $response = self::saveEmployerSettings($_POST);
                        break;
                    }
                    default: {
                        array_push($response->errors, "This is not a recognised action");
                        break;
                    }
                }
            } else if(self::getUserRole() == "subscriber"){
                switch ($action) {
                    case "addExpense": {
                        $response = lp_financialReporter_Expense::addExpense($_POST, $_FILES);
                        break;
                    }
                    case "removeExpense": {
                        $response = lp_financialReporter_Expense::removeExpense($_POST["expenseId"]);
                        break;
                    }
                    case "getAllExpensesForCurrentUser": {
                        $response = lp_financialReporter_Expense::getAllExpensesForCurrentUser();
                        break;
                    }
                    default: {
                        array_push($response->errors, "This is not a recognised action");
                        break;
                    }
                }
            }

            $response->action = $action;
            return $response;
        }

        // Registering a new user
        public static function registerNewUser($registrationData) {
            $response = (object) array(
                "email" => null,
                "errors" => array()
            );

            // Checking if the data provied is valid, based on the options specified
            $dataValidated = lp_financialReporter_InputData::validateData($registrationData, array(
                "required" => array("user_login", "first_name", "last_name", "email"),
                "email" => array("email")
            ));

            // Looping through any errors that may have been returned from the data
            // validation, and adding them to the response errors array
            foreach($dataValidated->errors as $key => $error){
                array_push($response->errors, $error);
            }

            // If the data is validated then proceed with the registration
            if($dataValidated->dataValidated) {
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

                if($_SERVER['SERVER_NAME'] == "localhost") {
                    $userData["user_pass"] = "testing";
                }

                // Creating a new user, and storing the resulting ID in a temporary variable
                $userId = wp_insert_user($userData);


                // Checking if the attempt to create the user resulted in any errors
                if(is_wp_error($userId)) {
                    foreach($userId->errors as $key => $error){
                        foreach($error as $errKey => $errorData){
                            array_push($response->errors, $errorData);
                        }
                    }
                } else {
                    // If there was no error returned from the insert, then log this user in

                    // Setting the user's meta data for their first and last name
                    update_user_meta($userId, "first_name", $sanitisedData["first_name"]);
                    update_user_meta($userId, "last_name", $sanitisedData["last_name"]);

                    if($_SERVER['SERVER_NAME'] == "localhost"){
                        array_push($response->errors, "As this site is running locally (for testing purposes), no registration email will be sent, and so your password has defaulted to 'testing'.");
                    } else {
                        // Sending a new user notification to the user
                        wp_new_user_notification($userId, null, "both");

                        $response->email = $sanitisedData["email"];
                    }
                }
            }

            // Returning the response object to the caller
            return $response;
        }

        public static function saveEmployerSettings($postData) {
            $response = (object) array(
                "successful" => false,
                "errors" => array()
            );

            $validateData = lp_financialReporter_InputData::validateData($postData, array());

            if($validateData->dataValidated){
                $santisedData = lp_financialReporter_InputData::sanitiseData($_POST);

                if(isset($santisedData["deleteDatabaseOnThemeDeactivate"])) {
                    $response->successful = update_option("lp_financialReporter_deleteDatabaseOnThemeDeactivate", $santisedData["deleteDatabaseOnThemeDeactivate"]);
                } else {
                    array_push($response->errors, "No value was given for whether or not the expense databases should be deleted upon theme deactivation");
                }

                if(isset($santisedData["receiptsRequiredForAllExpenses"])) {
                    $response->successful = update_option("lp_financialReporter_receiptsRequiredForAllExpenses", $santisedData["receiptsRequiredForAllExpenses"]);
                } else {
                    array_push($response->errors, "No value was given for whether or not receipts are required for all expenses");
                }
            } else {
                foreach($validateData->errors as $error){
                    array_push($response->errors, $error);
                }
            }
            return $response;
        }

        public static function useCustomLogin() {
            return home_url("/user-login");
        }
    }
?>