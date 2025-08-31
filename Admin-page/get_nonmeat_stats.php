<?php
// Admin-page/get_nonmeat_stats.php
header('Content-Type: application/json; charset=utf-8');
require_once "../Project-root/db_connect.php";

// avg price per product
$sql = "SELECT p.sku, p.name, ROUND(AVG(h.retail_price),2) AS avg_retail_price FROM non_meat_product p LEFT JOIN non_meat_price_history h ON p.sku=h.sku GROUP BY p.sku ORDER BY p.name";
$res = $conn->query($sql);
$avg = [];
while ($r = $res->fetch_assoc()) $avg[] = $r;

echo json_encode(['success'=>true,'avg_prices'=>$avg]);
