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

$mainErrors = [];
$cancelOrderErrors = [];
$totalBill = 0;

// Get order status and data for "progress" table
$stmt = mysqli_prepare($conn, "SELECT  order_status, cancelled_by, cancel_reason, placed_at, accepted_at, ready_at, picked_up_at, closed_at FROM orders WHERE customer_id = ? AND order_id = ?");
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

// Get delivery address, rider name and phone number
$stmt = mysqli_prepare($conn, "SELECT  o.delivery_address, u.name AS rider_name, u.phone AS rider_phone FROM orders o LEFT JOIN users u ON o.rider_id = u.user_id WHERE o.order_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_GET["order_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$riderDetails = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["cancelOrderBtn"])) {

        // Validate cancel reason
        if (empty($_POST["cancelReason"])) {
            $cancelOrderErrors[] = "Please specify a reason for cancelling this order.";
        } else if (strlen($_POST["cancelReason"]) < 10) {
            $cancelOrderErrors[] = "Cancellation reason must be at least 10 characters long.";
        } else if (strlen($_POST["cancelReason"]) > 120) {
            $cancelOrderErrors[] = "Cancellation reason cannot exceed 120 characters long.";
        }

        if (count($cancelOrderErrors) === 0) {
            $stmt = mysqli_prepare($conn, "UPDATE orders SET order_status = 'cancelled', cancelled_by = 'customer', cancel_reason = ?, closed_at = NOW() WHERE order_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $_POST["cancelReason"], $_GET["order_id"]);
            mysqli_stmt_execute($stmt);
            header("Location:orderDetails.php?order_id=" . $_GET["order_id"]);
            exit();
        }
    }
}
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
                $mainErrors[] = "This order was cancelled by the <b>" . ucfirst($progressTableData["cancelled_by"]) . "</b>.";
                $mainErrors[] = "Reason: <b>" . $progressTableData["cancel_reason"] . "</b>"; 
            }
            ?>

            <label class="orderPlaceTime">Placed at <?php echo "<label class='orderPlaceTime' style='font-weight: bold;'>" . $progressTableData["placed_at"] . "</label>"; ?></label>
        </div>

        <div id="tableBlock">
            <div id="errorBlock"><?php if (!empty($mainErrors)) echo implode("<br>", $mainErrors); ?></div>

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

                <?php if ($progressTableData["order_status"] !== "cancelled") { ?>
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
                <?php } ?>

                <tr>
                    <td><label class="tableLabel"><?php echo $progressTableData['order_status'] !== 'cancelled' ? "Delivered" : "Cancelled"; ?></label></td>
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
            <label class="labelText">Deliver To: <?php echo $riderDetails['delivery_address']; ?></label>
        </div>

        <div>
            <label class="labelText">Rider:</label>
            <label class="labelText">
                <?php
                if (!empty($riderDetails["rider_name"])) {
                    echo $riderDetails["rider_name"] . " · " . $riderDetails["rider_phone"];
                } else {
                    echo "N/A";
                }
                ?>
            </label>
        </div>

        <div class="inputBlock">
            <a href="../orders/orders.php" id="backLink">Back to My Orders</a>
        </div>

        <hr>

        <?php if ($progressTableData["order_status"] === "pending") { ?>
        <div>
            <form method="post">
                <h1 class="subTitleName">Cancel Order</h1>
                <div id="errorBlock"><?php if (!empty($cancelOrderErrors)) echo implode("<br>", $cancelOrderErrors); ?></div>
                <div class="inputBlock">
                    <label class="inputLabel">Cancellation Reason</label>
                    <br>
                    <input type="text" name="cancelReason" class="inputField" value="<?php if (!empty($_POST['cancelReason'])) echo $_POST['cancelReason']; ?>" placeholder="Please mention the reason for cancelling this order">
                    <button type="submit" name="cancelOrderBtn" id="cancelOrderBtn">Cancel Order</button>
                </div>
            </form>
        </div>
        <?php } ?>
    </div>
</body>

</html>