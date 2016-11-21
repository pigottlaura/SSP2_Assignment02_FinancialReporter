<?php
    class lp_financialReporter_InputData {
        function __construct(){
        }

        // Validating input data
        public static function validateData($data, $options) {
            $result = (object) array(
                "dataValidated" => false,
                "errors" => array()
            );

            return $result;
        }

        public static function sanitiseData($data) {
            $sanitisedData = array();

            foreach($data as $key => $value) {
                $sanitisedData[$key] = trim($value);
                $sanitisedData[$key] = htmlentities($sanitisedData[$key]);
                $sanitisedData[$key] = strip_tags($sanitisedData[$key]);
                $sanitisedData[$key] = str_replace("'", "`", $sanitisedData[$key]);
            }

            return $sanitisedData;
        }
    }
?>