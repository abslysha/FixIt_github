<?php
session_start();
include 'database.php';

$email = $_POST['email'];
$password = $_POST['password']; 

$stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['userID'];
    $_SESSION['name'] = $user['name'];
    header("Location: userdb.html");
} else {
    header("Location: login.html?error=1");
}

?>