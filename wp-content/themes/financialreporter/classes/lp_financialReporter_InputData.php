<?php
    class lp_financialReporter_InputData {
        
        function __construct(){
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        // Validating input data
        public static function validateData($data, $options) {
            // Creating a temporary object, to store the result, and any errors
            // of, the attempt to validate the data, based on the options supplied
            $result = (object) array(
                "dataValidated" => false,
                "errors" => array()
            );

            // TO DO

            return $result;
        }

        public static function sanitiseData($data) {
            // Creating a temporary array, to store the data as it is
            // sanitised, which will then be returned to the user
            $sanitisedData = array();

            // Looping through each piece of data supplied to the function
            // i.e. in a form request, this would be each input
            foreach($data as $key => $value) {
                // Passing the data through the trim(), htmlentities() and
                // strip_tags() methods, storing it back in the temporary
                // array each time, using the same key to identify it as in the
                // original array i.e. an input with the name of "username" will
                // be stored in the temporary associative array under "username"
                $sanitisedData[$key] = trim($value);
                $sanitisedData[$key] = htmlentities($sanitisedData[$key]);
                $sanitisedData[$key] = strip_tags($sanitisedData[$key]);
                $sanitisedData[$key] = str_replace("'", "`", $sanitisedData[$key]);
            }

            // Returing the associative array of sanitised data to the user
            return $sanitisedData;
        }
    }
?>