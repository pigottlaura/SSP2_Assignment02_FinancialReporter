<?php
    class lp_financialReporter_Pages {
        // Creating a private array of all the pages that will be required in this theme,
        // so that they can be looped through and created. Specifying the page attributes,
        // which are used to create the page, and specifying whether this page should be
        // excluded from the main menu, so that this can be stored and referenced when
        // the navigation is being generated
        private static $requiredPages = array(
            array(
                "pageAttributes" => array("post_title" => "Employee Expenses", "post_name" => "employee-expenses"),
                "excludeFromMenu" => true
            ),
            array(
                "pageAttributes" => array("post_title" => "Employer Expenses", "post_name" => "employer-expenses"),
                "excludeFromMenu" => true
            ),
            array(
                "pageAttributes" => array("post_title" => "Expense Categories", "post_name" => "expense-categories"),
                "excludeFromMenu" => true
            ),
            array(
                "pageAttributes" => array("post_title" => "Expenses", "post_name" => "expenses"),
                "excludeFromMenu" => false
            ),
            array(
                "pageAttributes" => array("post_title" => "Login", "post_name" => "user-login"),
                "excludeFromMenu" => true
            ),
            array(
                "pageAttributes" => array("post_title" => "Register", "post_name" => "user-register"),
                "excludeFromMenu" => true
            )
        );

        function __construct() {
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        // Publicly used method, to check if all required pages already exist
        public static  function checkRequiredPages(){
            // Assuming all pages exist, until proven otherwise
            $allPagesExist = true;

            // Looping through the array of required pages, to see if
            // each one of them exists
            foreach(self::$requiredPages as $key => $page){
                // Checking if this page exists, based on it's title
                if(get_page_by_title($page["pageAttributes"]["post_title"]) == null){
                    // Since this page does not exist, setting the result to false
                    $allPagesExist = false;
                }
            }

            // If the result is fault
            if($allPagesExist == false) {
                // Create the pages required for the theme
                self::createRequiredPages();
            }
        }

        // Privately used method, to create the required pages of the theme
        private static function createRequiredPages() {
            // Creating two temporary arrays, to store the ids of all pages,
            // and the ids of any pages that should be excluded from the main
            // navigation
            $allPageIds = array();
            $excludePageIds = array();

            // Looping through all of the required pages (from the static variable
            // declared at the start of this class)
            foreach(self::$requiredPages as $key => $page) {
                // Setting all required pages to be of type "page" and their
                // status to be "publish"
                $page["pageAttributes"]["post_type"] = "page";
                $page["pageAttributes"]["post_status"] = "publish";

                // Creating the new page, based on the page attributes defined for each one.
                // Storing the resulting id in a temporary variable
                $pageId = wp_insert_post($page["pageAttributes"]);

                // Adding the id of this page to the array of all page ids
                array_push($allPageIds, $pageId);

                // Checking if this page has be specified to be excluded from the
                // main navigation
                if ($page["excludeFromMenu"]) {
                    // Storing this new page's id in the exclude page ids array
                    array_push($excludePageIds, $pageId);
                }
            }

            // Updating the all pages, and exluded pages, arrays in the options table,
            // based on the temporary arrays of page ids created in this method. Imploding
            // these arrays to comma seperated strings
            update_option("lp_financialReporter_allPages", implode(",", $allPageIds));
            update_option("lp_financialReporter_excludePagesFromMenu", implode(",", $excludePageIds));

        }

        // Publicly used method, to remove all pages created by the theme, when the theme is
        // deactivated
        public static function removeThemePages(){
            // Getting the array of all pages created by this theme, from the options table
            $themePageIds = get_option("lp_financialReporter_allPages");

            // Exploding the string of ids into an array, so that it can be looped through
            $themePages = explode(",", $themePageIds);

            // Looping through each of the page ids in the array generated above
            foreach($themePages as $key => $pageId) {
                // Deleting this page, using the id from which it was identified.
                // Forcing this page to bypass the trash and be fully deleted
                wp_delete_post($pageId, true);
            }
        }

        // Public method, invoked when the main navigation is being added to a page (based on
        // a filter defined in the Setup class)
        public static function excludeFromMenu($items, $menu, $args) {
            // Getting the array of all pages created by this theme, that are to be
            // excluded from the main menu, from the options table
            $excludePagesFromMenu = get_option("lp_financialReporter_excludePagesFromMenu");

            // Exploding the string of ids into an array, so that it can be looped through
            $excludePagesFromMenuArray = explode(",", $excludePagesFromMenu);

            // Looping through each of the items that are currently to be added to the
            // main navigation
            foreach ($items as $key => $item ) {
                // Checking if the object id of this item is in the list of pages to
                // be excluded from the main navigation
                if(in_array($item->object_id, $excludePagesFromMenuArray)){
                    // Removing this item from the array
                    unset($items[$key]);
                }
            }

            // Returning the updated list of items (which will now exclued the specified
            // pages) to the caller
            return $items;
        }
    }
?>