<?php
require 'auth_check.php';
require 'db_connect.php';

$userID = $_SESSION['user_id'];

// pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// TOTAL REPORT (optional untuk pagination UI)
$totalStmt = $conn->prepare("SELECT COUNT(*) as total FROM report WHERE userID = ?");
$totalStmt->bind_param("i", $userID);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// MAIN QUERY (✔ FIX proof_photo ADA)
$stmt = $conn->prepare("
    SELECT 
        reportID, 
        title, 
        description, 
        location, 
        status, 
        DateReported, 
        reject_reason,
        proof_photo
    FROM report
    WHERE userID = ?
    ORDER BY DateReported DESC
    LIMIT ? OFFSET ?
");

$stmt->bind_param("iii", $userID, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Report</title>

    <style>
        table { width:100%; border-collapse: collapse; }
        th, td { padding:10px; border:1px solid #ddd; text-align:center; }

        .no-attachment { color:#999; }

        img.proof {
            width:45px;
            border-radius:6px;
            cursor:pointer;
            transition:0.2s;
        }

        img.proof:hover {
            transform: scale(1.1);
        }

        /* Modal */
        #proofModal {
            display:none;
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.8);
            justify-content:center;
            align-items:center;
            z-index:999;
        }

        #proofModal img {
            max-width:90%;
            max-height:90%;
            border-radius:10px;
        }

        .badge {
            padding:5px 10px;
            border-radius:5px;
            color:white;
        }
        .Pending { background:orange; }
        .InProgress { background:blue; }
        .Completed { background:green; }
        .Rejected { background:red; }
    </style>
</head>

<body>

<h2>My Report</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Description</th>
        <th>Location</th>
        <th>Status</th>
        <th>Date</th>
        <th>Proof</th>
        <th>Reject Reason</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['reportID'] ?></td>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= htmlspecialchars($row['location']) ?></td>

        <td>
            <span class="badge <?= str_replace(' ', '', $row['status']) ?>">
                <?= $row['status'] ?>
            </span>
        </td>

        <td><?= $row['DateReported'] ?></td>

        <!-- ✔ PROOF IMAGE -->
        <td>
            <?php if (!empty($row['proof_photo'])): ?>
                <img 
                    src="<?= htmlspecialchars($row['proof_photo']) ?>" 
                    class="proof"
                    onclick="openProofModal(this.src)"
                >
            <?php else: ?>
                <span class="no-attachment">—</span>
            <?php endif; ?>
        </td>

        <!-- Reject Reason -->
        <td>
            <?= !empty($row['reject_reason']) ? htmlspecialchars($row['reject_reason']) : '—' ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Pagination -->
<div style="margin-top:20px;">
<?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?>" style="margin:5px;">
        <?= $i ?>
    </a>
<?php endfor; ?>
</div>

<!-- IMAGE MODAL -->
<div id="proofModal">
    <img id="proofImg">
</div>

<script>
function openProofModal(src) {
    document.getElementById('proofImg').src = src;
    document.getElementById('proofModal').style.display = 'flex';
}

document.getElementById('proofModal').onclick = function () {
    this.style.display = 'none';
};
</script>

</body>
</html>