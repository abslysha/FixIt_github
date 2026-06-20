<?php
require 'auth_admin.php';
require 'db_connect.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
    $technician = mysqli_real_escape_string($conn, $_POST['technician']);
    
    // Update the technician name and push status into "In Progress"
    $update_query = "UPDATE reports SET assigned_to='$technician', status='In Progress' WHERE id='$report_id'";
    
    if (mysqli_query($conn, $update_query)) {
        if (mysqli_affected_rows($conn) > 0) {
            $message = "<p style='color: green; margin-bottom: 15px;'>Task successfully assigned! Report status is now 'In Progress'.</p>";
        } else {
            $message = "<p style='color: red; margin-bottom: 15px;'>Report ID not found.</p>";
        }
    } else {
        $message = "<p style='color: red; margin-bottom: 15px;'>Error updating record: " . mysqli_error($conn) . "</p>";
    }
}

$admin_name = $_SESSION['name'] ?? 'Admin';
$avatar_letter = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - Assign Task</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo"><img src="FixIt_Logo.png" alt="FixIt Logo"></div>
    <button class="sidebar-menu-btn"><span></span><span></span><span></span></button>
    <nav>
        <a href="dashboard.php" class="nav-item">Dashboard</a>
        <a href="report-data.php" class="nav-item">Report Data</a>
        <a href="user-management.php" class="nav-item">User Management</a>
        <a href="assign-task.php" class="nav-item active">Assign Task</a>
    </nav>
    <div class="sidebar-spacer"></div>
    <a href="login.php" class="nav-item">Logout</a>
</aside>

<main class="main">
    <header class="topbar">
        <h1 class="topbar-title">ASSIGN TASK</h1>
        <div class="topbar-actions">
            <button class="icon-btn">🔔<span class="notif-dot"></span></button>
            <div class="avatar"><?php echo $avatar_letter; ?></div>
        </div>
    </header>

    <section class="table-card">
        <h3 style="margin-bottom:20px;">Assign Maintenance Task</h3>
        
        <?php echo $message; ?>

        <form class="assign-form" action="assign-task.php" method="POST">
            <div class="form-group">
                <label>Report ID (Numbers only, e.g., 1)</label>
                <input type="text" name="report_id" placeholder="Enter Report ID" required>
            </div>

            <div class="form-group">
                <label>Assign To</label>
                <select name="technician" required>
                    <option value="">Select Technician</option>
                    <option value="Ravi">Ravi</option>
                    <option value="Raj">Raj</option>
                    <option value="Priya">Priya</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">Assign Task</button>
        </form>
    </section>
</main>
</body>
</html>