<?php
require 'auth_maintenance.php';
require 'db_connect.php';

$staff_id = $_SESSION['user_id'] ?? '';
$tech_name = $_SESSION['name'] ?? '';
$message = "";

// Handle status updates requested by the technician inline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task_status'])) {
    $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status_value']);

    if ($new_status === 'Completed') {
        // Completed status requires a proof photo upload
        if (isset($_FILES['proof_photo']) && $_FILES['proof_photo']['error'] === UPLOAD_ERR_OK) {

            $allowedExt = ['png', 'jpg', 'jpeg'];
            $originalName = $_FILES['proof_photo']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExt)) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = uniqid('proof_', true) . '.' . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['proof_photo']['tmp_name'], $destination)) {
                    $update_sql = "UPDATE report SET status='Completed', proof_photo='" . mysqli_real_escape_string($conn, $destination) . "' WHERE reportID='$report_id' AND staffID='$staff_id'";
                    if (mysqli_query($conn, $update_sql)) {
                        $message = "<p style='color: #2ab5b5; font-weight:600; margin: 15px 0;'>Task #$report_id marked as Completed with proof photo!</p>";
                    } else {
                        $message = "<p style='color: #e53e3e; font-weight:600; margin: 15px 0;'>Database error: " . mysqli_error($conn) . "</p>";
                    }
                } else {
                    $message = "<p style='color: #e53e3e; font-weight:600; margin: 15px 0;'>Failed to upload proof photo. Please try again.</p>";
                }
            } else {
                $message = "<p style='color: #e53e3e; font-weight:600; margin: 15px 0;'>Invalid file type. Only PNG, JPG, JPEG allowed.</p>";
            }
        } else {
            $message = "<p style='color: #e53e3e; font-weight:600; margin: 15px 0;'>Please upload a proof photo to mark this task as Completed.</p>";
        }
    } else {
        // Pending / In Progress don't need a photo
        $update_sql = "UPDATE report SET status='$new_status' WHERE reportID='$report_id' AND staffID='$staff_id'";
        if (mysqli_query($conn, $update_sql)) {
            $message = "<p style='color: #2ab5b5; font-weight:600; margin: 15px 0;'>Task #$report_id successfully updated to '$new_status'!</p>";
        }
    }
}

// Fetch all assignments for this technician matching their ERD Foreign Key
$tasks_query = mysqli_query($conn, "SELECT * FROM report WHERE staffID = '$staff_id' ORDER BY reportID DESC");
$total_count = mysqli_num_rows($tasks_query);

