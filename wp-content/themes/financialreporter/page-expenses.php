<?php // Controls Expenses Page ?>
<?php
    // Only logged in users can access this page
    if(is_user_logged_in()) {
        // Redirecting the user to the appropriate page based on their rolw
        if(lp_financialReporter_User::getUserRole() == "administrator"){
            wp_redirect("/ssp2/assignment02/employer-expenses");
        } else {
            wp_redirect("/ssp2/assignment02/employee-expenses");
        }
    } else {
        // This user is not yet logged in, so redirecting them to the login page
        wp_redirect("/ssp2/assignment02/user-login");
    }
?>