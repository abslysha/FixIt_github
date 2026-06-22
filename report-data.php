<?php
require 'auth_admin.php';
require 'db_connect.php';

// JOIN dengan table user untuk dapatkan nama dan phone
$all_reports = mysqli_query($conn, "
    SELECT r.*, u.name AS user_name, u.phone AS user_phone, u.email AS user_email
    FROM report r
    LEFT JOIN user u ON r.userID = u.userID
    ORDER BY r.reportID DESC
");
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
            <h1 class="topbar-title">REPORT DATA</h1>
            <div class="topbar-actions">
                <button class="icon-btn">🔔<span class="notif-dot"></span></button>
                <div class="avatar"><?php echo $avatar_letter; ?></div>
            </div>
        </header>

        <section class="table-card">
            <div class="table-controls">
                <div class="show-entries">
                    Show 
                    <select id="entriesSelect">
                        <option value="<?php echo $total_count; ?>">All (<?php echo $total_count; ?>)</option>
                    </select> 
                    entries
                </div>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search report...">
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Issue</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody id="reportTable">
                    <?php if($total_count > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($all_reports)): ?>
                        <tr>
                            <td>#<?php echo $row['reportID']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title'] ?? ''); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                            <td><?php echo isset($row['DateReported']) ? date('Y-m-d', strtotime($row['DateReported'])) : date('Y-m-d'); ?></td>
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
                            <td>
                                <button type="button" class="view-detail-btn"
                                    data-reportid="<?php echo htmlspecialchars($row['reportID']); ?>"
                                    data-name="<?php echo htmlspecialchars($row['user_name'] ?? 'N/A'); ?>"
                                    data-phone="<?php echo htmlspecialchars($row['user_phone'] ?? 'N/A'); ?>"
                                    data-email="<?php echo htmlspecialchars($row['user_email'] ?? 'N/A'); ?>"
                                    data-title="<?php echo htmlspecialchars($row['title'] ?? ''); ?>"
                                    data-description="<?php echo htmlspecialchars($row['description'] ?? ''); ?>"
                                    data-location="<?php echo htmlspecialchars($row['location'] ?? ''); ?>"
                                    data-status="<?php echo htmlspecialchars($status); ?>"
                                    data-proof="<?php echo htmlspecialchars($row['proof_photo'] ?? ''); ?>">
                                    View Detail
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888; padding: 20px;">No records found in the database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="table-footer">
                <span>Showing 1 to <?php echo $total_count; ?> of <?php echo $total_count; ?> entries</span>
            </div>
        </section>
    </main>

    <!-- MODAL: Report Detail -->
    <div id="detailModal" class="modal-overlay">
        <div class="modal-box">
            <button type="button" class="modal-close" id="modalCloseBtn">&times;</button>
            <h2 id="modalReportID">Report #</h2>
            <span id="modalStatusBadge" class="status-badge">Status</span>

            <div class="modal-detail-grid">
                <div class="modal-detail-item">
                    <span class="modal-label">Reported By</span>
                    <span class="modal-value" id="modalName"></span>
                </div>
                <div class="modal-detail-item">
                    <span class="modal-label">Phone Number</span>
                    <span class="modal-value" id="modalPhone"></span>
                </div>
                <div class="modal-detail-item">
                    <span class="modal-label">Email</span>
                    <span class="modal-value" id="modalEmail"></span>
                </div>
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
            </div>

            <div id="modalProofSection" style="display:none; margin-top:16px;">
                <span class="modal-label">Proof Photo (Completed)</span>
                <img id="modalProofImage" src="" alt="Proof Photo" class="modal-proof-image">
            </div>
        </div>
    </div>

    <script>
        document.getElementById("searchInput").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            document.querySelectorAll("#reportTable tr").forEach(row => {
                
                if(row.cells.length > 1) {
                    row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
                }
            });
        });

        // MODAL LOGIC
        const modal = document.getElementById("detailModal");
        const closeBtn = document.getElementById("modalCloseBtn");

        document.querySelectorAll(".view-detail-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                document.getElementById("modalReportID").textContent = "Report #" + this.dataset.reportid;
                document.getElementById("modalName").textContent = this.dataset.name;
                document.getElementById("modalPhone").textContent = this.dataset.phone;
                document.getElementById("modalEmail").textContent = this.dataset.email;
                document.getElementById("modalTitle").textContent = this.dataset.title;
                document.getElementById("modalDescription").textContent = this.dataset.description;
                document.getElementById("modalLocation").textContent = this.dataset.location;

                const statusBadge = document.getElementById("modalStatusBadge");
                statusBadge.textContent = this.dataset.status;
                statusBadge.className = "status-badge";
                if (this.dataset.status === "Pending") statusBadge.classList.add("badge-pending");
                if (this.dataset.status === "In Progress") statusBadge.classList.add("badge-inprogress");
                if (this.dataset.status === "Completed") statusBadge.classList.add("badge-completed");

                const proofSection = document.getElementById("modalProofSection");
                const proofImage = document.getElementById("modalProofImage");
                if (this.dataset.proof && this.dataset.proof !== "") {
                    proofImage.src = this.dataset.proof;
                    proofSection.style.display = "block";
                } else {
                    proofSection.style.display = "none";
                }

                modal.classList.add("active");
            });
        });

        closeBtn.addEventListener("click", () => modal.classList.remove("active"));
        modal.addEventListener("click", (e) => {
            if (e.target === modal) modal.classList.remove("active");
        });
    </script>
</body>
</html>