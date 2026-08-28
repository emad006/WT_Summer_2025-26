<?php
function redirectUser($role) {
    if ($role === "customer") {
        header("Location: ../customer/dummyDashboard.php");
        exit();
    } else if ($role === "restaurant") {
        header("Location: ../restaurant/dashboard.php");
        exit();
    } else if ($role === "rider") {
        header("Location: ../rider/dummyDashboard.php");
        exit();
    } else if ($role === "admin") {
        header("Location: ../admin/dummyDashboard.php");
        exit();
    }
}
?>
