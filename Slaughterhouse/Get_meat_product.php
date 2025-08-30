<?php
// Slaughterhouse/get_meat_products.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

$sql = "SELECT product_ID, meat_type, IFNULL(yield_weight,0) AS yield_weight, 
               IFNULL(processing_date,'') AS processing_date, IFNULL(storage_temperature,0) AS storage_temperature
        FROM meat_product
        ORDER BY processing_date DESC, product_ID DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) $out[] = $row;

echo json_encode($out);
$conn->close();

