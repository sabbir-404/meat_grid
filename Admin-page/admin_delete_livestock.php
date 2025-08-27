<?php
// admin_delete_livestock.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once _DIR_ . '/../Project-root/db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing product_id']);
    exit;
}
$product_id = $conn->real_escape_string($input['product_id']);

$stmt = $conn->prepare("DELETE FROM livestock_entries WHERE product_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}
$stmt->bind_param("s", $product_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();