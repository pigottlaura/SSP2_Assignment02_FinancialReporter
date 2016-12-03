<?php
    class lp_financialReporter_DatabaseTables {
        // Specifying which tables are required for this theme, as an array which will be looped through
        // to create them or delete them
        private static $requiredTables = array('lp_financialReporter_expense', 'lp_financialReporter_expense_category');

        function __construct() {
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        // Creating a funciton to check if the database tables required for this theme are already setup
        public static function checkRequiredTables(){

            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Querying the tables schema, to get any results for databases with the same names
            // as those specified in the array above (by imploding the array into a list of strings seperated by ","
            $existingExpenseTables = $wpdb->get_results("SELECT * FROM INFORMATION_SCHEMA.tables WHERE TABLE_NAME IN ('" . implode("', '", self::$requiredTables) . "');");

            // Checking if the number of databases returned from the query, matches
            // the number that was specified as required above
            if(count($existingExpenseTables) != count(self::$requiredTables)){
                // These tables do not already exist, so creating them
                self::createRequiredTables();
            }
        }

        public static function deleteThemeTables() {
            if(get_option("lp_financialReporter_deleteDatabaseOnThemeDeactivate") == "true") {
                global $wpdb;

                lp_financialReporter_File::deleteAllReceipts();

                foreach (self::$requiredTables as $tableName) {
                    $success = $wpdb->query("DROP TABLE " . $tableName);
                }
            }
        }

        // Creating the database tables required for this theme
        private static function createRequiredTables() {

            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Creating the expense and expense category tables (with foreign keys and references enabled)
            // EXPENSE CATEGORY table
                // The id will auto increment and be the primary key
                // The name will be less than 20 characters
            // EXPENSE table
                // The id will auto increment and be the primary key
                // The employee_id will be a BIGINT (to match with the domain of id's in WP tables) and will
                // reference an id from the wp_users table
                // The category will be an INT, and will reference an id from the expense_category table
                // The receipt will be less that 125, and will store null or a link to an image/file
                // The cost will be a decimal, with a maximum of 8 digits (2 of which have to be decimal places)
                // The description will be text, with no specified length
                // The status can only ever contain "Pending", "Approved" or "Rejected", and will default to "Pending"
            $wpdb->query("CREATE TABLE lp_financialReporter_expense_category (id INT(10) AUTO_INCREMENT, name VARCHAR(20) UNIQUE, CONSTRAINT expense_category_pk PRIMARY KEY(id));");
            $wpdb->query("CREATE TABLE lp_financialReporter_expense (id INT(10) AUTO_INCREMENT, employee_id BIGINT(20) UNSIGNED NOT NULL, category INT(10) NOT NULL, receipt VARCHAR(125), cost DECIMAL(8, 2) NOT NULL, description TEXT NOT NULL, status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending', date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP, decision_date TIMESTAMP, CONSTRAINT expense_employee_fk FOREIGN KEY(employee_id) REFERENCES wp_users(id), CONSTRAINT expense_category_fk FOREIGN KEY(category) REFERENCES lp_financialReporter_expense_category(id), CONSTRAINT expense_pk PRIMARY KEY (id));");

            // Adding in the default categories to the expense_cateogry table. Employers
            // can update these through the website once they log in.
            $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Food');");
            $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Petrol');");
            $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Accomodation');");
            $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Transport');");
            $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Other');");
        }
    }
?>