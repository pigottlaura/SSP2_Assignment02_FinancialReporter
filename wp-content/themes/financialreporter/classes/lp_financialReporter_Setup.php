<?php
    class lp_financialReporter_Setup {
        function __construct() {
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        public static function pageLoading() {
            lp_financialReporter_Setup::addThemeSupports();
            lp_financialReporter_Setup::addActions();
            lp_financialReporter_Setup::addFilters();
        }

        // Function which is invoked by the "after_switch_theme" action
        // i.e. every time the theme is activated
        public static function activated() {
            // Checking that all database tables required by this theme exist
            // (and if not, then creating them) using the DatabaseTables class
            lp_financialReporter_DatabaseTables::checkRequiredTables();

            // Checking that all pages required by this theme exist
            // (and if not, then creating them) using the Pages class
            lp_financialReporter_Pages::checkRequiredPages();
        }

        // Function which is invoked by te "switch_theme" action
        // i.e. every time another theme is activated (this them is deactivated)
        public static function deactivated() {
            // Removing all pages created by the theme, when the theme was activated
            lp_financialReporter_Pages::removeThemePages();
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
            add_action("delete_user", "lp_financialReporter_Setup::onDeleteUser");
        }

        public static function addFilters() {
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


            /*
            $sidebarWidgets = array(
                "main-sidebar" => array (
                    array(
                       "id" => "recent-posts-2"
                    )
                )
            );

            var_dump(get_option("page_on_front"));
            apply_filters("sidebars_widgets", $sidebarWidgets);
            */
        }

        // Public method, invoked when a user is being deleted (based on
        // an action defined above).
        public static function onDeleteUser($userId){
            // Deleting all receipts
            lp_financialReporter_File::deleteUserReceipts($userId);
            lp_financialReporter_Expense::removeAllExpensesForUser($userId);
        }
    }
?>