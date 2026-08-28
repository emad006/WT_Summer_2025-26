<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

// Get all orders for customer
$stmt = mysqli_prepare($conn, "SELECT o.order_id, r.shop_name AS restaurant_name, SUM(oi.quantity) AS total_items, ROUND((o.total + o.delivery_fee), 0) AS grand_total, o.placed_at AS order_date, o.order_status FROM orders o INNER JOIN restaurants r ON o.restaurant_id = r.user_id INNER JOIN order_items oi ON o.order_id = oi.order_id WHERE o.customer_id = ? GROUP BY o.order_id, r.shop_name, o.total, o.delivery_fee, o.placed_at, o.order_status ORDER BY o.placed_at DESC;");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$allOrders = mysqli_stmt_get_result($stmt);



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["filterBtn"])) {
    $baseQuery = "SELECT r.user_id AS restaurant_id, r.shop_name, c.cuisine_name, r.is_open, COALESCE(ROUND(AVG(rev.rating), 1), 0.0) AS rating, COUNT(rev.review_id) AS total_ratings FROM restaurants r INNER JOIN cuisines c ON r.cuisine_id = c.cuisine_id INNER JOIN users u ON r.user_id = u.user_id LEFT JOIN orders o ON r.user_id = o.restaurant_id LEFT JOIN reviews rev ON o.order_id = rev.order_id AND rev.is_removed = 0";
    $whereClauses = ["u.account_status = 'active'"];
    $restQuery = " GROUP BY r.user_id, r.shop_name, c.cuisine_name, r.is_open";
    $orderBy = "";
    
    if (!empty($_POST["search"])) {
        $searchToken = $_POST["search"];
        $whereClauses[] = "r.shop_name LIKE '%$searchToken%'";
    }

    if (!empty($_POST["cuisineType"])) {
        $cuisineType = $_POST["cuisineType"];
        $whereClauses[] = "c.cuisine_id = '$cuisineType'";
    }

    if (isset($_POST["status"]) && $_POST["status"] !== "") {
        $openStatus = $_POST["status"];
        $whereClauses[] = "r.is_open = '$openStatus'";
    }

    if (!empty($_POST["sortBy"])) {
        if ($_POST["sortBy"] === "sortByName") {
            $orderBy = " ORDER BY r.shop_name ASC";
        } else {
            $orderBy = " ORDER BY rating DESC";
        }
    }

    // Build final query
    $finalQuery = $baseQuery . " WHERE " . implode(" AND ", $whereClauses) . $restQuery . $orderBy;
    
    // Query the database
    $stmt = mysqli_prepare($conn, $finalQuery);
    mysqli_stmt_execute($stmt);
    $allRestaurants = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>Customer - My Orders</title>
</head>

<body>
    <div id="navbar">
        <div id="navLeft">
            <a href="../dashboard/dashboard.php" class="navLink">Dashboard</a>
            <a href="../browseRestaurant/browseRestaurant.php" class="navLink">Browse</a>
            <a href="#" class="navLink">Cart</a>
            <a href="orders.php" class="navLink navLinkActive">My Orders</a>
            <a href="../profile/profile.php" class="navLink">Profile</a>
            <a href="../../Common/logout.php" class="navLink">Logout</a>
        </div>

        <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
    </div>



    <div id="mainArea">
        <h1 id="titleName">My Orders</h1>

        <form method="post">
            <div id="filterArea">
                <div class="filterGroup">
                    <label class="labelText">Status</label>
                    <br>
                    <select name="status" class="inputField">
                        <option value="">All</option>
                        <option value="pending" <?php if ($_POST["status"] === "1") echo "selected"; ?>>Pending</option>
                        <option value="preparing" <?php if ($_POST["status"] === "0") echo "selected"; ?>>Preparing</option>
                        <option value="ready" <?php if ($_POST["status"] === "0") echo "selected"; ?>>Ready</option>
                        <option value="on_the_way" <?php if ($_POST["status"] === "0") echo "selected"; ?>>On the Way</option>
                        <option value="delivered" <?php if ($_POST["status"] === "0") echo "selected"; ?>>Delivered</option>
                        <option value="cancelled" <?php if ($_POST["status"] === "0") echo "selected"; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="filterGroup">
                    <label class="labelText">From</label>
                    <br>
                    <input type="date" name="fromDate" class="inputField" value="<?php if (!empty($_POST['fromDate'])) echo $_POST['fromDate']; ?>">
                </div>

                <div class="filterGroup">
                    <label class="labelText">To</label>
                    <br>
                    <input type="date" name="toDate" class="inputField" value="<?php if (!empty($_POST['toDate'])) echo $_POST['toDate']; ?>">
                </div>

                <div class="filterGroup">
                    <button type="submit" name="filterBtn" id="filterBtn">Apply Filter</button>
                </div>
            </div>
        </form>

        <div id="tableBlock"> <!-- TODO: Hide table and show a info div when there are no results -->
            <table border="1">
                <tr>
                    <th>Order</th>
                    <th>Restaurant</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Placed</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php
                while ($row = mysqli_fetch_assoc($allOrders)) {
                    echo "<tr>";
                    echo "<td><label class='tableLabel'>#" . $row["order_id"] . "</label></td>";
                    echo "<td><label class='tableLabel'>" . $row["restaurant_name"] . "</label></td>";
                    echo "<td><label class='tableLabel'>" . $row["total_items"] . "</label></td>";
                    echo "<td><label class='tableLabel'>Tk " . $row["grand_total"] . "</label></td>";
                    echo "<td><label class='tableLabel'>" . $row["order_date"] . "</label></td>";

                    if ($row["order_status"] === "pending") {
                        echo "<td><label class='tableLabel' style='color: #F59E0B;'>" . ucfirst($row["order_status"]) . "</label></td>";
                    } else if ($row["order_status"] === "preparing") {
                        echo "<td><label class='tableLabel' style='color: #3B82F6;'>" . ucfirst($row["order_status"]) . "</label></td>";
                    } else if ($row["order_status"] === "ready") {
                        echo "<td><label class='tableLabel' style='color: #8B5CF6;'>" . ucfirst($row["order_status"]) . "</label></td>";
                    } else if ($row["order_status"] === "on_the_way") {
                        echo "<td><label class='tableLabel' style='color: #06B6D4;'>On The Way</label></td>";
                    } else if ($row["order_status"] === "delivered") {
                        echo "<td><label class='tableLabel' style='color: #10B981;'>" . ucfirst($row["order_status"]) . "</label></td>";
                    } else if ($row["order_status"] === "cancelled") {
                        echo "<td><label class='tableLabel' style='color: #EF4444;'>" . ucfirst($row["order_status"]) . "</label></td>";
                    }

                    echo "<td><a class='viewMenuLink' href='viewMenu.php?order_id=" . $row["order_id"] . "'>Track</a></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>