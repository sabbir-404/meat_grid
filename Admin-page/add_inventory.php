<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
if (empty($data['wholesale_ID']) || empty($data['sku'])) { echo json_encode(['success'=>false,'error'=>'Missing wholesale_ID or sku']); exit; }
$quantity = isset($data['quantity']) ? (float)$data['quantity'] : 0;
$price = isset($data['price_per_unit']) && $data['price_per_unit']!=='' ? (float)$data['price_per_unit'] : null;
$stmt = $conn->prepare("INSERT INTO wholesale_inventory (wholesale_ID, sku, quantity, price_per_unit) VALUES (?,?,?,?)");
$stmt->bind_param("ssdd", $data['wholesale_ID'], $data['sku'], $quantity, $price);
if ($stmt->execute()) echo json_encode(['success'=>true,'id'=>$conn->insert_id]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
$stmt->close(); $conn->close();
