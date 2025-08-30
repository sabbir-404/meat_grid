<?php
// Slaughterhouse/delete_meat_product.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

$product_ID = trim($_POST['product_ID'] ?? '');
if ($product_ID === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing product_ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM meat_product WHERE product_ID = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]);
    exit;
}

$stmt->bind_param("s", $product_ID);

if ($stmt->execute()) {
    echo json_encode(['success'=>true,'deleted'=>$stmt->affected_rows]);
} else {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}

$stmt->close();
$conn->close();

