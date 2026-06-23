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

// KEMASKINI: Ditambah 'proof_photo' ke dalam SELECT statement
$reportsStmt = $conn->prepare("
    SELECT reportID, title, description, location, status, DateReported, reject_reason, proof_photo
    FROM report
    WHERE userID = ?
    ORDER BY DateReported DESC
    LIMIT ? OFFSET ?
");

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
        .no-attachment {
            color: #7a869a;
            font-size: 0.8rem;
        }

        .reject-box {
            margin-top: 5px;
            font-size: 12px;
            color: #a83232;
            background: #ffe0e0;
            padding: 4px 8px;
            border-radius: 6px;
        }

        /* KEMASKINI: CSS untuk mencantikkan susunan gambar bukti */
        .task-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e0e4ed;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .task-thumb:hover {
            transform: scale(1.05);
        }
    </style>
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
        <a href="userdb.php" class="nav-item">Dashboard</a>
        <a href="reportdamage.php" class="nav-item">Report Damage</a>
        <a href="myreport.php" class="nav-item active">My Report</a>
        <a href="trackstatus.php" class="nav-item">Track Status</a>
    </nav>

    <div class="sidebar-spacer"></div>

    <a href="login.php" class="nav-item">Logout</a>
</aside>

<main class="main">

<header class="topbar">
    <h1 class="topbar-title"><em>MY</em> REPORT</h1>

    <div class="topbar-actions">
        <button class="icon-btn">🔔<span class="notif-dot"></span></button>
        <div class="avatar"><?php echo htmlspecialchars($initial); ?></div>
    </div>
</header>

<?php if (isset($_GET['submitted'])): ?>
    <p style="color:#28a745; font-weight:600;">Report submitted successfully!</p>
<?php endif; ?>

<section class="stat-cards">

    <div class="stat-card blue">
        <span>My Reports</span>
        <h2><?php echo $totalReports; ?></h2>
    </div>

    <div class="stat-card yellow">
        <span>Pending</span>
        <h2><?php echo $pending; ?></h2>
    </div>

    <div class="stat-card pink">
        <span>In Progress</span>
        <h2><?php echo $inProgress; ?></h2>
    </div>

    <div class="stat-card green">
        <span>Completed</span>
        <h2><?php echo $completed; ?></h2>
    </div>

</section>

<section class="table-card">

<div class="table-controls">
    <div>Show
        <select>
            <option>10</option>
            <option>25</option>
            <option>50</option>
        </select>
        entries
    </div>

    <input type="text" placeholder="Search">
</div>

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Issue</th>
    <th>Description</th>
    <th>Location</th>
    <th>Date</th>
    <th>Status</th>
    <th>Proof</th>
</tr>
</thead>

<tbody>

<?php if ($reportsResult->num_rows === 0): ?>
<tr>
    <td colspan="7" style="text-align:center;">No reports yet</td>
</tr>
<?php else: ?>

<?php while ($report = $reportsResult->fetch_assoc()): ?>
<tr>

<td><?php echo htmlspecialchars($report['reportID']); ?></td>
<td><?php echo htmlspecialchars($report['title']); ?></td>
<td><?php echo htmlspecialchars($report['description']); ?></td>
<td><?php echo htmlspecialchars($report['location']); ?></td>
<td><?php echo date('d M Y', strtotime($report['DateReported'])); ?></td>

<td>
<?php
$badgeClass = 'badge-pending';
if ($report['status'] === 'In Progress') $badgeClass = 'badge-inprogress';
if ($report['status'] === 'Completed') $badgeClass = 'badge-completed';
if ($report['status'] === 'Rejected') $badgeClass = 'badge-rejected';
?>

<span class="status-badge <?php echo $badgeClass; ?>">
    <?php echo htmlspecialchars($report['status']); ?>
</span>

<?php if ($report['status'] === 'Rejected' && !empty($report['reject_reason'])): ?>
    <div class="reject-box">
        ⚠️ <?php echo htmlspecialchars($report['reject_reason']); ?>
    </div>
<?php endif; ?>
</td>

<td>
<?php 
// KEMASKINI: Logik memaparkan gambar bukti yang dihantar oleh maintenance
if ($report['status'] === 'Completed' && !empty($report['proof_photo']) && file_exists($report['proof_photo'])): 
?>
    <img src="<?php echo htmlspecialchars($report['proof_photo']); ?>" class="task-thumb" alt="Proof" onclick="window.open(this.src, '_blank')">
<?php else: ?>
    <span class="no-attachment">—</span>
<?php endif; ?>
</td>

</tr>
<?php endwhile; ?>

<?php endif; ?>

</tbody>
</table>

</section>

</main>

</body>
</html>