function handleSubmit() {

    var name = document.getElementById("name").value;
    var age = document.getElementById("age").value;
    var email = document.getElementById("email").value;
    var phone = document.getElementById("phone").value;
    var password = document.getElementById("password").value;
    var confPassword = document.getElementById("confirmPassword").value;
    var message = document.getElementById("message").value;

    var valid = true;

    // Name validation
    var nameRegex = /^[A-Za-z\s]+$/;
    if (name.length < 3 || !nameRegex.test(name)) {
        alert("Name must be only letters, minimum 3 characters");
        return;
    }

    // Age validation
    if (isNaN(age) || age < 18) {
        alert("Age must be a number, 18 or above");
        return;
    }

    // Email validation
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("Enter a valid email address");
        return;
    }

    // Phone validation
    if (phone.length !== 10 || isNaN(phone)) {
        alert("Phone number must be exactly 10 digits");
        return;
    }

    // Password validation
    var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/;
    if (!passwordRegex.test(password)) {
        alert("Password needs 8+ characters, uppercase, lowercase, number and special character");
        return;
    }

    // Confirm Password validation
    if (password !== confPassword) {
        alert("Passwords do not match");
        return;
    }

    // Message validation
    if (message.length < 10) {
        alert("Message must be at least 10 characters");
        return false;
    }

    alert("Contact Information Successfully Submitted\n" +
        "Name: " + name + "\n" +
        "Age: " + age + "\n" +
        "Email: " + email + "\n" +
        "Phone: " + phone + "\n" +
        "Message: " + message
    );
}

function ToggleMode() {
    var body = document.body;
    var button = document.getElementById("switchBtn");

    if (body.style.backgroundColor == "black") {
        body.style.backgroundColor = "";
        body.style.color = "";
        button.innerHTML = "Switch to Dark Mode";
    }
    else {
        body.style.backgroundColor = "black";
        body.style.color = "white";
        button.innerHTML = "Switch to Light Mode";
    }
}

function startClock() {
    updateClock();
    setInterval(updateClock, 1000);
}

function updateClock() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();

    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    if (seconds < 10) {
        seconds = "0" + seconds;
    }

    document.getElementById("clock").innerHTML = "Current Time: " + hours + ":" + minutes + ":" + seconds;

    var greeting = "";
    if (hours < 12) {
        greeting = "Good Morning!";
    }
    else if (hours < 18) {
        greeting = "Good Afternoon!";
    }
    else {
        greeting = "Good Evening!";
    }

    document.getElementById("greeting").innerHTML = greeting;
}