<?php
// Admin-page/get_wholesalers.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

$res = $conn->query("SELECT wholesale_ID, name, address, stock_capacity FROM wholesale ORDER BY name");
$out = [];
if ($res) {
  while ($r = $res->fetch_assoc()) $out[] = $r;
}
echo json_encode($out);
$conn->close();
