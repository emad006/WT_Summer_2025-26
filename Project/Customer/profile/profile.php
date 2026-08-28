<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/login/login.php");
    exit();
}

// Get customer info
$stmt = mysqli_prepare($conn, "SELECT name, email, phone, address, password FROM users WHERE role = 'customer' AND user_id = ?");
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

        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors) ?></div>

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
                <input type="text" name="phone" class="inputField" value="<?php echo $userRow['phone']; ?>">
            </div>

            <div class="inputBlock">
                <label class="inputLabel">Delivery Address</label>
                <br>
                <textarea name="addr" class="inputField textAreaField" placeholder="Enter your delivery address"><?php echo $userRow["address"]; ?></textarea>
            </div>

            <div class="inputBlock">
                <form method="post">
                    <button type="submit" id="submitBtn">Save Changes</button>
                    <button type="submit" id="changePassBtn">Change Password</button>
                </form>
            </div>


            <hr>


            <h1 id="titleName">Delete My Account</h1>

            <div class="inputBlock">
                <label class="inputLabel">Password</label>
                <br>
                <input type="password" name="password" class="inputField" placeholder="Enter your password to confirm account deletion">
            </div>

            <div class="inputBlock">
                <form method="post">
                    <button type="submit" id="deleteBtn">Delete Account</button>
                </form>
            </div>
        </form>
    </div>
</body>

</html>