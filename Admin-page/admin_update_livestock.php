<?php
// admin_update_livestock.php
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) exit(json_encode(['success'=>false,'error'=>'Invalid JSON']));

$stmt = $conn->prepare(
"UPDATE livestock_entries SET
    user_type=?, breed=?, quantity=?, avg_weight=?
WHERE product_id=?"
);
$stmt->bind_param(
"ssdis",
$data['user_type'],
$data['breed'],
$data['quantity'],
$data['avg_weight'],
$data['product_id']
);

echo $stmt->execute()
? json_encode(['success'=>true])
: json_encode(['success'=>false,'error'=>$stmt->error]);
