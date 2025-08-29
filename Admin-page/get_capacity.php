<?php
// get_capacity.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

// Fetch all slaughterhouses
$sql = "SELECT slaughterhouse_ID, COALESCE(name,'') AS name, COALESCE(slaughter_rate,0) AS slaughter_rate, COALESCE(processing_capacity,0) AS processing_capacity, COALESCE(average_weight,0) AS average_weight FROM slaughterhouse ORDER BY name";
$res = $conn->query($sql);
if (!$res) {
    http_response_code(500);
    echo json_encode(['success'=>false, 'error'=>'DB error: '.$conn->error]);
    exit;
}
$out = [];
while ($row = $res->fetch_assoc()) $out[] = $row;
echo json_encode($out);
$conn->close();