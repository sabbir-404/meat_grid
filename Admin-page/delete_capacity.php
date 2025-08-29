<?php
// delete_capacity.php
session_start();
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

$id = $_REQUEST['slaughterhouse_ID'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing slaughterhouse_ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM slaughterhouse WHERE slaughterhouse_ID = ?");
if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>$conn->error]); exit; }
$stmt->bind_param('s', $id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'deleted'=>$stmt->affected_rows]);
} else {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}
$stmt->close();
$conn->close();

