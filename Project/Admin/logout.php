<?php
session_start();
session_unset();
session_destroy();
header("Location:../Common/login/login.php");
exit();
?>