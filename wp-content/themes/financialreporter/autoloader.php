<?php
    spl_autoload_register("myAutoLoader");

    function myAutoLoader($className) {
        $path = "/classes/";
        include $path.$className . ".php";
    }

?>