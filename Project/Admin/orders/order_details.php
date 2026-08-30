<?php
session_start();

include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

$error_message = "";

$order_id = $_GET["order_id"];

$sql = "SELECT o.*, c.name AS customer_name, c.phone AS customer_phone, res.shop_name AS restaurant_name, ru.address AS restaurant_address, r.name AS rider_name, r.phone AS rider_phone FROM orders o LEFT JOIN users c ON o.customer_id = c.user_id LEFT JOIN restaurants res ON o.restaurant_id = res.user_id LEFT JOIN users ru ON o.restaurant_id = ru.user_id LEFT JOIN users r ON o.rider_id = r.user_id WHERE o.order_id = $order_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

$sql = "SELECT item_name, unit_price, quantity FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $sql);

$order_status = $order["order_status"];
?>


<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Order Details</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="../dashboard/dashboard.php" class="navigation_link">Dashboard</a>
            <a href="../users/users.php" class="navigation_link">Users</a>
            <a href="../cusines/cusines.php" class="navigation_link">Cusines</a>
            <a href="orders.php" class="navigation_link active_link">Orders</a>
            <a href="../reviews/reviews.php" class="navigation_link">Reviews</a>
            <a href="../profile/profile.php" class="navigation_link">Profile</a>
            <a href="../logout.php" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"> · Admin</div>
    </div>

    <div id="main_box">
        <h2>Order #</h2>

        <div id="back_box" class="box">
            <a href="orders.php" class="action_btn">Back To All Orders</a>
        </div>

        <div id="error_box" class="box"></div>


        <div id="table_box_info" class="box">
            <table border="1">
                <tr>
                    <th>Customer</th>
                    <td></td>
                </tr>

                <tr>
                    <th>Restaurant</th>
                    <td></td>
                </tr>

                <tr>
                    <th>Rider</th>
                    <td>
                        
                    </td>
                </tr>

                <tr>
                    <th>Deliver To</th>
                    <td></td>
                </tr>

                <tr>
                    <th>Payment</th>
                    <td>
                        
                    </td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                       
                    </td>
                </tr>
            </table>
        </div>

        <h3>Progress</h3>

        <div id="table_box_progress" class="box">
            <table border="1">
                <tr>
                    <th>Step</th>
                    <th>Time</th>
                </tr>

                <tr>
                    <th>Order Placed At</th>
                    <td></td>
                </tr>

                <tr>
                    <th>Order Accepted At</th>
                    <td></td>
                </tr>

                <tr>
                    <th>Order Ready At</th>
                    <td></td>
                </tr>

                <tr>
                    <th>Order Picked Up At</th>
                    <td></td>
                </tr>

                <tr>
                    <th>Order Closed At</th>
                    <td></td>
                </tr>
            </table>
        </div>

        <h3>Items</h3>

        <div id="table_box_items" class="box">
            <table border="1">
                <tr>
                    <th>Item</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>

                <tr>
                    <th colspan="3">Delivery Fee</th>
                    <td>Tk </td>
                </tr>

                <tr>
                    <th colspan="3">Total</th>
                    <td>Tk </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
