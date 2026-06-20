<?php
require 'auth_admin.php';
require 'db_connect.php';

// Fetch stats counters from your 'report' table
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report");
$total_reports = mysqli_fetch_assoc($total_query)['total'] ?? 0;

$pending_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE status='Pending'");
$pending_reports = mysqli_fetch_assoc($pending_query)['total'] ?? 0;

$progress_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE status='In Progress'");
$progress_reports = mysqli_fetch_assoc($progress_query)['total'] ?? 0;

$completed_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM report WHERE status='Completed'");
$completed_reports = mysqli_fetch_assoc($completed_query)['total'] ?? 0;

// Calculate percentages safely to avoid division by zero
$pending_pct = $total_reports > 0 ? round(($pending_reports / $total_reports) * 100) : 0;
$progress_pct = $total_reports > 0 ? round(($progress_reports / $total_reports) * 100) : 0;
$completed_pct = $total_reports > 0 ? round(($completed_reports / $total_reports) * 100) : 0;

// Fetch top 5 recent reports from your 'report' table
$recent_reports = mysqli_query($conn, "SELECT * FROM report ORDER BY reportID DESC LIMIT 5");

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

            <a href="dashboard.html" class="nav-item">
                Dashboard
            </a>

            <a href="report-data.html" class="nav-item active">
                Report Data
            </a>

            <a href="user-management.html" class="nav-item">
                User Management
            </a>

            <a href="assign-task.html" class="nav-item">
                Assign Task
            </a>

        </nav>

        <div class="sidebar-spacer"></div>

        <a href="login.html" class="nav-item">
    Logout
        </a>

    </aside>

    <!-- Main Content -->
    <main class="main">

        <header class="topbar">

            <h1 class="topbar-title">
                REPORT DATA
            </h1>

            <div class="topbar-actions">

                <button class="icon-btn">
                    🔔
                    <span class="notif-dot"></span>
                </button>

                <div class="avatar">
                    M
                </div>

            </div>

        </header>

        <section class="table-card">

            <div class="table-controls">

                <div class="show-entries">
                    Show

                    <select id="entries">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>

                    entries
                </div>

                <div class="search-box">
                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Search report..."
                    >
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

                <tbody id="reportTable">

                </tbody>

            </table>

            <div class="table-footer">

                <span>
                    Showing 1 to 10 of 430 entries
                </span>

                <div class="pagination">

                    <button class="page-btn">
                        Previous
                    </button>

                    <button class="page-btn active">
                        1
                    </button>

                    <button class="page-btn">
                        2
                    </button>

                    <button class="page-btn">
                        3
                    </button>

                    <button class="page-btn">
                        Next
                    </button>

                </div>

            </div>

        </section>

    </main>

    <script>
        const searchInput =
            document.getElementById("searchInput");

        searchInput.addEventListener("keyup", function () {

            let filter =
                searchInput.value.toLowerCase();

            let rows =
                document.querySelectorAll("#reportTable tr");

            rows.forEach(row => {

                row.style.display =
                    row.innerText.toLowerCase()
                    .includes(filter)
                    ? ""
                    : "none";

            });

        });
    </script>

</body>
</html>