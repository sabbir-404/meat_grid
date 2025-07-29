<?php
// admin_delete_livestock.php
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) exit(json_encode(['success'=>false,'error'=>'Invalid JSON']));

$stmt = $conn->prepare("DELETE FROM livestock_entries WHERE product_id=?");
$stmt->bind_param("s", $data['product_id']);

echo $stmt->execute()
? json_encode(['success'=>true])
: json_encode(['success'=>false,'error'=>$stmt->error]);
