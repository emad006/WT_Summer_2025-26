<?php
session_start();

include "../db.php";

if($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

// Variables
$pending_count = 0;
$all_users_count = 0;
$pending_users = [];

// Get number of pending and all users (status box)
$sql = "SELECT COUNT(*) AS 'pending_count' FROM users WHERE account_status = 'pending'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$pending_count = $row["pending_count"];

$sql = "SELECT COUNT(*) AS 'all_user_count' FROM users";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$all_users_count = $row["all_user_count"];

// Get pending users (table)
$sql = "SELECT u.name, u.role, u.email, u.phone, res.shop_name AS restaurant_name, u.address AS restaurant_address, rid.vehicle_type AS rider_vehicle, rid.nid AS rider_nid FROM users u LEFT JOIN restaurants res ON u.user_id = res.user_id LEFT JOIN riders rid ON u.user_id = rid.user_id WHERE u.account_status = 'pending'";

$pending_user_result = mysqli_query($conn, $sql);
?>


<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Users</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="#" class="navigation_link">Dashboard</a>
            <a href="#" class="navigation_link active_link">Users</a>
            <a href="#" class="navigation_link">Cusines</a>
            <a href="#" class="navigation_link">Orders</a>
            <a href="#" class="navigation_link">Reviews</a>
            <a href="#" class="navigation_link">Profile</a>
            <a href="#" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>Users</h2>

        <div id="error_box" class="box">
            Password reset for Rakib Hasan (rakib@example.com).<br>
            Temporary password: Tq7f-92Kd<br>
            Give this to the user directly. It will not be shown again.
        </div>

        <div id="status_box" class="box">
            <a href="#" id="link_pending" class="status_link status_active_link" onclick="showPendingApproval()">Pending Approval (<?php echo $pending_count; ?>)</a>
            <a href="#" id="link_all_users" class="status_link" onclick="hidePendingApproval()">All Users (<?php echo $all_users_count; ?>)</a>
        </div>

        <div id="table_box_pending" class="box">
            <table border="1">
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email & phone</th>
                    <th>Details</th>
                    <th>Action</th>
                </tr>

                <?php
                while($row = mysqli_fetch_assoc($pending_user_result)) {
                    echo "<tr>";
                    echo "<td>" . $row["name"] . "</td>";
                    echo "<td>" . ucfirst($row["role"]) . "</td>";
                    echo "<td>" . $row["email"] . "<br>" . $row["phone"] . "</td>";

                    if ($row["role"] == "restaurant") {
                        echo "<td>" . $row["restaurant_name"] . " · " . $row["restaurant_address"] . "</td>";
                    } elseif ($row["role"] == "rider") {
                        if ($row["rider_vehicle"] == "on_foot") {
                            $rider_vehicle = "On Foot";
                        }  else {
                            $rider_vehicle = ucfirst($row["rider_vehicle"]);
                        }
                        echo "<td>" . $rider_vehicle . " · NID: " . $row["rider_nid"] . "</td>";
                    } else {
                        echo "<td>N/A</td>";
                    }

                    echo "<td><a href='#'>Approve</a> · <a href='#'>Reject</a></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div id="filter_box" class="box hide_box">
            <form method="post">
                <div id="role_filter" class="filter">
                    Role<br>
                    <select name="roles">
                        <option value="">All roles</option>
                        <option value="customer">Customer</option>
                        <option value="restaurant">Restaurant</option>
                        <option value="rider">Rider</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div id="status_filter" class="filter">
                    Status<br>
                    <select name="status">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspended</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div id="search_filter" class="filter">
                    Search<br>
                    <input type="text" name="search" placeholder="name or email">
                </div>

                <div id="filter_button" class="filter">
                    <br>
                    <button type="submit">Filter</button>
                </div>
            </form>
        </div>

        <div id="table_box_all_users" class="box hide_box">
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <tr>
                    <td>1</td>
                    <td>Shajmin</td>
                    <td>Restaurant</td>
                    <td>mezban@example.com</td>
                    <td>Active</td>
                    <td>
                        <a href="#">Suspend</a> · <a href="#">Reset Password</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>





<script>
    function hidePendingApproval() {
        let pending_link = document.getElementById("link_pending");
        let all_users_link = document.getElementById("link_all_users");

        let pending_table = document.getElementById("table_box_pending");
        let filter_box = document.getElementById("filter_box");
        let all_users_table = document.getElementById("table_box_all_users");


        pending_link.classList.remove("status_active_link");
        all_users_link.classList.add("status_active_link");

        pending_table.classList.add("hide_box");
        filter_box.classList.remove("hide_box");
        all_users_table.classList.remove("hide_box");
    }

    function showPendingApproval() {
        let pending_link = document.getElementById("link_pending");
        let all_users_link = document.getElementById("link_all_users");

        let pending_table = document.getElementById("table_box_pending");
        let filter_box = document.getElementById("filter_box");
        let all_users_table = document.getElementById("table_box_all_users");

        all_users_link.classList.remove("status_active_link");
        pending_link.classList.add("status_active_link");

        pending_table.classList.remove("hide_box");
        filter_box.classList.add("hide_box");
        all_users_table.classList.add("hide_box");
    }
</script>

</html>