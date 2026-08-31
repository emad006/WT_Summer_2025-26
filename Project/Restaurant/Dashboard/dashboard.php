
<?php

session_start();

include "../../Common/lib/dbConfig.php";

// Check if restaurant is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "restaurant") {
    header("Location: ../../Common/login/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];



// GET RESTAURANT INFORMATION


$sql = "SELECT shop_name, is_open
        FROM restaurants
        WHERE user_id = $user_id";

$result = mysqli_query($conn, $sql);

$restaurant = mysqli_fetch_assoc($result);

$shop_name = $restaurant["shop_name"] ?? "Restaurant";
$is_open = $restaurant["is_open"] ?? 0;



// GET ORDER COUNTS


$sql = "SELECT order_status, COUNT(*) AS total
        FROM orders
        WHERE restaurant_id = $user_id
        AND order_status IN ('pending', 'preparing', 'ready')
        GROUP BY order_status";

$result = mysqli_query($conn, $sql);

// Start all counts at 0
$new_orders = 0;
$preparing = 0;
$ready = 0;

// Put database counts into variables
while ($row = mysqli_fetch_assoc($result)) {

    if ($row["order_status"] == "pending") {
        $new_orders = $row["total"];
    }

    if ($row["order_status"] == "preparing") {
        $preparing = $row["total"];
    }

    if ($row["order_status"] == "ready" ) {
        $ready = $row["total"];
    }

    
}



// GET TODAY'S DELIVERED ORDERS
// AND TODAY'S SALES


$sql = "SELECT

        COUNT(
            CASE
                WHEN order_status = 'delivered'
                AND DATE(closed_at) = CURDATE()
                THEN 1
            END
        ) AS delivered_today,

        COALESCE(
            SUM(
                CASE
                    WHEN DATE(placed_at) = CURDATE()
                    AND order_status != 'cancelled'
                    THEN total
                    ELSE 0
                END
            ),
            0
        ) AS sales_today

        FROM orders

        WHERE restaurant_id = $user_id";

$result = mysqli_query($conn, $sql);

$today = mysqli_fetch_assoc($result);

$delivered_today = $today["delivered_today"];
$sales_today = $today["sales_today"];

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Dashboard - FoodRush</title>

    <link rel="stylesheet" href="../../Restaurant/Dashboard/style.css">

</head>


<body>


<!-- NAVIGATION -->

<div class="bar">

    <a href="../Dashboard/dashboard.php" class="on">
        Dashboard
    </a>

    <a href="../Menu/r_menu.php">
        Manage Menu
    </a>

    <a href="../Order/r_orders.php">
        Order Queue
    </a>

    <a href="../../Common/changePassword/changePassword.php">
        Profile
    </a>

    <a href="../logout.php">
        Logout
    </a>

    <span>
        <?= htmlspecialchars($shop_name) ?> · Restaurant
    </span>

</div>


<div class="wrap">

    <h2>
        Welcome back, <?= htmlspecialchars($shop_name) ?>
    </h2>


    <!-- SHOP STATUS -->

    <div class="box2">

        <b>Shop status:</b>

        <?php if ($is_open): ?>

            <span class="tag t-green">
                Open
            </span>

            — customers can place orders now.

        <?php else: ?>

            <span class="tag t-red">
                Closed
            </span>

            — shop is currently closed.

        <?php endif; ?>

    </div>


    <!-- ORDER INFORMATION -->

    <table>

        <tr>

            <th>New orders</th>
            <th>Preparing</th>
            <th>Ready</th>
            <th>Delivered today</th>
            <th>Sales today</th>

        </tr>


        <tr>

            <td>
                <?= $new_orders ?>
            </td>

            <td>
                <?= $preparing ?>
            </td>

            <td>
                <?= $ready ?>
            </td>

            <td>
                <?= $delivered_today ?>
            </td>

            <td>
                Tk <?= number_format($sales_today, 0) ?>
            </td>

        </tr>

    </table>


    <!-- ORDER QUEUE BUTTON -->

    <p>

        <a href="../Order/r_orders.php">

            <button class="inline" type="button">
                Go to order queue
            </button>

        </a>

    </p>


</div>

</body>

</html>

