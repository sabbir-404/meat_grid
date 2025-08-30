<?php
// Slaughterhouse/get_meat_summary.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

$sql = "SELECT meat_type, COUNT(*) AS total_count, IFNULL(SUM(yield_weight),0) AS total_weight
        FROM meat_product
        GROUP BY meat_type
        ORDER BY total_weight DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error'=>'Prepare failed: '.$conn->error]);
    exit;
}

$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) $out[] = $row;

echo json_encode($out);
$conn->close();

