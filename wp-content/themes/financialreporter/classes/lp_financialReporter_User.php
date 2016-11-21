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
    }
?>