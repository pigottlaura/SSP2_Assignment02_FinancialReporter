<?php
    class lp_financialReporter_InputData {
        
        function __construct(){
            // Not allowing this class to be instantiated
            throw new Exception("Cannot instantiate this class. Please use the static methods provided instead.");
        }

        // Validating input data i.e. to ensure that the required data is provided, and that
        // the values contained are in the expected format
        public static function validateData($data, $options) {
            // Creating a temporary object, to store the result, and any errors
            // of, the attempt to validate the data, based on the options supplied
            $result = (object) array(
                "successful" => true,
                "errors" => array()
            );

            // Looping through each of the data inputs passed to the method
            foreach($data as $inputName => $inputValue){
                // Creating a "pretty" version of the input name, incase it needs to be
                // traced out in any errors from the validation
                $prettyInputName = "\"" . str_replace("_", " ", $inputName) . "\"";

                // Checking if "required" was specified as an option, and that this
                // input was specified within the "required" array
                if(isset($options["required"]) && in_array($inputName, $options["required"])){
                    // Checking that the value of this input is set, and that it's length is
                    // greater than 0
                    if(isset($inputValue) == false || strlen($inputValue) == 0){
                        // Since this input did not contain a value, adding this an an error to
                        // the response object's array
                        array_push($result->errors, $prettyInputName . " is a required field");
                    }
                }

                // Checking if "email" was specified as an option, and that this
                // input was specified within the "email" array
                if(isset($options["email"]) && in_array($inputName, $options["email"])){
                    // Checking that the value of this input is a valid email
                    if(is_email($inputValue) == false){
                        // Since this input did not contain a valid email, adding this an an error to
                        // the response object's array
                        array_push($result->errors, $prettyInputName . " must contain a valid email address");
                    }
                }

                // Checking if "number" was specified as an option, and that this
                // input was specified within the "number" array
                if(isset($options["number"]) && in_array($inputName, $options["number"])){
                    // Checking that the value of this input is a valid number
                    if(is_numeric($inputValue) == false){
                        // Since this input did not contain a valid number, adding this an an error to
                        // the response object's array
                        array_push($result->errors, $prettyInputName . " must contain a valid number");
                    }
                }

                // Checking if "boolean" was specified as an option, and that this
                // input was specified within the "boolean" array
                if(isset($options["boolean"]) && in_array($inputName, $options["boolean"])){
                    // Checking that the value of this input is a valid boolean
                    if(is_bool($inputValue) == false){
                        // Since this input did not contain a valid boolean, adding this an an error to
                        // the response object's array
                        array_push($result->errors, $prettyInputName . " must be true or false");
                    }
                }
            }

            // Determining whether or not this validation was successful,
            // based on the number of errors in the result errors array
            $result->successful = count($result->errors) > 0 ? false : true;

            // Returning the result object to the caller
            return $result;
        }

        // Sanitising data i.e. to remove any unexpected code or scripts from it
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

            // Returning the associative array of sanitised data to the user
            return $sanitisedData;
        }
    }
?>