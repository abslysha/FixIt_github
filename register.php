<?php
require 'db_connect.php';
 
$error = "";
$success = "";
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
 
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
 
        if ($result->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
 
            $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $name, $email, $hashedPassword, $role);
 
            if ($insert->execute()) {
                $success = "Account created! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt Register</title>
 
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
 
    <h1>Create Account</h1>
 
    <?php if ($error): ?>
        <p style="color:#e53e3e; font-size:13px; margin-bottom:10px;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
 
    <?php if ($success): ?>
        <p style="color:#28a745; font-size:13px; margin-bottom:10px;">
            <?php echo htmlspecialchars($success); ?> <a href="login.php">Go to Login</a>
        </p>
    <?php endif; ?>
 
    <form action="register.php" method="POST">
 
        <label>Full Name</label>
 
        <input
            type="text"
            name="name"
            placeholder="Enter your full name"
            required>
 
        <label>Email</label>
 
        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required>
 
        <label>Register as</label>
 
        <select id="roleDropdown" name="role" required>
        <option value="" disabled selected>Select Role</option>
        <option value="user">User</option>
        <option value="maintenance">Maintenance</option>
        <option value="admin">Administrator</option>
    </select>
 
        <label>Password</label>
 
        <div class="password-wrapper">
 
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required>
 
            <span class="eye"
                onclick="togglePassword()">
                👁
            </span>
 
        </div>
 
        <label>Confirm Password</label>
 
        <div class="password-wrapper">
 
            <input
                type="password"
                id="confirmPassword"
                name="confirmPassword"
                placeholder="Confirm your password"
                required>
 
            <span class="eye"
                onclick="toggleConfirmPassword()">
                👁
            </span>
 
        </div>
 
        <div class="remember-container">
 
            <input type="checkbox">
 
            <span>Remember Me</span>
 
        </div>
 
        <button
            type="submit"
            class="register-btn">
 
            Register
 
        </button>
 
        <div class="login-link">
 
            Already have an account?
 
            <a href="login.php">
                Login
            </a>
 
        </div>
 
    </form>
 
</div>
 
 
    <script src="script.js"></script>
 
</body>
 
</html>