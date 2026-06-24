<?php
require 'auth_admin.php'; // Fail ini wajib ada session_start() & semakan role admin
require 'db_connect.php';

// Fetch stats counters from the singular 'report' table
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report");
$total_reports = mysqli_fetch_assoc($total_query)['total'] ?? 0;

$pending_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE status='Pending'");
$pending_reports = mysqli_fetch_assoc($pending_query)['total'] ?? 0;

$progress_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE status='In Progress'");
$progress_reports = mysqli_fetch_assoc($progress_query)['total'] ?? 0;

$completed_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE status='Completed'");
$completed_reports = mysqli_fetch_assoc($completed_query)['total'] ?? 0;

// Calculate percentages safely to avoid division by zero
$pending_pct = $total_reports > 0 ? round(($pending_reports / $total_reports) * 100) : 0;
$progress_pct = $total_reports > 0 ? round(($progress_reports / $total_reports) * 100) : 0;
$completed_pct = $total_reports > 0 ? round(($completed_reports / $total_reports) * 100) : 0;

// Count how many reports still need admin attention (not yet viewed, not yet assigned,
// and still Pending — Rejected/Completed reports don't need a notification)
// Used to drive the notification dot on the bell icon
$new_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE (is_viewed = 0 OR is_viewed IS NULL) AND (staffID IS NULL OR staffID = '') AND status = 'Pending'");
$new_reports_count = mysqli_fetch_assoc($new_query)['total'] ?? 0;

// Fetch top 5 recent reports sorted by your true primary column 'reportID'
$recent_reports = mysqli_query($conn, "SELECT * FROM report ORDER BY reportID DESC LIMIT 5");

// Extract dynamic avatar letter
$admin_name = $_SESSION['name'] ?? 'Admin';
$avatar_letter = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="FixIt_Logo.png" alt="FixIt Logo">
        </div>
        <button class="sidebar-menu-btn">
            <span></span><span></span><span></span>
        </button>
        <nav>
            <a href="dashboard.php" class="nav-item active">Dashboard</a>
            <a href="report-data.php" class="nav-item">Report Data</a>
            <a href="user-management.php" class="nav-item">User Management</a>
            <a href="assign-task.php" class="nav-item">Assign Task</a>
        </nav>
        <div class="sidebar-spacer"></div>
        <a href="logout.php" class="nav-item">Logout</a> </aside>

    <main class="main">
        <header class="topbar">
            <h1 class="topbar-title"><em>ADMIN</em> DASHBOARD</h1>
            <div class="topbar-actions">
                <button class="icon-btn">
                    🔔
                    <?php if ($new_reports_count > 0): ?>
                        <span class="notif-dot"></span>
                    <?php endif; ?>
                </button>
                <div class="avatar"><?php echo $avatar_letter; ?></div>
            </div>
        </header>

        <section class="stat-cards">
            <div class="stat-card blue">
                <div class="stat-info">
                    <span class="stat-label">Total Reports</span>
                    <span class="stat-value"><?php echo $total_reports; ?></span>
                </div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-info">
                    <span class="stat-label">Pending</span>
                    <span class="stat-value"><?php echo $pending_reports; ?></span>
                </div>
            </div>
            <div class="stat-card pink">
                <div class="stat-info">
                    <span class="stat-label">In Progress</span>
                    <span class="stat-value"><?php echo $progress_reports; ?></span>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-info">
                    <span class="stat-label">Completed</span>
                    <span class="stat-value"><?php echo $completed_reports; ?></span>
                </div>
            </div>
        </section>

        <section class="charts-row">
            <div class="card">
                <h3 class="card-title">Report Status Breakdown</h3>
                <canvas id="statusPieChart" style="max-height: 220px;"></canvas>
            </div>
            <div class="card">
                <h3 class="card-title">Report Status Overview</h3>
                <div class="status-summary">
                    <div class="legend-item"><span class="legend-dot yellow"></span>Pending (<?php echo $pending_pct; ?>%)</div>
                    <div class="legend-item"><span class="legend-dot pink"></span>In Progress (<?php echo $progress_pct; ?>%)</div>
                    <div class="legend-item"><span class="legend-dot green"></span>Completed (<?php echo $completed_pct; ?>%)</div>
                </div>
            </div>
        </section>

        <section class="table-card">
            <div class="table-controls">
                <div class="show-entries">
                    Show <select><option>5</option></select> entries
                </div>
                <div class="search-box">
                    <input type="text" id="dashboardSearch" placeholder="Search...">
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>reportID</th>
                        <th>Issue</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Assigned To</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="dashboardTable">
                    <?php while($row = mysqli_fetch_assoc($recent_reports)): ?>
                    <tr>
                        <td>#<?php echo $row['reportID']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['title'] ?? ''); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['staffID'] ?? 'Unassigned'); ?></td>
                        <td><?php echo isset($row['DateReported']) ? date('Y-m-d', strtotime($row['DateReported'])) : date('Y-m-d'); ?></td>
                        <td>
                            <?php 
                            $status = $row['status'] ?? 'Pending';
                            $badge_class = 'badge-pending';
                            if($status == 'In Progress') $badge_class = 'badge-inprogress';
                            if($status == 'Completed') $badge_class = 'badge-completed';
                            if($status == 'Rejected')    $badge_class = 'badge-rejected';
                            ?>
                            <span class="status-badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.getElementById("dashboardSearch").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            document.querySelectorAll("#dashboardTable tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
            });
        });

        const pendingCount = <?php echo $pending_reports; ?>;
        const progressCount = <?php echo $progress_reports; ?>;
        const completedCount = <?php echo $completed_reports; ?>;

        new Chart(document.getElementById('statusPieChart'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'In Progress', 'Completed'],
                datasets: [{
                    data: [pendingCount, progressCount, completedCount],
                    backgroundColor: ['#f5c842', '#3a92e5', '#7ecb7e'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>