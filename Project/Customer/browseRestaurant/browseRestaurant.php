<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

// Get cusine types
$stmt = mysqli_prepare($conn, "SELECT cuisine_id, cuisine_name FROM cuisines ORDER BY cuisine_name");
mysqli_stmt_execute($stmt);
$allCuisines = mysqli_stmt_get_result($stmt);

// Get restaurants
$stmt = mysqli_prepare($conn, "SELECT r.user_id AS restaurant_id, r.shop_name, c.cuisine_name, r.is_open, COALESCE(ROUND(AVG(rev.rating), 1), 0.0) AS rating, COUNT(rev.review_id) AS total_ratings FROM restaurants r INNER JOIN cuisines c ON r.cuisine_id = c.cuisine_id INNER JOIN users u ON r.user_id = u.user_id LEFT JOIN orders o ON r.user_id = o.restaurant_id LEFT JOIN reviews rev ON o.order_id = rev.order_id AND rev.is_removed = 0 WHERE u.account_status = 'active' GROUP BY r.user_id, r.shop_name, c.cuisine_name, r.is_open ORDER BY r.shop_name ASC");
mysqli_stmt_execute($stmt);
$allRestaurants = mysqli_stmt_get_result($stmt);

$finalQuery = "SELECT r.user_id AS restaurant_id, r.shop_name, c.cuisine_name, r.is_open, COALESCE(ROUND(AVG(rev.rating), 1), 0.0) AS rating, COUNT(rev.review_id) AS total_ratings FROM restaurants r INNER JOIN cuisines c ON r.cuisine_id = c.cuisine_id INNER JOIN users u ON r.user_id = u.user_id LEFT JOIN orders o ON r.user_id = o.restaurant_id LEFT JOIN reviews rev ON o.order_id = rev.order_id AND rev.is_removed = 0 WHERE u.account_status = 'active' GROUP BY r.user_id, r.shop_name, c.cuisine_name, r.is_open ORDER BY r.shop_name ASC";

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
}

// Query the database
$stmt = mysqli_prepare($conn, $finalQuery);
mysqli_stmt_execute($stmt);
$allRestaurants = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title>Customer - Dashboard</title>
    </head>

    <body>
        <div id="navbar">
            <div id="navLeft">
                <a href="../dashboard/dashboard.php" class="navLink">Dashboard</a>
                <a href="browseRestaurant.php" class="navLink navLinkActive">Browse</a>
                <a href="#" class="navLink">Cart</a>
                <a href="#" class="navLink">My Orders</a>
                <a href="../profile/profile.php" class="navLink">Profile</a>
                <a href="../../Common/logout.php" class="navLink">Logout</a>
            </div>

            <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
        </div>



        <div id="mainArea">
            <h1 id="titleName">Browse Restaurants</h1>

            <form method="post">
                <div id="filterArea">
                    <div class="filterGroup">
                        <label class="labelText">Search</label>
                        <br>
                        <input type="text" name="search" class="inputField" id="searchBox" value="<?php if (!empty($_POST['search'])) echo $_POST['search']; ?>">
                    </div>

                    <div class="filterGroup">
                        <label class="labelText">Cusine</label>
                        <br>
                        <select name="cuisineType" class="inputField">
                            <option value="">Select Cusine</option>
                            <?php
                            while ($row = mysqli_fetch_assoc($allCuisines)) {
                                echo "<option value='" . $row["cuisine_id"] . "'>" . $row["cuisine_name"] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filterGroup">
                        <label class="labelText">Status</label>
                        <br>
                        <select name="status" class="inputField">
                            <option value="">All</option>
                            <option value="1" <?php if ($_POST["status"] === "1") echo "selected"; ?>>Open</option>
                            <option value="0" <?php if ($_POST["status"] === "0") echo "selected"; ?>>Closed</option>
                        </select>
                    </div>

                    <div class="filterGroup">
                        <label class="labelText">Sort By</label>
                        <br>
                        <select name="sortBy" class="inputField">
                            <option value="sortByName" <?php if ($_POST["sortBy"] === "sortByName") echo "selected"; ?>>Name (A-Z)</option>
                            <option value="sortByRating" <?php if ($_POST["sortBy"] === "sortByRating") echo "selected"; ?>>Rating</option>
                        </select>
                    </div>

                    <div class="filterGroup">
                        <button type="submit" name="filterBtn" id="filterBtn">Apply Filter</button>
                    </div>
                </div>
            </form>

            <div>
                <label class="labelText">Recently Viewed</label>
            </div>

            <div id="tableBlock"> <!-- TODO: Hide table and show a info div when there are no results -->
                <table border="1">
                    <tr>
                        <th>Restaurant</th>
                        <th>Cusine</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                    <?php
                    while ($row = mysqli_fetch_assoc($allRestaurants)) {
                        echo "<tr>";
                        echo "<td><label class='tableLabel'>" . $row["shop_name"] . "</label></td>";
                        echo "<td><label class='tableLabel'>" . $row["cuisine_name"] . "</label></td>";
                        echo "<td><label class='tableLabel'>" . $row["rating"] . " (" . $row["total_ratings"] . ")</label></td>";
                        if ($row["is_open"] === 1) {
                            echo "<td><label class='tableLabel' style='color: green;'>Open</label></td>";
                        } else {
                            echo "<td><label class='tableLabel' style='color: red;'>Closed</label></td>";
                        }
                        echo "<td><a class='viewMenuLink' href='viewMenu.php?restaurant_id=" . $row["restaurant_id"] . "'>View Menu</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </body>
</html>