<?php
require 'auth_check.php';
require 'db_connect.php';
require 'id_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $userID = $_SESSION['user_id'];
    $attachment = null;

    // Handle the optional photo/file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

        $allowedExt = ['png', 'jpg', 'jpeg', 'pdf', 'docx'];
        $originalName = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExt)) {
            $uploadDir = 'uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = uniqid('report_', true) . '.' . $ext;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                $attachment = $destination;
            }
        }
    }

    $reportID = generateNextId($conn, 'report', 'reportID', 'R');

    // ===== TEMPORARY DEBUG - remove after checking =====
    var_dump($userID);
    die();
    // ===== END TEMPORARY DEBUG =====

    $stmt = $conn->prepare("INSERT INTO report (reportID, userID, title, description, location, attachment, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("ssssss", $reportID, $userID, $title, $description, $location, $attachment);

    if ($stmt->execute()) {
        header("Location: myreport.php?submitted=1");
    } else {
        header("Location: reportdamage.php?error=1");
    }

    $stmt->close();
    exit();

} else {
    header("Location: reportdamage.php");
    exit();
}
?>