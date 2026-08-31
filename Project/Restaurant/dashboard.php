<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Include configuration and helper functions
include "../Common/lib/dbConfig.php";
include "../Common/lib/helperFunctions.php";

// Access Control Guard
if (empty($_SESSION["user_id"]) || $_SESSION["role"] !== "restaurant") {
    header("Location: ../Common/login/login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];

// 1. Fetch Restaurant Info
$sql_rest = "SELECT shop_name, is_open FROM restaurants WHERE user_id = $user_id";
$res_rest = mysqli_query($conn, $sql_rest);
$rest = mysqli_fetch_assoc($res_rest);

$shop_name = $rest['shop_name'] ?? ($_SESSION['name'] ?? 'Restaurant');
$is_open   = (int)($rest['is_open'] ?? 0);

// 2. Fetch Live Queue Counts (New/Pending, Preparing, Ready)
$sql_counts = "
    SELECT order_status, COUNT(*) as cnt 
    FROM orders 
    WHERE restaurant_id = $user_id AND order_status IN ('pending', 'preparing', 'ready')
    GROUP BY order_status
";
$res_counts = mysqli_query($conn, $sql_counts);

$counts = ['pending' => 0, 'preparing' => 0, 'ready' => 0];
while ($row = mysqli_fetch_assoc($res_counts)) {
    $counts[$row['order_status']] = (int)$row['cnt'];
}

// 3. Fetch Delivered Today & Sales Today
$sql_today = "
    SELECT 
        COUNT(CASE WHEN order_status = 'delivered' AND DATE(closed_at) = CURDATE() THEN 1 END) AS delivered_today,
        COALESCE(SUM(CASE WHEN DATE(placed_at) = CURDATE() AND order_status != 'cancelled' THEN total ELSE 0 END), 0) AS sales_today
    FROM orders
    WHERE restaurant_id = $user_id
";
$res_today = mysqli_query($conn, $sql_today);
$today_stats = mysqli_fetch_assoc($res_today);

$new_orders      = $counts['pending'];
$preparing       = $counts['preparing'];
$ready           = $counts['ready'];
$delivered_today = (int)($today_stats['delivered_today'] ?? 0);
$sales_today     = (float)($today_stats['sales_today'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — FoodRush</title>
    <link rel="stylesheet" href="../Restaurant/style.css">
</head>
<body>
    <div class="bar">
        <a href="../Dashboard/dashboard.php" class="on">Dashboard</a>
        <a href="../Menu/r_menu.php">Manage Menu</a>
        <a href="../Order/r_orders.php">Order Queue</a>
        <a href="../Common/changePassword/changePassword.php">Profile</a>
        <a href="../Common/logout.php">Logout</a>
        <span><?= htmlspecialchars($shop_name) ?> &middot; Restaurant</span>
    </div>

    <div class="wrap">
        <h2>Welcome back, <?= htmlspecialchars($shop_name) ?></h2>

        <div class="box2">
            <b>Shop status:</b> 
            <?php if ($is_open): ?>
                <span class="tag t-green">Open</span> &nbsp; &mdash; customers can place orders now.
            <?php else: ?>
                <span class="tag t-red">Closed</span> &nbsp; &mdash; shop is currently closed.
            <?php endif; ?>
        </div>

        <table>
            <tr>
                <th>New orders</th>
                <th>Preparing</th>
                <th>Ready</th>
                <th>Delivered today</th>
                <th>Sales today</th>
            </tr>
            <tr>
                <td><?= $new_orders ?></td>
                <td><?= $preparing ?></td>
                <td><?= $ready ?></td>
                <td><?= $delivered_today ?></td>
                <td>Tk <?= number_format($sales_today, 0) ?></td>
            </tr>
        </table>

        <p>
            <a href="r_orders.php">
                <button class="inline" type="button">Go to order queue</button>
            </a>
        </p>
    </div>
</body>
</html>