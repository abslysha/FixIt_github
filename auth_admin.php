<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in AND has the admin role matching login.php
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>