<?php
session_start();

include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
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
            <a href="../cusines/cusines.php" class="navigation_link">Cusines</a>
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
            </table>
        </div>
    </div>
</body>

</html>