$avatar_letter = strtoupper(substr($tech_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - My Task</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="maintanance.css">
    <style>
        .inline-select {
            padding: 5px 10px;
            border-radius: 4px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        .task-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e0e4ed;
            cursor: pointer;
        }
        .task-file-link {
            font-size: 0.85rem;
            color: #1e6fb5;
        }
        .no-attachment {
            color: #7a869a;
            font-size: 0.8rem;
        }

        /* ===== Image Popup Modal ===== */
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

        /* ===== Proof Upload Modal ===== */
        .proof-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(26, 26, 46, 0.55);
            z-index: 1001;
            justify-content: center;
            align-items: center;
        }
        .proof-modal-overlay.active {
            display: flex;
        }
        .proof-modal-box {
            background: white;
            border-radius: 14px;
            padding: 28px;
            width: 90%;
            max-width: 400px;
            position: relative;
        }
        .proof-modal-box h3 {
            font-family: 'DM Sans', sans-serif;
            font-size: 16px;
            margin-bottom: 14px;
            color: #1a1a2e;
        }
        .proof-modal-box input[type="file"] {
            width: 100%;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .proof-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .proof-btn-cancel {
            background: #e53e3e;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .proof-btn-submit {
            background: #2ab5b5;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="FixIt_Logo.png" alt="FixIt Logo" onerror="this.style.display='none';this.parentElement.innerHTML='<span style=\'color:white;font-weight:700;font-size:18px;\'>Fi</span>'">
        </div>
        <nav>
            <a href="dashboardM.php" class="nav-item">
                Dashboard
            </a>
            <a href="my-taskM.php" class="nav-item active">
                My Task
            </a>
        </nav>
        <div class="sidebar-spacer"></div>
        <a href="logout.php" class="nav-item">Logout</a>
    </aside>

    <main class="main">
        <header class="topbar">
            <h1 class="topbar-title">MY TASK</h1>
            <div class="topbar-actions">
                <div class="avatar"><?php echo $avatar_letter; ?></div>
            </div>
        </header>

        <div class="table-card">
            <?php echo $message; ?>
            
            <div class="table-controls">
                <div class="show-entries">
                    Show <select id="entriesSelect"><option value="<?php echo $total_count; ?>">All (<?php echo $total_count; ?>)</option></select> entries
                </div>
                <div class="search-box">
                    <input type="text" id="taskSearch" placeholder="Search task...">
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                        <th>Report ID</th>
                        <th>Location</th>
                        <th>Issue</th>
                        <th>Photo</th>
                        <th>Proof Photo</th>
                        <th>Status</th>
                        <th>Date Reported</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody id="taskTableBody">
                    <?php if($total_count > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($tasks_query)): 
                            $current_status = $row['status'] ?? 'Pending';
                            $attachment = $row['attachment'] ?? null;
                            $ext = $attachment ? strtolower(pathinfo($attachment, PATHINFO_EXTENSION)) : '';
                            $proof = $row['proof_photo'] ?? null;
                        ?>
                        <tr>
                            <td><input type="checkbox" class="row-checkbox"></td>
                            <td>#<?php echo $row['reportID']; ?></td>
                            <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title'] ?? 'No Title'); ?></strong></td>
                            <td>
                                <?php if ($attachment && file_exists($attachment)): ?>
                                    <?php if (in_array($ext, ['png', 'jpg', 'jpeg'])): ?>
                                        <img src="<?php echo htmlspecialchars($attachment); ?>" class="task-thumb" alt="Report photo" onclick="openPhotoModal('<?php echo htmlspecialchars($attachment); ?>')">
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($attachment); ?>" target="_blank" class="task-file-link">📄 View file</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="no-attachment">No photo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($proof && file_exists($proof)): ?>
                                    <img src="<?php echo htmlspecialchars($proof); ?>" class="task-thumb" alt="Proof photo" onclick="openPhotoModal('<?php echo htmlspecialchars($proof); ?>')">
                                <?php else: ?>
                                    <span class="no-attachment">Not uploaded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $badge_class = 'badge-pending';
                                if($current_status == 'In Progress') $badge_class = 'badge-inprogress';
                                if($current_status == 'Completed') $badge_class = 'badge-completed';
                                ?>
                                <span class="status-badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($current_status); ?>
                                </span>
                            </td>
                            <td style="color:#7a869a;"><?php echo isset($row['DateReported']) ? date('d M Y', strtotime($row['DateReported'])) : 'N/A'; ?></td>
                            <td>
                                <select name="status_value" class="inline-select status-dropdown" data-reportid="<?php echo $row['reportID']; ?>">
                                    <option value="Pending" <?php if($current_status == 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="In Progress" <?php if($current_status == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                    <option value="Completed" <?php if($current_status == 'Completed') echo 'selected'; ?>>Completed</option>
                                </select>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center;color:#7a869a;padding:2rem;">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="table-footer">
                <span id="entryInfo">Showing 1 to <?php echo $total_count; ?> of <?php echo $total_count; ?> entries</span>
            </div>
        </div>
    </main>

    <!-- Photo Popup Modal (view existing photos) -->
    <div class="photo-modal-overlay" id="photoModalOverlay" onclick="closePhotoModal(event)">
        <div class="photo-modal-content">
            <span class="photo-modal-close" onclick="closePhotoModal(event)">&times;</span>
            <img id="photoModalImg" src="" alt="Report photo full size">
        </div>
    </div>

    <!-- Proof Upload Modal (shown when marking Completed) -->
    <div class="proof-modal-overlay" id="proofModalOverlay">
        <div class="proof-modal-box">
            <h3>Upload proof photo to mark this task as Completed</h3>
            <form action="my-taskM.php" method="POST" enctype="multipart/form-data" id="proofForm">
                <input type="hidden" name="update_task_status" value="1">
                <input type="hidden" name="status_value" value="Completed">
                <input type="hidden" name="report_id" id="proofReportId" value="">
                <input type="file" name="proof_photo" accept=".png,.jpg,.jpeg" required>
                <div class="proof-modal-actions">
                    <button type="button" class="proof-btn-cancel" id="proofCancelBtn">Cancel</button>
                    <button type="submit" class="proof-btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden form used for instant Pending / In Progress updates (no photo needed) -->
    <form action="my-taskM.php" method="POST" id="quickStatusForm" style="display:none;">
        <input type="hidden" name="update_task_status" value="1">
        <input type="hidden" name="report_id" id="quickReportId" value="">
        <input type="hidden" name="status_value" id="quickStatusValue" value="">
    </form>

    <script>
        document.getElementById("taskSearch").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            document.querySelectorAll("#taskTableBody tr").forEach(row => {
                if(row.cells.length > 1) {
                    row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
                }
            });
        });

        document.getElementById("selectAll").addEventListener("change", function() {
            document.querySelectorAll(".row-checkbox").forEach(cb => cb.checked = this.checked);
        });

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

        // Handle status dropdown changes
        const proofModal = document.getElementById("proofModalOverlay");
        const proofReportIdInput = document.getElementById("proofReportId");
        const proofCancelBtn = document.getElementById("proofCancelBtn");

        document.querySelectorAll(".status-dropdown").forEach(select => {
            // Remember the previously selected value so Cancel can revert it
            select.dataset.prevValue = select.value;

            select.addEventListener("change", function() {
                const reportId = this.dataset.reportid;

                if (this.value === "Completed") {
                    // Open the proof upload modal instead of submitting directly
                    proofReportIdInput.value = reportId;
                    proofModal.classList.add("active");
                    proofModal.dataset.activeSelect = reportId;
                } else {
                    // Pending / In Progress: submit immediately, no photo required
                    document.getElementById("quickReportId").value = reportId;
                    document.getElementById("quickStatusValue").value = this.value;
                    document.getElementById("quickStatusForm").submit();
                }
            });
        });

        proofCancelBtn.addEventListener("click", function() {
            // Revert the dropdown back to its previous value
            const activeId = proofModal.dataset.activeSelect;
            document.querySelectorAll(".status-dropdown").forEach(select => {
                if (select.dataset.reportid === activeId) {
                    select.value = select.dataset.prevValue;
                }
            });
            proofModal.classList.remove("active");
        });
    </script>
</body>
</html>