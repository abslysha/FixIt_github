<?php
// Force PHP to show errors on the screen if anything goes wrong
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';
 
$error = "";
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
 
    // Matching your exact ERD column and table layout names
    $roleTables = [
        'admin'       => ['table' => 'admin', 'idCol' => 'adminID'],
        'maintenance' => ['table' => 'maintenance', 'idCol' => 'staffID'], 
        'user'        => ['table' => 'user', 'idCol' => 'userID'],
    ];
 
    $foundUser = null;
    $foundRole = null;
 
    foreach ($roleTables as $role => $info) {
        $stmt = $conn->prepare("SELECT {$info['idCol']} as id, name, password FROM {$info['table']} WHERE email = ?");
        
        if ($stmt === false) {
            die("Database Error in table '{$info['table']}': " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
 
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            // Checking password safely
            if (password_verify($password, $row['password']) || $password === $row['password']) {
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="loginstyle.css">
</head>

<body>

    <div class="browser">

        <!-- LEFT PANEL -->

        <div class="left-panel">

            <div class="branding">

                <img src="FixIt_Logo.png"
                     alt="FixIt Logo"
                     class="logo">

                <div class="logo-text">

                    <h3>FixIt</h3>

                    <p>
                        FACULTY DAMAGE<br>
                        REPORTING SYSTEM
                    </p>

                </div>

            </div>

            <div class="tagline">

                Report and track<br>
                damage issues easily<br>
                at your faculty

            </div>

        
            <!-- Worker Illustration -->

            <img src="worker.png"
                 alt="Worker"
                 class="worker">

        </div>

        <!-- RIGHT PANEL -->

        <div class="right-panel">

            <h1>Welcome Back!</h1>

            <p class="subtitle">
                Please login to continue
            </p>

            <label>Email</label>

            <input type="email"
                   placeholder="Enter your email">

            <label>Password</label>

            <div class="password-wrapper">

                <input type="password"
                       id="password"
                       placeholder="Enter your password">

                <span class="eye"
                      onclick="togglePassword()">
                    👁
                </span>

            </div>

            <div class="options">

                <label class="remember">

                    <input type="checkbox">

                    Remember Me

                </label>

                <a href="#">
                    Forgot Password?
                </a>

            </div>

            <button class="login-btn">

                Login

            </button>

            <div class="register">

                Not registered yet?

                <a href="register.html">
                    Sign Up
                </a>

            </div>

        </div>

    </div>

    <script src="script.js"></script>

</body>

</html>