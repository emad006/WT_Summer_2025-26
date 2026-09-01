
<?php

session_start();

include "../../Common/lib/dbConfig.php";


// Check restaurant login
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "restaurant") {
    header("Location: ../../Common/login/login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];
$order_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;


// Check order ID
if ($order_id <= 0) {
    header("Location: r_orders.php");
    exit();
}


// ----------------------------------------
// GET RESTAURANT NAME
// ----------------------------------------

$sql = "SELECT shop_name
        FROM restaurants
        WHERE user_id = $user_id";

$result = mysqli_query($conn, $sql);

$restaurant = mysqli_fetch_assoc($result);

$shop_name = $restaurant["shop_name"] ?? "Restaurant";


// ----------------------------------------
// ORDER ACTIONS
// ----------------------------------------

if (isset($_POST["action"])) {

    $action = $_POST["action"];


    // Accept order
    if ($action == "accept") {

        mysqli_query(
            $conn,
            "UPDATE orders
             SET order_status = 'preparing',
                 accepted_at = NOW()
             WHERE order_id = $order_id
             AND restaurant_id = $user_id
             AND order_status = 'pending'"
        );
    }


    // Reject order
    elseif ($action == "reject") {

        $reason = mysqli_real_escape_string(
            $conn,
            $_POST["cancel_reason"] ?? "Other"
        );

        mysqli_query(
            $conn,
            "UPDATE orders
             SET order_status = 'cancelled',
                 cancelled_by = 'restaurant',
                 cancel_reason = '$reason',
                 closed_at = NOW()
             WHERE order_id = $order_id
             AND restaurant_id = $user_id
             AND order_status = 'pending'"
        );
    }


    // Mark ready
    elseif ($action == "ready") {

        mysqli_query(
            $conn,
            "UPDATE orders
             SET order_status = 'ready',
                 ready_at = NOW()
             WHERE order_id = $order_id
             AND restaurant_id = $user_id
             AND order_status = 'preparing'"
        );
    }


    header("Location: r_order.php?id=$order_id");
    exit();
}


// ----------------------------------------
// GET ORDER
// ----------------------------------------

$sql = "SELECT
            o.*,
            customer.name AS customer_name,
            rider.name AS rider_name,
            rider.phone AS rider_phone,
            r.vehicle_type

        FROM orders o

        JOIN users customer
            ON o.customer_id = customer.user_id

        LEFT JOIN users rider
            ON o.rider_id = rider.user_id

        LEFT JOIN riders r
            ON o.rider_id = r.user_id

        WHERE o.order_id = $order_id
        AND o.restaurant_id = $user_id";

$result = mysqli_query($conn, $sql);

$order = mysqli_fetch_assoc($result);


// Order not found
if (!$order) {
    header("Location: r_orders.php");
    exit();
}


// ----------------------------------------
// GET ORDER ITEMS
// ----------------------------------------

$sql = "SELECT *
        FROM order_items
        WHERE order_id = $order_id";

$items = mysqli_query($conn, $sql);

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Order #<?= $order["order_id"] ?> - FoodRush
    </title>

    <link rel="stylesheet" href="../../Restaurant/Order/styleorder1.css">

</head>


<body>


<!-- NAVIGATION -->

<div class="bar">

    <a href="../Dashboard/dashboard.php">
        Dashboard
    </a>

    <a href="../Menu/r_menu.php">
        Manage Menu
    </a>

    <a href="r_orders.php" class="on">
        Order Queue
    </a>

    <a href="../../Common/changePassword/changePassword.php">
        Profile
    </a>

    <a href="../../Common/logout.php">
        Logout
    </a>

    <span>
        <?= htmlspecialchars($shop_name) ?> · Restaurant
    </span>

</div>


<div class="wrap">


    <h2>
        Order #<?= $order["order_id"] ?>
    </h2>


    <!-- ORDER STATUS -->

    <?php if ($order["order_status"] == "pending"): ?>

        <p>
            <span class="tag t-blue">
                Pending
            </span>
        </p>


    <?php elseif ($order["order_status"] == "preparing"): ?>

        <p>
            <span class="tag t-amber">
                Preparing
            </span>
        </p>


    <?php elseif ($order["order_status"] == "ready"): ?>

        <p>
            <span class="tag t-orng">
                Ready for pickup
            </span>
        </p>

        <?php if ($order["rider_id"]): ?>

            <div class="msg">
                Rider <?= htmlspecialchars($order["rider_name"]) ?>
                has accepted this delivery.
            </div>

        <?php else: ?>

            <div class="msg">
                Waiting for a rider.
            </div>

        <?php endif; ?>


    <?php elseif ($order["order_status"] == "on_the_way"): ?>

        <p>
            <span class="tag t-orng">
                On the way
            </span>
        </p>


    <?php elseif ($order["order_status"] == "delivered"): ?>

        <p>
            <span class="tag t-green">
                Delivered
            </span>
        </p>


    <?php elseif ($order["order_status"] == "cancelled"): ?>

        <p>
            <span class="tag t-red">
                Cancelled
            </span>
        </p>

        <div class="msg err">

            <b>Cancelled by:</b>

            <?= htmlspecialchars($order["cancelled_by"]) ?>

            <br>

            <b>Reason:</b>

            <?= htmlspecialchars(
                $order["cancel_reason"] ?? "Not specified"
            ) ?>

        </div>

    <?php endif; ?>


    <!-- CUSTOMER INFORMATION -->

    <table>

        <tr>

            <th>Customer</th>

            <td>
                <?= htmlspecialchars($order["customer_name"]) ?>

                ·

                <?= htmlspecialchars($order["delivery_phone"]) ?>
            </td>

        </tr>


        <tr>

            <th>Deliver to</th>

            <td>
                <?= htmlspecialchars($order["delivery_address"]) ?>
            </td>

        </tr>


        <tr>

            <th>Rider</th>

            <td>

                <?php if ($order["rider_id"]): ?>

                    <?= htmlspecialchars($order["rider_name"]) ?>

                    ·

                    <?= htmlspecialchars($order["rider_phone"]) ?>

                    <?php if ($order["vehicle_type"]): ?>

                        · <?= htmlspecialchars($order["vehicle_type"]) ?>

                    <?php endif; ?>

                <?php else: ?>

                    Not assigned yet

                <?php endif; ?>

            </td>

        </tr>


        <tr>

            <th>Payment</th>

            <td>
                Cash on delivery ·
                Tk <?= number_format($order["total"], 0) ?>
            </td>

        </tr>

    </table>


    <!-- ORDER ITEMS -->

    <h3>Items</h3>

    <table>

        <tr>

            <th>Item</th>
            <th>Unit price</th>
            <th>Quantity</th>
            <th>Subtotal</th>

        </tr>


        <?php while ($item = mysqli_fetch_assoc($items)): ?>

        <tr>

            <td>
                <?= htmlspecialchars($item["item_name"]) ?>
            </td>

            <td>
                Tk <?= number_format($item["unit_price"], 0) ?>
            </td>

            <td>
                <?= (int)$item["quantity"] ?>
            </td>

            <td>
                Tk <?= number_format(
                    $item["unit_price"] * $item["quantity"],
                    0
                ) ?>
            </td>

        </tr>

        <?php endwhile; ?>


        <tr>

            <td colspan="3">
                <b>Delivery fee</b>
            </td>

            <td>
                Tk <?= number_format($order["delivery_fee"], 0) ?>
            </td>

        </tr>


        <tr>

            <td colspan="3">
                <b>Total</b>
            </td>

            <td>

                <b>
                    Tk <?= number_format($order["total"], 0) ?>
                </b>

            </td>

        </tr>

    </table>


    <!-- RESTAURANT ACTIONS -->


    <?php if ($order["order_status"] == "pending"): ?>

        <hr>

        <h3>Accept Order</h3>

        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="accept"
            >

            <button type="submit">
                Accept & Start Preparing
            </button>

        </form>


        <hr>

        <h3>Reject Order</h3>

        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="reject"
            >

            <label>Reason</label>

            <select name="cancel_reason">

                <option value="Item out of stock">
                    Item out of stock
                </option>

                <option value="Kitchen closed">
                    Kitchen closed
                </option>

                <option value="Too busy right now">
                    Too busy right now
                </option>

                <option value="Delivery area not covered">
                    Delivery area not covered
                </option>

                <option value="Other">
                    Other
                </option>

            </select>

            <button type="submit" class="dan">
                Reject Order
            </button>

        </form>


    <?php elseif ($order["order_status"] == "preparing"): ?>

        <hr>

        <h3>Order Ready?</h3>

        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="ready"
            >

            <button type="submit">
                Mark Ready for Pickup
            </button>

        </form>

    <?php endif; ?>


</div>

</body>

</html>

