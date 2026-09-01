<?php

session_start();

include __DIR__ . "/../Common/lib/dbConfig.php";
include __DIR__ . "/lib/riderLib.php";

requireRider();

$riderId = (int)$_SESSION["user_id"];

if (isset($_GET["order_id"])) {
    $orderId = (int)$_GET["order_id"];
} else if (isset($_POST["order_id"])) {
    $orderId = (int)$_POST["order_id"];
} else {
    $orderId = 0;
}

if ($orderId <= 0) {
    header("Location:d_available.php");
    exit();
}

$stmt = mysqli_prepare($conn,
    "SELECT order_id FROM orders
      WHERE rider_id = ? AND order_status IN ('ready','on_the_way')
      LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$activeOrder = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare($conn, "SELECT is_on_duty FROM riders WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$dutyRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$isOnDuty = $dutyRow ? (int)$dutyRow["is_on_duty"] : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acceptBtn"])) {
    if (!$isOnDuty) {
        header("Location:d_offer.php?order_id=" . $orderId . "&err=offduty");
        exit();
    }

    if ($activeOrder) {
        header("Location:d_available.php?err=busy");
        exit();
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE orders SET rider_id = ?
          WHERE order_id = ?
            AND rider_id IS NULL
            AND order_status = 'ready'");
    mysqli_stmt_bind_param($stmt, "ii", $riderId, $orderId);
    mysqli_stmt_execute($stmt);

    $rowsChanged = (int)mysqli_stmt_affected_rows($stmt);

    if ($rowsChanged === 1) {
        header("Location:d_active.php");
        exit();
    } else {
        header("Location:d_offer.php?order_id=" . $orderId . "&err=taken");
        exit();
    }
}

$stmt = mysqli_prepare($conn,
    "SELECT o.order_id, o.total, o.order_status, o.rider_id,
            o.delivery_address, o.delivery_phone, o.ready_at,
            TIMESTAMPDIFF(MINUTE, o.ready_at, NOW()) AS waiting_min,
            r.shop_name,
            ru.address AS pickup_address,
            ru.phone   AS pickup_phone,
            cu.name    AS customer_name
       FROM orders o
       JOIN restaurants r  ON r.user_id  = o.restaurant_id
       JOIN users       ru ON ru.user_id = r.user_id
       JOIN users       cu ON cu.user_id = o.customer_id
      WHERE o.order_id = ?");
mysqli_stmt_bind_param($stmt, "i", $orderId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    header("Location:d_available.php");
    exit();
}

$items = [];
$stmt = mysqli_prepare($conn,
    "SELECT item_name, quantity FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, "i", $orderId);
mysqli_stmt_execute($stmt);
$itemResult = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($itemResult)) {
    $items[] = $row;
}

$isAvailable = ($order["order_status"] === "ready" && $order["rider_id"] === null);

$statusNames = [
    "pending"    => "Pending",
    "preparing"  => "Preparing",
    "ready"      => "Ready",
    "on_the_way" => "On the way",
    "delivered"  => "Delivered",
    "cancelled"  => "Cancelled"
];
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>FoodRush - Delivery #<?php echo (int)$order["order_id"]; ?></title>
</head>

<body>
    <?php renderRiderNavbar("available"); ?>

    <div id="mainArea">
        <h1 id="titleName">Delivery #<?php echo (int)$order["order_id"]; ?></h1>

        <?php if ($isAvailable) { ?>

            <?php if (isset($_GET["err"]) && $_GET["err"] === "offduty") { ?>
                <div class="msgBox msgError">
                    You are off duty. Go online before accepting a delivery.
                </div>
            <?php } else { ?>
                <div class="msgBox">
                    This delivery is unassigned. Accepting locks it to you.
                </div>
            <?php } ?>

            <table class="dataTable">
                <tr>
                    <th style="width:170px">Pick up from</th>
                    <td>
                        <b><?php echo htmlspecialchars($order["shop_name"]); ?></b><br>
                        <?php echo htmlspecialchars($order["pickup_address"]); ?><br>
                        <?php echo htmlspecialchars($order["pickup_phone"]); ?>
                    </td>
                </tr>
                <tr>
                    <th>Deliver to</th>
                    <td>
                        <b><?php echo htmlspecialchars($order["customer_name"]); ?></b><br>
                        <?php echo htmlspecialchars($order["delivery_address"]); ?><br>
                        <?php echo htmlspecialchars($order["delivery_phone"]); ?>
                    </td>
                </tr>
                <tr>
                    <th>Ready since</th>
                    <td>
                        <?php
                        if ($order["ready_at"]) {
                            echo date("g:i A", strtotime($order["ready_at"]));
                            echo " (" . (int)$order["waiting_min"] . " minutes ago)";
                        } else {
                            echo "&mdash;";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Cash to collect</th>
                    <td>
                        <b>Tk <?php echo number_format($order["total"]); ?></b>
                        &mdash; cash on delivery
                    </td>
                </tr>
            </table>

            <h3 class="sectionTitle">Items to carry</h3>
            <table class="dataTable">
                <tr>
                    <th>Item</th>
                    <th style="width:80px">Qty</th>
                </tr>
                <?php foreach ($items as $item) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item["item_name"]); ?></td>
                        <td><?php echo (int)$item["quantity"]; ?></td>
                    </tr>
                <?php } ?>
                <?php if (count($items) === 0) { ?>
                    <tr>
                        <td colspan="2"><small>No items recorded for this order.</small></td>
                    </tr>
                <?php } ?>
            </table>

            <div class="inputBlock">
                <form method="post" class="inlineForm">
                    <input type="hidden" name="order_id" value="<?php echo (int)$order["order_id"]; ?>">
                    <button type="submit" name="acceptBtn" class="btn primaryBtn"
                        <?php if (!$isOnDuty || $activeOrder) echo "disabled"; ?>>
                        Accept this delivery
                    </button>
                </form>

                <form method="get" action="d_available.php" class="inlineForm">
                    <button type="submit" class="btn secondaryBtn">Back to list</button>
                </form>
            </div>

            <?php if ($activeOrder) { ?>
                <p class="noteText">
                    You already have an active delivery
                    (#<?php echo (int)$activeOrder["order_id"]; ?>), so you cannot accept this one.
                </p>
            <?php } else if (!$isOnDuty) { ?>
                <p class="noteText">
                    You are off duty, so the Accept button is disabled.
                    <a href="d_available.php">Go online first</a>.
                </p>
            <?php } ?>

        <?php } else { ?>

            <div class="msgBox msgError">
                <b>Another rider accepted this delivery first.</b>
                It is no longer available.
            </div>

            <table class="dataTable">
                <tr>
                    <th style="width:170px">Pick up from</th>
                    <td>
                        <?php echo htmlspecialchars($order["shop_name"]); ?>
                        &middot;
                        <?php echo htmlspecialchars($order["pickup_address"]); ?>
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="tag tagOrange">
                            <?php
                            $statusKey = $order["order_status"];
                            echo e(isset($statusNames[$statusKey]) ? $statusNames[$statusKey] : $statusKey);
                            ?>
                        </span>
                        &mdash; assigned to another rider
                    </td>
                </tr>
            </table>

            <div class="inputBlock">
                <form method="get" action="d_available.php">
                    <button type="submit" class="btn secondaryBtn">Back to available list</button>
                </form>
            </div>

        <?php } ?>
    </div>

</body>

</html>
