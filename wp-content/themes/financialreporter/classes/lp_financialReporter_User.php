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
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array
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
                        $response = lp_financialReporter_Expense::makeDecisionOnExpense($_POST);
                        break;
                    }
                    case "addNewExpenseCategory": {
                        $response = lp_financialReporter_Expense::addCategory($_POST);
                        break;
                    }
                    case "removeExpenseCategory": {
                        $response = lp_financialReporter_Expense::removeCategory($_POST);
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
                        $response = lp_financialReporter_Expense::removeExpense($_POST);
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

            // Adding the requested action to the response object
            $response->action = $action;

            // Returning the response object to the caller
            return $response;
        }

        // Registering a new user
        public static function registerNewUser($postData) {
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array
            $response = (object) array(
                "email" => null,
                "errors" => array()
            );

            // Checking if the data provied is valid, based on the options specified
            $dataValidated = lp_financialReporter_InputData::validateData($postData, array(
                "required" => array("username", "first_name", "last_name", "email"),
                "email" => array("email")
            ));

            // If the data is validated then proceed with the registration
            if($dataValidated->successful) {
                // Sanitising the data passed to insure it contains no unexpected data
                $sanitisedData = lp_financialReporter_InputData::sanitiseData($postData);

                // Creating a temporary user data array, that wil be passed to the wp_insert_user
                // function. Using the sanitised data to access the values.
                $userData = array(
                    "user_login" => $sanitisedData["username"],
                    "user_pass" => null,
                    "display_name" => $sanitisedData["first_name"] . " " . $sanitisedData["last_name"],
                    "user_email" => $sanitisedData["email"]
                );

                // When testing locally, no registration email will be generated, so defaulting
                // the password to "testing"
                if($_SERVER['SERVER_NAME'] == "localhost") {
                    $userData["user_pass"] = "testing";
                }

                // Creating a new user, and storing the resulting ID in a temporary variable
                $userId = wp_insert_user($userData);

                // Checking if the attempt to create the user resulted in any errors
                if(is_wp_error($userId)) {
                    // Looping through any of the errors stored in the user id variable,
                    foreach($userId->errors as $key => $error){
                        // Looping through the value of each error
                        foreach($error as $errKey => $errorData){
                            // Adding the error to the response object errors array
                            array_push($response->errors, $errorData);
                        }
                    }
                } else {
                    // Since there was no error returned from the insert, setting the user's
                    // meta data for their first and last name
                    update_user_meta($userId, "first_name", $sanitisedData["first_name"]);
                    update_user_meta($userId, "last_name", $sanitisedData["last_name"]);

                    // Determining whether the site is running locally (testing) or remotely,
                    // as no email will be send if it is running locally
                    if($_SERVER['SERVER_NAME'] == "localhost"){
                        array_push($response->errors, "As this site is running locally (for testing purposes), no registration email will be sent, and so your password has defaulted to 'testing'.");
                    } else {
                        // Sending a new user notification to the user
                        wp_new_user_notification($userId, null, "both");

                        $response->email = $sanitisedData["email"];
                    }
                }
            } else {
                // Looping through any errors that may have been returned from the data
                // validation, and adding them to the response errors array
                foreach($dataValidated->errors as $key => $error){
                    array_push($response->errors, $error);
                }
            }

            // Returning the response object to the caller
            return $response;
        }

        public static function saveEmployerSettings($postData) {
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array
            $response = (object) array(
                "successful" => false,
                "errors" => array()
            );

            // Ensuring this user is a administrator i.e. employer
            if(lp_financialReporter_User::getUserRole() == "administrator") {
                // Validating the data provided by the user i.e. to ensure that it is
                // in the expected format, and that all required fields have been supplied
                $dataValidated = lp_financialReporter_InputData::validateData($postData, array(
                    "required" => "deleteDatabaseOnThemeDeactivate", "receiptsRequiredForAllExpenses",
                    "boolean" => "deleteDatabaseOnThemeDeactivate", "receiptsRequiredForAllExpenses"
                ));

                // Checking that the data validation was successful
                if ($dataValidated->successful) {
                    // Sanitising the data passed to insure it contains no unexpected data
                    $santisedData = lp_financialReporter_InputData::sanitiseData($_POST);

                    // Updating the relevant options, with the values passed to the server
                    $response->successful = update_option("lp_financialReporter_deleteDatabaseOnThemeDeactivate", $santisedData["deleteDatabaseOnThemeDeactivate"]);
                    $response->successful = update_option("lp_financialReporter_receiptsRequiredForAllExpenses", $santisedData["receiptsRequiredForAllExpenses"]);
                } else {
                    // Looping through any errors returned by the data validation, and adding
                    // them to the response's errors array
                    foreach($dataValidated->errors as $key => $error){
                        array_push($response->errors, $error);
                    }
                }
            } else {
                array_push($response->errors, "This user does not have permission to change these settings");
            }

            return $response;
        }

        // Publicly used method, set up as an action in the Setup class
        public static function useCustomLogin() {
            // Redirecting all login attempts to the custom login page of the theme
            return home_url("/user-login");
        }
    }
?>