<?php
// Slaughterhouse/get_batch_details.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

$batch_ID = $_GET['batch_ID'] ?? '';
if ($batch_ID === '') {
    echo json_encode(['error'=>'Missing batch_ID']);
    exit;
}

$sql = "
  SELECT bq.product_ID, bq.batch_yield_kg, bq.price_per_kg, bq.batch_price_total, mp.meat_type, mp.processing_date
  FROM batch_quantity bq
  LEFT JOIN meat_product mp ON bq.product_ID = mp.product_ID
  WHERE bq.batch_ID = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) { http_response_code(500); echo json_encode(['error'=>$conn->error]); exit; }
$stmt->bind_param("s",$batch_ID);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
$conn->close();

