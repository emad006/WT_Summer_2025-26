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