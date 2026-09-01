<?php

if (!function_exists("e")) {
    function e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
    }
}

if (!function_exists("riderRedirect")) {
    function riderRedirect($path)
    {
        header("Location: " . $path);
        exit();
    }
}

if (!function_exists("requireRider")) {
    function requireRider()
    {
        if (empty($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "rider") {
            riderRedirect("../Common/login/login.php");
        }
    }
}

if (!function_exists("renderRiderNavbar")) {
    function renderRiderNavbar($active = "")
    {
        $links = [
            "dashboard" => ["Dashboard",       "riderDashboard.php"],
            "available" => ["Available",       "d_available.php"],
            "active"    => ["Active Delivery", "d_active.php"],
            "history"   => ["History",         "d_history.php"],
            "profile"   => ["Profile",         "../Common/changePassword/changePassword.php"],
            "logout"    => ["Logout",          "r_logout.php"]
        ];

        $name = isset($_SESSION["name"]) ? $_SESSION["name"] : "Rider";

        echo '<div id="navbar">';
        echo '<div id="navLeft">';
        foreach ($links as $key => $link) {
            $class = ($key === $active) ? "navLink navLinkActive" : "navLink";
            echo '<a href="' . e($link[1]) . '" class="' . $class . '">' . e($link[0]) . '</a>';
        }
        echo '</div>';
        echo '<div id="navRight">' . e($name) . ' &middot; Rider</div>';
        echo '</div>';
    }
}

if (!function_exists("riderIsOnDuty")) {
    function riderIsOnDuty($conn, $riderId)
    {
        $stmt = mysqli_prepare($conn, "SELECT is_on_duty FROM riders WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $riderId);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        return $row ? (int)$row["is_on_duty"] === 1 : false;
    }
}
