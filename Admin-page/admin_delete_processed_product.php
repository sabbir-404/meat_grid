<?php
// Admin-page/admin_delete_processed_product.php
session_start();
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['sku'])) {
    echo json_encode(['success'=>false,'error'=>'Missing sku']);
    exit;
}

$sku = $conn->real_escape_string($data['sku']);
$stmt = $conn->prepare("DELETE FROM processed_product WHERE sku=?");
$stmt->bind_param("s", $sku);
if ($stmt->execute() && $stmt->affected_rows>0) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Not found or DB error: '.$stmt->error]);
}
