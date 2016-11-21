<?php
    class lp_financialReporter_InputData {
        function __construct(){
        }

        // Validating input data
        public static function validateData($data, $options){
            $result = (object) array(
                "dataValidated" => false,
                "errors" => array()
            );

            return $result;
        }
    }
?>