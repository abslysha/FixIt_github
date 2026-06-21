<?php
session_start();
require 'db_connect.php';
 
$error = "";
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
 
    // Double-check your database table and column names here!
    $roleTables = [
        'admin'       => ['table' => 'admin', 'idCol' => 'adminID'],
        'maintenance' => ['table' => 'maintenance', 'idCol' => 'staffID'], // Is it staffID or maintenanceID?
        'user'        => ['table' => 'user', 'idCol' => 'userID'],
    ];
 
    $foundUser = null;
    $foundRole = null;
 
    foreach ($roleTables as $role => $info) {
        $stmt = $conn->prepare("SELECT {$info['idCol']} as id, name, password FROM {$info['table']} WHERE email = ?");
        
        // Error Handler: If the statement fails to prepare, print the MySQL error immediately
        if ($stmt === false) {
            die("Database Error in table '{$info['table']}': " . $conn->error . " (Check if your column names match exactly!)");
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
 
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $foundUser = $row;
                $foundRole = $role;
                $stmt->close();
                break;
            }
        }
        $stmt->close();
    }
 
    if ($foundUser) {
        $_SESSION['user_id'] = $foundUser['id'];
        $_SESSION['name'] = $foundUser['name'];
        $_SESSION['role'] = $foundRole;
 
        if ($foundRole === 'admin') {
            header("Location: dashboard.php");
        } elseif ($foundRole === 'maintenance') {
            header("Location: dashboardM.php");
        } else {
            header("Location: userdb.php");
        }
        exit();
    } else {
        $error = "Incorrect email or password.";
    }
}
?>