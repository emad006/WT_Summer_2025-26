<?php

session_start();

include __DIR__ . "/../Common/lib/dbConfig.php";
include __DIR__ . "/lib/riderLib.php";

requireRider();

$riderId = (int)$_SESSION["user_id"];

$isOnDuty = riderIsOnDuty($conn, $riderId);

$stmt = mysqli_prepare($conn,
    "SELECT order_id, order_status
       FROM orders
      WHERE rider_id = ?
        AND order_status IN ('assigned', 'picked_up')
      LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$activeOrder = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$waiting = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total
       FROM orders
      WHERE order_status = 'ready'
        AND rider_id IS NULL"));

$stmt = mysqli_prepare($conn,
    "SELECT
        COUNT(*)                                                        AS jobs,
        COALESCE(SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END), 0) AS delivered,
        COALESCE(SUM(CASE WHEN order_status = 'delivered' THEN delivery_fee ELSE 0 END), 0) AS earned
       FROM orders
      WHERE rider_id = ?
        AND order_status IN ('delivered', 'cancelled')
        AND DATE(closed_at) = CURDATE()");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$today = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>FoodRush - Rider Dashboard</title>
</head>

<body>
    <?php renderRiderNavbar("dashboard"); ?>

    <div id="mainArea">
        <h1 id="titleName">Welcome, <?php echo e($_SESSION["name"]); ?></h1>

        <div class="infoBox">
            <?php if ($isOnDuty) { ?>
                You are <span class="tag tagGreen">ON DUTY</span> and can accept deliveries.
            <?php } else { ?>
                You are <span class="tag tagGray">OFF DUTY</span>.
                <a href="d_available.php">Go online</a> to start receiving offers.
            <?php } ?>
        </div>

        <?php if ($activeOrder) { ?>
            <div class="msgBox msgWarn">
                You are carrying delivery
                <b>#<?php echo (int)$activeOrder["order_id"]; ?></b>.
                <a href="d_active.php">Open it</a> to mark pickup or delivery.
            </div>
        <?php } else if ($isOnDuty && (int)$waiting["total"] > 0) { ?>
            <div class="msgBox">
                <b><?php echo (int)$waiting["total"]; ?></b>
                <?php echo ((int)$waiting["total"] === 1) ? "delivery is" : "deliveries are"; ?>
                waiting for a rider.
                <a href="d_available.php">See them</a>.
            </div>
        <?php } else if ($isOnDuty) { ?>
            <div class="msgBox">
                Nothing is ready for pickup at the moment. Check back in a few minutes.
            </div>
        <?php } ?>

        <div class="infoBox">
            <b>Today</b><br>
            Jobs closed: <?php echo (int)$today["jobs"]; ?> &nbsp;&middot;&nbsp;
            Delivered: <?php echo (int)$today["delivered"]; ?> &nbsp;&middot;&nbsp;
            Earned: <?php echo number_format((float)$today["earned"], 2); ?> Tk
        </div>

        <div class="inputBlock">
            <a class="btn primaryBtn" href="d_available.php">Available Deliveries</a>
            <a class="btn secondaryBtn" href="d_active.php">Active Delivery</a>
            <a class="btn secondaryBtn" href="d_history.php">History</a>
        </div>
    </div>

</body>

</html>
