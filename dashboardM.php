<?php
require 'auth_maintenance.php';
require 'db_connect.php';

$tech_name = $_SESSION['name'] ?? '';

// Fetch stats counters assigned specifically to this technician out of the 'report' table
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE assigned_to = '$tech_name'");
$total_tasks = mysqli_fetch_assoc($total_query)['total'] ?? 0;

$pending_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE assigned_to = '$tech_name' AND status='Pending'");
$pending_tasks = mysqli_fetch_assoc($pending_query)['total'] ?? 0;

$progress_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE assigned_to = '$tech_name' AND status='In Progress'");
$progress_tasks = mysqli_fetch_assoc($progress_query)['total'] ?? 0;

$completed_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE assigned_to = '$tech_name' AND status='Completed'");
$completed_tasks = mysqli_fetch_assoc($completed_query)['total'] ?? 0;

// Fetch top 5 recent tasks assigned to this technician
$recent_tasks = mysqli_query($conn, "SELECT * FROM report WHERE assigned_to = '$tech_name' ORDER BY reportID DESC LIMIT 5");

$avatar_letter = strtoupper(substr($tech_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - Maintenance Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="maintanance.css">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="FixIt_Logo.png" alt="FixIt Logo" onerror="this.style.display='none';this.parentElement.innerHTML='<span style=\'color:white;font-weight:700;font-size:18px;\'>Fi</span>'">
        </div>
        <nav>
            <a href="dashboardM.php" class="nav-item active">
                <i class="ti ti-layout-dashboard" style="font-size:20px;display:block;margin-bottom:4px;"></i>
                Dashboard
            </a>
            <a href="my-taskM.php" class="nav-item">
                <i class="ti ti-checklist" style="font-size:20px;display:block;margin-bottom:4px;"></i>
                My Task
            </a>
        </nav>
        <div class="sidebar-spacer"></div>
        <a href="login.php" class="nav-item">
            <i class="ti ti-logout" style="font-size:20px;display:block;margin-bottom:4px;"></i>
            Logout
        </a>
    </aside>

    <main class="main">
        <header class="topbar">
            <h1 class="topbar-title">MAINTENANCE DASHBOARD</h1>
            <div class="topbar-actions">
                <button class="icon-btn"><i class="ti ti-bell"></i></button>
                <div class="avatar"><?php echo $avatar_letter; ?></div>
            </div>
        </header>

        <section class="stat-cards">
            <div class="stat-card blue">
                <div class="stat-info">
                    <span class="stat-label">Total Assigned</span>
                    <span class="stat-value"><?php echo $total_tasks; ?></span>
                </div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-info">
                    <span class="stat-label">Pending</span>
                    <span class="stat-value"><?php echo $pending_tasks; ?></span>
                </div>
            </div>
            <div class="stat-card pink">
                <div class="stat-info">
                    <span class="stat-label">In Progress</span>
                    <span class="stat-value"><?php echo $progress_tasks; ?></span>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-info">
                    <span class="stat-label">Completed</span>
                    <span class="stat-value"><?php echo $completed_tasks; ?></span>
                </div>
            </div>
        </section>

        <div class="table-card">
            <h3 style="margin-bottom:15px; font-family:'DM Sans'; color:#2d3748;">Recent Assigned Tasks</h3>
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
                <tbody>
                    <?php if(mysqli_num_rows($recent_tasks) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($recent_tasks)): ?>
                        <tr>
                            <td>#<?php echo $row['reportID']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['issue'] ?? ''); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['assigned_to'] ?? ''); ?></td>
                            <td><?php echo isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : date('Y-m-d'); ?></td>
                            <td>
                                <?php 
                                $status = $row['status'] ?? 'Pending';
                                $badge_class = 'badge-pending';
                                if($status == 'In Progress') $badge_class = 'badge-inprogress';
                                if($status == 'Completed') $badge_class = 'badge-completed';
                                ?>
                                <span class="status-badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;color:#7a869a;padding:2rem;">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>