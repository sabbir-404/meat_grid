<?php
// Admin-page/fetch_processed_products.php
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

$sku = isset($_GET['sku']) ? $conn->real_escape_string($_GET['sku']) : null;

if ($sku) {
    $stmt = $conn->prepare(
        "SELECT pp.*, mp.product_ID AS meat_product_id, mp.yield_weight, mp.processing_date
         FROM processed_product pp
         LEFT JOIN meat_product mp ON mp.sku = pp.sku
         WHERE pp.sku = ?"
    );
    $stmt->bind_param("s", $sku);
} else {
    $stmt = $conn->prepare(
        "SELECT pp.*, mp.product_ID AS meat_product_id, mp.yield_weight, mp.processing_date
         FROM processed_product pp
         LEFT JOIN meat_product mp ON mp.sku = pp.sku
         ORDER BY pp.created_at DESC"
    );
}

if (!$stmt) {
    echo json_encode([]);
    exit;
}

$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
