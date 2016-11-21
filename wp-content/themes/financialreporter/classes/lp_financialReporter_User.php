<?php
    class lp_financialReporter_User {
        function __construct(){
        }

        // Get user's role i.e. admin/subscriber
        public static function getUserRole() {
            global $wp_roles;
            $usersRole ='';

            foreach ($wp_roles->role_names as $role => $name ) {
                if (current_user_can( $role ) ){
                    $usersRole = $role;
                }
            }
            return $usersRole;
        }

        public static function attemptAction($action){
            switch ($action) {
                case "addExpense": {
                    lp_financialReporter_Expense::addExpense($_POST);
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
            }
        }

        public static function registerNewUser($registrationData) {
            if(lp_financialReporter_InputData::validateData($registrationData, array())) {
                $sanitisedData = lp_financialReporter_InputData::sanitiseData($registrationData);

                $userData = array(
                    "user_login" => $sanitisedData["username"],
                    "user_pass" => null,
                    "display_name" => $sanitisedData["first_name"] . " " . $sanitisedData["last_name"],
                    "user_pass" => $sanitisedData["password"],
                    "user_email" => $sanitisedData["email"]
                );

                $userId = wp_insert_user($userData);

                if(!is_wp_error($userId)){
                    update_user_meta($userId, "first_name", $sanitisedData["first_name"]);
                    update_user_meta($userId, "last_name", $sanitisedData["last_name"]);

                    wp_set_current_user($userId);
                    wp_set_auth_cookie($userId);

                    wp_new_user_notification($userId);

                    wp_redirect("/ssp2/assignment02/expenses");
                }
            }
        }
    }
?>