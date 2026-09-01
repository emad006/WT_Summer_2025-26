<?php
session_start();

include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

$error_message = "";

$order_id = $_GET["order_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["action_btn"])) {

        if ($_POST["action_btn"] == "Force Cancel This Order") {
            $cancel_reason = $_POST["cancel_reason"];

            if (empty($cancel_reason)) {
                $error_message = "A reason is required to cancel this order.";
            } else {
                $sql = "UPDATE orders SET order_status = 'cancelled', cancelled_by = 'admin', cancel_reason = '$cancel_reason', closed_at = NOW() WHERE order_id = $order_id AND order_status IN ('pending', 'preparing', 'ready', 'on_the_way')";
                mysqli_query($conn, $sql);
                header("Location:order_details.php?order_id=$order_id");
                exit();
            }
        }
    }
}

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
            <a href="../cuisines/cuisines.php" class="navigation_link">Cuisines</a>
            <a href="orders.php" class="navigation_link active_link">Orders</a>
            <a href="../reviews/reviews.php" class="navigation_link">Reviews</a>
            <a href="../profile/profile.php" class="navigation_link">Profile</a>
            <a href="../logout.php" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>Order #<?php echo $order["order_id"]; ?></h2>

        <div id="back_box" class="box">
            <a href="orders.php" class="action_btn">Back To All Orders</a>
        </div>

        <div id="error_box" class="box"><?php if (!empty($error_message)) echo $error_message; ?></div>


        <div id="table_box_info" class="box">
            <table border="1">
                <tr>
                    <th>Customer</th>
                    <td><?php echo $order["customer_name"]; ?> · <?php echo $order["customer_phone"]; ?></td>
                </tr>

                <tr>
                    <th>Restaurant</th>
                    <td><?php echo $order["restaurant_name"]; ?> · <?php echo $order["restaurant_address"]; ?></td>
                </tr>

                <tr>
                    <th>Rider</th>
                    <td>
                        <?php
                        if (empty($order["rider_name"])) {
                            echo "Not assigned yet";
                        } else {
                            echo $order["rider_name"] . " · " . $order["rider_phone"];
                        }
                        ?>
                    </td>
                </tr>

                <tr>
                    <th>Deliver To</th>
                    <td><?php echo $order["delivery_address"]; ?> · <?php echo $order["delivery_phone"]; ?></td>
                </tr>

                <tr>
                    <th>Payment</th>
                    <td>
                        <?php
      
                        echo "Cash on delivery · Tk " . $order["total"];

                        if ($order["order_status"] == "delivered") {
                            echo " collected";
                        }
                        ?>
                    </td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        <?php
                        if ($order_status == "on_the_way") {
                            echo "On the way";
                        } else {
                            echo ucfirst($order_status);
                        }

                        if ($order["order_status"] == "cancelled") {
                            echo " by " . ucfirst($order["cancelled_by"]) . " · " . $order["cancel_reason"];
                        }
                        ?>
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
                    <td><?php echo $order["placed_at"]; ?></td>
                </tr>

                <tr>
                    <th>Order Accepted At</th>
                    <td><?php if (empty($order["accepted_at"])) echo "-"; else echo $order["accepted_at"]; ?></td>
                </tr>

                <tr>
                    <th>Order Ready At</th>
                    <td><?php if (empty($order["ready_at"])) echo "-"; else echo $order["ready_at"]; ?></td>
                </tr>

                <tr>
                    <th>Order Picked Up At</th>
                    <td><?php if (empty($order["picked_up_at"])) echo "-"; else echo $order["picked_up_at"]; ?></td>
                </tr>

                <tr>
                    <th>Order Closed At</th>
                    <td><?php if (empty($order["closed_at"])) echo "-"; else echo $order["closed_at"]; ?></td>
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

                <?php
                while ($row = mysqli_fetch_assoc($items_result)) {
                    $subtotal = $row["unit_price"] * $row["quantity"];

                    echo "<tr>";
                    echo "<td>" . $row["item_name"] . "</td>";
                    echo "<td>Tk " . $row["unit_price"] . "</td>";
                    echo "<td>" . $row["quantity"] . "</td>";
                    echo "<td>Tk " . $subtotal . "</td>";
                    echo "</tr>";
                }
                ?>

                <tr>
                    <th colspan="3">Delivery Fee</th>
                    <td>Tk <?php echo $order["delivery_fee"]; ?></td>
                </tr>

                <tr>
                    <th colspan="3">Total</th>
                    <td>Tk <?php echo $order["total"]; ?></td>
                </tr>
            </table>
        </div>

        <?php

        if ($order["order_status"] == "pending" || $order["order_status"] == "preparing" || $order["order_status"] == "ready" || $order["order_status"] == "on_the_way") {
            echo "<h3>Force Cancel</h3>";
            echo "<div id='cancel_box' class='box'>";
            echo "<form method='post'>";
            echo "Reason<br>";
            echo "<input type='text' name='cancel_reason' id='reason_input' placeholder='Why is this order being cancelled?'>";
            echo "<br>";
            echo "<input type='submit' id='cancel_btn' name='action_btn' value='Force Cancel This Order'>";
            echo "</form>";
            echo "</div>";
        }
        ?>
    </div>
</body>

</html>
