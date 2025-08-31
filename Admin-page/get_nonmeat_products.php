<?php
// Admin-page/get_nonmeat_products.php
header('Content-Type: application/json; charset=utf-8');
require_once "../Project-root/db_connect.php";

$res = $conn->query("SELECT sku, name, category, avg_unit_weight_g, shelf_life_days, price_per_kg, protein_per_100g, fat_per_100g, notes, DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at FROM non_meat_product ORDER BY created_at DESC");
if (!$res) {
    echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode(['success'=>true,'data'=>$data]);
