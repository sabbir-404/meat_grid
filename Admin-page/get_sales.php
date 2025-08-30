<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../Project-root/db_connect.php";

// optional filter ?retailer_ID=nn (GET param)
$retailer = isset($_GET['retailer_ID']) ? (int)$_GET['retailer_ID'] : null;

$sql = "SELECT purchase_ID, buyer_ID, product_ID, quantity, price_per_kg, total_price, purchase_date
        FROM purchase WHERE buyer_type = 'retailer' ";
if ($retailer) $sql .= " AND buyer_ID = " . $retailer;
$sql .= " ORDER BY purchase_date DESC, purchase_ID DESC LIMIT 200";

$res = $conn->query($sql);
$out = [];
if ($res) while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
$conn->close();
