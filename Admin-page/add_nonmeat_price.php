<?php
// Admin-page/add_nonmeat_price.php
header('Content-Type: application/json; charset=utf-8');
require_once "../Project-root/db_connect.php";

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['success'=>false,'error'=>'Invalid JSON']); exit; }
if (empty($data['sku']) || empty($data['date_recorded'])) { echo json_encode(['success'=>false,'error'=>'Missing sku or date_recorded']); exit; }

$sku = $data['sku'];
$date_recorded = $data['date_recorded'];
$wholesale_price = isset($data['wholesale_price']) && $data['wholesale_price'] !== '' ? (float)$data['wholesale_price'] : null;
$retail_price = isset($data['retail_price']) && $data['retail_price'] !== '' ? (float)$data['retail_price'] : null;
$source = $data['source'] ?? null;
$notes = $data['notes'] ?? null;

$stmt = $conn->prepare("INSERT INTO non_meat_price_history (sku, date_recorded, wholesale_price, retail_price, source, notes) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ssd dds", $sku, $date_recorded, $wholesale_price, $retail_price, $source, $notes);
// Because of spaces in type string above, safer to call the statement again:
$stmt = $conn->prepare("INSERT INTO non_meat_price_history (sku, date_recorded, wholesale_price, retail_price, source, notes) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ssddss", $sku, $date_recorded, $wholesale_price, $retail_price, $source, $notes);

if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
