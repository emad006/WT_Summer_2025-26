<?php
session_start();

include "../lib/dbConfig.php";
include "../lib/helperFunctions.php";

if (!empty($_SESSION["user_id"])) {
    redirectUser($_SESSION["role"]);
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // <-------- Validation Start -------->

    // Check if email is empty
    if (empty($_POST["email"])) {
        $errors[] = "Email is required.";
    } else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) { // Check if the email is valid
        $errors[] = "Invalid email format.";
    } else if (strlen($_POST["email"] > 150)) { // Check if the email is greater than 150 characters
        $errors[] = "Email cannot exceed 150 characters.";
    }

    // Check if password is empty
    if (empty($_POST["password"])) {
        $errors[] = "Password is required.";
    } else if (strlen($_POST["password"]) < 8) { // Check if the password is at least 8 characters long
        $errors[] = "Password must be at least 8 characters long.";
    } else if (strlen($_POST["password"]) > 255) { // Check if the password is less than 255 characters long
        $errors[] = "Password cannot exceed more than 255 characters long.";
    }

    // <-------- Validation End -------->


    // <-------- Query the Database -------->

    if (count($errors) === 0) {

        $stmt = mysqli_prepare($conn, "SELECT user_id, name, password, role, account_status FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $_POST["email"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $userRow = mysqli_fetch_assoc($result);

        // Check if database returned a user
        if (!$userRow) {
            $errors[] = "Email or password is incorrect.";
        } else {
            // Check if the password is correct
            if ($userRow["password"] !== $_POST["password"]) {
                $errors[] = "Email or password is incorrect.";
            } else { // Password is valid, proceeding
                switch ($userRow["account_status"]) {
                    case "active":
                        break;
                    case "pending":
                        $errors[] = "Your account is waiting for admin approval.";
                        break;
                    case "rejected":
                        $errors[] = "Your registration was rejected.";
                        break;
                    case "suspended":
                        $errors[] = "";
                        break;
                    case "deleted":
                        break;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>FoodRush - Login</title>
</head>

<body>
    <div id="mainArea">
        <h1 id="titleName">Login to FoodRush</h1>

        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors) ?></div>

        <form method="post">
            <div class="inputBlock">
                <label class="inputLabel">Email:</label>
                <br>
                <input type="email" name="email" class="inputField" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>" placeholder="Enter your email" required>
            </div>

            <div class="inputBlock">
                <label class="inputLabel">Password:</label>
                <br>
                <input type="password" name="password" class="inputField" value="<?php if (!empty($_POST['password'])) echo $_POST['password']; ?>" placeholder="Enter your password" required>
            </div>

            <div class="inputBlock">
                <input type="checkbox" name="rememberMe" <?php if (!empty($_POST['rememberMe'])) echo 'checked'; ?>><label id="rememberMeLabel">Remember me</label>
                <br>
                <button type="submit" id="submitBtn">Login</button>
                <br>
                <a href="../registration/registration.php" id="registerLink">Don't have an account? Register</a>
            </div>
        </form>
    </div>

</body>

</html>