<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
if (empty($data['wholesale_ID']) || empty($data['name'])) { echo json_encode(['success'=>false,'error'=>'Missing id or name']); exit; }
$stmt = $conn->prepare("INSERT INTO wholesale (wholesale_ID, name, address, stock_capacity) VALUES (?,?,?,?)");
$stmt->bind_param("sssi", $data['wholesale_ID'], $data['name'], $data['address'], $data['stock_capacity']);
if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
$stmt->close(); $conn->close();
