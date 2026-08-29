<?php
session_start();

include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

$error_message = "";

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Cusines</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="../dashboard/dashboard.php" class="navigation_link">Dashboard</a>
            <a href="../users/users.php" class="navigation_link">Users</a>
            <a href="cusines.php" class="navigation_link active_link">Cusines</a>
            <a href="../orders/orders.php" class="navigation_link">Orders</a>
            <a href="../reviews/reviews.php" class="navigation_link">Reviews</a>
            <a href="../profile/profile.php" class="navigation_link">Profile</a>
            <a href="../logout.php" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>Cusine Categories</h2>

        <div id="error_box" class="box"></div>

        <div id="table_box_cusines" class="box">
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Cusine</th>
                    <th>Restaurants Using It</th>
                    <th>Action</th>
                </tr>
            </table>
        </div>

        <div id="form_box" class="box">
            <form method="post">
            </form>
        </div>
    </div>
</body>

</html>
