<?php
function redirectUser($role) {
    if ($role === "customer") {
        // header("Location:customerDashboard.php");
        header("Location:../../Customer/dashboard/dashboard.php");
        exit();
    } else if ($role === "restaurant") {
        // header("Location:restaurantDashboard.php");
        header("Location:../dummyDashboard.php");
        exit();
    } else if ($role === "rider") {
        // header("Location:riderDashboard.php");
        header("Location:../dummyDashboard.php");
        exit();
    } else if ($role === "admin") {
        // header("Location:adminDashboard.php");
        header("Location:../dummyDashboard.php");
        exit();
    }
}
?>