<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title>Customer - Dashboard</title>
    </head>

    <body>
        <div id="navbar">
        <div id="navLeft">
            <a href="#" class="navLink">Dashboard</a>
            <a href="#" class="navLink">Browse</a>
            <a href="#" class="navLink">Cart</a>
            <a href="#" class="navLink">My Orders</a>
            <a href="#" class="navLink navLinkActive">Profile</a>
            <a href="../../Common/logout.php" class="navLink">Logout</a>
        </div>

        <!-- TODO: Dynamic name + role display -->
        <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
        </div>
    </body>
</html>