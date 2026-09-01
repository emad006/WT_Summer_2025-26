<?php
session_start();

include "../Common/lib/dbConfig.php";
include "../Common/lib/helperFunctions.php";

requireRole("rider");

$riderId = (int)$_SESSION["user_id"];

$reasonsBeforePickup = [
    "Restaurant not ready",
    "Order not available at pickup",
    "Vehicle breakdown",
    "Other"
];

$reasonsAfterPickup = [
    "Customer not reachable",
    "Wrong address",
    "Customer refused the order",
    "Other"
];

$stmt = mysqli_prepare($conn,
    "SELECT o.order_id, o.total, o.order_status,
            o.delivery_address, o.delivery_phone, o.picked_up_at,
            r.shop_name,
            ru.address AS pickup_address,
            ru.phone   AS pickup_phone,
            cu.name    AS customer_name
       FROM orders o
       JOIN restaurants r  ON r.user_id  = o.restaurant_id
       JOIN users       ru ON ru.user_id = r.user_id
       JOIN users       cu ON cu.user_id = o.customer_id
      WHERE o.rider_id = ?
        AND o.order_status IN ('ready','on_the_way')
      LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($_SERVER["REQUEST_METHOD"] === "POST" && $order) {

    $orderId = $order["order_id"];

    /* ---------- Mark as picked up :  ready -> on_the_way ---------- */
    if (isset($_POST["pickupBtn"])) {

        $stmt = mysqli_prepare($conn,
            "UPDATE orders SET order_status = 'on_the_way', picked_up_at = NOW()
              WHERE order_id = ? AND rider_id = ? AND order_status = 'ready'");
        mysqli_stmt_bind_param($stmt, "ii", $orderId, $riderId);
        mysqli_stmt_execute($stmt);

        header("Location:d_active.php");
        exit();
    }

    /* ---------- Mark as delivered :  on_the_way -> delivered ----------*/
    if (isset($_POST["deliverBtn"])) {

        $stmt = mysqli_prepare($conn,
            "UPDATE orders SET order_status = 'delivered', closed_at = NOW()
              WHERE order_id = ? AND rider_id = ? AND order_status = 'on_the_way'");
        mysqli_stmt_bind_param($stmt, "ii", $orderId, $riderId);
        mysqli_stmt_execute($stmt);

        header("Location:d_history.php");
        exit();
    }

    /* ---------- Report a failed delivery :  -> cancelled ---------- */
    if (isset($_POST["reportBtn"])) {

        // Which list of reasons is legal right now?
        if ($order["order_status"] === "ready") {
            $allowedReasons = $reasonsBeforePickup;
        } else {
            $allowedReasons = $reasonsAfterPickup;
        }

        $reason = isset($_POST["reason"]) ? $_POST["reason"] : "";

        // cancel_reason goes straight to the admin's screen, so we only
        // accept a value that is actually in our own list.
        if (!in_array($reason, $allowedReasons, true)) {
            header("Location:d_active.php?err=reason");
            exit();
        }

        $stmt = mysqli_prepare($conn,
            "UPDATE orders SET order_status  = 'cancelled',
                               cancelled_by  = 'rider',
                               cancel_reason = ?,
                               closed_at     = NOW()
              WHERE order_id = ? AND rider_id = ?
                AND order_status IN ('ready','on_the_way')");
        mysqli_stmt_bind_param($stmt, "sii", $reason, $orderId, $riderId);
        mysqli_stmt_execute($stmt);

        header("Location:d_history.php");
        exit();
    }
}

if (!$order) {
    $state = 3;                                    // nothing assigned
} else if ($order["order_status"] === "ready") {
    $state = 1;                                    // accepted, not collected
} else {
    $state = 2;                                    // on the way
}

$items = [];
if ($state === 1) {
    $stmt = mysqli_prepare($conn,
        "SELECT item_name, quantity FROM order_items WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $order["order_id"]);
    mysqli_stmt_execute($stmt);
    $itemResult = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($itemResult)) {
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>FoodRush - Active Delivery</title>
</head>

<body>
    <?php renderNavbar("active"); ?>

    <div id="mainArea">

        <?php // ============== STATE 3 : nothing assigned ==============
              // Do NOT show an error or a blank page. A rider who just
              // finished a delivery lands here, and that is normal. ?>
        <?php if ($state === 3) { ?>

            <h1 id="titleName">Active Delivery</h1>

            <div class="msgBox">
                You have no active delivery right now.
            </div>

            <div class="inputBlock">
                <form method="get" action="d_available.php">
                    <button type="submit" class="btn primaryBtn">View available deliveries</button>
                </form>
            </div>

        <?php } else { ?>

            <h1 id="titleName">Active Delivery &mdash; #<?php echo (int)$order["order_id"]; ?></h1>

            <?php if (isset($_GET["err"]) && $_GET["err"] === "reason") { ?>
                <div class="msgBox msgError">
                    Please choose a valid reason from the list.
                </div>
            <?php } ?>

            <?php // ============ STATE 1 : accepted, not collected ============ ?>
            <?php if ($state === 1) { ?>

                <div class="infoBox">
                    <span class="tag tagOrange">Accepted</span>
                    &mdash; collect the food from the restaurant next.
                </div>

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
                        <th>Cash to collect</th>
                        <td><b>Tk <?php echo number_format($order["total"]); ?></b></td>
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
                        <tr><td colspan="2"><small>No items recorded for this order.</small></td></tr>
                    <?php } ?>
                </table>

                <h3 class="sectionTitle">Update</h3>
                <div class="inputBlock">
                    <form method="post">
                        <button type="submit" name="pickupBtn" class="btn primaryBtn">
                            Mark as picked up
                        </button>
                    </form>
                </div>

            <?php } else { ?>

                <?php // ============ STATE 2 : on the way ============
                      // The pickup button is GONE, not disabled. The items
                      // table is dropped. Drop-off moves to the top. ?>

                <div class="infoBox">
                    <span class="tag tagOrange">On the way</span>
                    <?php if ($order["picked_up_at"]) { ?>
                        &mdash; picked up <?php echo date("g:i A", strtotime($order["picked_up_at"])); ?>.
                    <?php } ?>
                    Deliver to the customer.
                </div>

                <table class="dataTable">
                    <tr>
                        <th style="width:170px">Deliver to</th>
                        <td>
                            <b><?php echo htmlspecialchars($order["customer_name"]); ?></b><br>
                            <?php echo htmlspecialchars($order["delivery_address"]); ?><br>
                            <?php echo htmlspecialchars($order["delivery_phone"]); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Picked up from</th>
                        <td>
                            <?php echo htmlspecialchars($order["shop_name"]); ?>
                            &middot;
                            <?php echo htmlspecialchars($order["pickup_address"]); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Cash to collect</th>
                        <td><b>Tk <?php echo number_format($order["total"]); ?></b></td>
                    </tr>
                </table>

                <h3 class="sectionTitle">Complete the delivery</h3>
                <p class="noteText">
                    Collect <b>Tk <?php echo number_format($order["total"]); ?></b> in cash
                    from the customer, then confirm below.
                </p>
                <div class="inputBlock">
                    <form method="post">
                        <button type="submit" name="deliverBtn" class="btn primaryBtn">
                            Mark as delivered
                        </button>
                    </form>
                </div>

            <?php } ?>

            <?php // ---------- Report a problem : shown in both states,
                  //            but with a different reason list ---------- ?>
            <h3 class="sectionTitle">Report a problem</h3>
            <div class="inputBlock">
                <form method="post">
                    <label class="inputLabel">Reason</label><br>
                    <select name="reason" style="width:280px; height:24px;">
                        <?php
                        if ($state === 1) {
                            $reasonList = $reasonsBeforePickup;
                        } else {
                            $reasonList = $reasonsAfterPickup;
                        }
                        foreach ($reasonList as $r) {
                        ?>
                            <option value="<?php echo htmlspecialchars($r); ?>">
                                <?php echo htmlspecialchars($r); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <br>
                    <button type="submit" name="reportBtn" class="btn dangerBtn">
                        Report failed delivery
                    </button>
                </form>
            </div>

        <?php } ?>
    </div>

</body>

</html>
