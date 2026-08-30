<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

// Kick out user if order id doesn't exist
if (empty($_GET["order_id"])) {
    header("Location:../orders/orders.php");
    exit();
}

$errors = [];
$totalBill = 0;

// Get order status and data for "progress" table
$stmt = mysqli_prepare($conn, "SELECT  order_status, placed_at, accepted_at, ready_at, picked_up_at, closed_at FROM  orders WHERE customer_id = ? AND order_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $_SESSION["user_id"], $_GET["order_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) { // Kick out user cause it isn't their order
    header("Location:../orders/orders.php");
    exit();
}

$progressTableData = mysqli_fetch_assoc($result);

// Get all items of this order
$stmt = mysqli_prepare($conn, "SELECT item_name, unit_price, quantity FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_GET["order_id"]);
mysqli_stmt_execute($stmt);
$allItems = mysqli_stmt_get_result($stmt);
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
        <h1 class="titleName">Order #<?php echo $_GET["order_id"] ?></h1>

        <div>
            <?php
            if ($progressTableData["order_status"] === "pending") {
                echo "<td><label class='labelText' style='color: #F59E0B;'>" . ucfirst($progressTableData["order_status"]) . "</label></td>";
            } else if ($progressTableData["order_status"] === "preparing") {
                echo "<td><label class='labelText' style='color: #3B82F6;'>" . ucfirst($progressTableData["order_status"]) . "</label></td>";
            } else if ($progressTableData["order_status"] === "ready") {
                echo "<td><label class='labelText' style='color: #8B5CF6;'>" . ucfirst($progressTableData["order_status"]) . "</label></td>";
            } else if ($progressTableData["order_status"] === "on_the_way") {
                echo "<td><label class='labelText' style='color: #06B6D4;'>On The Way</label></td>";
            } else if ($progressTableData["order_status"] === "delivered") {
                echo "<td><label class='labelText' style='color: #10B981;'>" . ucfirst($progressTableData["order_status"]) . "</label></td>";
            } else if ($progressTableData["order_status"] === "cancelled") {
                echo "<td><label class='labelText' style='color: #EF4444;'>" . ucfirst($progressTableData["order_status"]) . "</label></td>";
            }
            ?>

            <label class="orderPlaceTime">Placed at <?php echo "<label class='orderPlaceTime' style='font-weight: bold;'>" . $progressTableData["placed_at"] . "</label>"; ?></label>
        </div>

        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors); ?></div>

        <div id="tableBlock">
            <label class="labelText">Progress</label>

            <table border="1">
                <tr>
                    <th>Stage</th>
                    <th>Date & Time</th>
                </tr>

                <tr>
                    <td><label class="tableLabel">Order Placed</label></td>
                    <td><label class="tableLabel"><?php echo empty($progressTableData["placed_at"]) ?  "" : $progressTableData["placed_at"]; ?></label></td>
                </tr>

                <?php if ($progressTableData["order_status"] !== "cancelled") {?>
                <tr>
                    <td><label class="tableLabel">Accepted & Preparing</label></td>
                    <td><label class="tableLabel"><?php echo empty($progressTableData["accepted_at"]) ?  "" : $progressTableData["accepted_at"]; ?></label></td>
                </tr>

                <tr>
                    <td><label class="tableLabel">Ready for Pickup</label></td>
                    <td><label class="tableLabel"><?php echo empty($progressTableData["ready_at"]) ?  "" : $progressTableData["ready_at"]; ?></label></td>
                </tr>

                <tr>
                    <td><label class="tableLabel">Out for Delivery</label></td>
                    <td><label class="tableLabel"><?php echo empty($progressTableData["picked_up_at"]) ?  "" : $progressTableData["picked_up_at"]; ?></label></td>
                </tr>
                <?php }?>

                <tr>
                    <td><label class="tableLabel"><?php echo $progressTableData['order_status'] !== 'cancelled' ? "Delivered" : "Cancelled";?></label></td>
                    <td><label class="tableLabel"><?php echo empty($progressTableData["closed_at"]) ?  "" : $progressTableData["closed_at"]; ?></label></td>
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

                <?php
                while ($row = mysqli_fetch_assoc($allItems)) {
                    $totalBill += $row["unit_price"] * $row["quantity"];
                    echo "<tr>";
                    echo "<td><label class='tableLabel'>" . $row["item_name"] . "</label></td>";
                    echo "<td><label class='tableLabel'>Tk " . $row["unit_price"] . "</label></td>";
                    echo "<td><label class='tableLabel'>" . $row["quantity"] . "</label></td>";
                    echo "<td><label class='tableLabel'>Tk " . $row["unit_price"] * $row["quantity"] . "</label></td>";
                    echo "</tr>";
                }
                ?>

                <tr>
                    <td colspan="3">Subtotal</td>
                    <td>Tk <?php echo $totalBill; ?></td>
                </tr>

                <tr>
                    <td colspan="3">Delivery Fee</td>
                    <td>Tk 60</td>
                </tr>

                <tr>
                    <td colspan="3">Total</td>
                    <td>Tk <?php echo $totalBill += 60; ?></td>
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