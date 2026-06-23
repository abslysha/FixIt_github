<?php
require 'auth_check.php';
require 'db_connect.php';
 
$userID = $_SESSION['user_id'];
$initial = strtoupper(substr($_SESSION['name'], 0, 1));
 
// Stats
$totalReports = 0;
$pending = 0;
$inProgress = 0;
$completed = 0;
 
$statsStmt = $conn->prepare("SELECT status, COUNT(*) as count FROM report WHERE userID = ? GROUP BY status");
$statsStmt->bind_param("s", $userID);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
 
while ($row = $statsResult->fetch_assoc()) {
    $totalReports += $row['count'];
    if ($row['status'] === 'Pending') $pending = $row['count'];
    if ($row['status'] === 'In Progress') $inProgress = $row['count'];
    if ($row['status'] === 'Completed') $completed = $row['count'];
}
$statsStmt->close();
 
// Pagination
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;
$totalPages = max(1, ceil($totalReports / $perPage));
 
$reportsStmt = $conn->prepare("SELECT reportID, title, description, location, status, DateReported, proof_photo FROM report WHERE userID = ? ORDER BY DateReported DESC LIMIT ? OFFSET ?");
$reportsStmt->bind_param("sii", $userID, $perPage, $offset);
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
    <style>
        .task-thumb {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e0e4ed;
            cursor: pointer;
        }
        .no-attachment {
            color: #7a869a;
            font-size: 0.8rem;
        }

        /* Photo Popup Modal */
        .photo-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.75);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .photo-modal-overlay.active {
            display: flex;
        }
        .photo-modal-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
        }
        .photo-modal-content img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 8px;
            display: block;
        }
        .photo-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }
    </style>
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
                        <th>Proof Photo</th>
                    </tr>
                </thead>
 
                <tbody>
 
                    <?php if ($reportsResult->num_rows === 0): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#7a869a;">No reports yet. <a href="reportdamage.php">Submit one now</a>.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($report = $reportsResult->fetch_assoc()): ?>
                            <?php $proof = $report['proof_photo'] ?? null; ?>
                            <tr>
                                <td onclick="window.location='trackstatus.php?id=<?php echo urlencode($report['reportID']); ?>'" style="cursor:pointer;"><?php echo htmlspecialchars($report['reportID']); ?></td>
                                <td onclick="window.location='trackstatus.php?id=<?php echo urlencode($report['reportID']); ?>'" style="cursor:pointer;"><?php echo htmlspecialchars($report['title']); ?></td>
                                <td onclick="window.location='trackstatus.php?id=<?php echo urlencode($report['reportID']); ?>'" style="cursor:pointer;"><?php echo htmlspecialchars($report['description']); ?></td>
                                <td onclick="window.location='trackstatus.php?id=<?php echo urlencode($report['reportID']); ?>'" style="cursor:pointer;"><?php echo htmlspecialchars($report['location']); ?></td>
                                <td onclick="window.location='trackstatus.php?id=<?php echo urlencode($report['reportID']); ?>'" style="cursor:pointer;"><?php echo date('d M Y', strtotime($report['DateReported'])); ?></td>
                                <td onclick="window.location='trackstatus.php?id=<?php echo urlencode($report['reportID']); ?>'" style="cursor:pointer;">
                                    <?php
                                        $badgeClass = 'badge-pending';
                                        if ($report['status'] === 'In Progress') $badgeClass = 'badge-inprogress';
                                        if ($report['status'] === 'Completed') $badgeClass = 'badge-completed';
                                        if ($report['status'] === 'Rejected') $badgeClass = 'badge-rejected';
                                    ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $report['status']; ?></span>
                                </td>
                                <td>
                                    <?php if ($report['status'] === 'Completed' && $proof && file_exists($proof)): ?>
                                        <img src="<?php echo htmlspecialchars($proof); ?>" class="task-thumb" alt="Proof of completion" onclick="openPhotoModal('<?php echo htmlspecialchars($proof); ?>')">
                                    <?php elseif ($report['status'] === 'Completed'): ?>
                                        <span class="no-attachment">No proof</span>
                                    <?php else: ?>
                                        <span class="no-attachment">—</span>
                                    <?php endif; ?>
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

    <!-- Photo Popup Modal -->
    <div class="photo-modal-overlay" id="photoModalOverlay" onclick="closePhotoModal(event)">
        <div class="photo-modal-content">
            <span class="photo-modal-close" onclick="closePhotoModal(event)">&times;</span>
            <img id="photoModalImg" src="" alt="Proof of completion full size">
        </div>
    </div>

    <script>
        function openPhotoModal(src) {
            document.getElementById("photoModalImg").src = src;
            document.getElementById("photoModalOverlay").classList.add("active");
        }

        function closePhotoModal(event) {
            if (event.target.id === "photoModalOverlay" || event.target.classList.contains("photo-modal-close")) {
                document.getElementById("photoModalOverlay").classList.remove("active");
                document.getElementById("photoModalImg").src = "";
            }
        }
    </script>
 
</body>
</html>