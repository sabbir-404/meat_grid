<?php
session_start();
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($_SESSION['user_id'], $data['product_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$user = (int)$_SESSION['user_id'];
$pid  = $conn->real_escape_string($data['product_id']);

$stmt = $conn->prepare(
    "DELETE FROM livestock_entries 
     WHERE product_id=? AND user_id=?"
);
$stmt->bind_param("si",$pid,$user);

if ($stmt->execute() && $stmt->affected_rows>0) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Not found or no permission']);
}
