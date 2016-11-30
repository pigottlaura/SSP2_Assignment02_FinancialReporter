<form method="POST" action="./">
    <label>First Name
        <input type="text" name="first_name" required>
    </label>
    <label>Last Name
        <input type="text" name="last_name" required>
    </label>
    <label>Username
        <input type="text" name="username" required>
    </label>
    <label>Email
        <input type="email" name="email" required>
    </label>
    <input type="submit" value="Register as an Employee">
</form>
or <a href="<?php echo home_url("/user-login"); ?>">Login</a>