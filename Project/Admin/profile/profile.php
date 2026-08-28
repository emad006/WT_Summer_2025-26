<?php
session_start();

include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

$success_message = "";
$error_message = "";

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   
}

?>


<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Profile</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="../dashboard/dashboard.php" class="navigation_link">Dashboard</a>
            <a href="../users/users.php" class="navigation_link">Users</a>
            <a href="#" class="navigation_link">Cusines</a>
            <a href="#" class="navigation_link">Orders</a>
            <a href="#" class="navigation_link">Reviews</a>
            <a href="profile.php" class="navigation_link active_link">Profile</a>
            <a href="../logout.php" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>My Profile</h2>

        <div id="success_box" class="box"></div>
        <div id="error_box" class="box"></div>

        <div id="form_box" class="box">
            <form method="post">
                <div class="input_block">
                    Full Name<br>
                    <input type="text" name="name" class="input_field">
                </div>

                <div class="input_block">
                    Email (cannot be changed)<br>
                    <input type="text" class="input_field"> disabled>
                </div>

                <div class="input_block">
                    Phone<br>
                    <input type="text" name="phone" class="input_field">
                </div>

                <div class="input_block">
                    Address<br>
                    <textarea name="address" class="input_field" id="address_field"></textarea>
                </div>

                <div class="input_block">
                    <input type="submit" id="save_btn" name="action_btn" value="Save Changes">
                </div>
            </form>

            <form method="get" action="../../Common/changePassword/changePassword.php">
                <input type="submit" id="password_btn" value="Change Password">
            </form>
        </div>
    </div>
</body>

</html>
