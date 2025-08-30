<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';
$entity = $_GET['entity'] ?? '';

// purchases table: purchase_ID, buyer_type, buyer_ID, product_ID, quantity, price_per_kg, total_price, purchase_date
// group by month
$sql = "SELECT DATE_FORMAT(purchase_date,'%Y-%m') AS month, SUM(total_price) AS total_sales
        FROM purchase WHERE 1=1 ";
$params = [];
if ($entity) {
  $sql .= " AND buyer_type = ? ";
  $params[] = $entity;
}
$sql .= " GROUP BY month ORDER BY month ASC";

if ($stmt = $conn->prepare($sql)) {
  if ($params) $stmt->bind_param(str_repeat('s',count($params)), ...$params);
  $stmt->execute();
  $res = $stmt->get_result();
  $out = [];
  while ($r=$res->fetch_assoc()) $out[]=$r;
  echo json_encode($out);
  $stmt->close();
} else {
  echo json_encode([]);
}
$conn->close();
