<?php
    // Including the autoloader file, which will in turn include all classes
    // of the theme, so that they will be available throughout all files
    include_once("autoloader.php");

    // Forcing the server to display errors when running remotely (if Debug is turned on)
    if(CONF_DEBUG){
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    }

    // Completing the setup, using predefined functions within the
    // classes of the theme
    lp_financialReporter_Setup::pageLoading();
?>