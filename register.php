<?php
include 'database.php';

if (isset($_POST['name'], $_POST['email'], $_POST['password'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Match table name and column names exactly
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);

    header("Location: login.html");
    exit();
} else {
    echo "Form data missing.";
}
?>
