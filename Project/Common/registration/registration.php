<?php
session_start();
// include "config.php";
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <title>FoodRush - Registration</title>
</head>

<body>
    <div id="mainArea">
        <h1 id="titleName">Create your Account</h1>

        <div id="errorBlock">
            <label>Hello World</label><br>
            <label>Hello World</label>
        </div>

        <div class="inputBlock">
            <label class="inputLabel">I am registering as</label>
            <div id="radioGroup">
                <input type="radio" name="role" class="radioField" value="Customer" onchange="toggleRoleFields()"><label class="radioText">Customer</label>
                <input type="radio" name="role" class="radioField" value="Restaurant" onchange="toggleRoleFields()"><label class="radioText">Restaurant</label>
                <input type="radio" name="role" class="radioField" value="Rider" onchange="toggleRoleFields()"><label class="radioText">Rider</label>
            </div>
        </div>



        <!-- #1: Full Name (Customer & Rider) -->
        <div class="inputBlock">
            <label class="inputLabel">Full Name</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your full name">
        </div>

        <!-- #1: Owner Name (Restaurant) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Owner Name</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your full name">
        </div>




        <!-- #2: Email (Customer) -->
        <div class="inputBlock">
            <label class="inputLabel">Email</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your email">
        </div>

        <!-- #2: Shop Name (Restuarant) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Shop Name</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your shop name">
        </div>

        <!-- #2: Vehicle Type (Rider) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Email</label>
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
        <div class="inputBlock">
            <label class="inputLabel">Phone</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your phone number">
        </div>

        <!-- #3: Cusine (Restaurant) -->
        <div class="inputBlock" style="display: none;">
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
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Phone</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your NID number">
        </div>



        <!-- #4: Delivery Address (Customer) -->
        <div class="inputBlock">
            <label class="inputLabel">Delivery Address</label>
            <br>
            <textarea class="inputField" id="textAreaField" placeholder="Enter your delivery address"></textarea>
        </div>

        <!-- #4: Email Address (Restuarant & Rider) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Email</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your email">
        </div>




        <!-- #5: Password (Customer) -->
        <div class="inputBlock">
            <label class="inputLabel">Password</label>
            <br>
            <input type="password" class="inputField" placeholder="Enter your password">
        </div>

        <!-- #5: Phone Number (Restaurant & Rider) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Phone</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your phone number">
        </div>



        <!-- #6: Confirm Password (Customer) -->
        <div class="inputBlock">
            <label class="inputLabel">Confirm Password</label>
            <br>
            <input type="password" class="inputField" placeholder="Confirm your password">
        </div>

        <!-- #6: Shop Address (Restaurant) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Shop Address</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your shop address">
        </div>

        <!-- #6: Home Address (Rider) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Home Address</label>
            <br>
            <input type="text" class="inputField" placeholder="Enter your home address">
        </div>

        <!-- #7: Password (Restaurant & Rider) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Password</label>
            <br>
            <input type="password" class="inputField" placeholder="Enter your password">
        </div>

        <!-- #8: Confirm Password (Restaurant & Rider) -->
        <div class="inputBlock" style="display: none;">
            <label class="inputLabel">Password</label>
            <br>
            <input type="password" class="inputField" placeholder="Confirm your password">
        </div>

        <div class="inputBlock">
            <button type="submit" id="submitBtn">Create Account</button>
            <br>
            <label id="customerAccNotice">Customer accounts are activated immediately</label>
            <br>
            <a href="login.php" id="loginLink">Already registered? Login</a>
        </div>
    </div>

</body>

</html>