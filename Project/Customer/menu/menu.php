<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

// Kick out user if no restaurant ID is sent
if (empty($_GET["restaurant_id"])) {
    header("Location:../browseRestaurant/browseRestaurant.php");
    exit();
}

// Set the cookie for recently viewed
setcookie("recently_viewed", $_GET["restaurant_id"], time() + (86400 * 30), "/");

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
    $_SESSION["cart_restaurant_id"] = null;
}

$errors = [];
$success = [];

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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["addToCartBtn"])) {
    // <-------- Validation Start -------->

    // Item ID validation (redundant, but just for safety)
    if (empty($_POST["item_id"])) {
        $errors[] = "Something went wrong.";
    }

    // Quantity validation
    if (empty($_POST["qty"])) {
        $errors[] = "Please specify a quantity.";
    } else if (!is_numeric($_POST["qty"])) {
        $errors[] = "Quantity must be a number.";
    } else if ($_POST["qty"] < 1) {
        $errors[] = "Quantity must be at least 1.";
    }

    // <-------- Validation End -------->

    if (count($errors) === 0) { // Proceed to add to cart

        // Check restaurant mismatch
        if (!empty($_SESSION["cart"]) && $_SESSION["cart_restaurant_id"] !== $_GET["restaurant_id"]) {
            $errors[] = "Your cart already has items from another restaurant. Please clear your cart first.";
        } else {
            $_SESSION["cart_restaurant_id"] = $_GET["restaurant_id"]; // Set restaurant id

            if (!isset($_SESSION["cart"][$_POST["item_id"]])) { // Check if that item already exists
                $_SESSION["cart"][$_POST["item_id"]] = $_POST["qty"];
            } else { // If item already in cart, increment qty
                $_SESSION["cart"][$_POST["item_id"]] += $_POST["qty"];
            }

            $success[] = "Item added to cart.";
        }
    }
}
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
            <a href="../browseRestaurant/browseRestaurant.php" class="navLink navLinkActive">Browse</a>
            <a href="../cart/cart.php" class="navLink">Cart</a>
            <a href="../orders/orders.php" class="navLink">My Orders</a>
            <a href="../profile/profile.php" class="navLink">Profile</a>
            <a href="../../Common/logout.php" class="navLink">Logout</a>
        </div>

        <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
    </div>



    <div id="mainArea">
        <h1 id="titleName"><?php echo $restaurantDetails["shop_name"]; ?></h1>

        <div id="successBlock"><?php if (!empty($success)) echo implode("<br>", $success); ?></div>
        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors); ?></div>
        <div id="statusBlock"><?php if ($restaurantDetails["is_open"] == 0) echo "This restaurant is currently closed and is not accepting orders. You can still view the menu."; ?></div>

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
                    echo "<form method='post'>";

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

                    if ($restaurantDetails["is_open"] === 1 && $row["is_available"] === 1) {
                        echo "<td><input type='text' name='qty' class='inputField' value='1'></td>";
                        echo "<td><button type='submit' name='addToCartBtn' class='addToCartBtn restaurantOpenButtonStyle'>Add</button></td>";
                    } else {
                        echo "<td><input type='text' name='qty' class='inputField' value='1' disabled></td>";
                        echo "<td><button type='submit' name='addToCartBtn' class='addToCartBtn restaurantClosedButtonStyle' disabled>Add</button></td>";
                    }

                    echo "<input type='hidden' name='item_id' value='" . $row["item_id"] . "'>";

                    echo "</tr>";
                    echo "</form>";
                }
                ?>
            </table>

            <a href="../browseRestaurant/browseRestaurant.php" id="backLink">Back to Restaurants</a>
        </div>
    </div>
</body>

</html>