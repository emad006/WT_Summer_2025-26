<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

$errors = [];
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>Customer - Order Details</title>
</head>

<body>
    <div id="navbar">
        <div id="navLeft">
            <a href="../dashboard/dashboard.php" class="navLink">Dashboard</a>
            <a href="../browseRestaurant/browseRestaurant.php" class="navLink">Browse</a>
            <a href="../cart/cart.php" class="navLink">Cart</a>
            <a href="../orders/orders.php" class="navLink navLinkActive">My Orders</a>
            <a href="../profile/profile.php" class="navLink">Profile</a>
            <a href="../../Common/logout.php" class="navLink">Logout</a>
        </div>

        <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
    </div>


    <div id="mainArea">
        <h1 class="titleName">Order Number</h1>

        <div>
            <label class="labelText">Pending</label>
            <label id="orderPlaceTime">Placed At</label>
        </div>

        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors); ?></div>

        <div id="tableBlock">
            <label class="labelText">Progress</label>

            <table border="1">
                <tr>
                    <th>Stage</th>
                    <th>Time</th>
                </tr>

            </table>
        </div>

        <div id="tableBlock">
            <label class="labelText">Items</label>

            <table border="1">
                <tr>
                    <th>Item</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </table>
        </div>

        <div>
            <label class="labelText">Deliver To</label>
        </div>

        <div>
            <label class="labelText">Rider</label>
        </div>
    </div>
</body>

</html>