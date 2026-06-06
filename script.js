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