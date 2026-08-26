let customerDivs = Array.from(document.getElementsByClassName("customerDiv"));
let restaurantDivs = Array.from(document.getElementsByClassName("restaurantDiv"));
let riderDivs = Array.from(document.getElementsByClassName("riderDiv"));


function toggleRoleFields() {
    const customer = document.getElementById("customerRoleSelector").checked;
    const restaurant = document.getElementById("restaurantRoleSelector").checked;
    const rider = document.getElementById("riderRoleSelector").checked;

    if (customer) {
        showCustomerFields();
    } else if (restaurant) {
        showRestaurantFields();
    } else if (rider) {
        showRiderFields();
    }
}

function showCustomerFields() {
    restaurantDivs.forEach(hideDiv);
    riderDivs.forEach(hideDiv);
    customerDivs.forEach(showDiv);
}

function showRestaurantFields() {
    customerDivs.forEach(hideDiv);
    riderDivs.forEach(hideDiv);
    restaurantDivs.forEach(showDiv);
}

function showRiderFields() {
    customerDivs.forEach(hideDiv);
    restaurantDivs.forEach(hideDiv);
    riderDivs.forEach(showDiv);
}

function showDiv(div) {
    div.style.display = "block";
    div.querySelectorAll("input, select, textarea").forEach(el => el.disabled = false);
}

function hideDiv(div) {
    div.style.display = "none";
    div.querySelectorAll("input, select, textarea").forEach(el => el.disabled = true);
}
