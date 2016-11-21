<?php
    // Adding theme support for menus
    add_theme_support('menus');

    // Setting up any initial requirements i.e. databases
    add_action("after_switch_theme", "lp_financialReporter_init");

    // Registering sidebar
    if(function_exists('register_sidebar')){
        register_sidebar(array(
            'name' => 'Main Sidebar',
            'id' => 'main-sidebar'
        ));
    }

    // Adding in function to get user's role i.e. admin/subscriber
    function get_user_role() {
        global $wp_roles;
        $usersRole ='';

        foreach ($wp_roles->role_names as $role => $name ) {
            if (current_user_can( $role ) ){
                $usersRole = $role;
            }
        }
        return $usersRole;
    }

    // Getting the name of a category based on it's id
    function lp_get_category($categoryId){
        global $wpdb;
        return $wpdb->get_var("SELECT name FROM lp_financialReporter_expense_category WHERE id=" . $categoryId);
    }

    function lp_get_employee_name($employeeId){
        global $wpdb;
        return $wpdb->get_var("SELECT display_name FROM wp_users WHERE id=" . $employeeId);
    }
    // Validating input data
    function lp_validate_data($data, $options){
        $result = (object) array(
            "dataValidated" => false,
            "errors" => array()
        );

        return $result;
    }

    function lp_financialReporter_init() {
        global $wpdb;
        $requiredDatabases = array('lp_financialReporter_expense', 'lp_financialReporter_expense_category');

        $existingExpenseDatabases = $wpdb->get_results("SELECT * FROM INFORMATION_SCHEMA.tables WHERE TABLE_NAME IN ('" . implode("', '", $requiredDatabases) . "');");
        if(count($existingExpenseDatabases) == count($requiredDatabases)){
            //echo "DATABASES ALREADY EXIST";
        } else {
            lp_financialReporter_create_databases();
        }
    }

    function lp_financialReporter_create_databases() {
        global $wpdb;

        // CREATE TABLES
        $wpdb->query("CREATE TABLE lp_financialReporter_expense_category (id INT(10) AUTO_INCREMENT, name VARCHAR(20), CONSTRAINT expense_category_pk PRIMARY KEY(id));");
        $wpdb->query("CREATE TABLE lp_financialReporter_expense (id INT(10) AUTO_INCREMENT, employee_id BIGINT(20) UNSIGNED NOT NULL, category INT(10) NOT NULL,	receipt VARCHAR(40), cost DECIMAL(8, 2) NOT NULL, description TEXT NOT NULL, status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending', date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP, decision_date TIMESTAMP, CONSTRAINT expense_employee_fk FOREIGN KEY(employee_id) REFERENCES wp_users(id), CONSTRAINT expense_category_fk FOREIGN KEY(category) REFERENCES expense_category(id), CONSTRAINT expense_pk PRIMARY KEY (id));");

        // ADD IN DEFAULT CATEGORIES
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Food');");
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Petrol');");
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Accomodation');");
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Transport');");
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Other');");
    }
?>