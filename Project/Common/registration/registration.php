<?php
session_start();

include "../lib/dbConfig.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <title>FoodRush - Registration</title>
</head>

<body>
    <div id="mainArea">
        <h1 id="titleName">Create your Account</h1>

        <div id="errorBlock">
            <label>Hello World</label><br>
            <label>Hello World</label>
        </div>

        <form method="post">
            <div class="inputBlock">
                <label class="inputLabel">I am registering as</label>
                <div id="radioGroup">
                    <input type="radio" name="role" class="radioField" id="customerRoleSelector" value="customer" onchange="toggleRoleFields()" checked><label class="radioText">Customer</label>
                    <input type="radio" name="role" class="radioField" id="restaurantRoleSelector" value="restaurant" onchange="toggleRoleFields()"><label class="radioText">Restaurant</label>
                    <input type="radio" name="role" class="radioField" id="riderRoleSelector" value="rider" onchange="toggleRoleFields()"><label class="radioText">Rider</label>
                </div>
            </div>



            <!-- #1: Full Name (Customer & Rider) -->
            <div class="inputBlock customerDiv riderDiv">
                <label class="inputLabel">Full Name</label>
                <br>
                <input type="text" name="name" class="inputField" placeholder="Enter your full name" required>
            </div>

            <!-- #1: Owner Name (Restaurant) -->
            <div class="inputBlock restaurantDiv" style="display: none;">
                <label class="inputLabel">Owner Name</label>
                <br>
                <input type="text" name="name" class="inputField" placeholder="Enter your full name" required>
            </div>




            <!-- #2: Email (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Email</label>
                <br>
                <input type="text" name="email" class="inputField" placeholder="Enter your email" required>
            </div>

            <!-- #2: Shop Name (Restaurant) -->
            <div class="inputBlock restaurantDiv" style="display: none;">
                <label class="inputLabel">Shop Name</label>
                <br>
                <input type="text" name="shopName" class="inputField" placeholder="Enter your shop name" required>
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
                <input type="text" name="phone" class="inputField" placeholder="Enter your phone number" required>
            </div>

            <!-- #3: Cusine (Restaurant) -->
            <div class="inputBlock restaurantDiv" style="display: none;">
                <label class="inputLabel">Cusine</label>
                <br>
                <select name="cusineType">
                    <option value="">Select Cusine</option>
                    <option value="Biriyani">Biriyani</option>
                    <option value="Fast Food">Fast Food</option>
                    <option value="Bengali">Bengali</option>
                    <option value="Italian">Italian</option>
                    <option value="Chinese">Chinese</option>
                </select>
            </div>

            <!-- #3: NID Number (Rider) -->
            <div class="inputBlock riderDiv" style="display: none;">
                <label class="inputLabel">Phone</label>
                <br>
                <input type="text" name="nidNum" class="inputField" placeholder="Enter your NID number" required>
            </div>



            <!-- #4: Delivery Address (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Delivery Address</label>
                <br>
                <textarea name="addr" class="inputField" id="textAreaField" placeholder="Enter your delivery address"></textarea>
            </div>

            <!-- #4: Email Address (Restuarant & Rider) -->
            <div class="inputBlock restaurantDiv riderDiv" style="display: none;">
                <label class="inputLabel">Email</label>
                <br>
                <input type="text" name="email" class="inputField" placeholder="Enter your email" required>
            </div>




            <!-- #5: Password (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Password</label>
                <br>
                <input type="password" name="password" class="inputField" placeholder="Enter your password" required>
            </div>

            <!-- #5: Phone Number (Restaurant & Rider) -->
            <div class="inputBlock restaurantDiv riderDiv" style="display: none;">
                <label class="inputLabel">Phone</label>
                <br>
                <input type="text" name="phone" class="inputField" placeholder="Enter your phone number" required>
            </div>




            <!-- #6: Confirm Password (Customer) -->
            <div class="inputBlock customerDiv">
                <label class="inputLabel">Confirm Password</label>
                <br>
                <input type="password" name="confPassword" class="inputField" placeholder="Confirm your password" required>
            </div>

            <!-- #6: Shop Address (Restaurant) -->
            <div class="inputBlock restaurantDiv" style="display: none;">
                <label class="inputLabel">Shop Address</label>
                <br>
                <textarea name="addr" class="inputField" id="textAreaField" placeholder="Enter your shop address"></textarea>
            </div>

            <!-- #6: Home Address (Rider) -->
            <div class="inputBlock riderDiv" style="display: none;">
                <label class="inputLabel">Home Address</label>
                <br>
                <textarea name="addr" class="inputField" id="textAreaField" placeholder="Enter your delivery address"></textarea>
            </div>




            <!-- #7: Password (Restaurant & Rider) -->
            <div class="inputBlock restaurantDiv riderDiv" style="display: none;">
                <label class="inputLabel">Password</label>
                <br>
                <input type="password" name="password" class="inputField" placeholder="Enter your password" required>
            </div>

            <!-- #8: Confirm Password (Restaurant & Rider) -->
            <div class="inputBlock restaurantDiv riderDiv" style="display: none;">
                <label class="inputLabel">Password</label>
                <br>
                <input type="password" name="confPassword" class="inputField" placeholder="Confirm your password" required>
            </div>

            <div class="inputBlock">
                <button type="submit" id="submitBtn">Create Account</button>
                <br>
                <label id="customerAccNotice">Customer accounts are activated immediately</label>
                <br>
                <a href="login.php" id="loginLink">Already registered? Login</a>
            </div>
    </div>
    </form>

</body>

</html>