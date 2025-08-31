<?php
// Admin-page/add_nonmeat_product.php
header('Content-Type: application/json; charset=utf-8');
require_once "../Project-root/db_connect.php";

// expects JSON body
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['success'=>false,'error'=>'Invalid JSON']); exit; }
if (empty($data['sku']) || empty($data['name'])) {
    echo json_encode(['success'=>false,'error'=>'Missing sku or name']); exit;
}

$stmt = $conn->prepare("INSERT INTO non_meat_product (sku, name, category, avg_unit_weight_g, shelf_life_days, price_per_kg, protein_per_100g, fat_per_100g, notes) VALUES (?,?,?,?,?,?,?,?,?)");
if (!$stmt) { echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]); exit; }

$sku = $data['sku'];
$name = $data['name'];
$category = $data['category'] ?? null;
$avg_unit_weight_g = isset($data['avg_unit_weight_g']) && $data['avg_unit_weight_g'] !== '' ? (float)$data['avg_unit_weight_g'] : null;
$shelf_life_days = isset($data['shelf_life_days']) && $data['shelf_life_days'] !== '' ? (int)$data['shelf_life_days'] : null;
$price_per_kg = isset($data['price_per_kg']) && $data['price_per_kg'] !== '' ? (float)$data['price_per_kg'] : null;
$protein = isset($data['protein_per_100g']) && $data['protein_per_100g'] !== '' ? (float)$data['protein_per_100g'] : null;
$fat = isset($data['fat_per_100g']) && $data['fat_per_100g'] !== '' ? (float)$data['fat_per_100g'] : null;
$notes = $data['notes'] ?? null;

$stmt->bind_param("ssddiddds", $sku, $name, $category, $avg_unit_weight_g, $shelf_life_days, $price_per_kg, $protein, $fat, $notes);

// Note: because PHP's bind types must match, map nulls: use SQL NULL by binding nulls directly
// Simpler approach: use separate prepared version that accepts strings, and cast empty -> NULL
$stmt = $conn->prepare("INSERT INTO non_meat_product (sku, name, category, avg_unit_weight_g, shelf_life_days, price_per_kg, protein_per_100g, fat_per_100g, notes) VALUES (?,?,?,?,?,?,?,?,?)");
$stmt->bind_param("sssdiddds",
    $sku,
    $name,
    $category,
    $avg_unit_weight_g,
    $shelf_life_days,
    $price_per_kg,
    $protein,
    $fat,
    $notes
);

if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
