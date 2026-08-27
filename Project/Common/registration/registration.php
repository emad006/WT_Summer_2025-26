<?php
session_start();

include "../lib/dbConfig.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // <-------- Validation Start -------->

    // Name validation
    if (empty($_POST["name"])) { // Check if name is empty
        $errors[] = "Name is required.";
    } else if (strlen($_POST["name"]) < 2) { // Check if the name is at least 2 characters long
        $errors[] = "Name must be at least 2 characters long.";
    } else if (strlen($_POST["name"]) > 100) { // Check if the name is less than 100 characters long
        $errors[] = "Name cannot exceed more than 100 characters long.";
    }

    // Email validation
    if (empty($_POST["email"])) { // Check if email is empty
        $errors[] = "Email is required.";
    } else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) { // Check if the email is valid
        $errors[] = "Invalid email format.";
    } else if (strlen($_POST["email"]) > 150) { // Check if the email is greater than 150 characters
        $errors[] = "Email cannot exceed 150 characters.";
    }

    // Password validation
    if (empty($_POST["password"])) { // Check if password is empty
        $errors[] = "Password is required.";
    } else if (strlen($_POST["password"]) < 8) { // Check if the password is at least 8 characters long
        $errors[] = "Password must be at least 8 characters long.";
    } else if (strlen($_POST["password"]) > 255) { // Check if the password is less than 255 characters long
        $errors[] = "Password cannot exceed more than 255 characters long.";
    } else if ($_POST["password"] !== $_POST["confPassword"]) { // Check if the passwords match
        $errors[] = "Passwords do not match.";
    }

    // Phone validation
    if (empty($_POST["phone"])) { // Check if phone is empty
        $errors[] = "Phone is required.";
    } else if (strlen($_POST["phone"]) < 10) { // Check if the phone is at least 10 characters long
        $errors[] = "Phone must be at least 10 characters long.";
    } else if (strlen($_POST["phone"]) > 20) { // Check if the phone is less than 20 characters long
        $errors[] = "Phone cannot exceed 20 characters long.";
    }

    // Address validation
    if (empty($_POST["addr"])) { // Check if address is empty
        $errors[] = "Address is required.";
    } else if (strlen($_POST["addr"]) < 10) { // Check if the address is at least 10 characters long
        $errors[] = "Address must be at least 10 characters long.";
    } else if (strlen($_POST["addr"]) > 255) { // Check if the address is less than 255 characters long
        $errors[] = "Address cannot exceed 255 characters long.";
    }

    // Selective validation based on selected role
    if ($_POST["role"] === "restaurant") {

        // Shop name validation
        if (empty($_POST["shopName"])) { // Check if shop name is empty
            $errors[] = "Shop name is required.";
        } else if (strlen($_POST["shopName"]) < 2) { // Check if the shop name is at least 2 characters long
            $errors[] = "Shop name must be at least 2 characters long.";
        } else if (strlen($_POST["shopName"]) > 120) { // Check if the shop name is less than 120 characters long
            $errors[] = "Shop name cannot exceed 120 characters long.";
        }

        // Cusine type validation
        if (empty($_POST["cusineType"])) {
            $errors[] = "Cusine type is required.";
        }
    } else if ($_POST["role"] === "rider") {

        // Vehicle type validation
        if (empty($_POST["vehicleType"])) { // Check if vehicle type is empty
            $errors[] = "Vehicle type is required.";
        }

        // NID number validation
        if (empty($_POST["nidNum"])) { // Check if nid number is empty
            $errors[] = "NID number is required.";
        } else if (strlen($_POST["nidNum"]) < 10) { // Check if the nid number is at least 10 characters long
            $errors[] = "NID number must be at least 10 characters long.";
        } else if (strlen($_POST["nidNum"]) > 30) { // Check if the nid number is less than 30 characters long
            $errors[] = "NID number cannot exceed 30 characters long.";
        }
    }

    // <-------- Validation End -------->

    // <-------- Query the Database -------->
    if (count($errors) === 0) {

        // Set role based on user type
        if ($_POST["role"] === "customer") {
            $status = "active";
        } else if ($_POST["role"] === "restaurant" || $_POST["role"] === "rider") {
            $status = "pending";
        }

        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Insert into "users"
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, phone, address, role, account_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssssss", $_POST["name"], $_POST["email"], $_POST["password"], $_POST["phone"], $_POST["addr"], $_POST["role"], $status);
            mysqli_stmt_execute($stmt);
            $newID = mysqli_insert_id($conn);

            // Check role of user and setup query
            if ($_POST["role"] === "customer") {
                $stmt = mysqli_prepare($conn, "INSERT INTO customers (user_id) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "i", $newID);
            } else if ($_POST["role"] === "restaurant") {
                $stmt = mysqli_prepare($conn, "INSERT INTO restaurants (user_id, shop_name, cuisine_id, is_open) VALUES (?, ?, ?, 1)");
                mysqli_stmt_bind_param($stmt, "isi", $newID, $_POST["shopName"], $_POST["cusineType"]);
            } else if ($_POST["role"] === "rider") {
                $stmt = mysqli_prepare($conn, "INSERT INTO riders (user_id, vehicle_type, nid, is_on_duty) VALUES (?, ?, ?, 1)");
                mysqli_stmt_bind_param($stmt, "iss", $newID, $_POST["vehicleType"], $_POST["nidNum"]);
            } else if ($_POST["role"] === "admin") {
                $stmt = mysqli_prepare($conn, "INSERT INTO admins (user_id) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "i", $newID);
            } else {
                throw new Exception("Invalid role.");
            }

            mysqli_stmt_execute($stmt);
            mysqli_commit($conn);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
            mysqli_rollback($conn);
        }

    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <title>FoodRush - Registration</title>
</head>

<body onload="toggleRoleFields()">
    <div id="mainArea">
        <h1 id="titleName">Create your Account</h1>

        <div id="errorBlock"><?php if (!empty($errors)) echo implode("<br>", $errors) ?></div>

        <form method="post">
            <div class="inputBlock">
                <label class="inputLabel">I am registering as</label>
                <div id="radioGroup"> <!-- FIXME: Fix fields defaulting to "Customer" on form submit. -->
                    <input type="radio" name="role" class="radioField" id="customerRoleSelector" value="customer" onchange="toggleRoleFields()" <?php if (!isset($_POST["role"]) || $_POST["role"] === "customer") echo "checked"; ?>>
                    <label class="radioText">Customer</label>

                    <input type="radio" name="role" class="radioField" id="restaurantRoleSelector" value="restaurant" onchange="toggleRoleFields()" <?php if (isset($_POST["role"]) && $_POST["role"] === "restaurant") echo "checked"; ?>>
                    <label class="radioText">Restaurant</label>

                    <input type="radio" name="role" class="radioField" id="riderRoleSelector" value="rider" onchange="toggleRoleFields()" <?php if (isset($_POST["role"]) && $_POST["role"] === "rider") echo "checked"; ?>>
                    <label class="radioText">Rider</label>
                </div>
            </div>



            <!-- #1: Full Name (Customer & Rider) -->
            <div class="inputBlock customerDiv riderDiv">
                <label class="inputLabel">Full Name</label>
                <br>
                <input type="text" name="name" class="inputField" value="<?php if (!empty($_POST['name'])) echo $_POST['name']; ?>" placeholder="Enter your full name">
            </div>

            <!-- #1: Owner Name (Restaurant) -->
            <div class="inputBlock restaurantDiv" style="display: none;">
                <label class="inputLabel">Owner Name</label>
                <br>
                <input type="text" name="name" class="inputField" value="<?php if (!empty($_POST['name'])) echo $_POST['name']; ?>" placeholder="Enter your full name">
            </div>




            <!-- #2: Email (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Email</label>
                <br>
                <input type="text" name="email" class="inputField" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>" placeholder="Enter your email">
            </div>

            <!-- #2: Shop Name (Restaurant) -->
            <div class="inputBlock restaurantDiv" style="display: none;">
                <label class="inputLabel">Shop Name</label>
                <br>
                <input type="text" name="shopName" class="inputField" value="<?php if (!empty($_POST['shopName'])) echo $_POST['shopName']; ?>" placeholder="Enter your shop name">
            </div>

            <!-- #2: Vehicle Type (Rider) -->
            <div class="inputBlock riderDiv" style="display: none;">
                <label class="inputLabel">Vehicle Type</label>
                <br>
                <select name="vehicleType">
                    <option value="">Select Vehicle</option>
                    <option value="Car">Car</option>
                    <option value="Motorcycle">Motorcycle</option>
                    <option value="Cycle">Cycle</option>
                    <option value="On Foot">On Foot</option>
                </select>
            </div>




            <!-- #3: Phone (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Phone</label>
                <br>
                <input type="text" name="phone" class="inputField" value="<?php if (!empty($_POST['phone'])) echo $_POST['phone']; ?>" placeholder="Enter your phone number">
            </div>

            <!-- #3: Cusine (Restaurant) -->
            <div class="inputBlock restaurantDiv" style="display: none;">
                <label class="inputLabel">Cusine</label>
                <br>
                <select name="cusineType">
                    <option value="">Select Cusine</option>
                    <?php
                    $stmt = mysqli_prepare($conn, "SELECT cuisine_id, cuisine_name FROM cuisines ORDER BY cuisine_name");
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row["cuisine_id"] . "'>" . $row["cuisine_name"] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- #3: NID Number (Rider) -->
            <div class="inputBlock riderDiv" style="display: none;">
                <label class="inputLabel">NID Number</label>
                <br>
                <input type="text" name="nidNum" class="inputField" value="<?php if (!empty($_POST['nidNum'])) echo $_POST['nidNum']; ?>" placeholder="Enter your NID number">
            </div>



            <!-- #4: Delivery Address (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Delivery Address</label>
                <br>
                <textarea name="addr" class="inputField textAreaField" value="<?php if (!empty($_POST['addr'])) echo $_POST['addr']; ?>" placeholder="Enter your delivery address"></textarea>
            </div>

            <!-- #4: Email Address (Restuarant & Rider) -->
            <div class="inputBlock restaurantDiv riderDiv" style="display: none;">
                <label class="inputLabel">Email</label>
                <br>
                <input type="text" name="email" class="inputField" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>" placeholder="Enter your email">
            </div>




            <!-- #5: Password (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Password</label>
                <br>
                <input type="password" name="password" class="inputField" value="<?php if (!empty($_POST['password'])) echo $_POST['password']; ?>" placeholder="Enter your password">
            </div>

            <!-- #5: Phone Number (Restaurant & Rider) -->
            <div class="inputBlock restaurantDiv riderDiv" style="display: none;">
                <label class="inputLabel">Phone</label>
                <br>
                <input type="text" name="phone" class="inputField" value="<?php if (!empty($_POST['phone'])) echo $_POST['phone']; ?>" placeholder="Enter your phone number">
            </div>




            <!-- #6: Confirm Password (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Confirm Password</label>
                <br>
                <input type="password" name="confPassword" class="inputField" value="<?php if (!empty($_POST['confPassword'])) echo $_POST['confPassword']; ?>" placeholder="Confirm your password">
            </div>

            <!-- #6: Shop Address (Restaurant) -->
            <div class="inputBlock restaurantDiv" style="display: none;">
                <label class="inputLabel">Shop Address</label>
                <br>
                <textarea name="addr" class="inputField textAreaField" value="<?php if (!empty($_POST['addr'])) echo $_POST['addr']; ?>" placeholder="Enter your shop address"></textarea>
            </div>

            <!-- #6: Home Address (Rider) -->
            <div class="inputBlock riderDiv" style="display: none;">
                <label class="inputLabel">Home Address</label>
                <br>
                <textarea name="addr" class="inputField textAreaField" value="<?php if (!empty($_POST['addr'])) echo $_POST['addr']; ?>" placeholder="Enter your delivery address"></textarea>
            </div>




            <!-- #7: Password (Restaurant & Rider) -->
            <div class="inputBlock restaurantDiv riderDiv" style="display: none;">
                <label class="inputLabel">Password</label>
                <br>
                <input type="password" name="password" class="inputField" value="<?php if (!empty($_POST['password'])) echo $_POST['password']; ?>" placeholder="Enter your password">
            </div>

            <!-- #8: Confirm Password (Restaurant & Rider) -->
            <div class="inputBlock restaurantDiv riderDiv" style="display: none;">
                <label class="inputLabel">Confirm Password</label>
                <br>
                <input type="password" name="confPassword" class="inputField" value="<?php if (!empty($_POST['confPassword'])) echo $_POST['confPassword']; ?>" placeholder="Confirm your password">
            </div>

            <div class="inputBlock">
                <button type="submit" id="submitBtn">Create Account</button>
                <br>
                <label id="customerAccNotice">Customer accounts are activated immediately</label>
                <br>
                <a href="../login/login.php" id="loginLink">Already registered? Login</a>
            </div>
        </form>
    </div>

</body>

</html>