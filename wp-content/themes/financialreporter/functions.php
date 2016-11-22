<?php
    // Including the autoloader file, which will in turn include all classes
    // of the theme, so that they will be available throughout all files
    include_once("autoloader.php");

    // Forcing the server to display errors when running remotely (if Debug is turned on)
    if(CONF_DEBUG){
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    }

    // Adding theme support for menus (i.e. so the navigation menu can be accessed from
    // the wp-admin panel, and displayed on the website
    add_theme_support('menus');

    // Registering the sidebar i.e. so that the main sidebar can be controlled from
    // within the appearance menu of the wp-admin panel, and displayed on the website.
    if(function_exists('register_sidebar')){
        register_sidebar(array(
            'name' => 'Main Sidebar',
            'id' => 'main-sidebar'
        ));
    }

    // Creating a funciton to check if the database tables required for this theme are already setup
    function lp_financialReporter_check_tables(){

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
            lp_financialReporter_create_tables();
        }
    }

    // Creating the database tables required for this theme
    function lp_financialReporter_create_tables() {

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

    // Creaing a filter, through which all uploads from this theme will be passed
    // i.e. so all receipts can be uploaded to the "receipts" folder of the "uploads"
    // directory
    function lp_financialReporter_useCustomDir($dirs) {
        $customDir = "/receipts";
        $dirs['subdir'] = $customDir;
        $dirs['path'] = $dirs['basedir'] . $customDir;
        $dirs['url'] = $dirs['baseurl'] . $customDir;

        return $dirs;
    }
    // Adding the filter
    add_filter('upload_dir', 'lp_financialReporter_useCustomDir');

    // Creating a filter, through which all uploads from this theme will be passed
    // i.e. so that all filenames of receipts will be appended with the current
    // timestamp (so as to avoid naming conflicts)
    function lp_financialReporter_useCustomFilename($file) {
        $file['name'] = time() . "_" . $file['name'];
        return $file;
    }
    // Adding the filter
    add_filter('wp_handle_upload_prefilter', 'lp_financialReporter_useCustomFilename' );


    function lp_financialReporter_createPages(){
        $allPageIds = array();
        $excludePageIds = array();
        $requiredPages = array(
            (object) array(
                "pageAttributes" => (object) array(
                    "post_title" => "Employee Expenses",
                    "post_name" => "employee-expenses"
                ),
                "excludeFromMenu" => true
            ),
            (object) array(
                "pageAttributes" => (object) array(
                    "post_title" => "Employer Expenses",
                    "post_name" => "employer-expenses"
                ),
                "excludeFromMenu" => true
            ),
            (object) array(
                "pageAttributes" => (object) array(
                    "post_title" => "Expense Categories",
                    "post_name" => "expense-categories"
                ),
                "excludeFromMenu" => true
            ),
            (object) array(
                "pageAttributes" => (object) array(
                    "post_title" => "Expenses",
                    "post_name" => "expenses"
                ),
                "excludeFromMenu" => false
            ),
            (object) array(
                "pageAttributes" => (object) array(
                    "post_title" => "Login",
                    "post_name" => "user-login"
                ),
                "excludeFromMenu" => true
            ),
            (object) array(
                "pageAttributes" => (object) array(
                    "post_title" => "Register",
                    "post_name" => "user-register"
                ),
                "excludeFromMenu" => true
            )
        );

        foreach($requiredPages as $key => $page) {
            // Setting all required pages to be of type "page" and their
            // status to be "publish"
            $page->pageAttributes->post_type = "page";
            $page->pageAttributes->post_status = "publish";

            $pageId = wp_insert_post($page->pageAttributes);

            array_push($allPageIds, $pageId);

            if ($page->excludeFromMenu) {
                array_push($excludePageIds, $pageId);
            }
        }

        update_option("lp_financialReporter_allPages", implode(",", $allPageIds));
        update_option("lp_financialReporter_excludePagesFromMenu", implode(",", $excludePageIds));

    }

    function lp_financialReporter_excludeFromMenu($args) {
        $args['exclude'] = get_option("lp_financialReporter_excludePagesFromMenu");
        return $args;
    }
    add_filter("wp_page_menu_args", "lp_financialReporter_excludeFromMenu");


    function lp_financialReporter_checkPages(){
        if(get_option("lp_financialReporter_allPages") == false){
            lp_financialReporter_createPages();
        }
    }
    // Setting up any initial requirements i.e. databases when the theme is "activated"
    function lp_financialReporter_activated() {
        // Checking that all database tables required by this theme exist
        lp_financialReporter_check_tables();

        lp_financialReporter_checkPages();
    }
    // Adding the action
    add_action("after_switch_theme", "lp_financialReporter_activated");

    function lp_financialReporter_deactivated(){
        $themePageIds = get_option("lp_financialReporter_allPages");
        $themePages = explode(",", $themePageIds);
        foreach($themePages as $key => $pageId) {
            wp_delete_post($pageId, true);
        }
        delete_option("lp_financialReporter_allPages");
        delete_option("lp_financialReporter_excludePagesFromMenu");
    }
    add_action("switch_theme", "lp_financialReporter_deactivated");

    // If a user is being deleted, then removing the expense they had claimed from
    // the database
    function lp_financialReporter_onDeleteUser($userId){
        $receipts = ("SELECT * FROM lp_financialReporter_expense WHERE receipt IS NOT NULL AND employee_id=" . $userId);
        if(count($receipts) > 0){
            // NEED TO DELETE RECEIPT FILES ASWELL (if they exist)
        }
        global $wpdb;
        $wpdb->query("DELETE FROM lp_financialReporter_expense WHERE employee_id=" . $userId);
    }
    add_action("delete_user", "lp_financialReporter_onDeleteUser");
?>