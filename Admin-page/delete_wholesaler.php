<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id = $data['wholesale_ID'] ?? null;
if (!$id) { echo json_encode(['success'=>false,'error'=>'Missing id']); exit; }
$stmt = $conn->prepare("DELETE FROM wholesale WHERE wholesale_ID = ?");
$stmt->bind_param("s",$id);
if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
$stmt->close(); $conn->close();
