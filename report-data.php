<?php
require 'auth_admin.php';
require 'db_connect.php';

$all_reports = mysqli_query($conn, "SELECT * FROM reports ORDER BY id DESC");
$total_count = mysqli_num_rows($all_reports);

$admin_name = $_SESSION['name'] ?? 'Admin';
$avatar_letter = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - Report Data</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo"><img src="FixIt_Logo.png" alt="FixIt Logo"></div>
        <button class="sidebar-menu-btn"><span></span><span></span><span></span></button>
        <nav>
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="report-data.php" class="nav-item active">Report Data</a>
            <a href="user-management.php" class="nav-item">User Management</a>
            <a href="assign-task.php" class="nav-item">Assign Task</a>
        </nav>
        <div class="sidebar-spacer"></div>
        <a href="login.php" class="nav-item">Logout</a>
    </aside>

    <main class="main">
        <header class="topbar">
            <h1 class="topbar-title">REPORT DATA</h1>
            <div class="topbar-actions">
                <button class="icon-btn">🔔<span class="notif-dot"></span></button>
                <div class="avatar"><?php echo $avatar_letter; ?></div>
            </div>
        </header>

        <section class="table-card">
            <div class="table-controls">
                <div class="show-entries">
                    Show <select id="entries"><option><?php echo $total_count; ?></option></select> entries
                </div>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search report...">
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>reportID</th>
                        <th>Issue</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="reportTable">
                    <?php while($row = mysqli_fetch_assoc($all_reports)): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['issue']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        <td>
                            <?php 
                            $badge_class = 'badge-pending';
                            if($row['status'] == 'In Progress') $badge_class = 'badge-inprogress';
                            if($row['status'] == 'Completed') $badge_class = 'badge-completed';
                            ?>
                            <span class="status-badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="table-footer">
                <span>Showing <?php echo $total_count; ?> of <?php echo $total_count; ?> entries</span>
            </div>
        </section>
    </main>

    <script>
        document.getElementById("searchInput").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            document.querySelectorAll("#reportTable tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
            });
        });
    </script>
</body>
</html>