<?php
// Admin-page/get_feedback.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

// Select feedback rows and try to resolve product name from either meat_product or processed_product
$sql = "
  SELECT
    f.feedback_ID,
    f.consumer_ID,
    f.product_ID,
    COALESCE(mp.meat_type, pp.name, f.product_ID) AS product_name,
    f.score,
    f.comment,
    f.submitted_date
  FROM feedback f
  LEFT JOIN meat_product mp ON f.product_ID = mp.product_ID
  LEFT JOIN processed_product pp ON f.product_ID = pp.sku
  ORDER BY f.submitted_date DESC, f.feedback_ID DESC
";

if (!$res = $conn->query($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'DB query failed: '.$conn->error]);
    exit;
}

$out = [];
while ($row = $res->fetch_assoc()) {
    // Normalise date format
    if (!empty($row['submitted_date'])) {
        $row['submitted_date'] = date('Y-m-d', strtotime($row['submitted_date']));
    }
    $out[] = $row;
}

echo json_encode($out);
$conn->close();

