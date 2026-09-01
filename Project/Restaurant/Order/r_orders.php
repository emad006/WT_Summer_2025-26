
<?php

session_start();

include "../../Common/lib/dbConfig.php";


// Check restaurant login
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "restaurant") {
    header("Location: ../../Common/login/login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];


// Restaurant name
$sql = "SELECT shop_name FROM restaurants WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$restaurant = mysqli_fetch_assoc($result);

$shop_name = $restaurant["shop_name"] ?? "Restaurant";


// Current tab
$tab = $_GET["tab"] ?? "new";

if (!in_array($tab, ["new", "preparing", "ready", "completed"])) {
    $tab = "new";
}


// Order counts
$new_count = 0;
$preparing_count = 0;
$ready_count = 0;

$sql = "SELECT order_status, COUNT(*) AS total
        FROM orders
        WHERE restaurant_id = $user_id
        GROUP BY order_status";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {

    if ($row["order_status"] == "pending") {
        $new_count = $row["total"];
    }

    if ($row["order_status"] == "preparing") {
        $preparing_count = $row["total"];
    }

    if ($row["order_status"] == "ready" ||
        $row["order_status"] == "on_the_way") {
        $ready_count += $row["total"];
    }
}


// Today's sales
$sql = "SELECT COUNT(*) AS total_orders,
               COALESCE(SUM(total), 0) AS total_sales
        FROM orders
        WHERE restaurant_id = $user_id
        AND DATE(placed_at) = CURDATE()";

$result = mysqli_query($conn, $sql);
$today = mysqli_fetch_assoc($result);


// Get orders
if ($tab == "new") {

    $sql = "SELECT
                o.order_id,
                u.name AS customer_name,
                o.delivery_phone,
                o.total,
                o.placed_at,
                (SELECT COUNT(*)
                 FROM order_items
                 WHERE order_id = o.order_id) AS item_count
            FROM orders o
            JOIN users u ON o.customer_id = u.user_id
            WHERE o.restaurant_id = $user_id
            AND o.order_status = 'pending'
            ORDER BY o.placed_at ASC";

}
elseif ($tab == "preparing") {

    $sql = "SELECT
                o.order_id,
                u.name AS customer_name,
                o.total,
                o.accepted_at,
                (SELECT COUNT(*)
                 FROM order_items
                 WHERE order_id = o.order_id) AS item_count
            FROM orders o
            JOIN users u ON o.customer_id = u.user_id
            WHERE o.restaurant_id = $user_id
            AND o.order_status = 'preparing'
            ORDER BY o.accepted_at ASC";

}
elseif ($tab == "ready") {

    $sql = "SELECT
                o.order_id,
                u.name AS customer_name,
                o.total,
                o.ready_at,
                o.order_status,
                u2.name AS rider_name
            FROM orders o
            JOIN users u ON o.customer_id = u.user_id
            LEFT JOIN users u2 ON o.rider_id = u2.user_id
            WHERE o.restaurant_id = $user_id
            AND o.order_status IN ('ready', 'on_the_way')
            ORDER BY o.ready_at ASC";

}
else {

    $from = $_GET["from"] ?? "";
    $to = $_GET["to"] ?? "";

    $sql = "SELECT
                o.order_id,
                u.name AS customer_name,
                o.total,
                o.closed_at,
                o.order_status,
                u2.name AS rider_name
            FROM orders o
            JOIN users u ON o.customer_id = u.user_id
            LEFT JOIN users u2 ON o.rider_id = u2.user_id
            WHERE o.restaurant_id = $user_id
            AND o.order_status IN ('delivered', 'cancelled')";

    if ($from != "" && $to != "") {

        $from = mysqli_real_escape_string($conn, $from);
        $to = mysqli_real_escape_string($conn, $to);

        $sql .= " AND DATE(o.closed_at)
                  BETWEEN '$from' AND '$to'";
    }

    $sql .= " ORDER BY o.closed_at DESC";
}


$orders = mysqli_query($conn, $sql);


// If SQL fails, show the error instead of a blank page
if (!$orders) {
    die("Database error: " . mysqli_error($conn));
}

?>


<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Order Queue - FoodRush</title>

    <link rel="stylesheet" href="styleorder.css">

</head>


<body>


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

    <h2>Order Queue</h2>


    <!-- TABS -->

    <p class="tabs">

        <?php if ($tab == "new"): ?>

            <b>New (<?= $new_count ?>)</b>

        <?php else: ?>

            <a href="r_orders.php?tab=new">
                New (<?= $new_count ?>)
            </a>

        <?php endif; ?>


        <?php if ($tab == "preparing"): ?>

            <b>Preparing (<?= $preparing_count ?>)</b>

        <?php else: ?>

            <a href="r_orders.php?tab=preparing">
                Preparing (<?= $preparing_count ?>)
            </a>

        <?php endif; ?>


        <?php if ($tab == "ready"): ?>

            <b>Ready (<?= $ready_count ?>)</b>

        <?php else: ?>

            <a href="r_orders.php?tab=ready">
                Ready (<?= $ready_count ?>)
            </a>

        <?php endif; ?>


        <?php if ($tab == "completed"): ?>

            <b>Completed</b>

        <?php else: ?>

            <a href="r_orders.php?tab=completed">
                Completed
            </a>

        <?php endif; ?>

    </p>


    <!-- NEW ORDERS -->

    <?php if ($tab == "new"): ?>

        <table>

            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Items</th>
                <th>Total</th>
                <th>Placed</th>
                <th>Action</th>
            </tr>


            <?php if (mysqli_num_rows($orders) == 0): ?>

                <tr>
                    <td colspan="7">
                        No new orders.
                    </td>
                </tr>

            <?php else: ?>

                <?php while ($order = mysqli_fetch_assoc($orders)): ?>

                    <tr>

                        <td>
                            #<?= $order["order_id"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["customer_name"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["delivery_phone"]) ?>
                        </td>

                        <td>
                            <?= $order["item_count"] ?>
                        </td>

                        <td>
                            Tk <?= number_format($order["total"], 0) ?>
                        </td>

                        <td>
                            <?= date("g:i A", strtotime($order["placed_at"])) ?>
                        </td>

                        <td>
                            <a href="r_order.php?id=<?= $order["order_id"] ?>">
                                View
                            </a>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

        </table>


    <!-- PREPARING ORDERS -->

    <?php elseif ($tab == "preparing"): ?>

        <table>

            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Accepted</th>
                <th>Action</th>
            </tr>


            <?php if (mysqli_num_rows($orders) == 0): ?>

                <tr>
                    <td colspan="6">
                        No orders being prepared.
                    </td>
                </tr>

            <?php else: ?>

                <?php while ($order = mysqli_fetch_assoc($orders)): ?>

                    <tr>

                        <td>
                            #<?= $order["order_id"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["customer_name"]) ?>
                        </td>

                        <td>
                            <?= $order["item_count"] ?>
                        </td>

                        <td>
                            Tk <?= number_format($order["total"], 0) ?>
                        </td>

                        <td>
                            <?= date("g:i A", strtotime($order["accepted_at"])) ?>
                        </td>

                        <td>
                            <a href="r_order.php?id=<?= $order["order_id"] ?>">
                                View
                            </a>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

        </table>


    <!-- READY ORDERS -->

    <?php elseif ($tab == "ready"): ?>

        <div class="msg">
            These orders are ready for pickup.
        </div>

        <table>

            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Ready</th>
                <th>Rider</th>
                <th>Status</th>
                <th>Action</th>
            </tr>


            <?php if (mysqli_num_rows($orders) == 0): ?>

                <tr>
                    <td colspan="7">
                        No ready orders.
                    </td>
                </tr>

            <?php else: ?>

                <?php while ($order = mysqli_fetch_assoc($orders)): ?>

                    <tr>

                        <td>
                            #<?= $order["order_id"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["customer_name"]) ?>
                        </td>

                        <td>
                            Tk <?= number_format($order["total"], 0) ?>
                        </td>

                        <td>
                            <?= date("g:i A", strtotime($order["ready_at"])) ?>
                        </td>

                        <td>

                            <?php if ($order["rider_name"]): ?>

                                <?= htmlspecialchars($order["rider_name"]) ?>

                            <?php else: ?>

                                Not assigned

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if ($order["order_status"] == "ready"): ?>

                                <span class="tag t-orng">
                                    Ready
                                </span>

                            <?php else: ?>

                                <span class="tag t-orng">
                                    On the way
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <a href="r_order.php?id=<?= $order["order_id"] ?>">
                                View
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

        </table>


    <!-- COMPLETED ORDERS -->

    <?php else: ?>

        <div class="box2">

            <form method="GET">

                <input type="hidden"
                       name="tab"
                       value="completed">

                <label>From</label>

                <input type="date"
                       name="from"
                       value="<?= htmlspecialchars($from ?? "") ?>">

                <label>To</label>

                <input type="date"
                       name="to"
                       value="<?= htmlspecialchars($to ?? "") ?>">

                <button type="submit">
                    Filter
                </button>

            </form>

        </div>


        <table>

            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Rider</th>
                <th>Finished</th>
                <th>Result</th>
                <th>Action</th>
            </tr>


            <?php if (mysqli_num_rows($orders) == 0): ?>

                <tr>
                    <td colspan="7">
                        No completed orders.
                    </td>
                </tr>

            <?php else: ?>

                <?php while ($order = mysqli_fetch_assoc($orders)): ?>

                    <tr>

                        <td>
                            #<?= $order["order_id"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order["customer_name"]) ?>
                        </td>

                        <td>
                            Tk <?= number_format($order["total"], 0) ?>
                        </td>

                        <td>

                            <?php if ($order["rider_name"]): ?>

                                <?= htmlspecialchars($order["rider_name"]) ?>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= date(
                                "d M, g:i A",
                                strtotime($order["closed_at"])
                            ) ?>
                        </td>

                        <td>

                            <?php if ($order["order_status"] == "delivered"): ?>

                                <span class="tag t-green">
                                    Delivered
                                </span>

                            <?php else: ?>

                                <span class="tag t-red">
                                    Cancelled
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <a href="r_order.php?id=<?= $order["order_id"] ?>">
                                View
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

        </table>

    <?php endif; ?>


    <!-- TODAY'S SUMMARY -->

    <table>

        <tr>
            <th>Orders received today</th>
            <th>Sales today</th>
        </tr>

        <tr>

            <td>
                <?= $today["total_orders"] ?>
            </td>

            <td>
                Tk <?= number_format($today["total_sales"], 0) ?>
            </td>

        </tr>

    </table>


</div>

</body>

</html>
