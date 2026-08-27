<?php
session_start();
echo "<h1>Welcome to Dashboard</h1>";
echo "<h2>Hello" . $_SESSION["role"] . "</h2>";



// // 1. Unset all session variables
// $_SESSION = [];

// // 2. Delete the session cookie (if set)
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(session_name(), '', time() - 42000,
//         $params["path"], $params["domain"],
//         $params["secure"], $params["httponly"]
//     );
// }

// // 3. Destroy the session
// session_destroy();
?>