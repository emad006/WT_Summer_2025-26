<?php
session_start();

include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

$selected_rating = "";
$selected_visibility = "";
$selected_restaurant = "";

$where_clauses = [];

$sql = "SELECT user_id, shop_name FROM restaurants ORDER BY shop_name";
$restaurant_list_result = mysqli_query($conn, $sql);

$sql = "SELECT rv.review_id, rv.order_id, rv.rating, rv.comment, rv.review_date, rv.is_removed, c.name AS customer_name, res.shop_name AS restaurant_name FROM reviews rv LEFT JOIN users c ON rv.customer_id = c.user_id LEFT JOIN orders o ON rv.order_id = o.order_id LEFT JOIN restaurants res ON o.restaurant_id = res.user_id ORDER BY rv.review_date DESC";
$reviews_result = mysqli_query($conn, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["filter_btn"])) {

        if (!empty($_POST["rating_filter"])) {
            $selected_rating = $_POST["rating_filter"];
            $where_clauses[] = "rv.rating = '$selected_rating'";
        }

        if (!empty($_POST["visibility_filter"])) {
            $selected_visibility = $_POST["visibility_filter"];

            if ($selected_visibility == "visible") {
                $where_clauses[] = "rv.is_removed = 0";
            } else {
                $where_clauses[] = "rv.is_removed = 1";
            }
        }

        if (!empty($_POST["restaurant_filter"])) {
            $selected_restaurant = $_POST["restaurant_filter"];
            $where_clauses[] = "o.restaurant_id = '$selected_restaurant'";
        }


        if (count($where_clauses) > 0) {
            $conditions = implode(" AND ", $where_clauses);
            $sql = "SELECT rv.review_id, rv.order_id, rv.rating, rv.comment, rv.review_date, rv.is_removed, c.name AS customer_name, res.shop_name AS restaurant_name FROM reviews rv LEFT JOIN users c ON rv.customer_id = c.user_id LEFT JOIN orders o ON rv.order_id = o.order_id LEFT JOIN restaurants res ON o.restaurant_id = res.user_id";
            $sql .= " WHERE $conditions ORDER BY rv.review_date DESC";
            $reviews_result = mysqli_query($conn, $sql);
        }
    }

    if (isset($_POST["action_btn"])) {
        $review_id = $_POST["row_review_id"];

        if ($_POST["action_btn"] == "Remove") {
            $sql = "UPDATE reviews SET is_removed = 1 WHERE review_id = $review_id";
            mysqli_query($conn, $sql);
        } else if ($_POST["action_btn"] == "Restore") {
            $sql = "UPDATE reviews SET is_removed = 0 WHERE review_id = $review_id";
            mysqli_query($conn, $sql);
        }

        header("Location:reviews.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Reviews</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="../dashboard/dashboard.php" class="navigation_link">Dashboard</a>
            <a href="../users/users.php" class="navigation_link">Users</a>
            <a href="../cuisines/cuisines.php" class="navigation_link">Cuisines</a>
            <a href="../orders/orders.php" class="navigation_link">Orders</a>
            <a href="reviews.php" class="navigation_link active_link">Reviews</a>
            <a href="../profile/profile.php" class="navigation_link">Profile</a>
            <a href="../logout.php" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>Review Moderation</h2>

        <div id="filter_box" class="box">
            <form method="post">
                <div id="rating_filter" class="filter">
                    Rating<br>
                    <select name="rating_filter">
                        <option value="">All ratings</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </div>

                <div id="visibility_filter" class="filter">
                    Visibility<br>
                    <select name="visibility_filter">
                        <option value="">All reviews</option>
                        <option value="visible">Visible</option>
                        <option value="removed">Removed</option>
                    </select>
                </div>

                <div id="restaurant_filter" class="filter">
                    Restaurant<br>
                    <select name="restaurant_filter">
                        <option value="">All restaurants</option>
                        <?php

                        while ($row = mysqli_fetch_assoc($restaurant_list_result)) {
                            echo "<option value='" . $row["user_id"] . "'>" . $row["shop_name"] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div id="filter_button" class="filter">
                    <br>
                    <button type="submit" id="filter_btn" name="filter_btn">Filter</button>
                </div>
            </form>
        </div>

        <div id="table_box_reviews" class="box">
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Restaurant</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php

                while ($row = mysqli_fetch_assoc($reviews_result)) {
                    echo "<tr>";
                    echo "<td>" . $row["review_id"] . "</td>";
                    echo "<td>#" . $row["order_id"] . "</td>";
                    echo "<td>" . $row["customer_name"] . "</td>";
                    echo "<td>" . $row["restaurant_name"] . "</td>";
                    echo "<td>" . $row["rating"] . "</td>";
                    echo "<td>" . $row["comment"] . "</td>";
                    echo "<td>" . $row["review_date"] . "</td>";


                    if ($row["is_removed"] == 0) {
                        echo "<td class='visible_review'>Visible</td>";
                    } else {
                        echo "<td class='removed_review'>Removed</td>";
                    }

                    echo "<td>";
                    echo "<form method='post' style='display:inline;'>";
                    echo "<input type='hidden' name='row_review_id' value='" . $row["review_id"] . "'>";

                    if ($row["is_removed"] == 0) {
                        echo "<input type='submit' class='action_btn' name='action_btn' value='Remove'>";
                    } else {
                        echo "<input type='submit' class='action_btn' name='action_btn' value='Restore'>";
                    }

                    echo "</form>";
                    echo "</td>";

                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>
