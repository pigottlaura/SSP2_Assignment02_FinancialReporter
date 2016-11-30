<?php // Controls User Registeration Page ?>
<?php
    // Only people that are not logged in can access this page page
    if(is_user_logged_in()){
        wp_redirect(home_url("/expenses"));
    } else {
        // If the user has submitted the registration form
        if(count($_POST) > 0){
            $registration = lp_financialReporter_User::registerNewUser($_POST);
        }
    }
?>
<?php get_header(); ?>

<div class="row">
    <div class="col-xs-3">
        <?php include("sidebar.php"); ?>
    </div>
    <div class="col-xs-9">
        <div class="row">
            <div class="col-xs-12">
                <?php include("components/the_loop_noLinks.php"); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8">
                <?php
                    if(isset($registration)) {
                        if(count($registration->errors) == 0){
                            if(isset($registration->email)){
                                echo "<p>An email has been sent to the email address you provided: " . $registration->email . "</p>";
                                echo "<p>Please use the link provided in this email to complete your registration.</p>";
                            }
                        } else {
                            foreach($registration->errors as $key => $error){
                                echo $error . "<br>";
                            }
                            include_once("components/registration_form.php");
                        }
                    } else {
                        include_once("components/registration_form.php");
                    }
                ?>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
