<?php
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

        <div id="right_nav">System Admin · Admin</div>
    </div>

    <div id="main_box">
        <h2>Users</h2>

        <div id="error_box" class="box">
            Password reset for Rakib Hasan (rakib@example.com).<br>
            Temporary password: Tq7f-92Kd<br>
            Give this to the user directly. It will not be shown again.
        </div>

        <div id="status_box" class="box">
            <a href="#" class="status_link status_active_link">Pending Approval (<span name="pending_approval_count">4</span>)</a>
            <a href="#" class="status_link">All Users (<span name="all_users_count">200</span>)</a>
        </div>

        <div id="table_box_pending" class="box">
            <table border="1">
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email & phone</th>
                    <th>Details</th>
                    <th>Applied</th>
                    <th>Action</th>
                </tr>

                <tr>
                    <td>Shajmin</td>
                    <td>Restaurant</td>
                    <td>mezban@example.com<br>123-456-7890</td>
                    <td>Bengali · Banani</td>
                    <td>21 Aug</td>
                    <td>
                        <a href="#">Approve</a> · <a href="#">Reject</a>
                    </td>
                </tr>
            </table>
        </div>

        <div id="filter_box" class="box">
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
                <input type="text" placeholder="name or email">
            </div>

            <div id="filter_button" class="filter">
                <button type="submit">Filter</button>
            </div>
        </div>

        <div id="table_box_all_users" class="box">
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <tr>
                    <td>1</td>
                    <td>Shajmin</td>
                    <td>Restaurant</td>
                    <td>mezban@example.com</td>
                    <td>21 Aug</td>
                    <td>Active</td>
                    <td>
                        <a href="#">Suspend</a> · <a href="#">Reset Password</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>