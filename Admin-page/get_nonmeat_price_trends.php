<?php
// Admin-page/get_nonmeat_price_trends.php
header('Content-Type: application/json; charset=utf-8');
require_once "../Project-root/db_connect.php";

$sku = $_GET['sku'] ?? null;
if ($sku) {
    $stmt = $conn->prepare("SELECT id, sku, DATE_FORMAT(date_recorded, '%Y-%m-%d') AS date_recorded, wholesale_price, retail_price, source, notes FROM non_meat_price_history WHERE sku = ? ORDER BY date_recorded ASC");
    $stmt->bind_param("s", $sku);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'data'=>$rows]);
    exit;
}

// if no sku passed, return aggregated latest prices
$res = $conn->query("SELECT p.sku, p.name, ROUND(AVG(h.retail_price),2) AS avg_retail FROM non_meat_product p LEFT JOIN non_meat_price_history h ON p.sku=h.sku GROUP BY p.sku ORDER BY p.name");
$data = [];
if ($res) while ($r = $res->fetch_assoc()) $data[] = $r;
echo json_encode(['success'=>true,'data'=>$data]);
