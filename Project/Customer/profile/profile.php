<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/login/login.php");
    exit();
}

$errors = [];
$success = [];


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["submitBtn"])) {
        
        // <-------- Validation Start -------->

        // Name validation
        if (empty($_POST["name"])) { // Check if name is empty
            $errors[] = "Name is required.";
        } else if (strlen($_POST["name"]) < 2) { // Check if the name is at least 2 characters long
            $errors[] = "Name must be at least 2 characters long.";
        } else if (strlen($_POST["name"]) > 100) { // Check if the name is less than 100 characters long
            $errors[] = "Name cannot exceed more than 100 characters long.";
        }

        // Phone validation
        if (empty($_POST["phone"])) { // Check if phone is empty
            $errors[] = "Phone is required.";
        } else if (strlen($_POST["phone"]) < 10) { // Check if the phone is at least 10 characters long
            $errors[] = "Phone must be at least 10 characters long.";
        } else if (strlen($_POST["phone"]) > 20) { // Check if the phone is less than 20 characters long
            $errors[] = "Phone cannot exceed 20 characters long.";
        }

        // Address validation
        if (empty($_POST["addr"])) { // Check if address is empty
            $errors[] = "Address is required.";
        } else if (strlen($_POST["addr"]) < 10) { // Check if the address is at least 10 characters long
            $errors[] = "Address must be at least 10 characters long.";
        } else if (strlen($_POST["addr"]) > 255) { // Check if the address is less than 255 characters long
            $errors[] = "Address cannot exceed 255 characters long.";
        }

        // <-------- Validation End -------->

        if (count($errors) === 0) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, phone = ?, address = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $_POST["name"], $_POST["phone"], $_POST["addr"], $_SESSION["user_id"]);
            mysqli_stmt_execute($stmt);

            $_SESSION["name"] = $_POST["name"];
            $success[] = "Profile update successfully.";
        }

    } else if (isset($_POST["changePassBtn"])) {
        header("Location:../../Common/changePassword/changePassword.php");
        exit();

    } else if (isset($_POST["deleteBtn"])) {

        // Fetch password from database
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $userPassword = mysqli_fetch_assoc($result)["password"];

        // <-------- Validation Start -------->

        // Password validation
        if (empty($_POST["password"])) { // Check if password is empty
            $errors[] = "Password is required.";
        } else if (strlen($_POST["password"]) < 8) { // Check if the password is at least 8 characters long
            $errors[] = "Password must be at least 8 characters long.";
        } else if (strlen($_POST["password"]) > 255) { // Check if the password is less than 255 characters long
            $errors[] = "Password cannot exceed more than 255 characters long.";
        } else if ($_POST["password"] !== $userPassword) { // Check if the passwords match
            $errors[] = "Password is incorrect.";
        }

        // <-------- Validation End -------->

        if (count($errors) === 0) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET account_status = 'deleted', remember_token = NULL WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
            mysqli_stmt_execute($stmt);

            header("Location:../../Common/logout.php");
            exit();
        }
    }
}

// Get customer info
$stmt = mysqli_prepare($conn, "SELECT name, email, phone, address FROM users WHERE role = 'customer' AND user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userRow = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>Customer - Profile</title>
</head>

<body>
    <div id="navbar">
        <div id="navLeft">
            <a href="../dashboard/dashboard.php" class="navLink">Dashboard</a>
            <a href="#" class="navLink">Browse</a>
            <a href="#" class="navLink">Cart</a>
            <a href="#" class="navLink">My Orders</a>
            <a href="#" class="navLink navLinkActive">Profile</a>
            <a href="../../Common/logout.php" class="navLink">Logout</a>
        </div>

        <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
    </div>



    <div id="mainArea">
        <h1 id="titleName">My Profile</h1>

        <div id="successBlock"><?php if (!empty($success)) echo implode("<br>", $success); ?></div>
        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors); ?></div>

        <form method="post">
            <div class="inputBlock">
                <label class="inputLabel">Full Name</label>
                <br>
                <input type="text" name="name" class="inputField" value="<?php echo $userRow["name"]; ?>" placeholder="Enter your name">
            </div>

            <div class="inputBlock">
                <label class="inputLabel">Email</label>
                <br>
                <input type="email" name="email" class="inputField" value="<?php echo $userRow["email"]; ?>" disabled>
            </div>

            <div class="inputBlock">
                <label class="inputLabel">Phone</label>
                <br>
                <input type="text" name="phone" class="inputField" value="<?php echo $userRow["phone"]; ?>">
            </div>

            <div class="inputBlock">
                <label class="inputLabel">Delivery Address</label>
                <br>
                <textarea name="addr" class="inputField textAreaField" placeholder="Enter your delivery address"><?php echo $userRow["address"]; ?></textarea>
            </div>

            <div class="inputBlock">
                <button type="submit" id="submitBtn" name="submitBtn">Save Changes</button>
                <button type="submit" id="changePassBtn" name="changePassBtn">Change Password</button>
            </div>


            <hr>


            <h1 id="titleName">Delete My Account</h1>

            <div class="inputBlock">
                <label class="inputLabel">Password</label>
                <br>
                <input type="password" name="password" class="inputField" placeholder="Enter your password to confirm account deletion">
            </div>

            <div class="inputBlock">
                <button type="submit" id="deleteBtn" name="deleteBtn">Delete Account</button>
            </div>
        </form>
    </div>
</body>

</html>