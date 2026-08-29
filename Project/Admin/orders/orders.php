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
    <title>Orders</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="../dashboard/dashboard.php" class="navigation_link">Dashboard</a>
            <a href="../users/users.php" class="navigation_link">Users</a>
            <a href="../cusines/cusines.php" class="navigation_link">Cusines</a>
            <a href="orders.php" class="navigation_link active_link">Orders</a>
            <a href="../reviews/reviews.php" class="navigation_link">Reviews</a>
            <a href="../profile/profile.php" class="navigation_link">Profile</a>
            <a href="../logout.php" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>All Orders</h2>

        <div id="table_box_stats" class="box">
            <table border="1">
                <tr>
                    <th>Total Orders</th>
                    <th>Revenue</th>
                    <th>Registered Users</th>
                    <th>Riders On Duty</th>
                </tr>

                <tr>
                    <td></td>
                    <td>Tk </td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <div id="filter_box" class="box">
            <form method="post">
                <div id="order_id_filter" class="filter">
                    Order ID<br>
                    <input type="text" name="order_id_filter" class="filter_input" placeholder="e.g. 5">
                </div>

                <div id="status_filter" class="filter">
                    Status<br>
                    <select name="status_filter">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready</option>
                        <option value="on_the_way">On the way</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div id="date_from_filter" class="filter">
                    From<br>
                    <input type="date" name="date_from_filter">
                </div>

                <div id="date_to_filter" class="filter">
                    To<br>
                    <input type="date" name="date_to_filter">
                </div>

                <div id="filter_button" class="filter">
                    <br>
                    <button type="submit" id="filter_btn" name="filter_btn">Filter</button>
                </div>
            </form>
        </div>

        <div id="table_box_orders" class="box">
            <table border="1">
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Restaurant</th>
                    <th>Rider</th>
                    <th>Total</th>
                    <th>Placed At</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
