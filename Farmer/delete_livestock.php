<?php
require_once "../Project-root/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['product_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Missing product_id']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM livestock_entries WHERE product_id = ?");
$success = $stmt->execute([$data['product_id']]);

echo json_encode(['success' => $success]);
