<?php
session_start();

include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

$total_orders = 0;
$revenue = 0;
$registered_users = 0;
$riders_on_duty = 0;
$open_restaurants = 0;

$sql = "SELECT COUNT(*) AS 'total_orders' FROM orders";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_orders = $row["total_orders"];


$sql = "SELECT SUM(total) AS 'revenue' FROM orders WHERE order_status = 'delivered'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$revenue = $row["revenue"];


$sql = "SELECT COUNT(*) AS 'registered_users' FROM users WHERE account_status <> 'deleted'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$registered_users = $row["registered_users"];


$sql = "SELECT COUNT(*) AS 'riders_on_duty' FROM riders WHERE is_on_duty = 1";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$riders_on_duty = $row["riders_on_duty"];


$sql = "SELECT COUNT(*) AS 'open_restaurants' FROM restaurants WHERE is_open = 1";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$open_restaurants = $row["open_restaurants"];


$sql = "SELECT o.order_id, o.total, o.order_status, res.shop_name AS restaurant_name FROM orders o LEFT JOIN restaurants res ON o.restaurant_id = res.user_id ORDER BY o.placed_at DESC LIMIT 5";
$latest_activity_result = mysqli_query($conn, $sql);
?>


<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Dashboard</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="dashboard.php" class="navigation_link active_link">Dashboard</a>
            <a href="../users/users.php" class="navigation_link">Users</a>
            <a href="#" class="navigation_link">Cusines</a>
            <a href="#" class="navigation_link">Orders</a>
            <a href="#" class="navigation_link">Reviews</a>
            <a href="#" class="navigation_link">Profile</a>
            <a href="#" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>Platform Overview</h2>


        <div id="table_box_stats" class="box">
            <table border="1">
                <tr>
                    <th>Total Orders</th>
                    <th>Revenue</th>
                    <th>Registered Users</th>
                    <th>Riders On Duty</th>
                    <th>Open Restaurants</th>
                </tr>

                <tr>
                    <td><?php echo $total_orders; ?></td>
                    <td>Tk <?php echo $revenue; ?></td>
                    <td><?php echo $registered_users; ?></td>
                    <td><?php echo $riders_on_duty; ?></td>
                    <td><?php echo $open_restaurants; ?></td>
                </tr>
            </table>
        </div>

        <h3>Latest Activity</h3>

        <div id="table_box_activity" class="box">
            <table border="1">
                <tr>
                    <th>Order</th>
                    <th>Restaurant</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>

                <?php
                while ($row = mysqli_fetch_assoc($latest_activity_result)) {
                    echo "<tr>";
                    echo "<td>#" . $row["order_id"] . "</td>";
                    echo "<td>" . $row["restaurant_name"] . "</td>";
                    echo "<td>Tk " . $row["total"] . "</td>";

                    $order_status = ucfirst(str_replace("_", " ", $row["order_status"]));
                    echo "<td>" . $order_status . "</td>";

                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>