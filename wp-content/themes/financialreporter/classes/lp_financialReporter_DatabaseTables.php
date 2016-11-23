<?php
    class lp_financialReporter_DatabaseTables {
        function __construct() {
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        // Creating a funciton to check if the database tables required for this theme are already setup
        public static function checkRequiredTables(){

            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Specifying which tables are required for this theme
            $requiredTables = array('lp_financialReporter_expense', 'lp_financialReporter_expense_category');

            // Querying the tables schema, to get any results for databases with the same names
            // as those specified in the array above (by imploding the array into a list of strings seperated by ","
            $existingExpenseTables = $wpdb->get_results("SELECT * FROM INFORMATION_SCHEMA.tables WHERE TABLE_NAME IN ('" . implode("', '", $requiredTables) . "');");

            // Checking if the number of databases returned from the query, matches
            // the number that was specified as required above
            if(count($existingExpenseTables) == count($requiredTables)){
                //echo "TABLES ALREADY EXIST";
            } else {
                // Since the tables don't already exist, creating them
                self::createRequiredTables();
            }
        }

        // Creating the database tables required for this theme
        private static function createRequiredTables() {

            // Accessing the global wpdb variable, to access the database
            global $wpdb;

            // Creating the expense and expense category tables (with foreign keys and references enabled)
            $wpdb->query("CREATE TABLE lp_financialReporter_expense_category (id INT(10) AUTO_INCREMENT, name VARCHAR(20), CONSTRAINT expense_category_pk PRIMARY KEY(id));");
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