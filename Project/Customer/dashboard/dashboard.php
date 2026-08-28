<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/login/login.php");
    exit();
}

$_SESSION["name"] = "Emad";
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

        <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
        </div>



        <div id="mainArea">
        <h1 id="titleName">Welcome <?php echo $_SESSION["name"]; ?></h1>

        <div class="tableBlock">
            <table border="1">
                <tr>
                    <th>Active Orders</th>
                    <th>Orders this Month</th>
                    <th>Total Spent</th>
                </tr>
            </table>
        </div>

        <div class="tableBlock">
            <label id="activeOrdersLabel">Your Active Orders</label>
            <table border="1">
                <tr>
                    <th>Order</th>
                    <th>Restaurant</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </table>
        </div>
    </body>
</html>