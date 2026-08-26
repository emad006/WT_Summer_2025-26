<?php
session_start();
// include "config.php";

if (!empty($_SESSION["user_id"])) {
    header("Location:dashboard.php");
    exit();
}

// $errors = array();
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

        <div id="errorArea"></div>

        <div class="inputBlock">
            <label class="inputLabel">Email:</label>
            <br>
            <input type="email" class="inputField" placeholder="Enter your email">
        </div>

        <div class="inputBlock">
            <label class="inputLabel">Password:</label>
            <br>
            <input type="password" class="inputField" placeholder="Enter your password">
        </div>

        <div class="inputBlock">
            <input type="checkbox"><label id="rememberMeLabel">Remember me</label>
            <br>
            <button type="submit" id="submitBtn">Login</button>
            <br>
            <a href="register.php" id="registerLink">Don't have an account? Register</a>
        </div>
    </div>

</body>

</html>