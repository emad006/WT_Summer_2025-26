<?php
session_start();

include "../db.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../../Common/login/login.php");
}

$error_message = "";

$rename_id = "";
$rename_name = "";

if (isset($_GET["rename_id"])) {
    $rename_id = $_GET["rename_id"];

    $sql = "SELECT cuisine_name FROM cuisines WHERE cuisine_id = $rename_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $rename_name = $row["cuisine_name"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["action_btn"])) {

        if ($_POST["action_btn"] == "Add Cusine") {
            $cusine_name = $_POST["cusine_name"];

            if (empty($cusine_name)) {
                $error_message = "Cusine name is required.";
            } else {
             
                $sql = "SELECT cuisine_id FROM cuisines WHERE cuisine_name = '$cusine_name'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);

                if ($row) {
                    $error_message = "That cusine already exists.";
                } else {
                    $sql = "INSERT INTO cuisines (cuisine_name) VALUES ('$cusine_name')";
                    mysqli_query($conn, $sql);
                    header("Location:cuisines.php");
                    exit();
                }
            }
        }

        if ($_POST["action_btn"] == "Save Name") {
            $cusine_id = $_POST["row_cusine_id"];
            $cusine_name = $_POST["cusine_name"];

            if (empty($cusine_name)) {
                $error_message = "Cusine name is required.";
            } else {
                $sql = "UPDATE cuisines SET cuisine_name = '$cusine_name' WHERE cuisine_id = $cusine_id";
                mysqli_query($conn, $sql);
                header("Location:cuisines.php");
                exit();
            }
        }

        if ($_POST["action_btn"] == "Remove") {
            $cusine_id = $_POST["row_cusine_id"];

            $sql = "SELECT COUNT(*) AS 'used_count' FROM restaurants WHERE cuisine_id = $cusine_id";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);

            if ($row["used_count"] > 0) {
                $error_message = "Cannot remove. " . $row["used_count"] . " restaurant(s) are still using this cusine.";
            } else {
                $sql = "DELETE FROM cuisines WHERE cuisine_id = $cusine_id";
                mysqli_query($conn, $sql);
                header("Location:cuisines.php");
                exit();
            }
        }
    }
}

$sql = "SELECT c.cuisine_id, c.cuisine_name, COUNT(res.user_id) AS 'used_count' FROM cuisines c LEFT JOIN restaurants res ON c.cuisine_id = res.cuisine_id GROUP BY c.cuisine_id, c.cuisine_name ORDER BY c.cuisine_id";
$cusine_result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Cusines</title>
</head>

<body>
    <div id="navigation_bar">
        <div id="left_nav">
            <a href="../dashboard/dashboard.php" class="navigation_link">Dashboard</a>
            <a href="../users/users.php" class="navigation_link">Users</a>
            <a href="cuisines.php" class="navigation_link active_link">Cuisines</a>
            <a href="../orders/orders.php" class="navigation_link">Orders</a>
            <a href="../reviews/reviews.php" class="navigation_link">Reviews</a>
            <a href="../profile/profile.php" class="navigation_link">Profile</a>
            <a href="../logout.php" class="navigation_link">Logout</a>
        </div>

        <div id="right_nav"><?php echo $_SESSION["name"]; ?> · Admin</div>
    </div>

    <div id="main_box">
        <h2>Cusine Categories</h2>

        <div id="error_box" class="box"><?php if (!empty($error_message)) echo $error_message; ?></div>

        <div id="table_box_cusines" class="box">
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Cusine</th>
                    <th>Restaurants Using It</th>
                    <th>Action</th>
                </tr>

                <?php
            
                while ($row = mysqli_fetch_assoc($cusine_result)) {
                    echo "<tr>";
                    echo "<td>" . $row["cuisine_id"] . "</td>";
                    echo "<td>" . $row["cuisine_name"] . "</td>";
                    echo "<td>" . $row["used_count"] . "</td>";

                    echo "<td>";

                    echo "<form method='get' style='display:inline;'>";
                    echo "<input type='hidden' name='rename_id' value='" . $row["cuisine_id"] . "'>";
                    echo "<input type='submit' class='action_btn rename_btn' value='Rename'>  ";
                    echo "</form>";

                    echo "<form method='post' style='display:inline;'>";
                    echo "<input type='hidden' name='row_cusine_id' value='" . $row["cuisine_id"] . "'>";
                    echo "<input type='submit' class='action_btn remove_btn' name='action_btn' value='Remove'>";
                    echo "</form>";

                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div id="form_box" class="box">
            <form method="post">
                <?php
           
                if (!empty($rename_id)) {
                    echo "<div id='form_title'>Rename Cusine</div>";
                    echo "Cusine name<br>";
                    echo "<input type='hidden' name='row_cusine_id' value='" . $rename_id . "'>";
                    echo "<input type='text' name='cusine_name' id='cusine_input' value='" . $rename_name . "'>";
                    echo "<br>";
                    echo "<input type='submit' id='form_btn' name='action_btn' value='Save Name'>";
                    echo "<a href='cuisines.php' id='cancel_link'>Cancel</a>";
                } else {
                    echo "<div id='form_title'>Add A Cusine</div>";
                    echo "Cusine name<br>";
                    echo "<input type='text' name='cusine_name' id='cusine_input' placeholder='e.g. Thai'>";
                    echo "<br>";
                    echo "<input type='submit' id='form_btn' name='action_btn' value='Add Cusine'>";
                }
                ?>
            </form>
        </div>
    </div>
</body>

</html>
