<?php
require 'auth_check.php';
require 'db_connect.php';
 
$user_id = $_SESSION['user_id'];
$initial = strtoupper(substr($_SESSION['name'], 0, 1));
 
$report = null;
 
if (isset($_GET['id'])) {
    $report_id = (int)$_GET['id'];
 
    // Only fetch the report if it belongs to the logged-in user
    $stmt = $conn->prepare("SELECT report_id, title, description, location, status, photo_path, created_at FROM reports WHERE report_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $report_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $report = $result->fetch_assoc();
    $stmt->close();
}
 
// Stats (same as other pages, for the top cards)
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - Track Status</title>
 
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
 
<body>
 
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
 
<a href="myreport.php" class="nav-item">
    My Report
</a>
 
<a href="trackstatus.php" class="nav-item active">
    Track Status
</a>
        </nav>
 
        <div class="sidebar-spacer"></div>
 
        <a href="login.php" class="nav-item">
            Logout
        </a>
 
    </aside>
 
    <!-- Main -->
    <main class="main">
 
        <header class="topbar">
 
            <h1 class="topbar-title">
                <em>TRACK</em> STATUS
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
 
        <section class="add-report-section">
            <a href="reportdamage.php" style="text-decoration: none;">
                <button class="add-report-btn">
                    <span style="font-size: 20px; font-weight: 300;">+</span>
                     ADD REPORT DAMAGE
                </button>
            </a>
        </section>
 
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
 
        <section class="table-card">
 
            <?php if (!$report): ?>
 
                <p style="color:#7a869a;">
                    <?php echo isset($_GET['id']) ? "Report not found." : "Select a report from "; ?>
                    <?php if (!isset($_GET['id'])): ?><a href="myreport.php">My Report</a> to track its status.<?php endif; ?>
                </p>
 
            <?php else: ?>
 
                <div class="track-header">
 
                    <h2>Report #<?php echo str_pad($report['report_id'], 3, '0', STR_PAD_LEFT); ?></h2>
 
                    <?php
                        $badgeClass = 'progress';
                        if ($report['status'] === 'Completed') $badgeClass = 'completed';
                        if ($report['status'] === 'Pending') $badgeClass = 'pending';
                    ?>
                    <span class="status-badge <?php echo $badgeClass; ?>">
                        <?php echo htmlspecialchars($report['status']); ?>
                    </span>
 
                </div>
 
                <br>
 
                <h2>Location : </h2>
                <span class="status-badge progress">
                        <?php echo htmlspecialchars($report['location']); ?>
                    </span>
 
                <h2>Issue : </h2>
                <span class="status-badge progress">
                        <?php echo htmlspecialchars($report['title']); ?>
                    </span>
 
                <h2>Description : </h2>
                <span class="status-badge progress">
                        <?php echo htmlspecialchars($report['description']); ?>
                    </span>
 
                <br>
 
                <?php
                    // Work out which timeline steps are "completed" based on status
                    $statusOrder = ['Pending', 'In Progress', 'Completed'];
                    $currentIndex = array_search($report['status'], $statusOrder);
                ?>
 
                <div class="timeline">
 
                    <div class="timeline-item completed">
                        Report Submitted
                    </div>
 
                    <div class="timeline-item <?php echo ($currentIndex >= 0) ? ($report['status'] === 'Pending' ? 'active-status' : 'completed') : ''; ?>">
                        Under Review
                    </div>
 
                    <div class="timeline-item <?php echo ($report['status'] === 'In Progress') ? 'active-status' : ($currentIndex >= 2 ? 'completed' : ''); ?>">
                        In Progress
                    </div>
 
                    <div class="timeline-item <?php echo ($report['status'] === 'Completed') ? 'active-status' : ''; ?>">
                        Completed
                    </div>
 
                </div>
 
                <br>
 
                <?php if ($report['photo_path'] && file_exists($report['photo_path'])): ?>
                    <h3>Uploaded Photo</h3>
                    <?php
                        $ext = strtolower(pathinfo($report['photo_path'], PATHINFO_EXTENSION));
                    ?>
                    <?php if (in_array($ext, ['png', 'jpg', 'jpeg'])): ?>
                        <img src="<?php echo htmlspecialchars($report['photo_path']); ?>"
                             alt="Damage Image"
                             class="track-image">
                    <?php else: ?>
                        <p><a href="<?php echo htmlspecialchars($report['photo_path']); ?>" target="_blank">📄 View attached file</a></p>
                    <?php endif; ?>
                <?php endif; ?>
 
            <?php endif; ?>
 
        </section>
 
    </main>
 
</body>
</html>