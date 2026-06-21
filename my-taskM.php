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
    
    $update_sql = "UPDATE report SET status='$new_status' WHERE reportID='$report_id' AND staffID='$staff_id'";
    if (mysqli_query($conn, $update_sql)) {
        $message = "<p style='color: #2ab5b5; font-weight:600; margin: 15px 0;'>Task #$report_id successfully updated to '$new_status'!</p>";
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
        <a href="login.php" class="nav-item">Logout</a>
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
                        ?>
                        <tr>
                            <td><input type="checkbox" class="row-checkbox"></td>
                            <td>#<?php echo $row['reportID']; ?></td>
                            <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title'] ?? 'No Title'); ?></strong></td>
                            <td>
                                <?php if ($attachment && file_exists($attachment)): ?>
                                    <?php if (in_array($ext, ['png', 'jpg', 'jpeg'])): ?>
                                        <a href="<?php echo htmlspecialchars($attachment); ?>" target="_blank">
                                            <img src="<?php echo htmlspecialchars($attachment); ?>" class="task-thumb" alt="Report photo">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($attachment); ?>" target="_blank" class="task-file-link">📄 View file</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="no-attachment">No photo</span>
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
                                <form action="my-taskM.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="update_task_status" value="1">
                                    <input type="hidden" name="report_id" value="<?php echo $row['reportID']; ?>">
                                    <select name="status_value" class="inline-select" onchange="this.form.submit()">
                                        <option value="Pending" <?php if($current_status == 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="In Progress" <?php if($current_status == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                        <option value="Completed" <?php if($current_status == 'Completed') echo 'selected'; ?>>Completed</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;color:#7a869a;padding:2rem;">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="table-footer">
                <span id="entryInfo">Showing 1 to <?php echo $total_count; ?> of <?php echo $total_count; ?> entries</span>
            </div>
        </div>
    </main>

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
    </script>
</body>
</html>