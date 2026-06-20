<?php
require 'auth_check.php';
require 'db_connect.php';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $user_id = $_SESSION['user_id'];
    $photoPath = null;
 
    // Handle the optional photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
 
        $allowedExt = ['png', 'jpg', 'jpeg', 'pdf', 'docx'];
        $originalName = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
 
        if (in_array($ext, $allowedExt)) {
            $uploadDir = 'uploads/';
 
            // Create the uploads folder if it doesn't exist yet
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
 
            // Unique filename so two uploads never overwrite each other
            $newFileName = uniqid('report_', true) . '.' . $ext;
            $destination = $uploadDir . $newFileName;
 
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                $photoPath = $destination;
            }
        }
    }
 
    $stmt = $conn->prepare("INSERT INTO reports (user_id, title, description, location, photo_path, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("issss", $user_id, $title, $description, $location, $photoPath);
 
    if ($stmt->execute()) {
        header("Location: myreport.php?submitted=1");
    } else {
        header("Location: reportdamage.php?error=1");
    }
 
    $stmt->close();
    exit();
} else {
    // If someone visits this file directly without submitting the form
    header("Location: reportdamage.php");
    exit();
}
?>