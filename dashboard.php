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

// FIXED: Removed the missing 'is_viewed' column reference to prevent SQL syntax crash
// Counts reports that are still Pending and do not have an assigned staff member yet
$new_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE (staffID IS NULL OR staffID = '') AND status = 'Pending'");
$new_reports_count = mysqli_fetch_assoc($new_query)['total'] ?? 0;

// Fetch ALL reports for the data table view, sorted descendingly by primary key
$all_reports = mysqli_query($conn, "SELECT * FROM report ORDER BY reportID DESC");

// Extract dynamic avatar letter
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
        <div class="sidebar-logo">
            <img src="FixIt_Logo.png" alt="FixIt Logo">
        </div>
        <button class="sidebar-menu-btn">
            <span></span><span></span><span></span>
        </button>
        <nav>
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="report-data.php" class="nav-item active">Report Data</a>
            <a href="user-management.php" class="nav-item">User Management</a>
            <a href="assign-task.php" class="nav-item">Assign Task</a>
        </nav>
        <div class="sidebar-spacer"></div>
        <a href="logout.php" class="nav-item">Logout</a>
    </aside>

    <main class="main">
        <header class="topbar">
            <h1 class="topbar-title"><em>REPORT</em> DATA</h1>
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

        <section class="table-card" style="margin-top: 24px;">
            <div class="table-controls">
                <div class="show-entries">
                    Show <select><option>All</option></select> entries
                </div>
                <div class="search-box">
                    <input type="text" id="reportSearch" placeholder="Search reports...">
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
                <tbody id="reportTable">
                    <?php if (mysqli_num_rows($all_reports) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($all_reports)): ?>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">No report records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        // Client-side search configuration
        document.getElementById("reportSearch").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            document.querySelectorAll("#reportTable tr").forEach(row => {
                // Ignore the placeholder empty row if it exists
                if(row.cells.length > 1) {
                    row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
                }
            });
        });
    </script>
</body>
</html>