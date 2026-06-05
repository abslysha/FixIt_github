<?php
$filename = "detail.txt";
if (file_exists($filename)) {
    $file = fopen($filename, "r");
    while (($line = fgets($file)) !== false) {
        $data = explode("\t",trim($line));
        echo "<tr>";
        foreach ($data as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    fclose($file);
} else {
    echo "<tr><td colspan='5'>No data available</td></tr>";
}
?>