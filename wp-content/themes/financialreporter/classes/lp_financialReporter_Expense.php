<?php
    class lp_financialReporter_Expense {
        public static $expenseDateFormat = "jS M Y @ G:ia";

        function __construct() {
        }

        public static function addExpense($expenseData, $files=null){
            if(lp_financialReporter_User::getUserRole() == "subscriber") {
                if (count($expenseData) > 0) {
                    $receiptPath = null;

                    // Only works locally at the moment
                    if(isset($files["receipt"]) && $_SERVER['SERVER_NAME'] == "localhost"){
                        $saveFile = lp_financialReporter_File::saveFile($files["receipt"]);
                        if(count($saveFile->errors) > 0){
                        }
                        if(isset($saveFile->filepath)) {
                            $receiptPath = $saveFile->filepath;
                        }
                    }

                    if (lp_financialReporter_InputData::validateData($expenseData, array())) {
                        global $wpdb;
                        $wpdb->show_errors(true);
                        $wpdb->query($wpdb->prepare(
                            "INSERT INTO lp_financialReporter_expense (employee_id, category, receipt, cost, description) VALUES(%d, %d, %s, %d, %s)",
                            array(get_current_user_id(), number_format($expenseData['category'], 0), $receiptPath, number_format($expenseData['cost'], 2), $expenseData['description'])
                        ));
                    }
                }
            }
            wp_redirect("./");
        }

        public static function removeExpense($expenseId){
            if(lp_financialReporter_User::getUserRole() == "subscriber") {
                // Allowing employees to remove expenses that have not yet been approved
                if (isset($expenseId)) {
                    global $wpdb;
                    $wpdb->delete(
                        "lp_financialReporter_expense",
                        array("id" => $_GET["expenseId"], "status" => "Pending"),
                        array("%d", "%s")
                    );
                }
            }
            wp_redirect("./");
        }

        public static function makeDecisionOnExpense($expenseId, $decision) {
            if(lp_financialReporter_User::getUserRole() == "administrator") {
                if (isset($expenseId) && isset($decision)) {
                    global $wpdb;
                    $expenseDecision = $_GET["decision"] == 0 ? "Rejected" : "Approved";
                    $wpdb->update("lp_financialReporter_expense",
                        array("status" => $expenseDecision, "decision_date" => date("Y-m-d H:i:s")),
                        array("id" => $_GET["expenseId"]),
                        array("%s", "%s"),
                        array("%d")
                    );
                }
            }
            wp_redirect("./");
        }

        public static function getAllExpenses() {
            $expenses = array();
            if(lp_financialReporter_User::getUserRole() == "administrator") {
                global $wpdb;
                if (isset($_COOKIE["orderBy"]) && isset($_COOKIE["order"])) {
                    $orderBy = $_COOKIE["orderBy"];
                    $order = $_COOKIE["order"];
                } else {
                    $orderBy = "date_submitted";
                    $order = "asc";
                }
                $expenses = $wpdb->get_results("SELECT lp_financialReporter_expense.*, wp_users.display_name, lp_financialReporter_expense_category.name as 'category_name' FROM lp_financialReporter_expense LEFT JOIN wp_users ON lp_financialReporter_expense.employee_id = wp_users.id LEFT JOIN lp_financialReporter_expense_category ON lp_financialReporter_expense.category = lp_financialReporter_expense_category.id ORDER BY " . $orderBy . " " . $order);
            }
            return $expenses;
        }

        public static function getAllExpensesForCurrentUser() {
            $expenses = array();
            if(lp_financialReporter_User::getUserRole() == "subscriber") {
                global $wpdb;
                if (isset($_COOKIE["orderBy"]) && isset($_COOKIE["order"])) {
                    $orderBy = $_COOKIE["orderBy"];
                    $order = $_COOKIE["order"];
                } else {
                    $orderBy = "date_submitted";
                    $order = "asc";
                }
                $expenses = $wpdb->get_results("SELECT lp_financialReporter_expense.*, lp_financialReporter_expense_category.name as 'category_name' FROM lp_financialReporter_expense LEFT JOIN lp_financialReporter_expense_category ON lp_financialReporter_expense.category = lp_financialReporter_expense_category.id WHERE lp_financialReporter_expense.employee_id = " . get_current_user_id() . " ORDER BY " . $orderBy . " " . $order);
            }
            return $expenses;
        }

        private static function appendReceiptToExpense($expenseID, $receiptFile){

        }

        public static function getAllCategories() {
            // Loading Categories in from Database
            global $wpdb;
            $categories = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense_category");
            return $categories;
        }

        // Getting the name of a category based on it's id
        public static function getCategoryName($categoryId) {
            global $wpdb;
            $categoryName = $wpdb->get_var("SELECT name FROM lp_financialReporter_expense_category WHERE id=" . $categoryId);
            return $categoryName;
        }

        public static function categoryInUse($categoryId) {
            global $wpdb;
            $results = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense WHERE category=" . $categoryId);
            $categoryInUse = count($results) > 0 ? true : false;
            return $categoryInUse;
        }

        public static function categoryExists($categoryName) {
            global $wpdb;
            $results = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense_category WHERE name=" . $categoryName);
            $categoryExists = count($results) > 0 ? true : false;
            return $categoryExists;
        }

        public static function addCategory($categoryName){
            if(lp_financialReporter_User::getUserRole() == "administrator") {
                if (self::categoryExists($categoryName) == false) {
                    echo "category does not already exist";
                    global $wpdb;
                    $wpdb->query($wpdb->prepare(
                        "INSERT INTO lp_financialReporter_expense_category (name) VALUES(%s)",
                        array($categoryName)
                    ));
                }
            }
            wp_redirect("./");
        }

        public static function removeCategory($categoryId) {
            if(lp_financialReporter_User::getUserRole() == "administrator") {
                if (self::categoryInUse($categoryId) == false) {
                    global $wpdb;
                    $wpdb->query("DELETE FROM lp_financialReporter_expense_category WHERE id=" . $categoryId);
                }
            }
            wp_redirect("./");
        }
    }
?>