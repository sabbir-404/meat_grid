<?php
// Admin-page/delete_price_record.php
header('Content-Type: application/json; charset=utf-8');
require_once "../Project-root/db_connect.php";
$id = $_POST['id'] ?? null;
if (!$id) { echo json_encode(['success'=>false,'error'=>'Missing id']); exit; }
$stmt = $conn->prepare("DELETE FROM non_meat_price_history WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
