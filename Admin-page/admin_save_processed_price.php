<?php
// Admin-page/admin_save_processed_price.php
session_start();
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['success'=>false,'error'=>'Invalid JSON']); exit; }

if (empty($data['sku']) || empty($data['date_recorded'])) {
    echo json_encode(['success'=>false,'error'=>'Missing sku or date_recorded']);
    exit;
}

// verify product exists (optional but helpful)
$chk = $conn->prepare("SELECT sku FROM processed_product WHERE sku=?");
$chk->bind_param("s", $data['sku']);
$chk->execute();
$res = $chk->get_result();
if (!$res->fetch_assoc()) {
    echo json_encode(['success'=>false,'error'=>'Product (sku) not found']);
    exit;
}

// prepare insert
$stmt = $conn->prepare(
    "INSERT INTO processed_price_history (sku, region, date_recorded, wholesale_price, retail_price, source, notes)
     VALUES (?,?,?,?,?,?,?)"
);
if (!$stmt) { echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]); exit; }

// types: sku(s), region(s), date(s), wholesale(d), retail(d), source(s), notes(s)
$sku = $data['sku'];
$region = $data['region'] ?? null;
$date_recorded = $data['date_recorded'];
$wholesale_price = isset($data['wholesale_price']) && $data['wholesale_price'] !== '' ? (float)$data['wholesale_price'] : null;
$retail_price = isset($data['retail_price']) && $data['retail_price'] !== '' ? (float)$data['retail_price'] : null;
$source = $data['source'] ?? null;
$notes = $data['notes'] ?? null;

$stmt->bind_param("ss sddss", $sku, $region, $date_recorded, $wholesale_price, $retail_price, $source, $notes);

// Note: bind_param requires a single string for types, without spaces.
// But some PHP versions may reject null for double; to avoid issues convert to null->NULL string or 0.0
// Simpler: build types string correctly and pass values as-is:
$stmt->bind_param("sssddss", $sku, $region, $date_recorded, $wholesale_price, $retail_price, $source, $notes);

if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>'DB error: '.$stmt->error]);
