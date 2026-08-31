<?php
session_start();
include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

$order_id_filter = "";
$status_filter = "";
$date_from_filter = "";
$date_to_filter = "";

$where_clauses = [];

$total_orders = 0;
$revenue = 0;
$registered_users = 0;
$riders_on_duty = 0;

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

$sql = "SELECT o.order_id, o.total, o.placed_at, o.order_status, c.name AS customer_name, res.shop_name AS restaurant_name, r.name AS rider_name FROM orders o LEFT JOIN users c ON o.customer_id = c.user_id LEFT JOIN restaurants res ON o.restaurant_id = res.user_id LEFT JOIN users r ON o.rider_id = r.user_id ORDER BY o.placed_at DESC";
$orders_result = mysqli_query($conn, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["filter_btn"])) {

        if (!empty($_POST["order_id_filter"])) {
            $order_id_filter = $_POST["order_id_filter"];
            $where_clauses[] = "o.order_id = $order_id_filter";
        }

        if (!empty($_POST["status_filter"])) {
            $status_filter = $_POST["status_filter"];
            $where_clauses[] = "o.order_status = '$status_filter'";
        }

        if (!empty($_POST["date_from_filter"])) {
            $date_from_filter = $_POST["date_from_filter"];
            $where_clauses[] = "o.placed_at >= '$date_from_filter 00:00:00'";
        }

        if (!empty($_POST["date_to_filter"])) {
            $date_to_filter = $_POST["date_to_filter"];
            $where_clauses[] = "o.placed_at <= '$date_to_filter 23:59:59'";
        }

        if (count($where_clauses) > 0) {
            $conditions = implode(" AND ", $where_clauses);
            $sql = "SELECT o.order_id, o.total, o.placed_at, o.order_status, c.name AS customer_name, res.shop_name AS restaurant_name, r.name AS rider_name FROM orders o LEFT JOIN users c ON o.customer_id = c.user_id LEFT JOIN restaurants res ON o.restaurant_id = res.user_id LEFT JOIN users r ON o.rider_id = r.user_id";
            $sql .= " WHERE $conditions ORDER BY o.placed_at DESC";
            $orders_result = mysqli_query($conn, $sql);
        }
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Orders</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="../dashboard/dashboard.php" class="navigation_link">Dashboard</a>
            <a href="../users/users.php" class="navigation_link">Users</a>
            <a href="../cuisines/cuisines.php" class="navigation_link">Cuisines</a>
            <a href="orders.php" class="navigation_link active_link">Orders</a>
            <a href="../reviews/reviews.php" class="navigation_link">Reviews</a>
            <a href="../profile/profile.php" class="navigation_link">Profile</a>
            <a href="../logout.php" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>All Orders</h2>

        <div id="table_box_stats" class="box">
            <table border="1">
                <tr>
                    <th>Total Orders</th>
                    <th>Revenue</th>
                    <th>Registered Users</th>
                    <th>Riders On Duty</th>
                </tr>

                <tr>
                    <td><?php echo $total_orders; ?></td>
                    <td>Tk <?php echo $revenue; ?></td>
                    <td><?php echo $registered_users; ?></td>
                    <td><?php echo $riders_on_duty; ?></td>
                </tr>
            </table>
        </div>

        <div id="filter_box" class="box">
            <form method="post">
                <div id="order_id_filter" class="filter">
                    Order ID<br>
                    <input type="text" name="order_id_filter" class="filter_input" placeholder="e.g. 5">
                </div>

                <div id="status_filter" class="filter">
                    Status<br>
                    <select name="status_filter">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready</option>
                        <option value="on_the_way">On the way</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div id="date_from_filter" class="filter">
                    From<br>
                    <input type="date" name="date_from_filter">
                </div>

                <div id="date_to_filter" class="filter">
                    To<br>
                    <input type="date" name="date_to_filter">
                </div>

                <div id="filter_button" class="filter">
                    <br>
                    <button type="submit" id="filter_btn" name="filter_btn">Filter</button>
                </div>
            </form>
        </div>

        <div id="table_box_orders" class="box">
            <table border="1">
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Restaurant</th>
                    <th>Rider</th>
                    <th>Total</th>
                    <th>Placed At</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php
                while ($row = mysqli_fetch_assoc($orders_result)) {
                    echo "<tr>";
                    echo "<td>#" . $row["order_id"] . "</td>";
                    echo "<td>" . $row["customer_name"] . "</td>";
                    echo "<td>" . $row["restaurant_name"] . "</td>";

                    if (empty($row["rider_name"])) {
                        echo "<td>-</td>";
                    } else {
                        echo "<td>" . $row["rider_name"] . "</td>";
                    }

                    echo "<td>Tk " . $row["total"] . "</td>";
                    echo "<td>" . $row["placed_at"] . "</td>";

                    if($row["order_status"] == "pending"){
                        echo "<td> <label style ='color: darkOrange; font-weight: bold; '>Pending</label> </td>";
                    }
                    else if($row["order_status"] == "preparing"){
                        echo "<td> <label style = 'color: green;  font-weight: bold;'>Preparing</label> </td>";
                    }
                    else if($row["order_status"] == "on_the_way"){
                        echo "<td> <label style = 'color: firebrick;  font-weight: bold;'>On the way</label> </td>";
                    }
                    else if($row["order_status"] == "ready"){
                        echo "<td> <label style= 'color: blue;  font-weight: bold;'>Ready</label> </td>";
                    }
                    else if($row["order_status"] == "delivered"){
                        echo "<td> <label style = 'color: darkgreen; font-weight: bold;'>Delivered</label></td>";
                    }else if($row["order_status"] == "cancelled"){
                        echo "<td> <label style = 'color: red; font-weight: bold;'>Cancelled</label></td>";
                    }

                    echo "<td>";

                    echo "<form method='get' action='order_details.php' style='display:inline;'>";
                    echo "<input type='hidden' name='order_id' value='" . $row["order_id"] . "'>";
                    echo "<input type='submit' class='action_btn' value='View'>";
                    echo "</form>";

                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>
