<?php

include "../Common/lib/dbConfig.php";

$sql = "SELECT * FROM restaurants";

$result = mysqli_query($conn,$sql);

if(!$result)
    {
        die("Query failed: " . mysqli_error($conn));
    }

while($row = mysqli_fetch_assoc($result))
    {
        echo $row['shop_name'] . "<br>";
    }
?>