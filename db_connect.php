<?php
// db_connect.php
// Save this in the SAME folder as your other PHP files.
// Every PHP page that needs the database starts with: require 'db_connect.php';

$host = "localhost";
$dbUsername = "root";   // default XAMPP username
$dbPassword = "";       // default XAMPP password is empty
$dbName = "fixit_db";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
