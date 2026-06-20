<?php
session_start();
include 'database.php'; // $conn must be a PDO connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['email'], $_POST['password'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Query the correct table
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] === 'maintenance') {
                header("Location: maintenance_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            header("Location: login.php?error=1");
            exit();
        }
    } else {
        echo "Form data missing.";
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

                <a href="register.php">
                    Sign Up
                </a>

            </div>

        </div>

    </div>

    <script src="script.js"></script>

</body>

</html>