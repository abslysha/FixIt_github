<?php
require 'auth_check.php';
require 'db_connect.php';
 
$user_id = $_SESSION['user_id'];
$initial = strtoupper(substr($_SESSION['name'], 0, 1));
 
// Stats
$totalReports = 0;
$pending = 0;
$inProgress = 0;
$completed = 0;
 
$statsStmt = $conn->prepare("SELECT status, COUNT(*) as count FROM reports WHERE user_id = ? GROUP BY status");
$statsStmt->bind_param("i", $user_id);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
 
while ($row = $statsResult->fetch_assoc()) {
    $totalReports += $row['count'];
    if ($row['status'] === 'Pending') $pending = $row['count'];
    if ($row['status'] === 'In Progress') $inProgress = $row['count'];
    if ($row['status'] === 'Completed') $completed = $row['count'];
}
$statsStmt->close();
 
// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;
$totalPages = max(1, ceil($totalReports / $perPage));
 
// Reports for this page
$reportsStmt = $conn->prepare("SELECT report_id, title, description, location, status, created_at FROM reports WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$reportsStmt->bind_param("iii", $user_id, $perPage, $offset);
$reportsStmt->execute();
$reportsResult = $reportsStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - My Report</title>
 
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
 
<body>
 
    <!-- Sidebar -->
    <aside class="sidebar">
 
        <div class="sidebar-logo">
            <img src="FixIt_Logo.png" alt="FixIt Logo">
 
        </div>
 
        <button class="sidebar-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>
 
        <nav>
 
        <a href="userdb.php" class="nav-item">
    Dashboard
</a>
 
<a href="reportdamage.php" class="nav-item">
    Report Damage
</a>
 
<a href="myreport.php" class="nav-item active">
    My Report
</a>
 
<a href="trackstatus.php" class="nav-item">
    Track Status
</a>
 
        </nav>
 
        <div class="sidebar-spacer"></div>
 
        <a href="login.php" class="nav-item">
    Logout
</a>
 
    </aside>
 
    <!-- Main Content -->
    <main class="main">
 
        <header class="topbar">
 
            <h1 class="topbar-title">
                <em>MY</em> REPORT
            </h1>
 
            <div class="topbar-actions">
 
                <button class="icon-btn">
                    🔔
                    <span class="notif-dot"></span>
                </button>
 
                <div class="avatar">
                    <?php echo htmlspecialchars($initial); ?>
                </div>
 
            </div>
 
        </header>
 
        <?php if (isset($_GET['submitted'])): ?>
            <p style="color:#28a745; margin-bottom:16px; font-weight:600;">Your report was submitted successfully!</p>
        <?php endif; ?>
 
        <!-- Statistics -->
        <section class="stat-cards">
 
            <div class="stat-card blue">
                <div class="stat-info">
                    <span class="stat-label">My Reports</span>
                    <span class="stat-value"><?php echo $totalReports; ?></span>
                </div>
            </div>
 
            <div class="stat-card yellow">
                <div class="stat-info">
                    <span class="stat-label">Pending</span>
                    <span class="stat-value"><?php echo $pending; ?></span>
                </div>
            </div>
 
            <div class="stat-card pink">
                <div class="stat-info">
                    <span class="stat-label">In Progress</span>
                    <span class="stat-value"><?php echo $inProgress; ?></span>
                </div>
            </div>
 
            <div class="stat-card green">
                <div class="stat-info">
                    <span class="stat-label">Completed</span>
                    <span class="stat-value"><?php echo $completed; ?></span>
                </div>
            </div>
 
        </section>
 
        <!-- Report Table -->
        <section class="table-card">
 
            <div class="table-controls">
 
                <div class="show-entries">
                    Show
                    <select>
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                    entries
                </div>
 
                <div class="search-box">
                    <input type="text" placeholder="Search">
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
 
                <tbody>
 
                    <?php if ($reportsResult->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#7a869a;">No reports yet. <a href="reportdamage.php">Submit one now</a>.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($report = $reportsResult->fetch_assoc()): ?>
                            <tr onclick="window.location='trackstatus.php?id=<?php echo $report['report_id']; ?>'" style="cursor:pointer;">
                                <td>#<?php echo str_pad($report['report_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($report['title']); ?></td>
                                <td><?php echo htmlspecialchars($report['description']); ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td><?php echo date('d M Y', strtotime($report['created_at'])); ?></td>
                                <td>
                                    <?php
                                        $badgeClass = 'badge-pending';
                                        if ($report['status'] === 'In Progress') $badgeClass = 'badge-inprogress';
                                        if ($report['status'] === 'Completed') $badgeClass = 'badge-completed';
                                    ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $report['status']; ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
 
                </tbody>
 
            </table>
 
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
 
                <a href="?page=<?php echo max(1, $page - 1); ?>"><button <?php echo ($page === 1) ? 'disabled' : ''; ?>>&lt;</button></a>
 
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>">
                        <button class="<?php echo ($i === $page) ? 'active-page' : ''; ?>"><?php echo $i; ?></button>
                    </a>
                <?php endfor; ?>
 
                <a href="?page=<?php echo min($totalPages, $page + 1); ?>"><button <?php echo ($page === $totalPages) ? 'disabled' : ''; ?>>&gt;</button></a>
 
            </div>
            <?php endif; ?>
 
        </section>
 
    </main>
 
</body>
</html>