<?php
    class lp_financialReporter_File {
        private static $allowedFiletypes = array("image/jpeg", "image/png");

        function __construct(){
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        // Publicly used method, to save a file to the uploads directory
        public static function saveFile($file) {
            // Creating a response object, which will be returned to the caller.
            // Setting up the default values, so that if none of the tasks in
            // this class are successful, the response will reflect this.
            // Successful will be a boolean value, errors will be stored in an array,
            // and the filepath will be stored as a string
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "filepath" => null
            );

            // Checking if the wp_handle_upload method exists, and if
            // not then loading it in from the wp-admin directory
            if (function_exists("wp_handle_upload") == false) {
                require_once(ABSPATH . "wp-admin/includes/file.php");
            }

            // Uploading the file, specifying that there is no form
            // to be handled. Storing the result in a temporary variable
            $result = wp_handle_upload($file, array('test_form' => false));

            // Checking if an error was returned from the result
            if(isset($result["error"])){
                // Storing the error in the response's errors array
                array_push($response->errors, $result["error"]);
            }

            // Checking if a url was returned from the result i.e.
            // that the file was successfully uploaded
            if(isset($result["url"])){
                // Removing the home url portion of the string, so that the
                // path to the image is now relative
                $relativePath = str_replace(home_url("/"), "/", $result["url"]);

                // Storing the relative file path in the response object, to be
                // returned to the caller, and setting the response's successful
                // boolean to true
                $response->filepath = $relativePath;
                $response->successful = true;
            }

            // Returning the response object to the caller
            return $response;
        }

        // Publicly used method, used to delete all receipts of a specific user.
        // Invoked when a user is deleted from the wp admin page
        public static function deleteUserReceipts($userId){
            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Finding all expenses that belong to the user, that have a value stored in
            // their receipt column
            $expensesWithReceipts = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense WHERE receipt IS NOT NULL AND employee_id=" . $userId);

            // Looping through any receipts that were returned from the database,
            // and removing them from the server
            foreach($expensesWithReceipts as $expense){
                unlink(ABSPATH . $expense->receipt);
            }
        }

        // Publicly used method, invoked when a user deletes a pending expense,
        // so that the receipt associated with it will also be deleted
        public static function deleteReceipt($expenseId){
            // Creating a default response of false, which will be returned to the
            // user if no receipt is found or deleted
            $successful = false;

            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Finding the expense with an id that matches the one passed to the method,
            // which must also belong to the current user, that has a value stored in
            // it's receipt column
            $expense = $wpdb->get_row("SELECT * FROM lp_financialReporter_expense WHERE receipt IS NOT NULL AND id=" . $expenseId . " AND employee_id=" . get_current_user_id());

            // Checking if any receipt was associated with this expense
            if(count($expense) > 0) {
                // Deleting the receipt from the server
                $successful = unlink(ABSPATH . $expense->receipt);
            }

            // Returning a boolean value, to specify whether this task was successful
            return $successful;
        }

        // Public method, invoked when the theme is being deactivated, and the Employer has specified
        // that all expense should be deleted
        public static function deleteAllReceipts() {
            // Creating a default response of false, which will be returned to the
            // user if no receipt is found or deleted
            $successful = false;

            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Accessing all expenses in the database, which contain a value in
            // their receipt column
            $expensesWithReceipts = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense WHERE receipt IS NOT NULL");

            // Looping through all expenses containing receipts, and deleting the receipts
            // from the server.
            foreach($expensesWithReceipts as $expense){
                $successful = unlink(ABSPATH . $expense->receipt);
            }

            // Returning a boolean value, to specify whether this task was successful
            return $successful;
        }

        // Public method, invoked when the scripts are being added to a page (based on
        // a filter defined in the Setup class)
        public static function useCustomDir($dirs) {
            // Checking if the current user is a subscriber i.e. only
            // subscribers will be uploading receipts
            if(lp_financialReporter_User::getUserRole() == "subscriber"){
                // Specifying the "receipts" directory as the custom dir for all
                // files uploaded with expenses
                $customDir = "/receipts";
                $dirs['subdir'] = $customDir;
                $dirs['path'] = $dirs['basedir'] . $customDir;
                $dirs['url'] = $dirs['baseurl'] . $customDir;
            }

            // Returning the updated (or default) directories to the caller
            return $dirs;
        }

        // Public method, invoked when the scripts are being added to a page (based on
        // a filter defined in the Setup class)
        public static function useCustomFilename($file) {
            // Checking if the current user is a subscriber i.e. only
            // subscribers will be uploading receipts
            if(lp_financialReporter_User::getUserRole() == "subscriber") {
                // Prepending the filename with the current timestamp i.e to
                // prevent name conflicts on the server
                $file['name'] = time() . "_" . $file['name'];
            }

            // Returning the updated (or original) file to the caller
            return $file;
        }
    }
?>