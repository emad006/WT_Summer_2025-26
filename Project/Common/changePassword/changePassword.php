<?php
session_start();

include "../lib/dbConfig.php";

$errors = [];
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>FoodRush - Change Password</title>
</head>

<body>
    <div id="navbar">
        <div id="navLeft">
            <a href="#" class="navLink">Dashboard</a>
            <a href="#" class="navLink">Browse</a>
            <a href="#" class="navLink">Cart</a>
            <a href="#" class="navLink">My Orders</a>
            <a href="#" class="navLink navLinkActive">Profile</a>
            <a href="#" class="navLink">Logout</a>
        </div>

        <div id="navRight">Emad · Customer</div>
    </div>

    <div id="mainArea">
        <h1 id="titleName">Change Password</h1>

        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors) ?></div>

        <form method="post">
            <div class="inputBlock">
                <label class="inputLabel">Current Password:</label>
                <br>
                <input type="password" name="currPassword" class="inputField" value="<?php if (!empty($_POST['currPassword'])) echo $_POST['currPassword']; ?>" placeholder="Enter your current password">
            </div>

            <div class="inputBlock">
                <label class="inputLabel">New Password:</label>
                <br>
                <input type="password" name="newPassword" class="inputField" value="<?php if (!empty($_POST['newPassword'])) echo $_POST['newPassword']; ?>" placeholder="Enter your new password">
            </div>

            <div class="inputBlock">
                <label class="inputLabel">Confirm New Password:</label>
                <br>
                <input type="password" name="confNewPassword" class="inputField" value="<?php if (!empty($_POST['confNewPassword'])) echo $_POST['confNewPassword']; ?>" placeholder="Confirm your new password">
            </div>

            <div class="inputBlock">
                <button type="submit" class="btn" id="submitBtn">Change Password</button>
                <button type="submit" class="btn" id="cancelBtn">Cancel</button>
            </div>
        </form>
    </div>

</body>

</html>