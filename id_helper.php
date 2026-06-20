<?php
// id_helper.php
// Generates the next formatted ID for a table, e.g. "U001", "U002", "A001"...
// Usage: generateNextId($conn, 'user', 'userID', 'U')
 
function generateNextId($conn, $table, $column, $prefix, $padding = 3) {
    $skip = strlen($prefix) + 1; // characters to skip when extracting the number part
 
    $sql = "SELECT $column FROM $table 
            WHERE $column LIKE '{$prefix}%' 
            ORDER BY CAST(SUBSTRING($column, $skip) AS UNSIGNED) DESC 
            LIMIT 1";
 
    $result = $conn->query($sql);
 
    if ($result && $result->num_rows > 0) {
        $lastId = $result->fetch_assoc()[$column];
        $number = (int) substr($lastId, strlen($prefix)) + 1;
    } else {
        $number = 1;
    }
 
    return $prefix . str_pad($number, $padding, '0', STR_PAD_LEFT);
}
?>