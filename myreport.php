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

        /* ===== KEMASKINI: CSS untuk Image Popup Modal ===== */
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

        /* Styling tambahan untuk butang View Detail (sama seperti design admin) */
        .view-detail-btn {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .view-detail-btn:hover {
            background-color: #138496;
        }

        /* CSS asal modal dikekalkan daripada dashboard admin */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000;
            justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #fff; padding: 24px; border-radius: 12px;
            width: 500px; max-width: 90%; position: relative;
        }
        .modal-close {
            position: absolute; top: 15px; right: 20px;
            background: none; border: none; font-size: 24px; cursor: pointer;
        }
        .modal-detail-grid {
            display: grid; grid-template-columns: 1fr; gap: 12px; margin-top: 16px;
        }
        .modal-label { font-weight: bold; color: #555; display: block; font-size: 13px; }
        .modal-value { color: #333; font-size: 15px; }
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
    <th>Detail</th> </tr>
</thead>

<tbody>

<?php if ($reportsResult->num_rows === 0): ?>
<tr>
    <td colspan="8" style="text-align:center;">No reports yet</td> </tr>
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
// KEMASKINI: onclick sekarang memanggil fungsi openPhotoModal()
if ($report['status'] === 'Completed' && !empty($report['proof_photo']) && file_exists($report['proof_photo'])): 
?>
    <img src="<?php echo htmlspecialchars($report['proof_photo']); ?>" class="task-thumb" alt="Proof" onclick="openPhotoModal('<?php echo htmlspecialchars($report['proof_photo']); ?>')">
<?php else: ?>
    <span class="no-attachment">—</span>
<?php endif; ?>
</td>

<td>
    <button type="button" class="view-detail-btn"
        data-reportid="<?php echo htmlspecialchars($report['reportID']); ?>"
        data-title="<?php echo htmlspecialchars($report['title'] ?? ''); ?>"
        data-description="<?php echo htmlspecialchars($report['description'] ?? ''); ?>"
        data-location="<?php echo htmlspecialchars($report['location'] ?? ''); ?>"
        data-status="<?php echo htmlspecialchars($report['status']); ?>"
        data-date="<?php echo date('d M Y', strtotime($report['DateReported'])); ?>"
        data-rejectreason="<?php echo htmlspecialchars($report['reject_reason'] ?? ''); ?>">
        View Detail
    </button>
</td>

</tr>
<?php endwhile; ?>

<?php endif; ?>

</tbody>
</table>

</section>

</main>

<div id="detailModal" class="modal-overlay">
    <div class="modal-box">
        <button type="button" class="modal-close" id="modalCloseBtn">&times;</button>
        <h2 id="modalReportID">Report #</h2>
        <span id="modalStatusBadge" class="status-badge" style="display:inline-block; margin-bottom:15px;">Status</span>

        <div class="modal-detail-grid">
            <div class="modal-detail-item">
                <span class="modal-label">Issue</span>
                <span class="modal-value" id="modalTitle"></span>
            </div>
            <div class="modal-detail-item">
                <span class="modal-label">Description</span>
                <span class="modal-value" id="modalDescription"></span>
            </div>
            <div class="modal-detail-item">
                <span class="modal-label">Location</span>
                <span class="modal-value" id="modalLocation"></span>
            </div>
            <div class="modal-detail-item">
                <span class="modal-label">Date Reported</span>
                <span class="modal-value" id="modalDate"></span>
            </div>
            <div class="modal-detail-item" id="modalRejectSection" style="display:none;">
                <span class="modal-label" style="color:#a83232;">Reject Reason</span>
                <span class="modal-value" id="modalRejectReason" style="color:#a83232; font-weight:600;"></span>
            </div>
        </div>
    </div>
</div>

<div class="photo-modal-overlay" id="photoModalOverlay" onclick="closePhotoModal()">
    <div class="photo-modal-content">
        <img id="photoModalImg" src="" alt="Proof Full Size">
    </div>
</div>

<script>
    function openPhotoModal(src) {
        document.getElementById("photoModalImg").src = src;
        document.getElementById("photoModalOverlay").classList.add("active");
    }

    function closePhotoModal() {
        document.getElementById("photoModalOverlay").classList.remove("active");
        document.getElementById("photoModalImg").src = "";
    }

    // LOGIK UNTUK MODAL VIEW DETAIL USER
    const detailModal = document.getElementById("detailModal");
    const closeBtn = document.getElementById("modalCloseBtn");

    document.querySelectorAll(".view-detail-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("modalReportID").textContent = "Report #" + this.dataset.reportid;
            document.getElementById("modalTitle").textContent = this.dataset.title;
            document.getElementById("modalDescription").textContent = this.dataset.description;
            document.getElementById("modalLocation").textContent = this.dataset.location;
            document.getElementById("modalDate").textContent = this.dataset.date;

            const statusBadge = document.getElementById("modalStatusBadge");
            statusBadge.textContent = this.dataset.status;
            statusBadge.className = "status-badge";
            if (this.dataset.status === "Pending") statusBadge.classList.add("badge-pending");
            if (this.dataset.status === "In Progress") statusBadge.classList.add("badge-inprogress");
            if (this.dataset.status === "Completed") statusBadge.classList.add("badge-completed");
            if (this.dataset.status === "Rejected") statusBadge.classList.add("badge-rejected");

            // Tunjukkan sebab kena reject kalau status Rejected
            const rejectSection = document.getElementById("modalRejectSection");
            if (this.dataset.status === "Rejected" && this.dataset.rejectreason !== "") {
                document.getElementById("modalRejectReason").textContent = this.dataset.rejectreason;
                rejectSection.style.display = "block";
            } else {
                rejectSection.style.display = "none";
            }

            detailModal.classList.add("active");
        });
    });

    closeBtn.addEventListener("click", () => detailModal.classList.remove("active"));
    detailModal.addEventListener("click", (e) => {
        if (e.target === detailModal) detailModal.classList.remove("active");
    });
</script>

</body>
</html>