<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
if (empty($data['id']) || empty($data['wholesale_ID']) || empty($data['sku'])) { echo json_encode(['success'=>false,'error'=>'Missing fields']); exit; }
$id = (int)$data['id'];
$quantity = isset($data['quantity']) ? (float)$data['quantity'] : 0;
$price = isset($data['price_per_unit']) && $data['price_per_unit']!=='' ? (float)$data['price_per_unit'] : null;
$stmt = $conn->prepare("UPDATE wholesale_inventory SET wholesale_ID=?, sku=?, quantity=?, price_per_unit=? WHERE id=?");
$stmt->bind_param("ssddi", $data['wholesale_ID'], $data['sku'], $quantity, $price, $id);
if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
$stmt->close(); $conn->close();
