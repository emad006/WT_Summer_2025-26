<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_conn_demo";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
