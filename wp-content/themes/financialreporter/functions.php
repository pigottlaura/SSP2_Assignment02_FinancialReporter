<?php
    include_once("autoloader.php");

    // Forcing Server to display errors when running remotely (if Debug is turned on)
    if(CONF_DEBUG){
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    }

    // Adding theme support for menus
    add_theme_support('menus');

    // Registering sidebar
    if(function_exists('register_sidebar')){
        register_sidebar(array(
            'name' => 'Main Sidebar',
            'id' => 'main-sidebar'
        ));
    }

    function lp_financialReporter_useCustomDir($dirs) {
        $customDir = "/receipts";
        $dirs['subdir'] = $customDir;
        $dirs['path'] = $dirs['basedir'] . $customDir;
        $dirs['url'] = $dirs['baseurl'] . $customDir;

        return $dirs;
    }

    function lp_financialReporter_useCustomFilename($file) {
        $file['name'] = time() . "_" . $file['name'];
        return $file;
    }

    $result = wp_upload_dir(null, true, true);
    add_filter('upload_dir', 'lp_financialReporter_useCustomDir');
    add_filter('wp_handle_upload_prefilter', 'lp_financialReporter_useCustomFilename' );

    // Setting up any initial requirements i.e. databases
    add_action("after_switch_theme", "lp_financialReporter_init");

    function lp_financialReporter_check_databases(){
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
        $wpdb->query("CREATE TABLE lp_financialReporter_expense (id INT(10) AUTO_INCREMENT, employee_id BIGINT(20) UNSIGNED NOT NULL, category INT(10) NOT NULL, receipt VARCHAR(125), cost DECIMAL(8, 2) NOT NULL, description TEXT NOT NULL, status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending', date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP, decision_date TIMESTAMP, CONSTRAINT expense_employee_fk FOREIGN KEY(employee_id) REFERENCES wp_users(id), CONSTRAINT expense_category_fk FOREIGN KEY(category) REFERENCES lp_financialReporter_expense_category(id), CONSTRAINT expense_pk PRIMARY KEY (id));");

        // ADD IN DEFAULT CATEGORIES
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Food');");
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Petrol');");
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Accomodation');");
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Transport');");
        $wpdb->query("INSERT INTO lp_financialReporter_expense_category(name) VALUES('Other');");
    }

    function lp_financialReporter_init() {

        lp_financialReporter_check_databases();
    }
?>