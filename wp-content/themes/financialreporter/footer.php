        <?php wp_footer(); ?>
        <?php // Including admin AJAX url in hidden input, so it can be accessed client side by script.js ?>
        <input type="hidden" id="ajax-url" value="<?php echo admin_url("admin-ajax.php"); ?>">
    </body>
</html>
