<?php
    // Adding theme support for menus
    add_theme_support('menus');

    // Registering sidebar
    if( function_exists('register_sidebar')){
        register_sidebar(array(
            'name' => 'Main Sidebar',
            'id' => 'main-sidebar'
        ));
    }

    // Adding in function to get user's role i.e. admin/subscriber
    function get_user_role() {
        global $wp_roles;
        $usersRole ='';

        foreach ($wp_roles->role_names as $role => $name ) {
            if (current_user_can( $role ) ){
                $usersRole = $role;
            }
        }
        return $usersRole;
    }

    function validateData($data, $options){
        $result = (object) array(
            "dataValidated" => false,
            "errors" => array()
        );

        return $result;
    }
?>