<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

// either return by id or by wholesale_ID
if (!empty($_GET['id'])) {
  $stmt = $conn->prepare("SELECT wi.*, COALESCE(pp.name, mp.meat_type) AS product_name FROM wholesale_inventory wi LEFT JOIN processed_product pp ON wi.sku=pp.sku LEFT JOIN meat_product mp ON wi.sku=mp.product_ID WHERE wi.id=?");
  $stmt->bind_param("i", $_GET['id']);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc() ?: [];
  echo json_encode($row);
  $stmt->close(); $conn->close(); exit;
}

$wholesale_ID = $_GET['wholesale_ID'] ?? '';
if ($wholesale_ID) {
  $stmt = $conn->prepare("SELECT wi.*, COALESCE(pp.name, mp.meat_type) AS product_name FROM wholesale_inventory wi LEFT JOIN processed_product pp ON wi.sku=pp.sku LEFT JOIN meat_product mp ON wi.sku=mp.product_ID WHERE wi.wholesale_ID=? ORDER BY wi.last_updated DESC");
  $stmt->bind_param("s",$wholesale_ID);
} else {
  $stmt = $conn->prepare("SELECT wi.*, COALESCE(pp.name, mp.meat_type) AS product_name FROM wholesale_inventory wi LEFT JOIN processed_product pp ON wi.sku=pp.sku LEFT JOIN meat_product mp ON wi.sku=mp.product_ID ORDER BY wi.last_updated DESC");
}
$stmt->execute();
$res = $stmt->get_result(); $out=[];
while ($r=$res->fetch_assoc()) $out[]=$r;
echo json_encode($out);
$stmt->close(); $conn->close();
