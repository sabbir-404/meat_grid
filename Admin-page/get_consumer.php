<?php
require_once "../Project-root/db_connect.php";

header('Content-Type: application/json');

$sql = "SELECT consumer_ID, name, address FROM consumer ORDER BY consumer_ID";
$result = $conn->query($sql);

$consumers = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $consumers[] = $row;
    }
}

echo json_encode(["success" => true, "data" => $consumers]);

$conn->close();
?>