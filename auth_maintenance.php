<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Secure the page by checking for the lowercase 'maintenance' role set by your login script
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'maintenance') {
    header("Location: login.php");
    exit();
}
?>