<?php
include "config.php";
$success = $error = "";

function registerUser($conn, $username, $password, $email, $age, $role, $phone)
{
    $sql = "insert into users (username, password, email, age, role, phone) values ('$username', '$password', '$email', '$age', '$role', '$phone')";
    return mysqli_query($conn, $sql);
}

function listUsers($conn)
{
    $sql = "select username, email, age, role, phone, photo, photoType from users order by username";
    return mysqli_query($conn, $sql);
}

function searchUsers($conn, $searchTerm)
{
    $sql = "select username, email, age, role, phone, photo, photoType from users where username like '%$searchTerm%'";
    return mysqli_query($conn, $sql);
}

function updateUserPhone($conn, $userEmail, $newPhone)
{
    $sql = "update users set phone='$newPhone' where email='$userEmail'";
    return mysqli_query($conn, $sql);
}

function deleteUser($conn, $userEmail)
{
    $sql = "delete from users where email='$userEmail'";
    mysqli_query($conn, $sql);
    return mysqli_affected_rows($conn);
}

function uploadPhoto($conn, $userEmail, $photoData, $photoType)
{
    $sql = "update users set photo='$photoData', photoType='$photoType' where email='$userEmail'";
    return mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Register user
    if (isset($_POST["register"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];
        $age = $_POST["age"];
        $role = $_POST["role"];
        $phone = $_POST["phone"];

        if (empty($username) || empty($password) || empty($email) || empty($age) || empty($role) || empty($phone)) {
            $error = "Fill the form";
        } else {
            if (registerUser($conn, $username, $password, $email, $age, $role, $phone)) {
                $success = "Registration Complete";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }

    // List all users
    if (isset($_POST["list"])) {
        $listResult = listUsers($conn);
    }

    // Search users by username
    if (isset($_POST["search"])) {
        $searchTerm = $_POST["searchTerm"];
        $searchResult = searchUsers($conn, $searchTerm);
    }

    // Update phone number
    if (isset($_POST["update"])) {
        $updateEmail = $_POST["updateEmail"];
        $newPhone = $_POST["newPhone"];

        if (updateUserPhone($conn, $updateEmail, $newPhone)) {
            $success = "Record Updated";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }

    // Delete user by email
    if (isset($_POST["remove"])) {
        $removeEmail = $_POST["removeEmail"];
        $deletedRows = deleteUser($conn, $removeEmail);

        if ($deletedRows > 0) {
            $success = "Record Deleted";
        } else {
            $error = "No matching record found";
        }
    }

    // Upload profile photo (blob)
    if (isset($_POST["upload"])) {
        $uploadEmail = $_POST["uploadEmail"];
        $photoData = addslashes(file_get_contents($_FILES["photo"]["tmp_name"]));
        $imageProperties = getimagesize($_FILES["photo"]["tmp_name"]);
        $photoType = $imageProperties["mime"];

        if (uploadPhoto($conn, $uploadEmail, $photoData, $photoType)) {
            $success = "Photo Uploaded";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<html>

<head>
    <title>User Registration</title>
</head>

<center>

    <body>
        <hr>

        <h1>Register User</h1>
        <form method="post">
            Username: <input type="text" name="username" required><br><br>
            Password: <input type="password" name="password" required><br><br>
            Email: <input type="email" name="email" required><br><br>
            Age: <input type="number" name="age" required><br><br>
            Role: <select name="role" required>
                <option value="Customer">Customer</option>
                <option value="Employee">Employee</option>
                <option value="Manager">Manager</option>
            </select><br><br>
            Phone: <input type="text" name="phone" required><br><br>
            <input type="submit" name="register" value="Register">
        </form>

        <hr>

        <h1>List All Users</h1>
        <form method="post">
            <input type="submit" name="list" value="List Users">
        </form>
        <?php if (isset($listResult)) { ?>
            <table border="1">
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Age</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Photo</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($listResult)) { ?>
                    <tr>
                        <td><?php echo $row["username"]; ?></td>
                        <td><?php echo $row["email"]; ?></td>
                        <td><?php echo $row["age"]; ?></td>
                        <td><?php echo $row["role"]; ?></td>
                        <td><?php echo $row["phone"]; ?></td>
                        <td><?php if (!empty($row["photo"])) {
                                echo "<img src='data:" . $row["photoType"] . ";base64," . base64_encode($row["photo"]) . "' width='60'>";
                            } else {
                                echo "No Image";
                            } ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>

        <hr>

        <h1>Search Users</h1>
        <form method="post">
            Username: <input type="text" name="searchTerm" required><br><br>
            <input type="submit" name="search" value="Search">
        </form>
        <?php if (isset($searchResult)) { ?>
            <table border="1">
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Age</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Photo</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($searchResult)) { ?>
                    <tr>
                        <td><?php echo $row["username"]; ?></td>
                        <td><?php echo $row["email"]; ?></td>
                        <td><?php echo $row["age"]; ?></td>
                        <td><?php echo $row["role"]; ?></td>
                        <td><?php echo $row["phone"]; ?></td>
                        <td><?php if (!empty($row["photo"])) {
                                echo "<img src='data:" . $row["photoType"] . ";base64," . base64_encode($row["photo"]) . "' width='60'>";
                            } else {
                                echo "No Image";
                            } ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>

        <hr>

        <h1>Update Phone Number</h1>
        <form method="post">
            Email: <input type="email" name="updateEmail" required><br><br>
            New Phone: <input type="text" name="newPhone" required><br><br>
            <input type="submit" name="update" value="Update">
        </form>

        <hr>

        <h1>Delete User</h1>
        <form method="post">
            Email: <input type="email" name="removeEmail" required><br><br>
            <input type="submit" name="remove" value="Delete">
        </form>

        <hr>

        <h1>Upload Photo</h1>
        <form method="post" enctype="multipart/form-data">
            Email: <input type="email" name="uploadEmail" required><br><br>
            Photo: <input type="file" name="photo" required><br><br>
            <input type="submit" name="upload" value="Upload">
        </form>

        <hr>

        <p style="color:green;"><?php echo $success ?></p>
        <p style="color:red;"><?php echo $error ?></p>

        <hr>
    </body>
</center>

</html>