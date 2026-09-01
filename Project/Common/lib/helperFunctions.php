<?php
function redirectUser($role) {
    if ($role === "customer") {
        // header("Location:customerDashboard.php");
        header("Location:../../Customer/dashboard/dashboard.php");
        exit();
    } else if ($role === "restaurant") {
        // header("Location:restaurantDashboard.php");
        header("Location:../../Restaurant/Dashboard/dashboard.php");
        exit();
    } else if ($role === "rider") {
        // header("Location:riderDashboard.php");
        header("Location:../../Rider/riderDashboard.php");
        exit();
    } else if ($role === "admin") {
        // header("Location:adminDashboard.php");
        header("Location:../../Admin/dashboard/dashboard.php");
        exit();
    }
}
?>