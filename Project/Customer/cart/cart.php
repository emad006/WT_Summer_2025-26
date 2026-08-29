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
$totalBill = 0;

$restaurantName = "";

if (!empty($_SESSION["cart"]) && !empty($_SESSION["cart_restaurant_id"])) {
    // Get restaurant name
    $stmt = mysqli_prepare($conn, "SELECT shop_name FROM restaurants WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["cart_restaurant_id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $restaurantName = mysqli_fetch_assoc($result)["shop_name"];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["updateQtyBtn"])) {

        // Validate quantity
        if (empty($_POST["qty"])) {
            $errors[] = "Please specify a quantity.";
        } else if (!is_numeric($_POST["qty"])) {
            $errors[] = "Quantity must be a number.";
        } else if ($_POST["qty"] < 1) {
            $errors[] = "Quantity must be at least 1.";
        } else if ($_POST["qty"] === $_SESSION["cart"][$_POST["item_id"]]) {
            $errors[] = "Quantity must be different from the current quantity.";
        }

        if (count($errors) === 0) {
            $_SESSION["cart"][$_POST["item_id"]] = $_POST["qty"];
            $success[] = "Quantity updated.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["item_id"])) {
        unset($_SESSION["cart"][$_GET["item_id"]]);
        $success[] = "Item removed.";

        if (empty($_SESSION["cart"])) {
            unset($_SESSION["cart_restaurant_id"]);
            $success[] = "Cart cleared.";
        }
    }
}

// Get customer information
$stmt = mysqli_prepare($conn, "SELECT address, phone FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userRow = mysqli_fetch_assoc($result);
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
                <a href="../browseRestaurant/browseRestaurant.php" id="browseLinkBtn">Browse Restaurants</a>
            </div>
        <?php } else { ?>

            <div>
                <label id="cartInfo"><?php if (!empty($restaurantName)) echo $restaurantName . " · A cart can hold items from one restaurant only"; ?></label>
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

                    <?php
                    foreach ($_SESSION["cart"] as $itemId => $itemQuantity) {

                        // Query the database
                        $stmt = mysqli_prepare($conn, "SELECT item_name, price FROM menu_items WHERE is_deleted = 0 AND item_id = ?");
                        mysqli_stmt_bind_param($stmt, "i", $itemId);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($result);

                        $totalBill += $row["price"] * $itemQuantity;

                        // Build the HTML table
                        echo "<tr>";
                        echo "<td><label class='tableLabel'>" . $row["item_name"] . "</label></td>";
                        echo "<td><label class='tableLabel'>Tk " . $row["price"] . "</label></td>";

                        echo "<td>";
                        echo "<form method='post'>";
                        echo "<input type='hidden' name='item_id' value='$itemId'>";
                        echo "<input type='text' name='qty' class='inputField qtyField' value='$itemQuantity'> ";
                        echo "<button type='submit' name='updateQtyBtn' class='updateBtn'>Update</button>";
                        echo "</form>";
                        echo "</td>";

                        echo "<td><label class='tableLabel'>Tk " . $row["price"] * $itemQuantity . "</label></td>";
                        echo "<td><a class='removeItemLink' href='cart.php?item_id=" . $itemId . "'>Remove</a></td>";
                        echo "</tr>";
                    }
                    ?>

                    <tr>
                        <td colspan="3">Subtotal</td>
                        <td colspan="2">Tk <?php echo $totalBill; ?></td>
                    </tr>

                    <tr>
                        <td colspan="3">Delivery Fee</td>
                        <td colspan="2">Tk 60</td>
                    </tr>

                    <tr>
                        <td colspan="3">Total Payable</td>
                        <td colspan="2">Tk <?php echo $totalBill += 60; ?></td>
                    </tr>
                </table>
            </div>

            <h1 class="titleName">Delivery Details</h1>

            <form method="post">
                <div class="inputBlock">
                    <label class="inputLabel">Delivery Address</label>
                    <br>
                    <textarea name="addr" class="inputField textAreaField" placeholder="Enter your delivery address"><?php echo $userRow["address"]; ?></textarea>
                </div>

                <div class="inputBlock">
                    <label class="inputLabel">Phone</label>
                    <br>
                    <input type="text" name="phone" class="inputField" value="<?php echo $userRow["phone"]; ?>" placeholder="Enter your phone">
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