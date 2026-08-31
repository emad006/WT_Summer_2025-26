<?php
session_start();

// Handle confirmed logout (UseLocationr clicked "Yes")
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm_logout"])) {
    // 1. Clear session variables
    session_unset();

    // 2. Destroy session data on the server
    session_destroy();

    // 3. Delete the session cookie from the browser
    setcookie(session_name(), '', time() - 3600, '/');

    // 4. Redirect to login
    header("Location:../Common/login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout Confirmation — FoodRush</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrap narrow" style="margin-top: 60px; text-align: center;">
        <h2>Log out</h2>
        <p style="margin: 16px 0;">Are you sure you want to log out?</p>

        <div style="display: flex; justify-content: center; gap: 12px; margin-top: 18px;">
            <!-- YES: Clears session and redirects to login -->
            <form method="POST" action="logout.php" style="display: inline; margin: 0;">
                <input type="hidden" name="confirm_logout" value="1">
                <button type="submit" class="dan" style="margin: 0; padding: 6px 22px;">Yes</button>
            </form>

            <!-- NO: Returns straight to Restaurant Dashboard -->
            <a href="../Restaurant/Dashboard/dashboard.php">
                <button type="button" class="sec" style="margin: 0; padding: 6px 22px;">No</button>
            </a>
        </div>
    </div>
</body>
</html>