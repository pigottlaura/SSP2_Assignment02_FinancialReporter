<?php // Controls Expenses Page ?>
<?php
    // Only logged in users can access this page
    if(is_user_logged_in()) {
        if(lp_financialReporter_User::getUserRole() == "administrator"){
            wp_redirect("/ssp2/assignment02/employer-expenses");
        } else {
            wp_redirect("/ssp2/assignment02/employee-expenses");
        }
    } else {
        wp_redirect("/ssp2/assignment02/user-login");
    }
?>