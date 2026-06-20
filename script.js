function togglePassword(){

    const password =
    document.getElementById("password");

    if(password.type === "password"){
        password.type = "text";
    }
    else{
        password.type = "password";
    }
}

function togglePassword(){

    const password =
    document.getElementById("password");

    password.type =
    password.type === "password"
    ? "text"
    : "password";
}

function toggleConfirmPassword(){

    const password =
    document.getElementById("confirmPassword");

    password.type =
    password.type === "password"
    ? "text"
    : "password";
}

document.getElementById('registerForm').addEventListener('submit', function(event) {
    
    event.preventDefault();

    const selectedRole = document.getElementById('roleDropdown').value;

    if (!selectedRole) {
        alert("Please select a role before registering.");
        return;
    }

    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return;
    }

    switch (selectedRole) {
        case 'user':
            window.location.href = 'userdb.php'; 
            break;
        case 'maintenance':
            window.location.href = 'dashboardM.php'; 
            break;
        case 'admin':
            window.location.href = 'dashboard.php'; 
            break;
        default:
            alert("An error occurred. Please try again.");
    }
});
