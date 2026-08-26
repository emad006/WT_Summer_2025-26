<?php
session_start();
include "config.php";

if (!empty($_SESSION["user_id"])) {
    header("Location:dashboard.php");
    exit();
}

$errors = array();
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
            <label>Email:</label>
            <br>
            <input type="email" placeholder="Enter your email">
        </div>

        <div class="inputBlock">
            <label>Password:</label>
            <br>
            <input type="password" placeholder="Enter your password">
        </div>

        <input type="checkbox" id="rememberMe">Keep me signed in
        <br>
        <button type="submit">Login</button>
        <br>
        <a href="register.php">Don't have an account? Register</a>
    </div>

</body>

</html>