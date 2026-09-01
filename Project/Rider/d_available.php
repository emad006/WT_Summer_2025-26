<?php

session_start();

include __DIR__ . "/../Common/lib/dbConfig.php";
include __DIR__ . "/lib/riderLib.php";

requireRider();

$riderId = (int)$_SESSION["user_id"];

$stmt = mysqli_prepare($conn,
    "SELECT order_id FROM orders
      WHERE rider_id = ? AND order_status IN ('ready','on_the_way')
      LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$activeOrder = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["dutyBtn"])) {
    $wantOnDuty = ($_POST["dutyBtn"] === "1") ? 1 : 0;

    if ($wantOnDuty === 0 && $activeOrder) {
        header("Location:d_available.php?err=busy");
        exit();
    }

    $stmt = mysqli_prepare($conn, "UPDATE riders SET is_on_duty = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $wantOnDuty, $riderId);
    mysqli_stmt_execute($stmt);

    header("Location:d_available.php");
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT is_on_duty FROM riders WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$dutyRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$isOnDuty = $dutyRow ? (int)$dutyRow["is_on_duty"] : 0;

$sortOptions = [
    "waiting" => "o.ready_at ASC",
    "cash"    => "o.total DESC",
    "shop"    => "r.shop_name ASC"
];

$sort = isset($_GET["sort"]) ? $_GET["sort"] : "waiting";
if (!array_key_exists($sort, $sortOptions)) {
    $sort = "waiting";
}

$shopFilter = isset($_GET["shop"]) ? (int)$_GET["shop"] : 0;

$sql = "SELECT o.order_id,
               o.total,
               o.delivery_address,
               TIMESTAMPDIFF(MINUTE, o.ready_at, NOW()) AS waiting_min,
               r.shop_name,
               ru.address AS pickup_address
          FROM orders o
          JOIN restaurants r  ON r.user_id  = o.restaurant_id
          JOIN users       ru ON ru.user_id = r.user_id
         WHERE o.order_status = 'ready'
           AND o.rider_id IS NULL";

if ($shopFilter > 0) {
    $sql = $sql . " AND o.restaurant_id = ?";
}
$sql = $sql . " ORDER BY " . $sortOptions[$sort];

$stmt = mysqli_prepare($conn, $sql);
if ($shopFilter > 0) {
    mysqli_stmt_bind_param($stmt, "i", $shopFilter);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$deliveries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $deliveries[] = $row;
}

$shopList = [];
$shopResult = mysqli_query($conn,
    "SELECT DISTINCT r.user_id, r.shop_name
       FROM orders o JOIN restaurants r ON r.user_id = o.restaurant_id
      WHERE o.order_status = 'ready' AND o.rider_id IS NULL
      ORDER BY r.shop_name");
while ($row = mysqli_fetch_assoc($shopResult)) {
    $shopList[] = $row;
}

if ($activeOrder) {
    $state = 3;
} else if (!$isOnDuty) {
    $state = 2;
} else if (count($deliveries) === 0) {
    $state = 4;
} else {
    $state = 1;
}

$linksAreLive = ($state === 1);
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>FoodRush - Available Deliveries</title>
</head>

<body>
    <?php renderRiderNavbar("available"); ?>

    <div id="mainArea">
        <h1 id="titleName">Available Deliveries</h1>

        <?php if (isset($_GET["err"]) && $_GET["err"] === "busy") { ?>
            <div class="msgBox msgError">
                You cannot go offline while a delivery is in progress.
            </div>
        <?php } ?>

        <?php if ($state === 3) { ?>
            <div class="msgBox msgWarn">
                <b>You already have an active delivery (#<?php echo (int)$activeOrder["order_id"]; ?>).</b>
                Finish or report it before accepting another.
                <a href="d_active.php">Go to active delivery</a>
            </div>
        <?php } ?>

        <?php if ($state === 2) { ?>
            <div class="msgBox msgWarn">
                <b>You are off duty.</b> Go online to accept deliveries.
            </div>
        <?php } ?>

        <div class="infoBox">
            <b>Duty status:</b>

            <?php if ($isOnDuty) { ?>
                <span class="tag tagGreen">Online</span>
                &mdash; you are visible for assignment. &nbsp;
                <form method="post" class="inlineForm">
                    <button type="submit" name="dutyBtn" value="0" class="btn dangerBtn"
                        <?php if ($activeOrder) echo "disabled"; ?>>Go offline</button>
                </form>
                <?php if ($activeOrder) { ?>
                    <br><small>You cannot go offline while a delivery is in progress.</small>
                <?php } ?>

            <?php } else { ?>
                <span class="tag tagGray">Offline</span> &nbsp;
                <form method="post" class="inlineForm">
                    <button type="submit" name="dutyBtn" value="1" class="btn primaryBtn">Go online</button>
                </form>
            <?php } ?>
        </div>

        <?php if (count($deliveries) === 0) { ?>

            <div class="msgBox">
                No deliveries are ready for pickup right now. Refresh in a few minutes.
            </div>

        <?php } else { ?>

            <?php if ($state === 1) { ?>
                <form method="get" class="infoBox">
                    <span class="filterItem">
                        <label class="inputLabel">Sort by</label><br>
                        <select name="sort">
                            <option value="waiting" <?php if ($sort === "waiting") echo "selected"; ?>>Waiting longest</option>
                            <option value="cash"    <?php if ($sort === "cash")    echo "selected"; ?>>Highest cash</option>
                            <option value="shop"    <?php if ($sort === "shop")    echo "selected"; ?>>Restaurant name</option>
                        </select>
                    </span>

                    <span class="filterItem">
                        <label class="inputLabel">Restaurant</label><br>
                        <select name="shop">
                            <option value="0">All restaurants</option>
                            <?php foreach ($shopList as $shop) { ?>
                                <option value="<?php echo (int)$shop["user_id"]; ?>"
                                    <?php if ($shopFilter === (int)$shop["user_id"]) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($shop["shop_name"]); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </span>

                    <span class="filterItem">
                        <label class="inputLabel">&nbsp;</label><br>
                        <button type="submit" class="btn primaryBtn">Apply</button>
                    </span>
                </form>
            <?php } ?>

            <table class="dataTable">
                <tr>
                    <th>Order</th>
                    <th>Pick up from</th>
                    <th>Deliver to</th>
                    <th>Cash to collect</th>
                    <th>Waiting</th>
                    <th>Action</th>
                </tr>

                <?php foreach ($deliveries as $d) { ?>
                    <tr>
                        <td>#<?php echo (int)$d["order_id"]; ?></td>

                        <td>
                            <?php echo htmlspecialchars($d["shop_name"]); ?><br>
                            <small><?php echo htmlspecialchars($d["pickup_address"]); ?></small>
                        </td>

                        <td><small><?php echo htmlspecialchars($d["delivery_address"]); ?></small></td>

                        <td>Tk <?php echo number_format($d["total"]); ?></td>

                        <td>
                            <?php
                            if ($d["waiting_min"] === null) {
                                echo "&mdash;";
                            } else {
                                echo (int)$d["waiting_min"] . " min";
                            }
                            ?>
                        </td>

                        <td>
                            <?php if ($linksAreLive) { ?>
                                <a href="d_offer.php?order_id=<?php echo (int)$d["order_id"]; ?>">View details</a>
                            <?php } else if ($state === 3) { ?>
                                <span class="disabledLink">Unavailable</span>
                            <?php } else { ?>
                                <span class="disabledLink">View details</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <?php if ($state === 1) { ?>
                <p class="noteText">
                    <?php echo count($deliveries); ?> deliveries available.
                    You have no active delivery.
                </p>
            <?php } ?>

        <?php } ?>
    </div>

</body>

</html>
