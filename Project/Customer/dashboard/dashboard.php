<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

// Fetch active order counts
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS active_order_count FROM orders WHERE order_status NOT IN ('delivered', 'cancelled') AND customer_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$activeOrders = mysqli_fetch_assoc($result)["active_order_count"];

// Fetch orders placed this month
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS orders_this_month FROM orders WHERE placed_at >= DATE_FORMAT(NOW(), '%Y-%m-01') AND customer_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ordersPlacedThisMonth = mysqli_fetch_assoc($result)["orders_this_month"];

// Fetch total money spent
$stmt = mysqli_prepare($conn, "SELECT ROUND(COALESCE(SUM(total), 0), 0) AS total_spent FROM orders WHERE order_status = 'delivered' AND customer_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$totalSpent = mysqli_fetch_assoc($result)["total_spent"];

// Fetch active orders
$stmt = mysqli_prepare($conn, "SELECT o.order_id, r.shop_name, o.total, o.order_status FROM orders o JOIN restaurants r ON o.restaurant_id = r.user_id WHERE o.order_status NOT IN ('delivered', 'cancelled') AND o.customer_id = ? ORDER BY o.placed_at DESC");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$allActiveOrders = mysqli_stmt_get_result($stmt);
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
            <a href="dashboard.php" class="navLink navLinkActive">Dashboard</a>
            <a href="../browseRestaurant/browseRestaurant.php" class="navLink">Browse</a>
            <a href="../cart/cart.php" class="navLink">Cart</a>
            <a href="../orders/orders.php" class="navLink">My Orders</a>
            <a href="../profile/profile.php" class="navLink">Profile</a>
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

                <tr>
                    <td><?php echo $activeOrders; ?></td>
                    <td><?php echo $ordersPlacedThisMonth; ?></td>
                    <td><?php echo $totalSpent; ?></td>
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
                    <th>Action</th>
                </tr>
                
                <?php
                while ($row = mysqli_fetch_assoc($allActiveOrders)) {
                    echo "<tr>";

                    echo "<td>#" . $row["order_id"] . "</td>";
                    echo "<td>" . $row["shop_name"] . "</td>";
                    echo "<td>" . $row["total"] . "</td>";
                    echo "<td>" . ucfirst($row["order_status"]) . "</td>"; // TODO: Add coloring based on status
                    echo "<td><a class='trackOrderLink' href='../orderDetails/orderDetails.php?order_id=" . $row["order_id"] . "'>Track</a></td>";
                    
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </body>
</html>