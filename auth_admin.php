<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in AND has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
?>