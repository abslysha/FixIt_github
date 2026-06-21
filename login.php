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
    <title>FixIt - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #666; font-weight: 500; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .btn {
            width: 100%; padding: 12px; background: #007bff; border: none; color: white; 
            font-weight: 600; border-radius: 4px; cursor: pointer; font-size: 1rem;
        }
        .btn:hover { background: #0056b3; }
        .error-msg { color: #dc3545; font-weight: 600; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Login to FixIt</h2>
    
    <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="Enter your email">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>
        <button type="submit" class="btn">Log In</button>
    </form>
</div>

</body>
</html>