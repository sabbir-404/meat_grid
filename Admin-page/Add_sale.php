<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../Project-root/db_connect.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$buyer_ID = isset($data['buyer_ID']) ? (int)$data['buyer_ID'] : null;
$product_ID = $data['product_ID'] ?? null;
$quantity = isset($data['quantity']) ? (float)$data['quantity'] : null;
$price_per_kg = isset($data['price_per_kg']) ? (float)$data['price_per_kg'] : null;
$purchase_date = $data['purchase_date'] ?? null;

if (!$buyer_ID || !$product_ID || !$quantity || !$price_per_kg) {
    echo json_encode(['success'=>false,'message'=>'buyer_ID, product_ID, quantity and price_per_kg are required']);
    exit;
}

// check retailer exists
$chk = $conn->prepare("SELECT retailer_ID FROM retailer WHERE retailer_ID = ?");
$chk->bind_param("i",$buyer_ID); $chk->execute();
if (!$chk->get_result()->fetch_assoc()) {
    echo json_encode(['success'=>false,'message'=>'Retailer not found (create retailer first)']);
    exit;
}

// generate simple purchase_ID (client may supply but we'll create one)
$pid = uniqid('P');

// compute total price
$total = $quantity * $price_per_kg;

// insert into purchase table (fields per your SQL dump)
$stmt = $conn->prepare("INSERT INTO purchase (purchase_ID, buyer_type, buyer_ID, product_ID, quantity, price_per_kg, total_price, purchase_date)
                        VALUES (?, 'retailer', ?, ?, ?, ?, ?, ?)");
if (!$stmt) { echo json_encode(['success'=>false,'message'=>'Prepare failed: '.$conn->error]); exit; }
$stmt->bind_param("sisddds", $pid, $buyer_ID, $product_ID, $quantity, $price_per_kg, $total, $purchase_date);
if ($stmt->execute()) echo json_encode(['success'=>true,'message'=>'Sale recorded','purchase_ID'=>$pid]);
else echo json_encode(['success'=>false,'message'=>$stmt->error]);

$stmt->close();
$conn->close();

