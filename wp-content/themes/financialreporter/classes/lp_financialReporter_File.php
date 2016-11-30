<?php
    class lp_financialReporter_File {
        private static $allowedFiletypes = array("image/jpeg", "image/png");

        function __construct(){
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        public static function saveFile($file) {
            $response = (object) array(
                "successful" => false,
                "errors" => array(),
                "filepath" => null
            );

            if (function_exists("wp_handle_upload") == false) {
                require_once(ABSPATH . "wp-admin/includes/file.php");
            }


            $result = wp_handle_upload($file, array('test_form' => false));

            if(isset($result["error"])){
                array_push($response->errors, $result["error"]);
            }
            if(isset($result["url"])){
                $response->filepath = $result["url"];
                $response->successful = true;
            }

            /*
            $originalFilename = $file["name"];
            $saveAsFilename = time() . "_" . $originalFilename;
            $tempPath = $file["tmp_name"];
            echo $tempPath;
            $mimeType = $file["type"];

            $saveToPath =  self::getUploadPath() . $saveAsFilename;
            echo $saveToPath;
            if(in_array($mimeType, self::$allowedFiletypes)) {
                echo "this file is allowed";
                if(self::uploadFile($file)) {
                    $response->filepath = $saveToPath;
                } else {
                    array_push($response->errors, "Could not save file");
                }
            } else {
                array_push($response->errors, "This filetype is not allowed");
            }

            */

            return $response;
        }

        /*
        public static function uploadFile($tempPath, $saveToPath=null) {
            $success = false;


            move_uploaded_file($tempPath, $saveToPath);

            return $success;
        }
        */

        public static function getUploadPath() {
            $wpUploadDirs = wp_upload_dir();
            $receiptUploadsDir = $wpUploadDirs['basedir'] . "/receipts/";

            if (file_exists($receiptUploadsDir) == false) {
                $success = wp_mkdir_p($receiptUploadsDir);
            }

            return $receiptUploadsDir;
        }

        public static function deleteUserReceipts($userId){
            // If a user is being deleted, then removing the expense they had claimed
            // from the database
            global $wpdb;
            $receipts = $wpdb->get_results("SELECT * FROM lp_financialReporter_expense WHERE receipt IS NOT NULL AND employee_id=" . $userId);
            if(count($receipts) > 0) {
                // NEED TO DELETE RECEIPT FILES ASWELL (if they exist)
            }
        }

        // Used as a filter (which is added in the Setup class)
        public static function useCustomDir($dirs) {
            $customDir = "/receipts";
            $dirs['subdir'] = $customDir;
            $dirs['path'] = $dirs['basedir'] . $customDir;
            $dirs['url'] = $dirs['baseurl'] . $customDir;

            return $dirs;
        }

        // Used as a filter (which is added in the Setup class)
        public static function useCustomFilename($file) {
            $file['name'] = time() . "_" . $file['name'];
            return $file;
        }
    }
?>