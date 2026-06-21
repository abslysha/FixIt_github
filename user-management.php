<?php
require 'auth_admin.php';
require 'db_connect.php';

$users_query = mysqli_query($conn, "SELECT userID, name, email FROM user ORDER BY userID ASC");
$admin_name = $_SESSION['name'] ?? 'Admin';
$avatar_letter = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - User Management</title>
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
        <a href="user-management.php" class="nav-item active">User Management</a>
        <a href="assign-task.php" class="nav-item">Assign Task</a>
    </nav>
    <div class="sidebar-spacer"></div>
    <a href="login.php" class="nav-item">Logout</a>
</aside>

<main class="main">
    <header class="topbar">
        <h1 class="topbar-title">USER MANAGEMENT</h1>
        <div class="topbar-actions">
            <button class="icon-btn">🔔<span class="notif-dot"></span></button>
            <div class="avatar"><?php echo $avatar_letter; ?></div>
        </div>
    </header>

    <section class="table-card">
        <div class="table-controls">
            <div class="show-entries">
                Show <select><option>All</option></select> entries
            </div>
            <div class="search-box">
                <input type="text" id="userSearch" placeholder="Search User...">
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <?php while($user = mysqli_fetch_assoc($users_query)): ?>
                <tr>
                    <td><?php echo $user['userID']; ?></td>
                    <td><?php echo htmlspecialchars($user['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                    <td>Student / Staff</td>
                    <td>
                        <span class="status-badge badge-completed">Active</span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>

<script>
document.getElementById("userSearch").addEventListener("keyup", function(){
    let value = this.value.toLowerCase();
    document.querySelectorAll("#userTable tr").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});
</script>
</body>
</html>