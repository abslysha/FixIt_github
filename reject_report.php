<?php
require 'auth_admin.php'; // Pastikan hanya admin boleh akses
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reportID = $_POST['reportID'] ?? null;
    $reason = $_POST['reject_reason'] ?? '';

    if (!$reportID) {
        die("Report ID tidak sah.");
    }

    // Update status report kepada 'Rejected', simpan reason jika ada
    $stmt = mysqli_prepare($conn, "UPDATE report SET status = 'Rejected', reject_reason = ? WHERE reportID = ?");
    mysqli_stmt_bind_param($stmt, "si", $reason, $reportID);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: report-data.php?rejected=1");
        exit();
    } else {
        die("Gagal kemaskini status report: " . mysqli_error($conn));
    }

} else {
    header("Location: report-data.php");
    exit();
}
?>