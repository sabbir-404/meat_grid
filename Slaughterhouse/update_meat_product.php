<?php
// Slaughterhouse/update_meat_product.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

$product_ID = trim($_POST['product_ID'] ?? '');
$meat_type = trim($_POST['meat_type'] ?? '');
$yield_weight = $_POST['yield_weight'] ?? null;
$processing_date = $_POST['processing_date'] ?? null;
$storage_temperature = $_POST['storage_temperature'] ?? null;

if ($product_ID === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing product_ID']);
    exit;
}

$yield_weight = $yield_weight !== '' ? (float)$yield_weight : 0.0;
$storage_temperature = $storage_temperature !== '' ? (float)$storage_temperature : 0.0;

$stmt = $conn->prepare("UPDATE meat_product SET meat_type = ?, yield_weight = ?, processing_date = ?, storage_temperature = ? WHERE product_ID = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]);
    exit;
}

$stmt->bind_param("sddds", $meat_type, $yield_weight, $processing_date, $storage_temperature, $product_ID);

if ($stmt->execute()) {
    echo json_encode(['success'=>true,'affected'=>$stmt->affected_rows]);
} else {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}

$stmt->close();
$conn->close();
