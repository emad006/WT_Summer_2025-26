<?php
include "./lib/dbConfig.php";

session_start();

if (!empty($_SESSION["user_id"])) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = NULL WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);
} else if (!empty($_COOKIE["remember_token"])) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = NULL WHERE remember_token = ?");
    mysqli_stmt_bind_param($stmt, "s", $_COOKIE["remember_token"]);
    mysqli_stmt_execute($stmt);
}

session_unset();
session_destroy();
setcookie("remember_token", "", time() - 3600, "/");
header("Location:login/login.php");
exit();
?>