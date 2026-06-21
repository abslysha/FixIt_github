<?php
// auth_check.php
// Put this in the same folder as your other PHP files.
// At the very top of every page that should require login, add:
//   require 'auth_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}
?>
