<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';
$out = [];
// union SKU list from processed_product and meat_product.product_ID for flexibility
$sql = "
  SELECT sku, name FROM processed_product
  UNION
  SELECT product_ID AS sku, meat_type AS name FROM meat_product
  ORDER BY sku
";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
$conn->close();
