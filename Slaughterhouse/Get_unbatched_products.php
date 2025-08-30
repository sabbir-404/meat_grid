<?php
// Slaughterhouse/get_unbatched_products.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

$sql = "
  SELECT mp.product_ID, mp.meat_type, IFNULL(mp.yield_weight,0) AS yield_weight,
         IFNULL(mp.processing_date,'') AS processing_date, IFNULL(mp.storage_temperature, '') AS storage_temperature,
         IFNULL(mp.price_per_kg, NULL) AS price_per_kg
  FROM meat_product mp
  LEFT JOIN batch_quantity bq ON mp.product_ID = bq.product_ID
  WHERE bq.product_ID IS NULL
  ORDER BY mp.processing_date DESC, mp.product_ID ASC
";

if (!$stmt = $conn->prepare($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
$conn->close();

