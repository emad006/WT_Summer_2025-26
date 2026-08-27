<?php
session_start();

include "../lib/dbConfig.php";
include "../lib/helperFunctions.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["submitBtn"])) { // Enter when submit button is pressed
        
        // <-------- Validation Start -------->

        // Current password validation
        if (empty($_POST["currPassword"])) {
            $errors[] = "Current password is required.";
        } else if (strlen($_POST["currPassword"]) < 8) { // Check if the password is at least 8 characters long
            $errors[] = "Current password must be at least 8 characters long.";
        } else if (strlen($_POST["currPassword"]) > 255) { // Check if the password is less than 255 characters long
            $errors[] = "Current password cannot exceed more than 255 characters long.";
        }

        // New password validation
        if (empty($_POST["newPassword"])) { // Check if password is empty
            $errors[] = "New password is required.";
        } else if (strlen($_POST["newPassword"]) < 8) { // Check if the password is at least 8 characters long
            $errors[] = "New password must be at least 8 characters long.";
        } else if (strlen($_POST["newPassword"]) > 255) { // Check if the password is less than 255 characters long
            $errors[] = "New password cannot exceed more than 255 characters long.";
        } else if ($_POST["newPassword"] === $_POST["currPassword"]) { // Check if the old and new passwords match
            $errors[] = "New password cannot be the same as the current password.";
        } else if ($_POST["newPassword"] !== $_POST["confNewPassword"]) { // Check if the passwords match
            $errors[] = "New passwords do not match.";
        } else { // All above validations passed

            // Get correct password from database
            $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "s", $_SESSION["user_id"]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $dbPassword = mysqli_fetch_assoc($result)["password"];

            // Check if provided password and actual password match
            if ($_POST["currPassword"] !== $dbPassword) {
                $errors[] = "Your password is incorrect.";
            }
        }
        // <-------- Validation End -------->

        // <-------- Query the Database -------->

        if (count($errors) === 0) {
            // Proceed to updating password
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, remember_token = NULL WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $_POST["newPassword"], $_SESSION["user_id"]); // Update password and reset cookie
            mysqli_stmt_execute($stmt);

            setcookie("remember_me", "", time()- 3600, "/");
            $success = "Password changed successfully."; // TODO: Implement displaying success message

            // Redirect user to dashboard
            redirectUser($_SESSION["role"]);
        }
    }
}

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
            <!-- TODO: Add logic for correct nav bar labels + link based on role -->
            <a href="#" class="navLink">Dashboard</a>
            <a href="#" class="navLink">Browse</a>
            <a href="#" class="navLink">Cart</a>
            <a href="#" class="navLink">My Orders</a>
            <a href="#" class="navLink navLinkActive">Profile</a>
            <a href="#" class="navLink">Logout</a>
        </div>

        <!-- TODO: Dynamic name + role display -->
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
                <button type="submit" name="submitBtn" class="btn" id="submitBtn">Change Password</button>
                <button type="submit" name=submitBtn class="btn" id="cancelBtn">Cancel</button>
            </div>
        </form>
    </div>

</body>

</html>