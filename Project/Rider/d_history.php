<?php

session_start();

include __DIR__ . "/../Common/lib/dbConfig.php";
include __DIR__ . "/lib/riderLib.php";

requireRider();

$riderId = (int)$_SESSION["user_id"];

$stmt = mysqli_prepare($conn,
    "SELECT
        COALESCE(SUM(order_status = 'delivered'), 0)   AS delivered_today,
        COALESCE(SUM(CASE WHEN order_status = 'delivered'
                          THEN total ELSE 0 END), 0)   AS cash_today,
        COALESCE(SUM(order_status = 'cancelled'), 0)   AS failed_today
       FROM orders
      WHERE rider_id = ? AND DATE(closed_at) = CURDATE()");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$fromDate = isset($_GET["from"]) ? $_GET["from"] : date("Y-m-d", strtotime("-7 days"));
$toDate   = isset($_GET["to"])   ? $_GET["to"]   : date("Y-m-d");

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
    $fromDate = date("Y-m-d", strtotime("-7 days"));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
    $toDate = date("Y-m-d");
}

$dateError = "";
if ($fromDate > $toDate) {
    $dateError = "From date cannot be after To date.";
}

$resultFilter = isset($_GET["result"]) ? $_GET["result"] : "all";
$resultClause = "";

if ($resultFilter === "delivered") {
    $resultClause = " AND o.order_status = 'delivered'";
} else if ($resultFilter === "failed") {
    $resultClause = " AND o.order_status = 'cancelled'";
} else {
    $resultFilter = "all";
}

$history = [];

if ($dateError === "") {
    $sql = "SELECT o.order_id, o.total, o.delivery_address, o.closed_at,
                   o.order_status, o.cancel_reason,
                   r.shop_name
              FROM orders o
              JOIN restaurants r ON r.user_id = o.restaurant_id
             WHERE o.rider_id = ?
               AND o.order_status IN ('delivered','cancelled')
               AND DATE(o.closed_at) BETWEEN ? AND ?"
           . $resultClause .
           " ORDER BY o.closed_at DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $riderId, $fromDate, $toDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>FoodRush - My Deliveries</title>
</head>

<body>
    <?php renderRiderNavbar("history"); ?>

    <div id="mainArea">
        <h1 id="titleName">My Deliveries</h1>

        <table class="dataTable">
            <tr>
                <th>Delivered today</th>
                <th>Cash collected today</th>
                <th>Failed today</th>
            </tr>
            <tr>
                <td><?php echo (int)$summary["delivered_today"]; ?></td>
                <td>Tk <?php echo number_format($summary["cash_today"]); ?></td>
                <td><?php echo (int)$summary["failed_today"]; ?></td>
            </tr>
        </table>

        <?php if ($dateError !== "") { ?>
            <div class="msgBox msgError"><?php echo e($dateError); ?></div>
        <?php } ?>

        <form method="get" class="infoBox">
            <span class="filterItem">
                <label class="inputLabel">From</label><br>
                <input type="date" name="from" value="<?php echo htmlspecialchars($fromDate); ?>">
            </span>

            <span class="filterItem">
                <label class="inputLabel">To</label><br>
                <input type="date" name="to" value="<?php echo htmlspecialchars($toDate); ?>">
            </span>

            <span class="filterItem">
                <label class="inputLabel">Result</label><br>
                <select name="result">
                    <option value="all"       <?php if ($resultFilter === "all")       echo "selected"; ?>>All</option>
                    <option value="delivered" <?php if ($resultFilter === "delivered") echo "selected"; ?>>Delivered</option>
                    <option value="failed"    <?php if ($resultFilter === "failed")    echo "selected"; ?>>Failed</option>
                </select>
            </span>

            <span class="filterItem">
                <label class="inputLabel">&nbsp;</label><br>
                <button type="submit" class="btn primaryBtn">Filter</button>
            </span>
        </form>

        <table class="dataTable">
            <tr>
                <th>Order</th>
                <th>Restaurant</th>
                <th>Delivered to</th>
                <th>Cash</th>
                <th>Finished</th>
                <th>Result</th>
            </tr>

            <?php foreach ($history as $h) { ?>
                <?php $wasDelivered = ($h["order_status"] === "delivered"); ?>
                <tr>
                    <td>#<?php echo (int)$h["order_id"]; ?></td>

                    <td><?php echo htmlspecialchars($h["shop_name"]); ?></td>

                    <td><small><?php echo htmlspecialchars($h["delivery_address"]); ?></small></td>

                    <td>
                        <?php
                        if ($wasDelivered) {
                            echo "Tk " . number_format($h["total"]);
                        } else {
                            echo "&mdash;";
                        }
                        ?>
                    </td>

                    <td>
                        <?php
                        if ($h["closed_at"]) {
                            echo date("j M, g:i A", strtotime($h["closed_at"]));
                        } else {
                            echo "&mdash;";
                        }
                        ?>
                    </td>

                    <td>
                        <?php if ($wasDelivered) { ?>
                            <span class="tag tagGreen">Delivered</span>
                        <?php } else { ?>
                            <span class="tag tagRed">Failed</span>
                            <?php if ($h["cancel_reason"]) { ?>
                                <br><small><?php echo htmlspecialchars($h["cancel_reason"]); ?></small>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if (count($history) === 0 && $dateError === "") { ?>
                <tr>
                    <td colspan="6"><small>No deliveries in this date range.</small></td>
                </tr>
            <?php } ?>
        </table>
    </div>

</body>

</html>
