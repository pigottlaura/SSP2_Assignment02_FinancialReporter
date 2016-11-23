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

        public static function activated() {
            // Checking that all database tables required by this theme exist
            // (and if not, then creating them)
            lp_financialReporter_DatabaseTables::checkRequiredTables();

            // Checking that all pages required by this theme exist
            // (and if not, then creating them)
            lp_financialReporter_Pages::checkRequiredPages();
        }

        public static function deactivated() {
            lp_financialReporter_Pages::removeThemePages();
        }

        public static function addActions() {
            // Adding the actions
            add_action("after_switch_theme", "lp_financialReporter_Setup::activated");
            add_action("switch_theme", "lp_financialReporter_Setup::deactivated");
            add_action("delete_user", "lp_financialReporter_Setup::onDeleteUser");
        }

        public static function addFilters() {
            // Creaing a filter, through which all uploads from this theme will be passed
            // i.e. so all receipts can be uploaded to the "receipts" folder of the "uploads"
            // directory
            add_filter('upload_dir', 'lp_financialReporter_File::useCustomDir');

            // Creating a filter, through which all uploads from this theme will be passed
            // i.e. so that all filenames of receipts will be appended with the current
            // timestamp (so as to avoid naming conflicts)
            add_filter('wp_handle_upload_prefilter', 'lp_financialReporter_File::useCustomFilename' );

            add_filter("wp_page_menu_args", "lp_financialReporter_Pages::excludeFromMenu");
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

        private static function onDeleteUser($userId){
            lp_financialReporter_File::deleteUserReceipts($userId);
        }
    }
?>