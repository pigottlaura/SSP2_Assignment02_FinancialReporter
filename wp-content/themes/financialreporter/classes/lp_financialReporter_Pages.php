<?php
    class lp_financialReporter_Pages {
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

        public static  function checkRequiredPages(){
            $allPagesExist = true;

            foreach(self::$requiredPages as $key => $page){
                if(get_page_by_title($page["pageAttributes"]["post_title"]) == null){
                    $allPagesExist = false;
                }
            }

            if($allPagesExist == false) {
                self::createRequiredPages();
            }
        }

        private static function createRequiredPages() {
            $allPageIds = array();
            $excludePageIds = array();

            foreach(self::$requiredPages as $key => $page) {
                // Setting all required pages to be of type "page" and their
                // status to be "publish"
                $page["pageAttributes"]["post_type"] = "page";
                $page["pageAttributes"]["post_status"] = "publish";

                $pageId = wp_insert_post($page["pageAttributes"]);

                array_push($allPageIds, $pageId);

                if ($page["excludeFromMenu"]) {
                    array_push($excludePageIds, $pageId);
                }
            }

            update_option("lp_financialReporter_allPages", implode(",", $allPageIds));
            update_option("lp_financialReporter_excludePagesFromMenu", implode(",", $excludePageIds));

        }

        public static function removeThemePages(){
            $themePageIds = get_option("lp_financialReporter_allPages");
            $themePages = explode(",", $themePageIds);
            foreach($themePages as $key => $pageId) {
                wp_delete_post($pageId, true);
            }
        }

        public static function excludeFromMenu($items, $menu, $args) {
            $excludePagesFromMenu = get_option("lp_financialReporter_excludePagesFromMenu");
            $excludePagesFromMenuArray = explode(",", $excludePagesFromMenu);

            foreach ($items as $key => $item ) {
                if(in_array($item->object_id, $excludePagesFromMenuArray)){
                    unset($items[$key]);
                }
            }

            return $items;
        }
    }
?>