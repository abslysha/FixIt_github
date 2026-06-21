<?php
require 'auth_admin.php';
require 'db_connect.php';

$message = "";

// Fetch staffID + name so we can save the correct ID, not just the name
$tech_list = mysqli_query($conn, "SELECT staffID, name FROM maintenance ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
    $technician = mysqli_real_escape_string($conn, $_POST['technician']); // this is now staffID

    // Updates assigned staffID and sets status to 'In Progress' inside your report table
    $update_query = "UPDATE report SET staffID='$technician', status='In Progress' WHERE reportID='$report_id'";

    if (mysqli_query($conn, $update_query)) {
        if (mysqli_affected_rows($conn) > 0) {
            $message = "<p style='color: #2ab5b5; font-weight:600; margin-bottom: 15px;'>Task successfully assigned! Report status changed to 'In Progress'.</p>";
        } else {
            $message = "<p style='color: #e53e3e; font-weight:600; margin-bottom: 15px;'>Report ID #$report_id not found in records.</p>";
        }
    } else {
        $message = "<p style='color: #e53e3e; font-weight:600; margin-bottom: 15px;'>Error executing query: " . mysqli_error($conn) . "</p>";
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
                <label>Report ID (Look up numbers in your reports log tab)</label>
                <input type="text" name="report_id" placeholder="Enter Report ID (e.g. 1)" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label>Assign To Technician</label>
                <select name="technician" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">Select Technician</option>
                    <?php if (mysqli_num_rows($tech_list) > 0): ?>
                        <?php while($tech = mysqli_fetch_assoc($tech_list)): ?>
                            <option value="<?php echo htmlspecialchars($tech['staffID']); ?>"><?php echo htmlspecialchars($tech['name']); ?></option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No technicians found</option>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="submit-btn" style="margin-top: 20px;">Assign Task</button>
        </form>
    </section>
</main>
</body>
</html>