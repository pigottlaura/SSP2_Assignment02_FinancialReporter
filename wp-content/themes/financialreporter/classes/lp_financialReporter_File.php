<?php
    class lp_financialReporter_File {
        private static $allowedFiletypes = array("image/jpeg", "image/png");

        function __construct(){
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        public static function saveFile($file) {
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "filepath" => null
            );

            if (function_exists("wp_handle_upload") == false) {
                require_once(ABSPATH . "wp-admin/includes/file.php");
            }


            $result = wp_handle_upload($file, array('test_form' => false));

            if(isset($result["error"])){
                array_push($response->errors, $result["error"]);
            }
            if(isset($result["url"])){
                $relativePath = str_replace(home_url("/"), "/", $result["url"]);
                $response->filepath = $relativePath;
                $response->successful = true;
            }

            return $response;
        }

        public static function deleteUserReceipts($userId){
            // If a user is being deleted, then removing the expense they had claimed
            // from the database
            global $wpdb;
            $expensesWithReceipts = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense WHERE receipt IS NOT NULL AND employee_id=" . $userId);
            //var_dump($expensesWithReceipts);
            if(count($expensesWithReceipts) > 0) {
                // NEED TO DELETE RECEIPT FILES ASWELL (if they exist)
                foreach($expensesWithReceipts as $expense){
                    unlink(ABSPATH . $expense->receipt);
                }
            }
        }

        public static function deleteReceipt($expenseId){
            $successful = false;
            global $wpdb;
            $expense = $wpdb->get_row("SELECT * FROM lp_financialReporter_expense WHERE id=" . $expenseId . " AND employee_id=" . get_current_user_id());
            if(count($expense) > 0) {
                $successful = unlink(ABSPATH . $expense->receipt);
            }
            return $successful;
        }

        // Used as a filter (which is added in the Setup class)
        public static function useCustomDir($dirs) {
            $customDir = "/receipts";
            $dirs['subdir'] = $customDir;
            $dirs['path'] = $dirs['basedir'] . $customDir;
            $dirs['url'] = $dirs['baseurl'] . $customDir;

            return $dirs;
        }

        // Used as a filter (which is added in the Setup class)
        public static function useCustomFilename($file) {
            $file['name'] = time() . "_" . $file['name'];
            return $file;
        }
    }
?>