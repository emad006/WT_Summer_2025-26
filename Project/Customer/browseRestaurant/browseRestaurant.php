<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Kick out user if role isn't customer
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location:../../Common/logout.php");
    exit();
}

// Get cusine types
$stmt = mysqli_prepare($conn, "SELECT cuisine_id, cuisine_name FROM cuisines ORDER BY cuisine_name");
mysqli_stmt_execute($stmt);
$allCuisines = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title>Customer - Dashboard</title>
    </head>

    <body>
        <div id="navbar">
            <div id="navLeft">
                <a href="../dashboard/dashboard.php" class="navLink">Dashboard</a>
                <a href="browseRestaurant.php" class="navLink navLinkActive">Browse</a>
                <a href="#" class="navLink">Cart</a>
                <a href="#" class="navLink">My Orders</a>
                <a href="../profile/profile.php" class="navLink">Profile</a>
                <a href="../../Common/logout.php" class="navLink">Logout</a>
            </div>

            <div id="navRight"><?php echo $_SESSION['name'] . " · Customer"; ?></div>
        </div>



        <div id="mainArea">
            <h1 id="titleName">Browse Restaurants</h1>

            <form method="post">
                <div id="filterArea">
                    <div class="filterGroup">
                        <label>Search</label>
                        <br>
                        <input type="text" name="search" class="inputField" id="searchBox" value="<?php if (!empty($_POST['search'])) echo $_POST['search']; ?>">
                    </div>

                    <div class="filterGroup">
                        <label>Cusine</label>
                        <br>
                        <select name="cuisineType" class="inputField">
                            <option value="">Select Cusine</option>
                            <?php
                            while ($row = mysqli_fetch_assoc($allCuisines)) {
                                echo "<option value='" . $row["cuisine_id"] . "'>" . $row["cuisine_name"] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filterGroup">
                        <label>Search</label>
                        <br>
                        <select name="status" class="inputField">
                            <option value="">All</option>
                            <option value="1">Open</option>
                            <option value="0">Closed</option>
                        </select>
                    </div>

                    <div class="filterGroup">
                        <label>Sort By</label>
                        <br>
                        <select name="sortBy" class="inputField">
                            <option value="sortByName">Name (A-Z)</option>
                            <option value="sortByRating">Rating</option>
                        </select>
                    </div>

                    <div class="filterGroup">
                        <button type="submit" name="filterBtn" id="filterBtn">Apply Filter</button>
                    </div>
                </div>
            </form>

            <div>
                <label>Recently Viewed</label>
            </div>

            <div id="tableBlock">
                <table border="1">
                    <tr>
                        <th>Restaurant</th>
                        <th>Cusine</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>