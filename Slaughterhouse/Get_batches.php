<?php
// Slaughterhouse/get_batches.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

$sql = "
  SELECT 
    batch_ID,
    MIN(production_date) AS production_date,
    GROUP_CONCAT(DISTINCT mp.meat_type SEPARATOR ', ') AS meat_types,
    IFNULL(SUM(bq.batch_yield_kg),0) AS total_weight,
    IFNULL(SUM(bq.batch_price_total),0) AS total_price
  FROM batch_quantity bq
  LEFT JOIN meat_product mp ON bq.product_ID = mp.product_ID
  GROUP BY batch_ID
  ORDER BY MIN(production_date) DESC, batch_ID DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error'=>'Prepare failed: '.$conn->error]);
    exit;
}
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
$conn->close();

