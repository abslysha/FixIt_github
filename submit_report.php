<?php
session_start();
include 'database.php';

$desciption = $_POST['description'];
$location = $_POST['location'];
$user_id = $_SESSION['user_id'];
$attachment = null;

if (!empty($_FILES['photo']['name'])) {
    $uploadDir = 'uploads/';
    $attachment = $uploadDir . basename($_FILES['photo']['name']);
    move_uploaded_file($_FILES['photo']['tmp_name'], $attachment);
}

$stmt = $conn->prepare("INSERT INTO report (description, location, attachment, userID, status) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$desciption, $location, $attachment, $user_id, 'pending']);

header("Location: userdb.html");
?>