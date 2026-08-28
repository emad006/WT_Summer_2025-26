<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

// Get restaurant details
$stmt = mysqli_prepare($conn, "SELECT r.user_id, r.shop_name, c.cuisine_name, u.address, COALESCE(ROUND(AVG(rev.rating), 1), 0.0) AS rating, COUNT(rev.review_id) AS total_ratings, r.is_open FROM restaurants r INNER JOIN cuisines c ON r.cuisine_id = c.cuisine_id INNER JOIN users u ON r.user_id = u.user_id LEFT JOIN orders o ON r.user_id = o.restaurant_id LEFT JOIN reviews rev ON o.order_id = rev.order_id AND rev.is_removed = 0 WHERE r.user_id = ? GROUP BY r.shop_name, c.cuisine_name, u.address, r.is_open");
mysqli_stmt_bind_param($stmt, "i", $_GET["restaurant_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$restaurantDetails = mysqli_fetch_assoc($result);

// Get all menu items
$stmt = mysqli_prepare($conn, "SELECT item_id, photo, item_name, description, ROUND(price, 0) AS price, is_available FROM menu_items WHERE user_id = ? AND is_deleted = 0 ORDER BY item_name ASC");
mysqli_stmt_bind_param($stmt, "i", $_GET["restaurant_id"]);
mysqli_stmt_execute($stmt);
$allMenuItems = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title><?php echo $restaurantDetails["shop_name"]; ?> - Menu</title>
</head>

<body>
    <div id="navbar">
        <div id="navLeft">
            <a href="../dashboard/dashboard.php" class="navLink">Dashboard</a>
            <a href="../browseRestaurant/browseRestaurant.php" class="navLink">Browse</a>
            <a href="#" class="navLink">Cart</a>
            <a href="../orders/orders.php" class="navLink navLinkActive">My Orders</a>
            <a href="../profile/profile.php" class="navLink">Profile</a>
            <a href="../../Common/logout.php" class="navLink">Logout</a>
        </div>

        <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
    </div>



    <div id="mainArea">
        <h1 id="titleName"><?php echo $restaurantDetails["shop_name"]; ?></h1>

        <div>
            <label id="restaurantInfo">
                <?php
                    echo $restaurantDetails["cuisine_name"] . " · ";
                    echo $restaurantDetails["address"] . " · ";
                    echo "Rating " . $restaurantDetails["rating"] . " from " . $restaurantDetails["total_ratings"] . " review(s)";
                    
                    if ($restaurantDetails["is_open"] === 1) {
                        echo "<label id='restaurantOpenStatus' class='restaurantStatusClass'>Open</label>";
                    } else {
                        echo "<label id='restaurantCloseStatus' class='restaurantStatusClass'>Closed</label>";
                    }
                ?>
            </label>
        </div>

        <div id="tableBlock">
            <table border="1">
                <tr>
                    <th>Photo</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Available</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>

                <?php
                while ($row = mysqli_fetch_assoc($allMenuItems)) {
                    echo "<tr>";
                    echo "<td><img src='" . $row["photo"] . "' alt='Menu Item' width='100' height='100'></td>";
                    echo "<td><label class='tableLabel'>" . $row["item_name"] . "</label></td>";
                    echo "<td><label class='tableLabel'>" . $row["description"] . "</label></td>";
                    echo "<td><label class='tableLabel'>Tk " . $row["price"] . "</label></td>";

                    if ($row["is_available"] === 1) {
                        echo "<td><label class='tableLabel' style='color: green;'>Yes</label></td>";
                    } else {
                        echo "<td><label class='tableLabel' style='color: red;'>No</label></td>";
                    }

                    echo "<td><input type='text' class='inputField' value='1'></td>";
                    echo "<td><button>Add to Cart</button></td>";
                    echo "</tr>";
                }
                ?>

                
            </table>
        </div>
    </div>
</body>

</html>