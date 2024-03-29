<?php
    class lp_financialReporter_Setup {
        function __construct() {
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        public static function pageLoading() {
            self::addThemeSupports();
            self::addActions();
            self::addFilters();
        }

        // Function which is invoked by the "after_switch_theme" action
        // i.e. every time the theme is activated
        public static function activated() {
            // Setting up the defaults for any options, in the wp_options table,
            // as required by this theme
            self::setupThemeOptions();

            // Checking that all database tables required by this theme exist
            // (and if not, then creating them) using the DatabaseTables class
            lp_financialReporter_DatabaseTables::checkRequiredTables();

            // Creating a new navigation menu
            self::createNavMenu();

            // Checking that all pages required by this theme exist
            // (and if not, then creating them) using the Pages class
            lp_financialReporter_Pages::checkRequiredPages();
        }

        // Function which is invoked by te "switch_theme" action
        // i.e. every time another theme is activated (this them is deactivated)
        public static function deactivated() {
            // Removing all pages created by the theme, when the theme was activated
            lp_financialReporter_Pages::removeThemePages();

            // Deleting the custom navigation menu
            self::deleteNavMenu();

            // Deleting the database tables, as created by the theme. Within this
            // method, a check will be made as to whether or not the Employer
            // wants these tables to be deleted upon theme deactivation
            lp_financialReporter_DatabaseTables::deleteThemeTables();

            // Deleting any options that were added to the wp_options table by this theme
            self::deleteThemeOptions();
        }

        // Adding action hooks every time a page is loaded, to detect actions such
        //// as the theme being activated, deactivated or a user being deleted
        public static function addActions() {
            // Invoking the activated() method of this class, every time this theme
            // is activated (so that required tables and pages can be checked and/or created
            add_action("after_switch_theme", "lp_financialReporter_Setup::activated");

            // Invoking the deactivated() method of this class, every time this theme
            // is deactivated (i.e. another theme is activated) so that pages and options
            // that were created when the theme was activated can be removed
            add_action("switch_theme", "lp_financialReporter_Setup::deactivated");

            // Adding a method to carry out a series of tasks every time a user is deleted
            // i.e. to remove their expenses and receipts
            add_action("delete_user", "lp_financialReporter_Setup::onDeleteUser");

            // Adding an action to detect when the scripts are being loaded into the page,
            // so that custom scripts and styles can be loaded at the same time
            add_action("wp_enqueue_scripts", "lp_financialReporter_Setup::enqueueCustomScripts");

            // Adding a series of AJAX actions, to detect a series of AJAX requests. All
            // of these actions will be sent to the ajax request method, as it will be the
            // User's class that will determine if and how these actions will be completed
            add_action("wp_ajax_addExpense", "lp_financialReporter_Setup::ajaxRequest");
            add_action("wp_ajax_removeExpense", "lp_financialReporter_Setup::ajaxRequest");
            add_action("wp_ajax_getAllExpensesForCurrentUser", "lp_financialReporter_Setup::ajaxRequest");
            add_action("wp_ajax_getAllExpenses", "lp_financialReporter_Setup::ajaxRequest");
            add_action("wp_ajax_expenseApproval", "lp_financialReporter_Setup::ajaxRequest");
            add_action("wp_ajax_addNewExpenseCategory", "lp_financialReporter_Setup::ajaxRequest");
            add_action("wp_ajax_removeExpenseCategory", "lp_financialReporter_Setup::ajaxRequest");
            add_action("wp_ajax_saveEmployerSettings", "lp_financialReporter_Setup::ajaxRequest");
        }

        // Publicly used method, invoked when an AJAX request is made to the server, using
        // one of the actions defined above
        public static function ajaxRequest(){
            // Storing the result of the user's attempt to complete this action, and any
            // resulting errors or data to be returned to the client
            $result = lp_financialReporter_User::attemptAction($_GET["action"]);

            // Encoding the result as JSON, and echoing it back to the caller.
            // Killing the request, so that control is returned to the client
            echo json_encode($result);
            die();
        }

        public static function addFilters() {
            add_filter("login_url", "lp_financialReporter_User::useCustomLogin");

            // Creaing a filter, through which all uploads from this theme will be passed,
            // so that a custom directory can be specified, using the File classs i.e. so
            // all receipts can be uploaded to the "receipts" folder of the "uploads" directory
            add_filter('upload_dir', 'lp_financialReporter_File::useCustomDir');

            // Creating a filter, through which all uploads from this theme will be passed,
            // so that the filename under which the file is stored can be customised i.e. so
            // that all filenames of receipts will be appended with the current timestamp (so
            // as to avoid naming conflicts)
            add_filter('wp_handle_upload_prefilter', 'lp_financialReporter_File::useCustomFilename' );

            // Creating a filter for when the navigation menu is being built, so that
            // some of the dynamically created pages can be excluded from it i.e.
            // incase the user has their menu set up to automatically include top
            // level pages in the navigation. Giving this filter a priority of 10, and
            // allowing it to accept three arguments
            add_filter("wp_get_nav_menu_items", "lp_financialReporter_Pages::excludeFromMenu", 10, 3);
        }

        public static function addThemeSupports() {
            // Adding theme support for menus (i.e. so the navigation menu can be accessed from
            // the wp-admin panel, and displayed on the website
            add_theme_support('menus');

            // Registering the sidebar i.e. so that the main sidebar can be controlled from
            // within the appearance menu of the wp-admin panel, and displayed on the website.
            if(function_exists('register_sidebar') == false){
                require_once(ABSPATH . "wp-includes/widgets.php");
            }
            register_sidebar(array(
                'name' => 'Main Sidebar',
                'id' => 'main-sidebar'
            ));
        }

        // Public method, invoked when a user is being deleted (based on
        // an action defined above).
        public static function onDeleteUser($userId){
            // First deleting any receipts the user may have uploaded
            lp_financialReporter_File::deleteUserReceipts($userId);

            // Deleting all expenses that the user has stored in the database
            lp_financialReporter_Expense::removeAllExpensesForUser($userId);
        }

        // Public method, invoked when the scripts are being added to a page (based on
        // an action defined above).
        public static function enqueueCustomScripts(){
            // Enqueueing the Bootstrap and Custom CSS Stylesheets
            wp_enqueue_style("bootstrap-css-stylesheet", "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css");
            wp_enqueue_style("main-css-stylesheet", get_template_directory_uri() . "/style.css", "bootstrap-css-stylesheet");

            // Checking if there is currently a user logged in
            if(is_user_logged_in()){
                // Determining what type of user is logged in, so that the appropriate script can
                // enqueued in the footer
                if(lp_financialReporter_User::getUserRole() == "administrator"){
                    wp_enqueue_script("employer-js-script", get_template_directory_uri() . "/js/employer-script.js", array(), null, true);
                } else if(lp_financialReporter_User::getUserRole() == "subscriber"){
                    wp_enqueue_script("employee-js-script", get_template_directory_uri() . "/js/employee-script.js", array(), null, true);
                }
            }

            // Enqueueing the main JS script, which will work in conjunction with either of
            // the two custom scripts above (if a user is logged in)
            wp_enqueue_script("main-js-script", get_template_directory_uri() . "/js/script.js", "jquery-js-script", null, true);

        }

        // Privately used method to create a navigation menu
        private static function createNavMenu(){
            // Creating a new navigation menu, and storing the resulting
            // ID in a temporary variable
            $navMenuId = wp_create_nav_menu("Header Menu");

            // Adding the two initial pages to the main navigation
            // i.e. home and expenses
            wp_update_nav_menu_item($navMenuId, 0, array(
                'menu-item-title' =>  __('Home'),
                'menu-item-url' => home_url("/"),
                'menu-item-status' => 'publish'
            ));
            wp_update_nav_menu_item($navMenuId, 0, array(
                'menu-item-title' =>  __('Expenses'),
                'menu-item-url' => home_url("/expenses"),
                'menu-item-status' => 'publish'
            ));

            // Storing the main navigation's id in the wp_options table
            update_option("lp_financialReporter_navMenuId", $navMenuId);
        }

        // Public method, invoked when the theme is being deactivated
        private static function deleteNavMenu(){
            // Deleting the main navigation, based on the id stored in
            // the wp_options table
            wp_delete_nav_menu(get_option("lp_financialReporter_navMenuId"));
        }

        // Public method, invoked when the theme is being activated
        private static function setupThemeOptions() {
            update_option("lp_financialReporter_debug", "on");
            update_option("lp_financialReporter_deleteDatabaseOnThemeDeactivate", "false");
            update_option("lp_financialReporter_receiptsRequiredForAllExpenses", "false");
        }

        // Public method, invoked when the theme is being deactivated
        private static function deleteThemeOptions() {
            delete_option("lp_financialReporter_deleteDatabaseOnThemeDeactivate");
            delete_option("lp_financialReporter_debug");
            delete_option("lp_financialReporter_navMenuId");
            delete_option("lp_financialReporter_allPages");
            delete_option("lp_financialReporter_excludePagesFromMenu");
        }
    }
?>