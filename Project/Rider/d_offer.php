<?php
/* =====================================================================
   FoodRush — D2 · Delivery offer detail
   Save as:  Project/Rider/d_offer.php

   The rider clicked "View details" on one delivery. This page shows
   the whole job — where to collect, where to drop, how much cash, what
   items — and gives them the Accept button.

   This is the most important page in the module. Read section STEP 3
   carefully; that is the part your teacher will ask about.
   ===================================================================== */

session_start();

include "../Common/lib/dbConfig.php";
include "../Common/lib/helperFunctions.php";

/* ---------- role guard ---------- */
/* requireRole() replaces the hand-written check. The old one built a
   relative path to the login page that only worked from this folder. */
requireRole("rider");

$riderId = (int)$_SESSION["user_id"];


/* =====================================================================
   STEP 1 — Which order are we looking at?

   The order id arrives in the URL (?order_id=5) or in the Accept form.
   (int) turns anything strange into a number:
       ?order_id=abc        becomes 0
       ?order_id=5 OR 1=1   becomes 5
   We still bind it in every query as well.
   ===================================================================== */
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


/* =====================================================================
   STEP 2 — The two guard checks

   d_available.php already greyed out the link for these cases. But
   greying a link is only decoration — anyone can type the URL by hand.
   The real rule lives here, on the page that actually does the writing.
   ===================================================================== */

// Am I already carrying a delivery?
$stmt = mysqli_prepare($conn,
    "SELECT order_id FROM orders
      WHERE rider_id = ? AND order_status IN ('ready','on_the_way')
      LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$activeOrder = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Am I on duty?
$stmt = mysqli_prepare($conn, "SELECT is_on_duty FROM riders WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$dutyRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$isOnDuty = $dutyRow ? (int)$dutyRow["is_on_duty"] : 0;


/* =====================================================================
   STEP 3 — ACCEPT THE DELIVERY

   THE PROBLEM
   Two riders have this page open at the same time. Both click Accept
   in the same second. If we wrote it the obvious way:

       SELECT rider_id FROM orders WHERE order_id = 5   -> NULL, free!
       UPDATE orders SET rider_id = me WHERE order_id = 5

   ...then BOTH riders see NULL, BOTH run the UPDATE, and the second one
   quietly overwrites the first. One order, two riders, no error shown.

   THE FIX
   Put the "is it still free" test INSIDE the UPDATE statement, and then
   ask the database how many rows actually changed. One statement does
   the checking and the writing together, so nothing can slip in
   between them.

       affected_rows = 1  ->  I claimed it
       affected_rows = 0  ->  somebody else got there first
   ===================================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["acceptBtn"])) {

    // A rider who is off duty may not accept.
    if (!$isOnDuty) {
        header("Location:d_offer.php?order_id=" . $orderId . "&err=offduty");
        exit();
    }

    // A rider may only carry one delivery at a time.
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
        header("Location:d_active.php");                 // I won the race
        exit();
    } else {
        header("Location:d_offer.php?order_id=" . $orderId . "&err=taken");
        exit();                                          // somebody beat me
    }
}

/* ---------------------------------------------------------------------
   NOTE — Accept writes rider_id AND NOTHING ELSE.

   order_status stays 'ready'. This is on purpose:

   * It removes the order from every other rider's list, because
     d_available.php filters on "rider_id IS NULL".
   * It keeps the customer's tracking page honest. The food is still
     sitting on the restaurant counter. Saying "On the way" now would
     be a lie the customer can see.

   The status only moves to 'on_the_way' when the rider physically
   collects the food, on d_active.php.
   --------------------------------------------------------------------- */


/* =====================================================================
   STEP 4 — Load the order

   Three JOINs:
     restaurants + users -> the shop name, its address and phone
     users (again)       -> the customer's name
   ===================================================================== */
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

// No such order at all -> back to the list, do not show a blank page.
if (!$order) {
    header("Location:d_available.php");
    exit();
}


/* =====================================================================
   STEP 5 — The items the rider has to carry

   We read item_name from order_items, NOT from menu_items.
   order_items holds a snapshot taken when the customer checked out.
   If the restaurant renames a dish tomorrow, the rider must still see
   what was actually ordered today.

   Same idea for the customer's phone: we use orders.delivery_phone
   (the number given with the order), not users.phone.
   ===================================================================== */
$items = [];
$stmt = mysqli_prepare($conn,
    "SELECT item_name, quantity FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, "i", $orderId);
mysqli_stmt_execute($stmt);
$itemResult = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($itemResult)) {
    $items[] = $row;
}


/* =====================================================================
   STEP 6 — Which of the two states?

   State 1: still free  -> show everything + Accept button
   State 2: gone        -> show the "another rider got it" message
   ===================================================================== */
$isAvailable = ($order["order_status"] === "ready" && $order["rider_id"] === null);

// Friendly names for the status column
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
    <?php renderNavbar("available"); ?>

    <div id="mainArea">
        <h1 id="titleName">Delivery #<?php echo (int)$order["order_id"]; ?></h1>

        <?php // ================ STATE 1 : still available ================ ?>
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

            <?php // ============ STATE 2 : somebody else took it ============ ?>
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
                            // Fall back to the raw value instead of warning on
                            // a status that is not in the list.
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
