<?php
// Admin-page/delete_nonmeat_product.php
header('Content-Type: application/json; charset=utf-8');
require_once "../Project-root/db_connect.php";

$sku = $_POST['sku'] ?? null;
if (!$sku) { echo json_encode(['success'=>false,'error'=>'Missing sku']); exit; }

$stmt = $conn->prepare("DELETE FROM non_meat_product WHERE sku = ?");
$stmt->bind_param("s", $sku);
if ($stmt->execute()) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
