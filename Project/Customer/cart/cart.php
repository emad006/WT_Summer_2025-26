<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$errors = [];
$success = [];

// Get restaurant name
$stmt = mysqli_prepare($conn, "SELECT shop_name FROM restaurants WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["cart_restaurant_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$restaurantName = mysqli_fetch_assoc($result)["shop_name"];
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>Customer - My Cart</title>
</head>

<body>
    <div id="navbar">
        <div id="navLeft">
            <a href="../dashboard/dashboard.php" class="navLink">Dashboard</a>
            <a href="../browseRestaurant/browseRestaurant.php" class="navLink">Browse</a>
            <a href="cart.php" class="navLink navLinkActive">Cart</a>
            <a href="../orders/orders.php" class="navLink">My Orders</a>
            <a href="../profile/profile.php" class="navLink">Profile</a>
            <a href="../../Common/logout.php" class="navLink">Logout</a>
        </div>

        <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
    </div>


    <div id="mainArea">
        <h1 class="titleName">My Cart</h1>

        <div id="successBlock"><?php if (!empty($success)) echo implode("<br>", $success); ?></div>
        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors); ?></div>

        <?php if (count($_SESSION["cart"]) === 0) { ?>
            <div id="statusBlock">Your cart is empty.</div>

            <div class="inputBlock">
                <a href="../browseRestaurant/browseRestaurant.php" id="browseBtn">Browse Restaurants</a>
            </div>
        <?php } else { ?>

        <div>
            <label id="cartInfo"><?php echo $restaurantName . " · A cart can hold items from one restaurant only"; ?></label>
        </div>

        <div id="tableBlock">
            <table border="1">
                <tr>
                    <th>Item</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </table>
        </div>

        <h1 class="titleName">Delivery Details</h1>

        <form method="post">
            <div class="inputBlock">
                <label class="inputLabel">Delivery Address</label>
                <br>
                <textarea name="addr" class="inputField textAreaField" placeholder="Enter your delivery address"></textarea>
            </div>

            <div class="inputBlock">
                <label class="inputLabel">Phone</label>
                <br>
                <input type="text" name="phone" class="inputField" value="" placeholder="Enter your phone">
            </div>

            <div class="inputBlock">
                <label class="inputLabel">Payment Method</label>
                <br>
                <input type="radio" name="payment" class="radioField" value="cash" checked>
                <label class="radioText">Cash on Delivery</label>
            </div>

            <div class="inputBlock">
                <button type="submit" id="placeOrderBtn" name="placeOrderBtn">Place Order</button>
                <a href="../browseRestaurant/browseRestaurant.php" id="continueShoppingLink">Continue Shopping</a>
            </div>
        </form>

        <?php } ?>
    </div>
</body>

</html>