<?php
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Users</title>
</head>

<body>
    <div id="main_box">
        <h2>Users</h2>

        <div id="error_box" class="box">
        </div>

        <div id="status_box" class="box">
            <a href="#">Pending Approval (4)</a>
            <a href="#">All Users (214)</a>
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